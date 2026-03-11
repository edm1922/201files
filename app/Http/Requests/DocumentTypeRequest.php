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
        $documentTypeId = $this->route('document_type')?->id;

        return [
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
                'alpha_dash:ascii',
                Rule::unique('document_types', 'code')->ignore($documentTypeId),
            ],
            'target'        => ['required', 'string', Rule::in(['employee', 'department'])],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'has_expiry'    => ['nullable', 'boolean'],
            'is_required'   => ['nullable', 'boolean'],
            'max_pages'     => ['required', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'   => 'Document type name is required.',
            'name.unique'     => 'A document type with this name already exists.',
            'name.max'        => 'Name must not exceed 255 characters.',
            'code.required'   => 'A short code is required.',
            'code.unique'     => 'This code is already taken by another document type.',
            'code.max'        => 'Code must not exceed 20 characters.',
            'code.alpha_dash' => 'Code may only contain letters, numbers, dashes, and underscores.',
            'target.required' => 'Please select a target (Employee or Department).',
            'target.in'       => 'Target must be either Employee or Department.',
            'department_id.exists' => 'The selected department does not exist.',
            'max_pages.required' => 'Max pages is required.',
            'max_pages.min'      => 'Max pages must be at least 1.',
        ];
    }

    /**
     * Prepare the data for validation — unchecked checkboxes aren't sent.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'has_expiry'  => $this->boolean('has_expiry'),
            'is_required' => $this->boolean('is_required'),
        ]);
    }
}
