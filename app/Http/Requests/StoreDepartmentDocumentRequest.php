<?php

namespace App\Http\Requests;

use App\Models\DocumentFolder;
use App\Models\DocumentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class StoreDepartmentDocumentRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->filled('date_received')) {
            $this->merge([
                'date_received' => now()->toDateString(),
            ]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin', 'encoder') ?? false;
    }

    public function rules(): array
    {
        return [
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'document_type_id' => [
                'required',
                'integer',
                Rule::exists('document_types', 'id')->where(fn ($query) => $query->where('department_id', (int) $this->input('department_id'))
                ),
            ],
            'document_location_id' => ['required', 'integer', 'exists:document_locations,id'],
            'document_folder_id' => ['nullable', 'integer', 'exists:document_folders,id'],
            'upload_mode' => ['required', Rule::in(['standard', 'scan_packet'])],
            'date_received' => ['required', 'date'],
            'expiry_date' => ['nullable', 'date'],
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['required', 'file', 'max:10240'],
            'force_fail_after_store' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (! $this->filled('department_id') || ! $this->filled('document_type_id')) {
                return;
            }

            $documentType = DocumentType::query()->find($this->integer('document_type_id'));
            $files = $this->file('files');
            $uploadMode = $this->input('upload_mode', 'standard');
            $departmentId = $this->integer('department_id');
            $selectedFolderId = $this->integer('document_folder_id');

            if (! $documentType) {
                return;
            }

            if ((int) $documentType->department_id !== $departmentId) {
                $validator->errors()->add('document_type_id', 'The selected document type does not belong to the selected department.');
            }

            if ((bool) $documentType->has_expiry && ! $this->filled('expiry_date')) {
                $validator->errors()->add('expiry_date', 'Expiry date is required for the selected document type.');
            }

            if ($this->filled('date_received') && $this->filled('expiry_date')) {
                $receivedDate = strtotime((string) $this->input('date_received'));
                $expiryDate = strtotime((string) $this->input('expiry_date'));

                if ($receivedDate !== false && $expiryDate !== false && $receivedDate > $expiryDate) {
                    $validator->errors()->add('date_received', 'Date received must be on or before the expiry date.');
                }
            }

            if ($selectedFolderId > 0) {
                $folderBelongsToDepartment = DocumentFolder::query()
                    ->whereKey($selectedFolderId)
                    ->where('department_id', $departmentId)
                    ->exists();

                if (! $folderBelongsToDepartment) {
                    $validator->errors()->add('document_folder_id', 'The selected virtual folder does not belong to the selected department.');
                }
            }

            if (is_array($files) && count($files) > 0) {
                if ($uploadMode === 'scan_packet') {
                    $scanPacketAllowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
                    $scanPacketAllowedMimes = [
                        'application/pdf',
                        'application/x-pdf',
                        'image/jpeg',
                        'image/jpg',
                        'image/pjpeg',
                        'image/png',
                        'image/x-png',
                    ];

                    foreach ($files as $file) {
                        if (! $file instanceof UploadedFile) {
                            continue;
                        }

                        $mime = strtolower($file->getMimeType() ?: '');
                        $extension = strtolower($file->getClientOriginalExtension() ?: '');

                        $extensionAllowed = in_array($extension, $scanPacketAllowedExtensions, true);
                        $mimeAllowed = in_array($mime, $scanPacketAllowedMimes, true);

                        if (! $extensionAllowed && ! $mimeAllowed) {
                            $validator->errors()->add('files', 'Invalid format: Scan packet mode only supports PDF, JPG, JPEG, and PNG.');
                            break;
                        }
                    }
                } else {
                    if (count($files) !== 1) {
                        $validator->errors()->add('files', 'Standard upload mode accepts exactly one file.');
                    }

                    $standardAllowedMimes = [
                        'application/pdf',
                        'image/jpeg',
                        'image/png',
                        'text/csv',
                        'application/csv',
                        'text/plain',
                        'application/msword',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    ];

                    foreach ($files as $file) {
                        if (! $file instanceof UploadedFile) {
                            continue;
                        }

                        $mime = $file->getMimeType() ?: '';
                        $extension = strtolower($file->getClientOriginalExtension() ?: '');

                        $extensionAllowed = in_array($extension, ['pdf', 'jpg', 'jpeg', 'png', 'csv', 'xls', 'xlsx', 'doc', 'docx'], true);
                        $mimeAllowed = in_array($mime, $standardAllowedMimes, true);

                        if (! $extensionAllowed && ! $mimeAllowed) {
                            $validator->errors()->add('files', 'Invalid format: Standard mode supports PDF, JPG, JPEG, PNG, DOC, DOCX, XLS, XLSX, and CSV.');
                            break;
                        }
                    }
                }
            }
        });
    }
}
