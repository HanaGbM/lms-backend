<?php

namespace App\Policies;

use App\Models\ChapterMaterial;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ChapterMaterialPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        return $user->hasPermissionTo('read_chapter_material')
            ? Response::allow()
            : Response::deny('You do not have permission to read chapter material');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ChapterMaterial $chapterMaterial): Response
    {
        return $user->hasPermissionTo('read_chapter_material')
            ? Response::allow()
            : Response::deny('You do not have permission to read chapter material');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->hasPermissionTo('create_chapter_material')
            ? Response::allow()
            : Response::deny('You do not have permission to create chapter material');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ChapterMaterial $chapterMaterial): Response
    {
        return $user->hasPermissionTo('update_chapter_material')
            ? Response::allow()
            : Response::deny('You do not have permission to update chapter material');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ChapterMaterial $chapterMaterial): Response
    {
        return $user->hasPermissionTo('delete_chapter_material')
            ? Response::allow()
            : Response::deny('You do not have permission to delete chapter material');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ChapterMaterial $chapterMaterial): Response
    {
        return $user->hasPermissionTo('delete_chapter_material')
            ? Response::allow()
            : Response::deny('You do not have permission to restore chapter material');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ChapterMaterial $chapterMaterial): Response
    {
        return $user->hasPermissionTo('delete_chapter_material')
            ? Response::allow()
            : Response::deny('You do not have permission to permanently delete chapter material');
    }
}
