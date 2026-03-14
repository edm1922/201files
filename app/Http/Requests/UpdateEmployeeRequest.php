<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('folder_code')) {
            $code = $this->folder_code;
            // Prepend prefix if only digits were provided
            if (is_numeric($code)) {
                $this->merge([
                    'folder_code' => 'CSC-HR-' . str_pad($code, 4, '0', STR_PAD_LEFT)
                ]);
            }
        }
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee');

        return [
            'system_id'    => ['required', 'string', 'max:100', Rule::unique('employees', 'system_id')->ignore($employeeId)],
            'first_name'   => ['required', 'string', 'max:100'],
            'middle_name'  => ['nullable', 'string', 'max:100'],
            'last_name'    => ['required', 'string', 'max:100'],
            'suffix'       => ['nullable', 'string', 'max:20'],
            'date_hired'   => ['nullable', 'date'],
            'status'       => ['required', 'string', 'in:active,awol,resigned'],
            'barcode_id'   => ['nullable', 'string', 'max:100', Rule::unique('employees', 'barcode_id')->ignore($employeeId)],
            'folder_code'  => ['required', 'string', 'max:255', Rule::unique('folder_locations', 'folder_code')->ignore($this->folder_location_id)],
            'company_id'   => ['nullable', 'integer', 'exists:companies,id'],
            'folder_location_id' => ['nullable', 'integer', 'exists:folder_locations,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'system_id.required'  => 'System ID is required.',
            'system_id.unique'    => 'This System ID is already in use.',
            'first_name.required' => 'First Name is required.',
            'last_name.required'  => 'Last Name is required.',
            'status.required'     => 'Status is required.',
            'status.in'           => 'Status must be active, awol, or resigned.',
            'barcode_id.unique'   => 'This Barcode ID is already in use.',
            'folder_code.required' => 'Folder Code is required.',
            'folder_code.unique'   => 'This Folder Code is already in use.',
            'company_id.exists'   => 'The selected company does not exist.',
            'folder_location_id.exists' => 'The selected folder location does not exist.',
        ];
    }
}
