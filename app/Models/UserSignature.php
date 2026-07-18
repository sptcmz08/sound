<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class UserSignature extends Model
{
    protected $fillable = ['user_id', 'signature_path', 'pin_hash'];

    protected $hidden = ['pin_hash'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verifyPin(string $pin): bool
    {
        return Hash::check($pin, $this->pin_hash);
    }
}
