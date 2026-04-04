<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocumentTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $documentTypeId = $this->route('documentType')?->id;

        if (! $documentTypeId && $this->filled('id')) {
            $documentTypeId = (int) $this->input('id');
        }

        return [
            'department_id' => ['nullable', 'exists:departments,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('document_types', 'name')->ignore($documentTypeId),
            ],
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('document_types', 'code')->ignore($documentTypeId),
            ],
            'has_expiry' => ['boolean'],
            'is_required' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Document type name is required.',
            'name.unique' => 'A document type with this name already exists.',
            'name.max' => 'Name must not exceed 255 characters.',
            'code.required' => 'A short code is required.',
            'code.unique' => 'This code is already taken by another document type.',
            'code.max' => 'Code must not exceed 20 characters.',
            'code.alpha_dash' => 'Code may only contain letters, numbers, dashes, and underscores.',
            'department_id.exists' => 'The selected department does not exist.',
        ];
    }

    /**
     * Prepare the data for validation — unchecked checkboxes aren't sent.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'has_expiry' => $this->boolean('has_expiry'),
            'is_required' => $this->boolean('is_required'),
        ]);
    }
}
