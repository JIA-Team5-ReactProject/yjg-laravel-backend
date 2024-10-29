<?php

namespace App\Services;

use App\Mail\ResetPassword;
use App\Models\PasswordResetCode;
use Illuminate\Support\Facades\Mail;

class ResetPasswordService
{
    public function __construct(protected string $email)
    {
    }
    public function __invoke(): \Illuminate\Http\JsonResponse
    {
        $secret = sprintf('%06d',rand(000000,999999));

        $updateOrInsert = PasswordResetCode::updateOrInsert(
            ['email' => $this->email],
            ['code' => $secret, 'updated_at' => now()],
        );

        if(!$updateOrInsert) return response()->json(['error' => __('messages.500')], 500);

        Mail::to($this->email)->send(new ResetPassword($secret));

        return response()->json(['message' => 'E-mail '.__('messages.200')]);
    }
}
