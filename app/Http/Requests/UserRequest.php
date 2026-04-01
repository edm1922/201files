<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user') ? $this->route('user')->id : null;
        
        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($userId)],
            'role' => ['required', 'string', Rule::in(['admin', 'encoder', 'viewer'])],
            'department_ids' => ['nullable', 'array'],
            'department_ids.*' => ['integer', 'exists:departments,id'],
        ];

        // Passwords are no longer validated in UserRequest as they are auto-generated on creation or handled in ForcePasswordChange

        return $rules;
    }
}
