<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'user_id' => 'required|exists:users,id',
            'code'    => 'required|string|min:4|max:10',
        ];
    }
}
