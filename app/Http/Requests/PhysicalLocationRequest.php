<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PhysicalLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'cabinet_id' => $this->cabinet_id ? mb_strtoupper(trim($this->cabinet_id)) : null,
            'rack_id'    => $this->rack_id ? mb_strtoupper(trim($this->rack_id)) : null,
        ]);
    }

    public function rules(): array
    {
        $locationId = $this->route('physical_location')?->id;

        return [
            'cabinet_id' => ['required', 'string', 'max:50'],
            'rack_id'    => [
                'required',
                'string',
                'max:50',
                Rule::unique('physical_locations')->where(function ($query) {
                    return $query->where('cabinet_id', $this->cabinet_id);
                })->ignore($locationId),
            ],
            'label'      => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'cabinet_id.required' => 'Cabinet ID is required.',
            'rack_id.required'    => 'Rack ID is required.',
            'rack_id.unique'      => 'A physical location with this specific Cabinet and Rack combination already exists.',
        ];
    }
}
