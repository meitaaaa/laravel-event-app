<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'title'       => 'sometimes|required|string|max:200',
            'description' => 'nullable|string',
            'event_date'  => 'sometimes|required|date',
            'start_time'  => 'sometimes|required|date_format:H:i',
            'end_time'    => 'nullable|date_format:H:i|after:start_time',
            'location'    => 'sometimes|required|string|max:200',
            'is_published'=> 'boolean',
            'flyer'       => 'nullable|image|max:2048',
            'certificate_template' => 'nullable|mimes:pdf,jpg,jpeg,png|max:4096',
        ];
    }
}
