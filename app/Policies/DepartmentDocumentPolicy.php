<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DepartmentDocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin', 'encoder', 'viewer');
    }

    public function view(User $user, Document $document): bool
    {
        return $this->viewAny($user)
            && $user->canAccessDepartment((int) $document->department_id);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin', 'encoder');
    }

    public function createForDepartment(User $user, int $departmentId): bool
    {
        return $this->create($user)
            && $user->canAccessDepartment($departmentId);
    }

    public function archive(User $user, Document $document): bool
    {
        return $user->hasRole('admin', 'encoder')
            && $user->canAccessDepartment((int) $document->department_id);
    }

    public function restore(User $user, Document $document): bool
    {
        return $user->hasRole('admin')
            && $user->canAccessDepartment((int) $document->department_id);
    }

    public function download(User $user, Document $document): bool
    {
        return $this->view($user, $document);
    }
}
