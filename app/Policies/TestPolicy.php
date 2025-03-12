<?php

namespace App\Policies;

use App\Models\Test;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        return $user->hasPermissionTo('read_test')
            ? Response::allow()
            : Response::deny('You do not have permission to view tests.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Test $test): Response
    {
        return $user->hasPermissionTo('read_test')
            ? Response::allow()
            : Response::deny('You do not have permission to view tests.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->hasPermissionTo('create_test')
            ? Response::allow()
            : Response::deny('You do not have permission to create tests.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Test $test): Response
    {
        return $user->hasPermissionTo('update_test')
            ? Response::allow()
            : Response::deny('You do not have permission to update tests.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Test $test): Response
    {
        return $user->hasPermissionTo('delete_test')
            ? Response::allow()
            : Response::deny('You do not have permission to delete tests.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Test $test): Response
    {
        return $user->hasPermissionTo('delete_test')
            ? Response::allow()
            : Response::deny('You do not have permission to restore tests.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Test $test): Response
    {
        return $user->hasPermissionTo('delete_test')
            ? Response::allow()
            : Response::deny('You do not have permission to permanently delete tests.');
    }
}
