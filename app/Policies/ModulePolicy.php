<?php

namespace App\Policies;

use App\Models\Module;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ModulePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        return $user->hasPermissionTo('read_module')
            ? Response::allow()
            : Response::deny('You do not have permission to read module');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Module $module): Response
    {
        return $user->hasPermissionTo('read_module')
            ? Response::allow()
            : Response::deny('You do not have permission to read module');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->hasPermissionTo('read_module')
            ? Response::allow()
            : Response::deny('You do not have permission to read module');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Module $module): Response
    {
        return $user->hasPermissionTo('read_module')
            ? Response::allow()
            : Response::deny('You do not have permission to read module');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Module $module): Response
    {
        return $user->hasPermissionTo('read_module')
            ? Response::allow()
            : Response::deny('You do not have permission to read module');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Module $module): Response
    {
        return $user->hasPermissionTo('read_module')
            ? Response::allow()
            : Response::deny('You do not have permission to read module');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Module $module): Response
    {
        return $user->hasPermissionTo('read_module')
            ? Response::allow()
            : Response::deny('You do not have permission to read module');
    }

    public function viewModuleTeachers(User $user): Response
    {
        return $user->hasPermissionTo('read_module_teachers')
            ? Response::allow()
            : Response::deny('You do not have permission to read module teachers');
    }

    public function viewModuleStudents(User $user): Response
    {
        return $user->hasPermissionTo('read_module_students')
            ? Response::allow()
            : Response::deny('You do not have permission to read module students');
    }

    public function assignTeachers(User $user, Module $module): Response
    {
        return $user->hasPermissionTo('assign_teachers_module')
            ? Response::allow()
            : Response::deny('You do not have permission to assign teachers to module');
    }

    public function assignStudents(User $user): Response
    {
        return $user->hasPermissionTo('assign_students_module')
            ? Response::allow()
            : Response::deny('You do not have permission to assign students to module');
    }
}
