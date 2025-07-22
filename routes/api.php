<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AdminModuleController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\ChapterMaterialController;
use App\Http\Controllers\DiscussionController;
use App\Http\Controllers\GradeReportController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OTPController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuestionResponseController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentModuleController;
use App\Http\Controllers\StudentRegistrationController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UpcomingEventsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'api',
], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [StudentRegistrationController::class, 'register']);

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

        Route::get('get-discussions/{moduleTeacher}', [DiscussionController::class, 'index']);
        Route::get('discussion-detail/{discussion}', [DiscussionController::class, 'show']);
        Route::post('create-discussion/{moduleTeacher}', [DiscussionController::class, 'store']);
        Route::patch('update-discussion/{discussion}', [DiscussionController::class, 'update']);
        Route::delete('delete-discussion/{discussion}', [DiscussionController::class, 'destroy']);


        Route::get('get-replies/{discussion}', [ReplyController::class, 'index']);
        Route::post('create-reply/{discussion}', [ReplyController::class, 'store']);
        Route::patch('update-reply/{reply}', [ReplyController::class, 'update']);
        Route::delete('delete-reply/{reply}', [ReplyController::class, 'destroy']);


        /**
         * Super Admin Endpoints */
        Route::resource('roles', RoleController::class);
        Route::get('permissions', [PermissionController::class, 'index']);


        Route::post('roles/{role}/attach-permissions', [RolePermissionController::class, 'attachPermission']);
        Route::post('roles/{role}/detach-permissions', [RolePermissionController::class, 'detachPermission']);
        Route::post('assign-role/{user}', [RoleController::class, 'assignRole']);

        Route::get('get-activity-logs', [ActivityLogController::class, 'index']);

        /**
         * Admin Endpoints */
        Route::resource('users', UserController::class);

        Route::get('get-teachers', [TeacherController::class, 'teachers']);
        Route::resource('modules', ModuleController::class);

        Route::post('assign-teachers/{module}', [AdminModuleController::class, 'assignTeachers']);

        Route::get('get-students', [StudentController::class, 'students']);
        Route::get('get-students-by-course/{module}', [StudentController::class, 'studentsByCourse']);

        Route::get('get-module-teachers/{module}', [AdminModuleController::class, 'getModuleTeachers']);
        Route::get('get-module-students/{id}', [AdminModuleController::class, 'getModuleStudents']);
        Route::post('assign-students/{moduleTeacher}', [AdminModuleController::class, 'assignStudents']);


        Route::resource('chapters', ChapterController::class);
        Route::get('all-chapters', [ChapterController::class, 'all']);
        Route::get('my-module-chapters/{moduleTeacher}', [ChapterController::class, 'myModuleChapters']);
        Route::post('sort-chapters', [ChapterController::class, 'sortChapters']);
        Route::resource('chapter-materials', ChapterMaterialController::class);
        Route::delete('delete-file/{id}', [ChapterMaterialController::class, 'deleteFile']);

        /**
         * Teachers Endpoints */
        Route::get('my-modules', [ModuleController::class, 'myModules']);

        Route::resource('tests', TestController::class);
        Route::resource('questions', QuestionController::class);


        Route::resource('meetings', MeetingController::class);
        Route::get('participants/{meeting}', [MeetingController::class, 'show']);


        Route::get('student-responses/{test}/{student}', [QuestionResponseController::class, 'studentResponses']);
        Route::post('evaluate-short-answer/{questionResponse}', [QuestionResponseController::class, 'evaluate']);

        Route::resource('announcements', AnnouncementController::class);

        Route::get('get-announcements', [AnnouncementController::class, 'getAnnouncements']);

        /**
         * Students Endpoints */
        Route::get('get-modules', [StudentController::class, 'modules']);
        Route::post('enroll-module/{moduleTeacher}', [StudentModuleController::class, 'store']);

        Route::get('my-enrolled-modules', [StudentController::class, 'myModules']);
        Route::get('my-enrolled-module-detail/{studentModule}', [StudentController::class, 'moduleDetail']);
        Route::get('module-chapters/{studentModule}', [StudentModuleController::class, 'moduleChapters']);
        Route::get('module-tests/{studentModule}', [StudentModuleController::class, 'moduleTests']);

        Route::get('test-questions/{test}', [StudentModuleController::class, 'testQuestions']);

        Route::post('start-test/{test}', [QuestionResponseController::class, 'startTest']);
        Route::post('complete-test/{test}', [QuestionResponseController::class, 'completeTest']);
        Route::post('question-response', [QuestionResponseController::class, 'questionResponse']);
        Route::get('grade-report/{test}', [GradeReportController::class, 'myGrade']);

        Route::get('module-grades/{studentModule}', [GradeReportController::class, 'moduleGrades']);
        // Route::get('grade-report/{test}/{student}', [GradeReportController::class, 'studentGrade']);

        Route::get('get-calendars', [CalendarController::class, 'index']);
        Route::get('upcoming-events', [UpcomingEventsController::class, 'upcomingEvents']);
    });
});
