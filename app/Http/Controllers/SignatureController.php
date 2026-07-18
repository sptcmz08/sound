<?php

namespace App\Http\Controllers;

use App\Models\UserSignature;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SignatureController extends Controller
{
    public function edit(Request $request)
    {
        return view('signature.edit', ['signature' => $request->user()->signature]);
    }

    public function update(Request $request, AuditLogService $audit)
    {
        $data = $request->validate([
            'signature_data' => ['nullable', 'string', 'max:3000000'],
            'pin' => ['required', 'digits:4'],
        ]);
        $existing = UserSignature::where('user_id', $request->user()->id)->first();
        $path = $existing?->signature_path;

        if (filled($data['signature_data'] ?? null)) {
            $binary = base64_decode((string) preg_replace('#^data:image/\w+;base64,#i', '', $data['signature_data']), true);
            $mime = $binary ? (new \finfo(FILEINFO_MIME_TYPE))->buffer($binary) : null;
            if (! $binary || strlen($binary) > 2 * 1024 * 1024 || ! in_array($mime, ['image/png', 'image/jpeg'], true)) {
                throw ValidationException::withMessages(['signature_data' => 'ลายเซ็นต้องเป็นไฟล์ PNG/JPG ขนาดไม่เกิน 2MB']);
            }
            $extension = $mime === 'image/jpeg' ? 'jpg' : 'png';
            $path = 'signatures/'.$request->user()->id.'/'.Str::uuid().'.'.$extension;
            Storage::disk('local')->put($path, $binary);
            if ($existing?->signature_path && $existing->signature_path !== $path) {
                Storage::disk('local')->delete($existing->signature_path);
            }
        }

        if (! $path) {
            throw ValidationException::withMessages(['signature_data' => 'กรุณาวาดหรืออัปโหลดลายเซ็น']);
        }

        UserSignature::updateOrCreate(['user_id' => $request->user()->id], [
            'signature_path' => $path,
            'pin_hash' => Hash::make($data['pin']),
        ]);
        $audit->record($request->user(), 'UPDATE', 'user_signature', $request->user()->id, null, ['saved' => true]);

        return back()->with('success', 'บันทึกลายเซ็นและรหัส PIN แล้ว');
    }

    public function show(Request $request, UserSignature $signature)
    {
        abort_unless($request->user()->id === $signature->user_id || $request->user()->isAdmin(), 403);
        abort_unless(Storage::disk('local')->exists($signature->signature_path), 404);

        return response()->file(Storage::disk('local')->path($signature->signature_path), ['Cache-Control' => 'private, no-store']);
    }
}
