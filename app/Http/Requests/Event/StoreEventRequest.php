<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
        'title'=>'required|string|max:200',
        'description'=>'nullable|string',
        'event_date'=>'required|date|after_or_equal:'.now()->addDays(3)->toDateString(),
        'start_time'=>'required|date_format:H:i',
        'end_time'=>'nullable|date_format:H:i|after:start_time',
        'location'=>'required|string|max:200',
        'is_published'=>'boolean',
        'flyer'=>'nullable|image|max:2048',
        'certificate_template'=>'nullable|mimes:pdf,jpg,png|max:4096',
        ];
    }
}
