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

    public function rules(): array
    {
        $employeeId = $this->route('employee');

        return [
            'system_id'    => ['required', 'string', 'max:100', Rule::unique('employees', 'system_id')->ignore($employeeId)],
            'first_name'   => ['required', 'string', 'max:100'],
            'middle_name'  => ['nullable', 'string', 'max:100'],
            'last_name'    => ['required', 'string', 'max:100'],
            'suffix'       => ['nullable', 'string', 'max:20'],
            'date_of_birth'=> ['nullable', 'date'],
            'status'       => ['required', 'string', 'in:active,awol,resigned'],
            'barcode_id'   => ['nullable', 'string', 'max:100', Rule::unique('employees', 'barcode_id')->ignore($employeeId)],
            'folder_code'  => ['nullable', 'string', 'max:100'],
            'company_id'   => ['nullable', 'integer', 'exists:companies,id'],
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
            'company_id.exists'   => 'The selected company does not exist.',
        ];
    }
}
