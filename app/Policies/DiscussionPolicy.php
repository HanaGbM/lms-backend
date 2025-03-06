<?php

namespace App\Policies;

use App\Models\Discussion;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DiscussionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        return $user->hasPermissionTo(permission: 'read_discussion')
            ? Response::allow()
            : Response::deny('You do not have permission to read discussion.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Discussion $discussion): Response
    {
        return $user->hasPermissionTo(permission: 'read_discussion')
            ? Response::allow()
            : Response::deny('You do not have permission to read discussion.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->hasPermissionTo(permission: 'create_discussion')
            ? Response::allow()
            : Response::deny('You do not have permission to create discussion.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Discussion $discussion): Response
    {
        return $user->hasPermissionTo(permission: 'update_discussion')
            ? Response::allow()
            : Response::deny('You do not have permission to update discussion.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Discussion $discussion): Response
    {
        return $user->hasPermissionTo(permission: 'delete_discussion')
            ? Response::allow()
            : Response::deny('You do not have permission to delete discussion.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Discussion $discussion): Response
    {
        return $user->hasPermissionTo(permission: 'delete_discussion')
            ? Response::allow()
            : Response::deny('You do not have permission to restore discussion.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Discussion $discussion): Response
    {
        return $user->hasPermissionTo(permission: 'delete_discussion')
            ? Response::allow()
            : Response::deny('You do not have permission to permanently delete discussion.');
    }
}
