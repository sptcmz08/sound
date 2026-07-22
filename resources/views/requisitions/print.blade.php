@php
    $regularFont = 'file:///'.str_replace('\\', '/', resource_path('fonts/IBMPlexSansThai-Regular.ttf'));
    $boldFont = 'file:///'.str_replace('\\', '/', resource_path('fonts/IBMPlexSansThai-Bold.ttf'));
@endphp
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>ใบเบิกพัสดุ {{ $requisition->request_no }}</title>
    <style>
        @font-face { font-family: 'Plex Thai PDF'; font-style: normal; font-weight: 400; src: url('{{ $regularFont }}') format('truetype'); }
        @font-face { font-family: 'Plex Thai PDF'; font-style: normal; font-weight: 700; src: url('{{ $boldFont }}') format('truetype'); }
        * { box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        body { margin: 0; background: #dfe4ea; color: #172033; font: 15px/1.48 'Plex Thai PDF', Tahoma, sans-serif; }
        .toolbar { position: sticky; top: 0; z-index: 10; display: flex; justify-content: center; gap: 10px; padding: 12px; background: #0f172a; }
        .toolbar button { border: 0; border-radius: 10px; padding: 10px 20px; background: #2563eb; color: #fff; font: inherit; font-weight: 700; cursor: pointer; }
        .toolbar button.secondary { background: #334155; }
        .paper { position: relative; width: 210mm; min-height: 297mm; margin: 18px auto; padding: 14mm 16mm 13mm; overflow: hidden; background: #fff; box-shadow: 0 12px 35px rgb(15 23 42 / .2); }
        .top-accent { position: absolute; inset: 0 0 auto; height: 7px; background: #1d4ed8; }
        .header { display: table; width: 100%; padding-bottom: 17px; border-bottom: 2px solid #1e3a5f; }
        .brand, .document-meta { display: table-cell; vertical-align: middle; }
        .brand-mark { display: inline-block; width: 46px; height: 46px; margin-right: 12px; border-radius: 10px; background: #1d4ed8; color: #fff; font-size: 25px; font-weight: 700; line-height: 46px; text-align: center; vertical-align: middle; }
        .brand-copy { display: inline-block; vertical-align: middle; }
        .brand-name { display: block; font-size: 19px; line-height: 1.2; font-weight: 700; color: #0f172a; }
        .brand-subtitle { display: block; margin-top: 1px; color: #64748b; font-size: 10px; letter-spacing: .35px; }
        .document-meta { width: 44%; text-align: right; }
        .document-title { margin: 0; color: #0f172a; font-size: 28px; line-height: 1.18; font-weight: 700; }
        .document-number { margin-top: 5px; color: #1d4ed8; font-size: 14px; font-weight: 700; letter-spacing: .25px; }
        .section-title { margin: 20px 0 9px; color: #1e3a5f; font-size: 14px; font-weight: 700; letter-spacing: .2px; }
        .info-grid { width: 100%; border: 1px solid #cbd5e1; border-radius: 9px; border-collapse: separate; border-spacing: 0; overflow: hidden; }
        .info-grid td { width: 50%; padding: 10px 13px; border: 0; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        .info-grid td:nth-child(2) { border-left: 1px solid #e2e8f0; }
        .info-grid tr:last-child td { border-bottom: 0; }
        .info-label { display: block; margin-bottom: 2px; color: #64748b; font-size: 11px; font-weight: 700; }
        .info-value { display: block; color: #0f172a; font-size: 15px; font-weight: 700; }
        .purpose { margin-top: 9px; padding: 10px 13px; border: 1px solid #cbd5e1; border-left: 4px solid #2563eb; border-radius: 7px; background: #f8fafc; }
        .purpose .info-label { display: inline; margin-right: 8px; }
        .purpose .info-value { display: inline; }
        .result { margin-top: 9px; padding: 10px 13px; border: 1px solid #c4b5fd; border-radius: 7px; background: #f5f3ff; color: #4c1d95; }
        .items-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .items-table th, .items-table td { border: 1px solid #94a3b8; padding: 8px 7px; vertical-align: middle; }
        .items-table th { height: 37px; background: #1e3a5f; color: #fff; text-align: center; font-size: 13px; font-weight: 700; }
        .items-table td { height: 38px; color: #1e293b; font-size: 14px; }
        .items-table tbody tr:nth-child(even) td { background: #f8fafc; }
        .center { text-align: center; }
        .right { text-align: right; }
        .approval-admin { margin-top: 34px; border: 1px solid #a7f3d0; border-radius: 10px; background: #ecfdf5; padding: 14px 16px; }
        .approval-table { width: 100%; border-collapse: collapse; }
        .approval-table td { border: 0; vertical-align: middle; }
        .approval-icon { width: 50px; }
        .approval-check { display: block; width: 38px; height: 38px; border-radius: 50%; background: #059669; color: #fff; font-size: 23px; font-weight: 700; line-height: 38px; text-align: center; }
        .approval-title { color: #065f46; font-size: 16px; font-weight: 700; }
        .approval-detail { margin-top: 1px; color: #047857; font-size: 13px; }
        .approval-note { width: 33%; color: #475569; font-size: 12px; text-align: right; }
        .signatures { width: 100%; margin-top: 30px; border-collapse: collapse; text-align: center; }
        .signatures td { width: 33.33%; padding: 0 16px; border: 0; vertical-align: bottom; }
        .sign-space { height: 60px; }
        .sign-space img { max-width: 170px; max-height: 56px; object-fit: contain; }
        .sign-line { border-top: 1px solid #334155; padding-top: 7px; font-weight: 700; }
        .sign-name { min-height: 23px; color: #334155; }
        .sign-date { margin-top: 2px; color: #64748b; font-size: 12px; }
        .dept-section { margin-top: 28px; border: 1px solid #93c5fd; border-radius: 10px; background: #eff6ff; padding: 14px 16px; }
        .dept-title { color: #1e40af; font-size: 14px; font-weight: 700; margin-bottom: 8px; }
        .dept-grid { display: table; width: 100%; }
        .dept-cell { display: table-cell; width: 50%; vertical-align: top; padding: 6px 8px; }
        .dept-line { border-bottom: 1px dotted #64748b; padding-bottom: 2px; margin-bottom: 4px; }
        .dept-label { color: #64748b; font-size: 11px; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .paper { width: auto; min-height: 271mm; margin: 0; padding: 0; box-shadow: none; }
            .approval-pinned, .signatures-pinned { position: absolute; right: 0; bottom: 0; left: 0; }
            @page { size: A4 portrait; margin: 13mm 15mm; }
        }
    </style>
</head>
<body>
    @if(empty($pdfMode))
    <div class="toolbar"><button class="secondary" type="button" onclick="window.close()">← กลับ</button><button type="button" onclick="window.print()">พิมพ์ / บันทึกเป็น PDF</button></div>
    @endif

    <main class="paper">
        <div class="top-accent"></div>
        <header class="header">
            <div class="brand"><span class="brand-mark">W</span><span class="brand-copy"><span class="brand-name">WIP Stock</span><span class="brand-subtitle">INVENTORY MANAGEMENT</span></span></div>
            <div class="document-meta"><h1 class="document-title">ใบเบิกพัสดุ</h1><div class="document-number">เลขที่ {{ $requisition->request_no }}</div></div>
        </header>

        <h2 class="section-title">ข้อมูลการเบิก</h2>
        <table class="info-grid">
            <tr><td><span class="info-label">วันที่เบิก</span><span class="info-value">{{ $requisition->requested_at->format('d/m/Y H:i') }} น.</span></td><td><span class="info-label">ชื่อพนักงานผู้เบิก</span><span class="info-value">{{ $requisition->requester->name }}</span></td></tr>
            <tr><td><span class="info-label">ประเภทการเบิก</span><span class="info-value">{{ $requisition->request_type->label() }}</span></td><td><span class="info-label">คลังสินค้า</span><span class="info-value">{{ $requisition->warehouse->code }} — {{ $requisition->warehouse->name }}</span></td></tr>
        </table>
        <div class="purpose"><span class="info-label">วัตถุประสงค์</span><span class="info-value">{{ $requisition->purpose }}</span></div>

        @if($requisition->targetProduct)
        <div class="result"><strong>ผลลัพธ์ที่เพิ่มเข้าสต็อก:</strong> {{ $requisition->targetProduct->code }} — {{ $requisition->targetProduct->name }} จำนวน <strong>{{ \App\Support\Quantity::format($requisition->target_quantity) }} {{ $requisition->targetProduct->unit->name }}</strong></div>
        @endif

        <h2 class="section-title">รายการ{{ $requisition->request_type->isBuild() ? 'ส่วนประกอบที่ใช้ผลิต' : 'พัสดุที่ขอเบิก' }}</h2>
        <table class="items-table">
            <thead><tr><th style="width:8%">ลำดับ</th><th style="width:18%">รหัส</th><th>รายการ</th><th style="width:14%">จำนวน</th><th style="width:13%">หน่วย</th><th style="width:18%">หมายเหตุ</th></tr></thead>
            <tbody>
                @foreach($requisition->items as $index => $item)
                <tr><td class="center">{{ $index + 1 }}</td><td>{{ $item->product->code }}</td><td>{{ $item->product->name }}</td><td class="right"><strong>{{ \App\Support\Quantity::format($item->quantity) }}</strong></td><td class="center">{{ $item->product->unit->name }}</td><td>{{ $item->note ?: '' }}</td></tr>
                @endforeach
                @for($row = $requisition->items->count(); $row < max(8, $requisition->items->count()); $row++)
                <tr><td class="center">{{ $row + 1 }}</td><td>&nbsp;</td><td></td><td></td><td></td><td></td></tr>
                @endfor
            </tbody>
        </table>

        <section class="approval-admin {{$requisition->items->count() <= 6 ? 'approval-pinned' : ''}}">
            <table class="approval-table"><tr><td class="approval-icon"><span class="approval-check">✓</span></td><td><div class="approval-title">อนุมัติใบเบิกและตัดสต็อกเรียบร้อยแล้ว</div><div class="approval-detail">อนุมัติโดย {{ $requisition->approver->name }} · {{ $requisition->approved_at->format('d/m/Y H:i') }} น.</div></td><td class="approval-note">สถานะเอกสาร<br><strong>อนุมัติแล้ว</strong></td></tr></table>
        </section>
    </main>
</body>
</html>
