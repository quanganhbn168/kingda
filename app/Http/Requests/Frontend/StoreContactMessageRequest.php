<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactMessageRequest extends FormRequest
{
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
            'subject',
            'message',
            'website',
        ]))->map(fn (mixed $value): mixed => is_string($value) ? trim($value) : $value)->all());
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'required_without:email', 'string', 'max:30', 'regex:/^[0-9+\s().-]{7,30}$/'],
            'email' => ['nullable', 'required_without:phone', 'email:rfc', 'max:150'],
            'company' => ['nullable', 'string', 'max:150'],
            'subject' => ['nullable', 'string', 'max:200'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
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
            'subject' => __('ui.contact.subject'),
            'message' => __('ui.contact.message'),
        ];
    }
}