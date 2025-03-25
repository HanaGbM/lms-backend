<?php

namespace App\Policies;

use App\Models\ModuleTeacher;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ModuleTeacherPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ModuleTeacher $moduleTeacher): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->hasPermissionTo('enroll_module')
            ? Response::allow()
            : Response::deny('You do not have permission to enroll module');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ModuleTeacher $moduleTeacher): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ModuleTeacher $moduleTeacher): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ModuleTeacher $moduleTeacher): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ModuleTeacher $moduleTeacher): bool
    {
        return false;
    }
}
