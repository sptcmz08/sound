<?php

namespace App\Http\Controllers;

use App\Enums\ProductType;
use App\Enums\RequisitionStatus;
use App\Enums\RequisitionType;
use App\Models\Product;
use App\Models\Requisition;
use App\Models\Unit;
use App\Models\UserSignature;
use App\Models\Warehouse;
use App\Services\AuditLogService;
use App\Services\RequisitionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RequisitionController extends Controller
{
    public function index(Request $request)
    {
        $query = Requisition::with(['requester', 'targetProduct.unit', 'warehouse', 'items.product.unit', 'approver', 'rejecter'])
            ->withCount('items')->latest();
        if (! $request->user()->isAdmin()) {
            $query->where('requested_by', $request->user()->id);
        }

        $statusCounts = [
            'pending' => (clone $query)->where('status', RequisitionStatus::PENDING)->count(),
            'approved' => (clone $query)->where('status', RequisitionStatus::APPROVED)->count(),
            'rejected' => (clone $query)->where('status', RequisitionStatus::REJECTED)->count(),
        ];

        $status = strtoupper((string) $request->query('status'));
        if (in_array($status, array_column(RequisitionStatus::cases(), 'value'), true)) {
            $query->where('status', $status);
        }

        return view('requisitions.index', ['rows' => $query->paginate(30), 'statusCounts' => $statusCounts]);
    }

    public function approvals()
    {
        return view('requisitions.approvals', ['rows' => Requisition::with(['requester', 'targetProduct', 'warehouse', 'items.product'])->orderByRaw("CASE WHEN status = 'PENDING' THEN 0 ELSE 1 END")->latest()->paginate(40), 'pendingCount' => Requisition::where('status', RequisitionStatus::PENDING)->count()]);
    }

    public function withdraw(Request $request)
    {
        $types = [RequisitionType::ISSUE_PART, RequisitionType::ISSUE_SUPPLY, RequisitionType::ISSUE_WIP, RequisitionType::ISSUE_FG];

        return $this->requisitionForm($request, 'withdraw', $types);
    }

    public function production(Request $request)
    {
        return $this->requisitionForm($request, 'production', [RequisitionType::BUILD_WIP, RequisitionType::BUILD_FG]);
    }

    public function createWip()
    {
        $unit = Unit::where('is_active', true)->orderBy('id')->first();
        if (! $unit) {
            return redirect()->route('settings')->withErrors(['unit' => 'กรุณาเพิ่มหน่วยนับก่อนผลิต WIP']);
        }

        return view('requisitions.create-wip', [
            'parts' => Product::with(['unit', 'balances'])->withSum('balances', 'quantity')
                ->where('product_type', ProductType::PART)->where('is_active', true)->orderBy('code')->get(),
            'savedWips' => Product::with('components')->where('product_type', ProductType::WIP)
                ->where('is_active', true)->whereHas('components')->orderBy('name')->get(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('code')->get(),
            'unit' => $unit,
        ]);
    }

    public function storeWip(Request $request, RequisitionService $service, AuditLogService $audit)
    {
        $data = $request->validate([
            'existing_wip_id' => ['nullable', 'exists:products,id'],
            'wip_name' => ['nullable', 'required_without:existing_wip_id', 'string', 'max:255'],
            'wip_image' => ['nullable', 'image', 'max:2048'],
            'output_quantity' => ['required', 'decimal:0,4', 'gt:0'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'components' => ['required', 'array', 'min:1'],
            'components.*.product_id' => ['required', 'distinct', 'exists:products,id'],
            'components.*.quantity' => ['required', 'decimal:0,4', 'gt:0'],
        ]);

        $requisition = DB::transaction(function () use ($data, $request, $service, $audit) {
            $parts = Product::whereIn('id', collect($data['components'])->pluck('product_id'))->get()->keyBy('id');
            foreach ($data['components'] as $line) {
                $part = $parts->get((int) $line['product_id']);
                if (! $part || ! $part->is_active || $part->product_type !== ProductType::PART) {
                    throw ValidationException::withMessages(['components' => 'ผลิต WIP ได้จาก PART ที่เปิดใช้งานเท่านั้น']);
                }
            }

            $product = null;
            $oldProduct = null;
            if (filled($data['existing_wip_id'] ?? null)) {
                $product = Product::with('components')->lockForUpdate()->findOrFail($data['existing_wip_id']);
                if (! $product->is_active || $product->product_type !== ProductType::WIP) {
                    throw ValidationException::withMessages(['existing_wip_id' => 'WIP ที่เลือกไม่พร้อมใช้งาน']);
                }
                $oldProduct = $product->toArray();
                $product->update(['updated_by' => $request->user()->id]);
            } else {
                $unit = Unit::where('is_active', true)->orderBy('id')->firstOrFail();
                $product = Product::create([
                    'code' => 'TMP-'.Str::uuid(),
                    'name' => $data['wip_name'],
                    'product_type' => ProductType::WIP,
                    'unit_id' => $unit->id,
                    'minimum_stock' => 0,
                    'is_active' => true,
                    'created_by' => $request->user()->id,
                    'updated_by' => $request->user()->id,
                ]);
                $product->update(['code' => 'WIP-'.now()->format('ymd').'-'.str_pad((string) $product->id, 5, '0', STR_PAD_LEFT)]);
            }
            if ($request->hasFile('wip_image')) {
                if ($product->image_path) {
                    Storage::disk('public')->delete($product->image_path);
                }
                $product->update(['image_path' => $request->file('wip_image')->store('products', 'public')]);
            }
            $product->components()->sync(collect($data['components'])->mapWithKeys(fn ($line) => [
                (int) $line['product_id'] => ['quantity' => $line['quantity']],
            ])->all());
            $audit->record($request->user(), $oldProduct ? 'UPDATE' : 'CREATE', 'product', $product->id, $oldProduct, $product->load('components')->toArray());

            $requisition = $service->create([
                'request_type' => RequisitionType::BUILD_WIP->value,
                'warehouse_id' => $data['warehouse_id'],
                'target_product_id' => $product->id,
                'target_quantity' => $data['output_quantity'],
                'purpose' => 'ผลิต WIP '.$product->name,
                'note' => null,
            ], $request->user());

            return $request->user()->isAdmin()
                ? $service->approve($requisition, $request->user())
                : $requisition;
        });

        return redirect()->route('requisitions.show', $requisition)
            ->with('success', $request->user()->isAdmin()
                ? 'บันทึกสูตร ผลิต WIP และปรับสต็อกเรียบร้อยแล้ว'
                : 'บันทึกสูตรและสร้างใบเบิกแล้ว กรุณารอ Admin ตรวจสอบและอนุมัติ');
    }

    public function issues(Request $request)
    {
        $selectedProduct = null;
        $query = Requisition::with(['requester', 'targetProduct', 'warehouse', 'items.product'])
            ->whereIn('status', [RequisitionStatus::PENDING, RequisitionStatus::APPROVED]);

        if ($request->filled('product_id')) {
            $selectedProduct = Product::findOrFail($request->integer('product_id'));
            $query->whereIn('request_type', [
                RequisitionType::ISSUE_PART->value,
                RequisitionType::ISSUE_SUPPLY->value,
                RequisitionType::ISSUE_WIP->value,
                RequisitionType::ISSUE_FG->value,
            ])->whereHas('items', fn ($items) => $items->where('product_id', $selectedProduct->id));
        }

        return view('requisitions.issues', [
            'rows' => $query
                ->orderByRaw("CASE WHEN status = 'PENDING' THEN 0 ELSE 1 END")
                ->latest()->paginate(40)->withQueryString(),
            'pendingCount' => Requisition::where('status', RequisitionStatus::PENDING)->count(),
            'selectedProduct' => $selectedProduct,
        ]);
    }

    public function create(Request $request)
    {
        $selectedType = collect(RequisitionType::cases())->first(fn (RequisitionType $type) => $type->value === $request->query('type'));
        if ($selectedType?->isBuild()) {
            return $this->production($request);
        }

        return $this->withdraw($request);
    }

    private function requisitionForm(Request $request, string $mode, array $types)
    {
        $selectedType = collect($types)->first(fn (RequisitionType $type) => $type->value === $request->query('type')) ?? $types[0];

        return view('requisitions.create', [
            'products' => Product::with(['unit', 'components.unit', 'balances'])->where('is_active', true)
                ->orderBy('code')->get(),
            'warehouses' => Warehouse::where('is_active', true)->get(),
            'types' => $types,
            'selectedType' => $selectedType,
            'formMode' => $mode,
        ]);
    }

    public function store(Request $request, RequisitionService $service)
    {
        $data = $request->validate(['request_type' => ['required', Rule::enum(RequisitionType::class)], 'warehouse_id' => ['required', 'exists:warehouses,id'], 'target_product_id' => ['nullable', 'exists:products,id'], 'target_quantity' => ['nullable', 'decimal:0,4', 'gt:0'], 'department_name' => ['nullable', 'string', 'max:255'], 'purpose' => ['nullable', 'string', 'max:255'], 'note' => ['nullable', 'string', 'max:2000'], 'items' => ['nullable', 'array'], 'items.*.product_id' => ['required_with:items', 'exists:products,id', 'distinct'], 'items.*.quantity' => ['required_with:items', 'decimal:0,4', 'gt:0'], 'items.*.note' => ['nullable', 'string', 'max:255']]);
        $type = RequisitionType::from($data['request_type']);
        if ($type->isBuild() && (blank($data['target_product_id'] ?? null) || blank($data['target_quantity'] ?? null))) {
            return back()->withInput()->withErrors(['target_product_id' => 'กรุณาเลือกสิ่งที่ต้องการสร้างและระบุจำนวน']);
        }
        if (! $type->isBuild() && empty($data['items'])) {
            return back()->withInput()->withErrors(['items' => 'กรุณาเพิ่มรายการที่ต้องการเบิก']);
        }
        $data['purpose'] = filled($data['purpose'] ?? null) ? $data['purpose'] : $type->label();
        $requisition = DB::transaction(function () use ($data, $request, $service) {
            $requisition = $service->create($data, $request->user());

            return $request->user()->isAdmin()
                ? $service->approve($requisition, $request->user())
                : $requisition;
        });

        return redirect()->route('requisitions.show', $requisition)->with('success', $request->user()->isAdmin()
            ? 'บันทึก อนุมัติ และปรับสต็อกเรียบร้อยแล้ว ใบเบิกพร้อมเปิดดู'
            : 'สร้างใบเบิกสำเร็จ กรุณาตรวจสอบเอกสารใบเบิกพัสดุด้านล่าง แล้วกดยืนยันส่งคำขอให้ Admin');
    }

    public function confirm(Request $request, Requisition $requisition)
    {
        abort_unless($request->user()->isAdmin() || $requisition->requested_by === $request->user()->id, 403);

        return redirect()->route('requisitions.show', $requisition)
            ->with('success', "ยืนยันส่งคำขอใบเบิก {$requisition->request_no} ให้ Admin อนุมัติเรียบร้อยแล้ว");
    }

    public function show(Request $request, Requisition $requisition)
    {
        abort_unless($request->user()->isAdmin() || $requisition->requested_by === $request->user()->id, 403);

        return view('requisitions.show', ['requisition' => $requisition->load(['items.product.unit', 'targetProduct.unit', 'warehouse', 'requester', 'approver', 'rejecter', 'stockDocuments'])]);
    }

    public function approve(Request $request, Requisition $requisition, RequisitionService $service)
    {
        $service->approve($requisition, $request->user());

        return redirect()->route('requisitions.show', $requisition)
            ->with('success', 'อนุมัติและตัดสต็อกเรียบร้อยแล้ว ใบเบิกพร้อมเปิดดู');
    }

    public function sign(Request $request, Requisition $requisition, AuditLogService $audit)
    {
        abort_unless($requisition->requested_by === $request->user()->id, 403);
        abort_unless($requisition->status === RequisitionStatus::APPROVED && ! $requisition->requester_signed_at && ! $requisition->requester->isAdmin(), 422);
        $data = $request->validate(['pin' => ['required', 'digits:4']]);
        $signature = UserSignature::where('user_id', $request->user()->id)->first();
        if (! $signature || ! $signature->verifyPin($data['pin'])) {
            throw ValidationException::withMessages(['pin' => 'รหัส PIN ลายเซ็นไม่ถูกต้อง']);
        }
        abort_unless(Storage::disk('local')->exists($signature->signature_path), 422, 'ไม่พบไฟล์ลายเซ็น');

        $extension = pathinfo($signature->signature_path, PATHINFO_EXTENSION) ?: 'png';
        $snapshot = 'requisition-signatures/'.$requisition->id.'-'.Str::uuid().'.'.$extension;
        Storage::disk('local')->put($snapshot, Storage::disk('local')->get($signature->signature_path));
        $requisition->update(['requester_signature_path' => $snapshot, 'requester_signed_at' => now()]);
        $audit->record($request->user(), 'SIGN', 'requisition', $requisition->id, null, ['signed_at' => $requisition->requester_signed_at]);

        return back()->with('success', 'ลงนามเรียบร้อยแล้ว เอกสาร PDF พร้อมดาวน์โหลดและพิมพ์ส่งแผนกเบิก');
    }

    public function signature(Request $request, Requisition $requisition)
    {
        abort_unless($request->user()->isAdmin() || $requisition->requested_by === $request->user()->id, 403);
        abort_unless($requisition->requester_signature_path && Storage::disk('local')->exists($requisition->requester_signature_path), 404);

        return response()->file(Storage::disk('local')->path($requisition->requester_signature_path), ['Cache-Control' => 'private, no-store']);
    }

    public function reject(Request $request, Requisition $requisition, RequisitionService $service)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        $service->reject($requisition, $request->user(), $data['reason']);

        return redirect()->route('requisitions.show', $requisition)->with('success', 'บันทึกการไม่อนุมัติแล้ว');
    }

    public function print(Request $request, Requisition $requisition)
    {
        abort_unless($request->user()->isAdmin() || $requisition->requested_by === $request->user()->id, 403);
        $requisition->load(['items.product.unit', 'targetProduct.unit', 'warehouse', 'requester', 'approver', 'stockDocuments']);
        abort_unless($requisition->isReadyForPdf(), 404);

        return view('requisitions.print', ['requisition' => $requisition, 'requesterSignatureData' => $this->signatureData($requisition)]);
    }

    public function pdf(Request $request, Requisition $requisition)
    {
        abort_unless($request->user()->isAdmin() || $requisition->requested_by === $request->user()->id, 403);
        $requisition->load(['items.product.unit', 'targetProduct.unit', 'warehouse', 'requester', 'approver', 'stockDocuments']);
        abort_unless($requisition->isReadyForPdf(), 404);

        File::ensureDirectoryExists(storage_path('fonts'));
        $pdf = Pdf::loadView('requisitions.print', ['requisition' => $requisition, 'pdfMode' => true, 'requesterSignatureData' => $this->signatureData($requisition)])
            ->setPaper('a4', 'portrait')
            ->setOption(['defaultMediaType' => 'print', 'isRemoteEnabled' => false]);

        $fonts = $pdf->getDomPDF()->getFontMetrics();
        $fonts->registerFont(['family' => 'Plex Thai PDF', 'style' => 'normal', 'weight' => 'normal'], resource_path('fonts/IBMPlexSansThai-Regular.ttf'));
        $fonts->registerFont(['family' => 'Plex Thai PDF', 'style' => 'normal', 'weight' => 'bold'], resource_path('fonts/IBMPlexSansThai-Bold.ttf'));

        return $pdf->stream('requisition-'.$requisition->request_no.'.pdf');
    }

    private function signatureData(Requisition $requisition): ?string
    {
        if (! $requisition->requester_signature_path || ! Storage::disk('local')->exists($requisition->requester_signature_path)) {
            return null;
        }
        $path = $requisition->requester_signature_path;
        $mime = strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'jpg' ? 'image/jpeg' : 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode(Storage::disk('local')->get($path));
    }
}
