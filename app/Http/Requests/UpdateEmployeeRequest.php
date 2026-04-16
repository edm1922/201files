<?php

namespace App\Http\Requests;

use App\Models\Folder;
use App\Models\FolderLocation;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employee = $this->route('employee');

        return [
            'system_id' => ['required', 'string', 'max:100', 'unique:employees,system_id,'.$employee?->id],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'suffix' => ['nullable', 'string', 'max:20'],
            'date_hired' => ['nullable', 'date'],
            'status' => ['required', 'string', 'in:active,awol,resigned'],
            'barcode_id' => ['nullable', 'string', 'max:100', 'unique:employees,barcode_id,'.$employee?->id],
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'folder_id' => [
                'nullable',
                'integer',
                'exists:folders,id',
                function ($attribute, $value, $fail) use ($employee) {
                    if (! $value) {
                        return;
                    }

                    $folder = Folder::query()->find($value);
                    if (! $folder) {
                        return;
                    }

                    $companyId = (int) $this->input('company_id');
                    if ((int) $folder->company_id !== $companyId) {
                        $fail('The selected folder code does not belong to the selected company.');

                        return;
                    }

                    $isCurrentAssignedFolder = $employee && (int) $employee->folder_id === (int) $folder->id;

                    if (! $folder->is_available && ! $isCurrentAssignedFolder) {
                        $fail('The selected folder code is no longer available.');
                    }
                },
            ],
            'folder_location_id' => [
                'nullable', 'integer', 'exists:folder_locations,id',
                function ($attribute, $value, $fail) use ($employee) {
                    if (! $value) {
                        return;
                    }

                    $loc = FolderLocation::find($value);
                    if (! $loc) {
                        return;
                    }

                    $companyId = (int) $this->input('company_id');

                    if ((int) $loc->company_id !== $companyId) {
                        $fail('The selected folder location does not belong to the selected company.');

                        return;
                    }

                    $isChangingLocation = ! $employee || (int) $employee->folder_location_id !== (int) $value;
                    if ($isChangingLocation && $loc->isFull()) {
                        $fail("The selected folder location has reached its maximum capacity ({$loc->max_capacity}).");
                    }
                },
            ],
            'atm_status' => ['nullable', 'string', 'in:on_process,for_releasing,received'],
            'bank_type_id' => ['nullable', 'integer', 'exists:bank_types,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'system_id.required' => 'System ID is required.',
            'system_id.unique' => 'This System ID is already in use.',
            'first_name.required' => 'First Name is required.',
            'last_name.required' => 'Last Name is required.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be active, awol, or resigned.',
            'barcode_id.unique' => 'This Barcode ID is already in use.',
            'company_id.required' => 'Company is required.',
            'company_id.exists' => 'The selected company does not exist.',
            'folder_id.exists' => 'The selected folder code does not exist.',
            'folder_location_id.exists' => 'The selected folder location does not exist.',
            'atm_status.in' => 'ATM status must be On Process, For Releasing, or Received.',
            'bank_type_id.exists' => 'The selected bank type is invalid.',
        ];
    }
}
