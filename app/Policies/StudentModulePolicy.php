<?php

namespace App\Policies;

use App\Models\StudentModule;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StudentModulePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        return $user->hasPermissionTo('read_student_module')
            ? Response::allow()
            : Response::deny('You do not have permission to view student modules.');
    }

    public function viewEnrolledModule(User $user): Response
    {
        return $user->hasPermissionTo('read_my_enrolled_module')
            ? Response::allow()
            : Response::deny('You do not have permission to view enrolled modules.');
    }


    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, StudentModule $studentModule): Response
    {
        return $user->hasPermissionTo('read_student_module')
            ? Response::allow()
            : Response::deny('You do not have permission to view student modules.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->hasPermissionTo('read_student_module')
            ? Response::allow()
            : Response::deny('You do not have permission to view student modules.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, StudentModule $studentModule): Response
    {
        return $user->hasPermissionTo('read_student_module')
            ? Response::allow()
            : Response::deny('You do not have permission to view student modules.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, StudentModule $studentModule): Response
    {
        return $user->hasPermissionTo('read_student_module')
            ? Response::allow()
            : Response::deny('You do not have permission to view student modules.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, StudentModule $studentModule): Response
    {
        return $user->hasPermissionTo('read_student_module')
            ? Response::allow()
            : Response::deny('You do not have permission to view student modules.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, StudentModule $studentModule): Response
    {
        return $user->hasPermissionTo('read_student_module')
            ? Response::allow()
            : Response::deny('You do not have permission to view student modules.');
    }
}
