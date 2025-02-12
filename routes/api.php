<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AdminModuleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DiscussionController;
use App\Http\Controllers\GradeReportController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OTPController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuestionResponseController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentModuleController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'api',
], function () {
    Route::post('login', [AuthController::class, 'login']);

    /**
     *  Other AuthController routes
     */
    Route::post('create-password', [AuthController::class, 'create_password'])->middleware('auth:api');
    Route::post('update-profile-image', [AuthController::class, 'update_profile_image'])->middleware('auth:api');
    Route::post('remove-profile-image', [AuthController::class, 'remove_profile_image'])->middleware('auth:api');

    Route::post('update-profile', [AuthController::class, 'update_profile'])->middleware('auth:api');

    Route::post('reset-password', [ResetPasswordController::class, 'reset_password']);
    Route::post('change-password', [ResetPasswordController::class, 'change_password'])->middleware('auth:api');

    Route::post('verify-otp', [OTPController::class, 'verify_otp']);
    Route::post('resend-otp', [OTPController::class, 'resend_otp']);

    Route::group([
        'middleware' => 'auth:api',
    ], function () {


        /**
         * Common Endpoints */
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/my-profile', [AuthController::class, 'profile']);

        Route::get('my-activity-logs', [ActivityLogController::class, 'myActivityLog']);


        Route::post('test-notification', [NotificationController::class, 'testNotification']);

        Route::get('my-notifications', [NotificationController::class, 'getNotification']);
        Route::post('read-all-notifications', [NotificationController::class, 'readNotifications']);
        Route::post('read-notification/{id}', [NotificationController::class, 'readNotification']);

        Route::get('get-discussions/{module}', [DiscussionController::class, 'index']);
        Route::get('discussion-detail/{discussion}', [DiscussionController::class, 'show']);
        Route::post('create-discussion/{module}', [DiscussionController::class, 'store']);
        Route::patch('update-discussion/{discussion}', [DiscussionController::class, 'update']);
        Route::delete('delete-discussion/{discussion}', [DiscussionController::class, 'destroy']);


        Route::get('get-replies/{discussion}', [ReplyController::class, 'index']);
        Route::post('create-reply/{discussion}', [ReplyController::class, 'store']);
        Route::patch('update-reply/{reply}', [ReplyController::class, 'update']);
        Route::delete('delete-reply/{reply}', [ReplyController::class, 'destroy']);
        /**
         * Admin Endpoints */
        Route::group(['middleware' => 'role:Admin',], function () {
            Route::get('get-activity-logs', [ActivityLogController::class, 'index']);

            Route::resource('users', UserController::class);
            Route::resource('roles', RoleController::class);

            Route::get('get-teachers', [TeacherController::class, 'teachers']);
            Route::resource('admin-modules', AdminModuleController::class);
        });


        /**
         * Teachers Endpoints */
        Route::group(['middleware' => 'role:Teacher'], function () {
            Route::resource('modules', ModuleController::class);
            Route::resource('courses', CourseController::class);

            Route::resource('questions', QuestionController::class);

            Route::get('assignments/{module}', [QuestionController::class, 'assignments']);

            Route::get('short-answer-test-responses/{module}', [QuestionResponseController::class, 'shortAnswerTestResponses']);
            Route::get('short-answer-assignment-responses/{module}', [QuestionResponseController::class, 'shortAnswerAssignmentResponses']);
            Route::post('evaluate-short-answer/{questionResponse}', [QuestionResponseController::class, 'evaluate']);
        });
    });

    /**
     * Students Endpoints */
    Route::group(['middleware' => 'role:Student'], function () {
        Route::get('get-modules', [StudentController::class, 'modules']);
        Route::post('enroll-module/{module}', [StudentModuleController::class, 'store']);

        Route::get('my-modules', [StudentController::class, 'myModules']);
        Route::get('module-courses/{studentModule}', [StudentModuleController::class, 'moduleCourses']);
        Route::get('module-tests/{studentModule}', [StudentModuleController::class, 'moduleTests']);
        Route::get('module-assignments/{studentModule}', [StudentModuleController::class, 'moduleAssignments']);

        Route::post('question-response', [QuestionResponseController::class, 'questionResponse']);
        Route::get('grade-report/{studentModule}', [GradeReportController::class, 'myGrade']);
    });
});
