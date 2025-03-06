<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RolePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        return $user->hasPermissionTo('read_role')
            ? Response::allow()
            : Response::deny('You do not have permission to view roles.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Role $role): Response
    {
        return $user->hasPermissionTo('read_role')
            ? Response::allow()
            : Response::deny('You do not have permission to view this role.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->hasPermissionTo('create_role')
            ? Response::allow()
            : Response::deny('You do not have permission to create roles.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Role $role): Response
    {
        return $user->hasPermissionTo('update_role')
            ? Response::allow()
            : Response::deny('You do not have permission to update this role.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Role $role): Response
    {
        return $user->hasPermissionTo('delete_role')
            ? Response::allow()
            : Response::deny('You do not have permission to delete this role.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Role $role): Response
    {
        return $user->hasPermissionTo('delete_role')
            ? Response::allow()
            : Response::deny('You do not have permission to restore this role.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Role $role): Response
    {
        return $user->hasPermissionTo('delete_role')
            ? Response::allow()
            : Response::deny('You do not have permission to permanently delete this role.');
    }

    public function viewAnyPermission(User $user): Response
    {
        return $user->hasPermissionTo('read_permission')
            ? Response::allow()
            : Response::deny('You do not have permission to read permissions.');
    }


    public function assignPermission(User $user): Response
    {
        return $user->hasPermissionTo('assign_role')
            ? Response::allow()
            : Response::deny('You do not have permission to assign permissions to a role.');
    }

    public function attachPermission(User $user): Response
    {
        return $user->hasPermissionTo('attach_permission')
            ? Response::allow()
            : Response::deny('You do not have permission to attach permissions to a role.');
    }

    public function detachPermission(User $user): Response
    {
        return $user->hasPermissionTo('detach_permission')
            ? Response::allow()
            : Response::deny('You do not have permission to detach permissions from a role.');
    }
}
