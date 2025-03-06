<?php

namespace App\Policies;

use App\Models\QuestionResponse;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class QuestionResponsePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        return $user->hasPermissionTo('read_question_response')
            ? Response::allow()
            : Response::deny('You do not have permission to read question responses');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, QuestionResponse $questionResponse): Response
    {
        return $user->hasPermissionTo('read_question_response')
            ? Response::allow()
            : Response::deny('You do not have permission to read question ');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->hasPermissionTo('create_question_response')
            ? Response::allow()
            : Response::deny('You do not have permission to create question response');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, QuestionResponse $questionResponse): Response
    {
        return $user->hasPermissionTo('update_question_response')
            ? Response::allow()
            : Response::deny('You do not have permission to update question response');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, QuestionResponse $questionResponse): Response
    {
        return $user->hasPermissionTo('delete_question_response')
            ? Response::allow()
            : Response::deny('You do not have permission to delete question response');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, QuestionResponse $questionResponse): Response
    {
        return $user->hasPermissionTo('delete_question_response')
            ? Response::allow()
            : Response::deny('You do not have permission to restore question response');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, QuestionResponse $questionResponse): Response
    {
        return $user->hasPermissionTo('delete_question_response')
            ? Response::allow()
            : Response::deny('You do not have permission to permanently delete question response');
    }


    public function readGradeReport(User $user): Response
    {
        return $user->hasPermissionTo('read_grade_report')
            ? Response::allow()
            : Response::deny('You do not have permission to read question responses');
    }
}
