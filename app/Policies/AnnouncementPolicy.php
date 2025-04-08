<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AnnouncementPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        return $user->hasPermissionTo('read_announcement')
            ? Response::allow()
            : Response::deny('You do not have permission to read announcement');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Announcement $announcement): Response
    {
        return $user->hasPermissionTo('read_announcement')
            ? Response::allow()
            : Response::deny('You do not have permission to read announcement');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->hasPermissionTo('create_announcement')
            ? Response::allow()
            : Response::deny('You do not have permission to create announcement');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Announcement $announcement): Response
    {
        return $user->hasPermissionTo('update_announcement')
            ? Response::allow()
            : Response::deny('You do not have permission to update announcement');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Announcement $announcement): Response
    {
        return $user->hasPermissionTo('delete_announcement')
            ? Response::allow()
            : Response::deny('You do not have permission to delete announcement');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Announcement $announcement): Response
    {
        return $user->hasPermissionTo('delete_announcement')
            ? Response::allow()
            : Response::deny('You do not have permission to restore announcement');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Announcement $announcement): Response
    {
        return $user->hasPermissionTo('delete_announcement')
            ? Response::allow()
            : Response::deny('You do not have permission to restore announcement');
    }
}
