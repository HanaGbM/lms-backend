<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        return $user->hasPermissionTo('read_user')
            ? Response::allow()
            : Response::deny('You do not have permission to read user');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): Response
    {
        return $user->hasPermissionTo('read_user')
            ? Response::allow()
            : Response::deny('You do not have permission to read user');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->hasPermissionTo('create_user')
            ? Response::allow()
            : Response::deny('You do not have permission to create user');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): Response
    {
        return $user->hasPermissionTo('update_user')
            ? Response::allow()
            : Response::deny('You do not have permission to update user');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): Response
    {
        return $user->hasPermissionTo('delete_user')
            ? Response::allow()
            : Response::deny('You do not have permission to delete user');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): Response
    {
        return $user->hasPermissionTo(permission: 'delete_user')
            ? Response::allow()
            : Response::deny('You do not have permission to restore user.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): Response
    {
        return $user->hasPermissionTo(permission: 'delete_user')
            ? Response::allow()
            : Response::deny('You do not have permission to permanently delete user.');
    }

    public function viewAnyTeachers(User $user): Response
    {
        return $user->hasPermissionTo('read_teacher')
            ? Response::allow()
            : Response::deny('You do not have permission to read teachers');
    }

    public function viewAnyStudents(User $user): Response
    {
        return $user->hasPermissionTo('read_student')
            ? Response::allow()
            : Response::deny('You do not have permission to read students');
    }
}
