<?php

namespace App\Policies;

use App\Models\Reply;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReplyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        return $user->hasPermissionTo(permission: 'read_reply')
            ? Response::allow()
            : Response::deny('You do not have permission to read reply');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Reply $reply): Response
    {
        return $user->hasPermissionTo(permission: 'read_reply')
            ? Response::allow()
            : Response::deny('You do not have permission to read reply');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->hasPermissionTo(permission: 'create_reply')
            ? Response::allow()
            : Response::deny('You do not have permission to create reply');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Reply $reply): Response
    {
        return $user->hasPermissionTo(permission: 'update_reply')
            ? Response::allow()
            : Response::deny('You do not have permission to update reply');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Reply $reply): Response
    {
        return $user->hasPermissionTo(permission: 'delete_reply')
            ? Response::allow()
            : Response::deny('You do not have permission to delete reply');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Reply $reply): Response
    {
        return $user->hasPermissionTo(permission: 'delete_reply')
            ? Response::allow()
            : Response::deny('You do not have permission to restore discussion.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Reply $reply): Response
    {
        return $user->hasPermissionTo(permission: 'delete_reply')
            ? Response::allow()
            : Response::deny('You do not have permission to permanently delete discussion.');
    }
}
