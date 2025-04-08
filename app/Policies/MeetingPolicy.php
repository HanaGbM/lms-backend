<?php

namespace App\Policies;

use App\Models\Meeting;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MeetingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        return $user->hasPermissionTo('read_meeting')
            ? Response::allow()
            : Response::deny('You do not have permission to read meeting');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Meeting $meeting): Response
    {
        return $user->hasPermissionTo('read_meeting')
            ? Response::allow()
            : Response::deny('You do not have permission to read meeting');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->hasPermissionTo('create_meeting')
            ? Response::allow()
            : Response::deny('You do not have permission to create meeting');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Meeting $meeting): Response
    {
        return $user->hasPermissionTo('update_meeting')
            ? Response::allow()
            : Response::deny('You do not have permission to update meeting');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Meeting $meeting): Response
    {
        return $user->hasPermissionTo('delete_meeting')
            ? Response::allow()
            : Response::deny('You do not have permission to delete meeting');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Meeting $meeting): Response
    {
        return $user->hasPermissionTo('delete_meeting')
            ? Response::allow()
            : Response::deny('You do not have permission to restore meeting');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Meeting $meeting): Response
    {
        return $user->hasPermissionTo('delete_meeting')
            ? Response::allow()
            : Response::deny('You do not have permission to permanently delete meeting');
    }
}
