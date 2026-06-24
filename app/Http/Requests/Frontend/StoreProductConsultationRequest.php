<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductConsultationRequest extends FormRequest
{
    protected $errorBag = 'productConsultation';

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(collect($this->only([
            'name',
            'phone',
            'email',
            'company',
            'message',
            'website',
        ]))->map(fn (mixed $value): mixed => is_string($value) ? trim($value) : $value)->all());
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\s().-]{7,30}$/'],
            'email' => ['nullable', 'email:rfc', 'max:150'],
            'company' => ['nullable', 'string', 'max:150'],
            'message' => ['nullable', 'string', 'max:5000'],
            'website' => ['nullable', 'prohibited'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('ui.contact.name'),
            'phone' => __('ui.contact.phone'),
            'email' => __('ui.contact.email'),
            'company' => __('ui.contact.company'),
            'message' => __('ui.contact.message'),
        ];
    }
}
