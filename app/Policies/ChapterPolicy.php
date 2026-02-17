<?php

namespace App\Policies;

use App\Models\Chapter;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ChapterPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        return $user->hasPermissionTo('read_chapter')
            ? Response::allow()
            : Response::deny('You do not have permission to read chapter');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Chapter $chapter): Response
    {
        return $user->hasPermissionTo('read_chapter')
            ? Response::allow()
            : Response::deny('You do not have permission to read chapter');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->hasPermissionTo('create_chapter')
            ? Response::allow()
            : Response::deny('You do not have permission to create chapter');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Chapter $chapter): Response
    {
        return $user->hasPermissionTo('update_chapter')
            ? Response::allow()
            : Response::deny('You do not have permission to update chapter');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Chapter $chapter): Response
    {
        return $user->hasPermissionTo('delete_chapter')
            ? Response::allow()
            : Response::deny('You do not have permission to delete chapter');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Chapter $chapter): Response
    {
        return $user->hasPermissionTo('delete_chapter')
            ? Response::allow()
            : Response::deny('You do not have permission to restore chapter');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Chapter $chapter): Response
    {
        return $user->hasPermissionTo('delete_chapter')
            ? Response::allow()
            : Response::deny('You do not have permission to permanently delete chapter');
    }

    public function sortChapters(User $user): Response
    {
        return $user->hasPermissionTo('sort_chapters')
            ? Response::allow()
            : Response::deny('You do not have permission to sort chapters');
    }
}
