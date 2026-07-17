<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyFolderSequence;
use App\Models\Employee;
use App\Models\Folder;
use Illuminate\Support\Facades\DB;

class FolderCodeService
{
    public function assignSpecificAvailableForCompany(Company $company, int $folderId): Folder
    {
        return DB::transaction(function () use ($company, $folderId): Folder {
            $folder = Folder::query()
                ->whereKey($folderId)
                ->where('company_id', $company->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $folder->is_available) {
                abort(422, 'The selected folder code is no longer available.');
            }

            $sequenceNumber = $this->resolveSequenceNumber($folder, $company);

            $folder->update([
                'sequence_number' => $sequenceNumber,
                'folder_code' => $this->formatCode($company->code, $sequenceNumber),
                'is_available' => false,
            ]);

            return $folder->fresh();
        });
    }

    public function previewNextCodeForCompany(Company $company): string
    {
        $sequence = CompanyFolderSequence::query()
            ->where('company_id', $company->id)
            ->first();

        $number = 1;
        if ($sequence) {
            $number = (int) $sequence->next_number;
        } else {
            $maxExisting = (int) (Folder::query()
                ->where('company_id', $company->id)
                ->max('sequence_number') ?? 0);
            $number = $maxExisting > 0 ? $maxExisting + 1 : 1;
        }

        while (Folder::where('company_id', $company->id)->where('sequence_number', $number)->exists()) {
            $number++;
        }

        return $this->formatCode($company->code, $number);
    }

    public function assignNextForCompany(Company $company): Folder
    {
        return DB::transaction(function () use ($company): Folder {
            $number = $this->takeNextSequenceNumber($company->id);
            $folderCode = $this->formatCode($company->code, $number);

            $folder = Folder::create([
                'company_id' => $company->id,
                'sequence_number' => $number,
                'folder_code' => $folderCode,
                'is_available' => false,
            ]);

            return $folder;
        });
    }

    public function assignBySequenceNumber(Company $company, int $sequenceNumber, ?int $exceptEmployeeId = null): ?Folder
    {
        $folder = Folder::query()
            ->where('company_id', $company->id)
            ->where('sequence_number', $sequenceNumber)
            ->lockForUpdate()
            ->first();

        if ($folder) {
            if (! $folder->is_available) {
                if ($exceptEmployeeId !== null) {
                    $alreadyTheirs = Employee::query()
                        ->where('id', $exceptEmployeeId)
                        ->where('folder_id', $folder->id)
                        ->exists();

                    if ($alreadyTheirs) {
                        return $folder;
                    }
                }

                return null;
            }

            $folder->update([
                'folder_code' => $this->formatCode($company->code, $sequenceNumber),
                'is_available' => false,
            ]);

            return $folder->fresh();
        }

        $this->ensureSequenceHeadBeyond($company->id, $sequenceNumber);

        return Folder::create([
            'company_id' => $company->id,
            'sequence_number' => $sequenceNumber,
            'folder_code' => $this->formatCode($company->code, $sequenceNumber),
            'is_available' => false,
        ]);
    }

    public function formatCode(string $companyCode, int $number): string
    {
        return config('brand.folder_prefix').'-'.strtoupper(trim($companyCode)).'-'.str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }

    protected function resolveSequenceNumber(Folder $folder, Company $company): int
    {
        $existingNumber = (int) ($folder->sequence_number ?? 0);

        if ($existingNumber <= 0 && preg_match('/(\d+)$/', (string) $folder->folder_code, $matches) === 1) {
            $existingNumber = (int) $matches[1];
        }

        if ($existingNumber > 0) {
            $collision = Folder::query()
                ->where('company_id', $company->id)
                ->where('sequence_number', $existingNumber)
                ->whereKeyNot($folder->id)
                ->exists();

            if (! $collision) {
                $this->ensureSequenceHeadBeyond($company->id, $existingNumber);

                return $existingNumber;
            }
        }

        return $this->takeNextSequenceNumber($company->id);
    }

    protected function ensureSequenceHeadBeyond(int $companyId, int $number): void
    {
        $sequence = CompanyFolderSequence::query()
            ->where('company_id', $companyId)
            ->lockForUpdate()
            ->first();

        if (! $sequence) {
            CompanyFolderSequence::create([
                'company_id' => $companyId,
                'next_number' => $number + 1,
            ]);

            return;
        }

        if ((int) $sequence->next_number <= $number) {
            $sequence->update(['next_number' => $number + 1]);
        }
    }

    protected function takeNextSequenceNumber(int $companyId): int
    {
        $sequence = CompanyFolderSequence::query()
            ->where('company_id', $companyId)
            ->lockForUpdate()
            ->first();

        if (! $sequence) {
            $maxExisting = (int) (Folder::query()
                ->where('company_id', $companyId)
                ->max('sequence_number') ?? 0);

            $startingNumber = $maxExisting > 0 ? $maxExisting + 1 : 1;

            $sequence = CompanyFolderSequence::create([
                'company_id' => $companyId,
                'next_number' => $startingNumber,
            ]);
        }

        $number = (int) $sequence->next_number;

        while (Folder::where('company_id', $companyId)->where('sequence_number', $number)->exists()) {
            $number++;
        }

        $sequence->update(['next_number' => $number + 1]);

        return $number;
    }
}
