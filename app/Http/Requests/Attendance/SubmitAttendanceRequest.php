<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAttendanceRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [ 'token' => 'required|digits:10' ];
    }
}
