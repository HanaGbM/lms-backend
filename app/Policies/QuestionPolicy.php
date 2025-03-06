<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class QuestionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        return $user->hasPermissionTo('read_question')
            ? Response::allow()
            : Response::deny('You do not have permission to read question');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Question $question): Response
    {
        return $user->hasPermissionTo('read_question')
            ? Response::allow()
            : Response::deny('You do not have permission to read question');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->hasPermissionTo('create_question')
            ? Response::allow()
            : Response::deny('You do not have permission to create question');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Question $question): Response
    {
        return $user->hasPermissionTo('update_question')
            ? Response::allow()
            : Response::deny('You do not have permission to update question');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Question $question): Response
    {
        return $user->hasPermissionTo('delete_question')
            ? Response::allow()
            : Response::deny('You do not have permission to delete question');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Question $question): Response
    {
        return $user->hasPermissionTo('delete_question')
            ? Response::allow()
            : Response::deny('You do not have permission to restore question');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Question $question): Response
    {
        return $user->hasPermissionTo('delete_question')
            ? Response::allow()
            : Response::deny('You do not have permission to permanently delete question');
    }
}
