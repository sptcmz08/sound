<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductSpreadsheetService
{
    public const HEADERS = ['code', 'name', 'product_type', 'unit_code', 'barcode', 'minimum_stock', 'location_text', 'note'];

    public function import(string $path, User $user): int
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, false, false, true);
        $spreadsheet->disconnectWorksheets();

        if (count($rows) < 2) {
            throw ValidationException::withMessages(['file' => 'ไฟล์ต้องมีหัวตารางและข้อมูลอย่างน้อย 1 รายการ']);
        }
        if (count($rows) > 5001) {
            throw ValidationException::withMessages(['file' => 'นำเข้าได้สูงสุด 5,000 รายการต่อไฟล์']);
        }

        $firstRow = array_shift($rows);
        $header = [];
        foreach ($firstRow as $column => $value) {
            $name = strtolower(trim((string) $value));
            $name = ltrim($name, "\xEF\xBB\xBF");
            if ($name !== '') {
                $header[$column] = $name;
            }
        }
        $missing = array_diff(['code', 'name', 'product_type', 'unit_code'], $header);
        if ($missing) {
            throw ValidationException::withMessages(['file' => 'หัวตารางขาด: '.implode(', ', $missing)]);
        }

        return DB::transaction(function () use ($rows, $header, $user) {
            $count = 0;
            $seenCodes = [];
            $seenBarcodes = [];
            foreach ($rows as $offset => $row) {
                $line = $offset + 2;
                $data = [];
                foreach ($header as $column => $name) {
                    $data[$name] = trim((string) ($row[$column] ?? ''));
                }
                if (collect($data)->every(fn ($value) => $value === '')) {
                    continue;
                }

                $code = $data['code'] ?? '';
                $name = $data['name'] ?? '';
                $type = strtoupper($data['product_type'] ?? '');
                $unitCode = strtoupper($data['unit_code'] ?? '');
                $barcode = blank($data['barcode'] ?? null) ? null : $data['barcode'];
                $minimum = blank($data['minimum_stock'] ?? null) ? '0' : $data['minimum_stock'];

                if ($code === '' || $name === '') {
                    $this->fail($line, 'code และ name ต้องไม่ว่าง');
                }
                if (mb_strlen($code) > 100 || mb_strlen($name) > 255 || ($barcode && mb_strlen($barcode) > 100)) {
                    $this->fail($line, 'code/barcode ยาวได้ไม่เกิน 100 ตัวอักษร และ name ไม่เกิน 255 ตัวอักษร');
                }
                if (mb_strlen($data['location_text'] ?? '') > 255 || mb_strlen($data['note'] ?? '') > 2000) {
                    $this->fail($line, 'location_text หรือ note ยาวเกินกำหนด');
                }
                if (isset($seenCodes[$code])) {
                    $this->fail($line, "รหัสสินค้า {$code} ซ้ำในไฟล์");
                }
                $seenCodes[$code] = true;
                if (! in_array($type, ['PART', 'FG'], true)) {
                    $this->fail($line, 'product_type ต้องเป็น PART หรือ FG');
                }
                $unit = Unit::whereRaw('UPPER(code) = ?', [$unitCode])->where('is_active', true)->first();
                if (! $unit) {
                    $this->fail($line, "ไม่พบหน่วยนับ {$unitCode}");
                }
                if (! is_numeric($minimum) || bccomp((string) $minimum, '0', 4) < 0) {
                    $this->fail($line, 'minimum_stock ต้องเป็นตัวเลขตั้งแต่ 0 ขึ้นไป');
                }
                if ($barcode) {
                    if (isset($seenBarcodes[$barcode])) {
                        $this->fail($line, "Barcode {$barcode} ซ้ำในไฟล์");
                    }
                    $seenBarcodes[$barcode] = true;
                    if (Product::withTrashed()->where('barcode', $barcode)->where('code', '!=', $code)->exists()) {
                        $this->fail($line, "Barcode {$barcode} ถูกใช้งานแล้ว");
                    }
                }

                $product = Product::withTrashed()->firstOrNew(['code' => $code]);
                if (! $product->exists) {
                    $product->created_by = $user->id;
                }
                $product->fill([
                    'name' => $name,
                    'barcode' => $barcode,
                    'product_type' => $type,
                    'unit_id' => $unit->id,
                    'minimum_stock' => $minimum,
                    'location_text' => blank($data['location_text'] ?? null) ? null : $data['location_text'],
                    'note' => blank($data['note'] ?? null) ? null : $data['note'],
                    'is_active' => true,
                    'updated_by' => $user->id,
                    'deleted_at' => null,
                ])->save();
                $count++;
            }

            if ($count === 0) {
                throw ValidationException::withMessages(['file' => 'ไม่พบข้อมูลสินค้าที่นำเข้าได้']);
            }

            return $count;
        });
    }

    public function writeBalanceWorkbook(iterable $balances): string
    {
        $balances = collect($balances)->values();
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Stock Balances');
        $headers = ['รหัส', 'Barcode', 'ชื่อสินค้า', 'ประเภท', 'คลัง', 'ยอดคงเหลือ', 'หน่วย', 'จำนวนขั้นต่ำ', 'ตำแหน่ง', 'สถานะ'];
        $sheet->mergeCells('A1:J1')->setCellValue('A1', 'รายงานสต็อกคงเหลือ');
        $sheet->mergeCells('A2:J2')->setCellValue('A2', 'Simple Stock • จัดทำเมื่อ '.now()->format('d/m/Y H:i').' น.');

        $summary = [
            ['A4:C4', 'A5:C5', 'จำนวนรายการ', $balances->count(), 'DBEAFE', '1D4ED8'],
            ['D4:F4', 'D5:F5', 'ยอดคงเหลือรวม', (float) $balances->sum(fn ($balance) => (float) $balance->quantity), 'D1FAE5', '047857'],
            ['G4:H4', 'G5:H5', 'หมดสต็อก', $balances->filter(fn ($balance) => (float) $balance->quantity === 0.0)->count(), 'FFE4E6', 'BE123C'],
            ['I4:J4', 'I5:J5', 'ใกล้หมด', $balances->filter(fn ($balance) => (float) $balance->quantity > 0 && (float) $balance->quantity <= (float) $balance->product->minimum_stock)->count(), 'FEF3C7', 'B45309'],
        ];
        foreach ($summary as [$labelRange, $valueRange, $label, $value, $fill, $color]) {
            $sheet->mergeCells($labelRange)->setCellValue(explode(':', $labelRange)[0], $label);
            $sheet->mergeCells($valueRange)->setCellValue(explode(':', $valueRange)[0], $value);
            $sheet->getStyle($labelRange)->applyFromArray([
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => $color]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fill]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getStyle($valueRange)->applyFromArray([
                'font' => ['bold' => true, 'size' => 18, 'color' => ['rgb' => $color]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fill]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getStyle($valueRange)->getNumberFormat()->setFormatCode($this->quantityNumberFormat($value));
        }

        $sheet->fromArray($headers, null, 'A7');
        $row = 8;
        foreach ($balances as $balance) {
            $status = ! $balance->product->is_active ? 'ไม่ใช้งาน' : ((float) $balance->quantity === 0.0 ? 'หมดสต๊อก' : ((float) $balance->quantity <= (float) $balance->product->minimum_stock ? 'ใกล้หมด' : 'ปกติ'));
            $textValues = [
                'A' => $balance->product->code, 'B' => $balance->product->barcode, 'C' => $balance->product->name,
                'D' => $balance->product->product_type->value, 'E' => $balance->warehouse->name,
                'G' => $balance->product->unit->name, 'I' => $balance->product->location_text, 'J' => $status,
            ];
            foreach ($textValues as $column => $value) {
                $sheet->setCellValueExplicit("{$column}{$row}", (string) $value, DataType::TYPE_STRING);
            }
            $sheet->setCellValue("F{$row}", (float) $balance->quantity);
            $sheet->setCellValue("H{$row}", (float) $balance->product->minimum_stock);
            $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode($this->quantityNumberFormat($balance->quantity));
            $sheet->getStyle("H{$row}")->getNumberFormat()->setFormatCode($this->quantityNumberFormat($balance->product->minimum_stock));
            $sheet->getStyle("A{$row}:J{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($row % 2 === 0 ? 'FFFFFF' : 'F8FAFC');
            $statusStyle = match ($status) {
                'ปกติ' => ['DCFCE7', '166534'],
                'ใกล้หมด' => ['FEF3C7', '92400E'],
                'หมดสต๊อก' => ['FFE4E6', '9F1239'],
                default => ['E2E8F0', '475569'],
            };
            $sheet->getStyle("J{$row}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => $statusStyle[1]]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $statusStyle[0]]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $row++;
        }
        $lastRow = $row - 1;
        if ($balances->isEmpty()) {
            $sheet->mergeCells('A8:J8')->setCellValue('A8', 'ไม่พบข้อมูลสต็อกตามเงื่อนไขที่เลือก');
            $sheet->getStyle('A8:J8')->applyFromArray([
                'font' => ['italic' => true, 'color' => ['rgb' => '64748B']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
            ]);
            $lastRow = 8;
        }
        $this->formatBalanceSheet($sheet, $lastRow, ! $balances->isEmpty());

        return $this->save($spreadsheet, 'stock-balances');
    }

    private function formatBalanceSheet(Worksheet $sheet, int $lastRow, bool $hasData): void
    {
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Leelawadee UI')->setSize(11)->getColor()->setRGB('334155');
        $sheet->setShowGridlines(false);
        $sheet->freezePane('A8');
        $sheet->setAutoFilter($hasData ? "A7:J{$lastRow}" : 'A7:J7');
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 22, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F172A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle('A2:J2')->applyFromArray([
            'font' => ['size' => 10, 'color' => ['rgb' => 'BFDBFE']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F172A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle('A7:J7')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1D4ED8']]],
        ]);
        $sheet->getStyle("A7:J{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');
        if ($hasData) {
            $sheet->getStyle("F8:H{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("A8:J{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        }
        $sheet->getRowDimension(1)->setRowHeight(38);
        $sheet->getRowDimension(2)->setRowHeight(23);
        $sheet->getRowDimension(3)->setRowHeight(10);
        $sheet->getRowDimension(4)->setRowHeight(22);
        $sheet->getRowDimension(5)->setRowHeight(32);
        $sheet->getRowDimension(6)->setRowHeight(10);
        $sheet->getRowDimension(7)->setRowHeight(30);
        for ($row = 8; $row <= $lastRow; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(25);
        }
        foreach (['A' => 16, 'B' => 21, 'C' => 34, 'D' => 13, 'E' => 24, 'F' => 18, 'G' => 14, 'H' => 18, 'I' => 20, 'J' => 16] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
        $sheet->getPageSetup()->setOrientation('landscape')->setFitToWidth(1)->setFitToHeight(0);
        $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 7);
        $sheet->getPageMargins()->setTop(0.4)->setRight(0.35)->setBottom(0.4)->setLeft(0.35);
        $sheet->getHeaderFooter()->setOddFooter('&LSimple Stock&Cหน้า &P / &N&R&D &T');
        $sheet->getPageSetup()->setPrintArea("A1:J{$lastRow}");
        $sheet->setSelectedCell('A1');
    }

    private function quantityNumberFormat(mixed $value): string
    {
        $number = (float) $value;

        return floor($number) === $number ? '#,##0' : '#,##0.####';
    }

    public function writeImportTemplate(): string
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Products');
        $sheet->fromArray(self::HEADERS, null, 'A1');
        $sheet->fromArray(['PART-100', 'สินค้าตัวอย่าง', 'PART', 'PCS', '885000000001', 5, 'A-01', 'ลบแถวตัวอย่างก่อนใช้งาน'], null, 'A2');
        $this->formatSheet($sheet, 2, count(self::HEADERS));
        $validation = new DataValidation;
        $validation->setType(DataValidation::TYPE_LIST)->setErrorStyle(DataValidation::STYLE_STOP)->setAllowBlank(false)->setShowErrorMessage(true)->setErrorTitle('ประเภทไม่ถูกต้อง')->setError('เลือก PART หรือ FG')->setFormula1('"PART,FG"');
        for ($row = 2; $row <= 5001; $row++) {
            $sheet->getCell("C{$row}")->setDataValidation(clone $validation);
        }
        $guide = $spreadsheet->createSheet();
        $guide->setTitle('วิธีใช้');
        $guide->fromArray([
            ['คู่มือนำเข้าสินค้า'],
            ['1. ห้ามเปลี่ยนชื่อหัวตารางแถวที่ 1'],
            ['2. product_type ใช้ PART หรือ FG เท่านั้น'],
            ['3. unit_code ต้องมีอยู่ในหน้าตั้งค่าของระบบ'],
            ['4. code และ barcode ห้ามซ้ำ'],
            ['5. รองรับสูงสุด 5,000 รายการต่อไฟล์'],
        ], null, 'A1');
        $guide->getColumnDimension('A')->setWidth(65);
        $guide->getStyle('A1')->getFont()->setBold(true)->setSize(16);

        return $this->save($spreadsheet, 'product-import-template');
    }

    private function formatSheet(Worksheet $sheet, int $lastRow, int $lastColumn): void
    {
        $lastColumnLetter = Coordinate::stringFromColumnIndex($lastColumn);
        $sheet->freezePane('A2');
        $sheet->setAutoFilter("A1:{$lastColumnLetter}{$lastRow}");
        $sheet->getStyle("A1:{$lastColumnLetter}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0D6EFD']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle("A1:{$lastColumnLetter}{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D9E2F3');
        foreach (range(1, $lastColumn) as $column) {
            $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
        }
    }

    private function save(Spreadsheet $spreadsheet, string $prefix): string
    {
        $directory = storage_path('app/tmp');
        File::ensureDirectoryExists($directory);
        $path = $directory.'/'.$prefix.'-'.now()->format('Ymd-His-u').'.xlsx';
        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($path);
        $spreadsheet->disconnectWorksheets();

        return $path;
    }

    public function escapeCsvCell(mixed $value): mixed
    {
        if (is_string($value) && preg_match('/^[=+\-@]/u', $value)) {
            return "'{$value}";
        }

        return $value;
    }

    private function fail(int $line, string $message): never
    {
        throw ValidationException::withMessages(['file' => "แถว {$line}: {$message}"]);
    }
}
