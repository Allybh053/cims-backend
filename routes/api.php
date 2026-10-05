<?php

use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\CaseFileController;
use App\Http\Controllers\Api\CaseFileShareController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DashboardSuperadminController;
use App\Http\Controllers\Api\DataApplicationController;
use App\Http\Controllers\Api\DataApplicationHistoryController;
use App\Http\Controllers\Api\ForumController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\GlobalController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\InquiryController;
use App\Http\Controllers\Api\InquiryResponseController;
use App\Http\Controllers\Api\IntegratedSearchController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProfilingController;
use App\Http\Controllers\Api\ReportFileController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\Superadmin\AnnouncementController as SuperadminAnnouncementController;
use App\Http\Controllers\Api\Superadmin\BranchController;
use App\Http\Controllers\Api\Superadmin\FaqCategoryController;
use App\Http\Controllers\Api\Superadmin\FaqController as SuperadminFaqController;
use App\Http\Controllers\Api\Superadmin\MaintenanceController as SuperadminMaintenanceController;
use App\Http\Controllers\Api\Superadmin\MenuController;
use App\Http\Controllers\Api\Superadmin\RoleController as SuperadminRoleController;
use App\Http\Controllers\Api\Superadmin\TenantController;
use App\Http\Controllers\Api\Superadmin\UserController as SuperadminUserController;
use App\Http\Controllers\Api\Superadmin\UserManualController as SuperadminUserManualController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserManualController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;  // <-- ADD THIS
use App\Http\Controllers\Auth\CentagateController;
use App\Http\Controllers\Auth\NewPasswordController;  // <-- ADD THIS
use App\Http\Controllers\Auth\PasswordResetLinkController;  // <-- ADD THIS
use App\Http\Controllers\PostPrivacyController;
use App\Http\Controllers\Soap\SSMController;
use App\Http\Controllers\SoapController;
use App\Http\Middleware\TrackVisitor;
//use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// ===== PUBLIC AUTH ROUTES =====
Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest')
    ->name('api.login');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware('guest')
    ->name('api.password.email');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('api.password.store');

// ===== OTHER PUBLIC ROUTES =====
Route::middleware('guest')->post('/check-token-reset-password', [UserController::class, 'checkTokenResetPassword']);

// ===== AUTHENTICATED ROUTES =====
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Logout route (authenticated)
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('api.logout');

    Route::get('/get-random-users', [GlobalController::class, 'getRandomUsers']);
    Route::get('/home', [HomeController::class, 'index']);

    Route::get('/soapjpn', [SoapController::class, 'show']);
    Route::get('/soapssm', [SoapController::class, 'show']);

    Route::post('ssm', [SSMController::class, 'getSsm']);
    Route::post('ssm/personal-involvement/roc', [SSMController::class, 'getPersonalInvolvementRoc']);
    Route::post('ssm/personal-involvement/rob', [SSMController::class, 'getPersonalInvolvementRob']);

    // UserController
    Route::middleware([TrackVisitor::class])->group(function () {
        Route::get('/logged-in-user', [UserController::class, 'loggedInUser']);
    });
    Route::post('/profile-image', [UserController::class, 'updateProfileImage']);
    Route::delete('/profile-image/{id}', [UserController::class, 'destroyProfileImage']);
    Route::patch('/update-user', [UserController::class, 'updateUser']);
    Route::get('/fetch-user', [UserController::class, 'getUser']);

    Route::get('/profiles/{id}', [ProfileController::class, 'show']);

    // Dashboard
    Route::get('dashboard/investigator', [DashboardController::class, 'getInvestigator']);
    Route::get('dashboard/coordinator/data', [DashboardController::class, 'getCoordinatorData']);
    Route::get('dashboard/coordinator/system', [DashboardController::class, 'getCoordinatorSystem']);
    Route::get('dashboard/coordinator/agency', [DashboardController::class, 'getCoordinatorAgency']);
    Route::get('dashboard/statistic', [DashboardController::class, 'getStatistic']);
    Route::get('dashboard/superadmin', [DashboardController::class, 'getSuperadmin']);

    Route::get('dashboard-superadmin/social-media', [DashboardSuperadminController::class, 'socialMedia']);
    Route::get('dashboard-superadmin/agency', [DashboardSuperadminController::class, 'agency']);

    // RoleController (Superadmin)
    Route::resource('roles', SuperadminRoleController::class)->except(['show']);

    // RoleController
    Route::get('roles/user', [RoleController::class, 'index']);

    // FaqController (Superadmin)
    Route::resource('faqs', SuperadminFaqController::class);

    // FaqCategoryController (Superadmin)
    Route::resource('faq-categories', FaqCategoryController::class);

    // AnnouncementController (Superadmin)
    Route::resource('announcements', SuperadminAnnouncementController::class);

    Route::get('announcements-list', [AnnouncementController::class, 'index']);
    Route::put('announcements-update/{id}', [AnnouncementController::class, 'update']);

    // TenantController (Superadmin)
    Route::resource('tenants', TenantController::class);

    // UserController (Superadmin)
    Route::resource('users', SuperadminUserController::class);
    Route::get('deleted-users', [SuperadminUserController::class, 'trashed']);

    // FaqController
    Route::get('faqs-user', [FaqController::class, 'index']);

    // ForumController - CommentController, LikeController
    Route::get('forums', [PostController::class, 'index']);
    Route::post('forums', [PostController::class, 'store']);

    Route::get('forum/{id}', [PostController::class, 'show']);
    Route::put('forum/{id}', [PostController::class, 'put']);
    Route::delete('forum/{id}', [PostController::class, 'destroy']);

    Route::put('forum/{id}/privacy', [PostPrivacyController::class, 'put']);

    Route::post('forum/{id}/comment', [CommentController::class, 'store']);
    Route::put('forum/{id}/comment', [CommentController::class, 'put']);
    Route::delete('forum/{id}/comment', [CommentController::class, 'destroy']);

    Route::post('forum/{id}/like', [LikeController::class, 'store']);
    Route::delete('forum/{id}/like', [LikeController::class, 'destroy']);

    // EventController
    Route::resource('events', EventController::class);
    Route::post('events/filter-date', [EventController::class, 'filter']);

    // BranchController
    Route::resource('branches', BranchController::class);

    // MediaController
    Route::get('file-managements/images', [MediaController::class, 'indexImage']);
    Route::get('file-managements/videos', [MediaController::class, 'indexVideo']);
    Route::get('file-managements/documents', [MediaController::class, 'indexDocument']);
    Route::post('file-managements/images', [MediaController::class, 'storeImage']);
    Route::post('file-managements/videos', [MediaController::class, 'storeVideo']);
    Route::post('file-managements/documents', [MediaController::class, 'storeDocument']);
    Route::put('file-managements', [MediaController::class, 'update']);
    Route::delete('file-managements/{id}', [MediaController::class, 'destroy']);

    // CaseFileController
    Route::resource('case-files', CaseFileController::class);
    Route::get('case-files/{id}/medias', [CaseFileController::class, 'mediaShow']);
    Route::post('case-files/media-store', [CaseFileController::class, 'mediaStore']);
    Route::post('case-files/media-delete', [CaseFileController::class, 'mediaDestroy']);
    Route::get('case-files/{id}/data-applications', [CaseFileController::class, 'fetchDataApplication']);
    Route::post('case-files/data-applications', [CaseFileController::class, 'destroyDataApplication']);
    Route::post('case-files/fetch-copy-media', [CaseFileController::class, 'fetchCopyMedia']);
    Route::post('case-files/store-copy-media', [CaseFileController::class, 'storeCopyMedia']);
    Route::get('case-files-recent', [CaseFileController::class, 'recent']);

    // CentagateController
    Route::post('centagate/request-auth', [CentagateController::class, 'requestAuth']);
    Route::post('centagate/rebind-device', [CentagateController::class, 'rebindDevice']);
    Route::post('centagate/request-fido', [CentagateController::class, 'requestFido']);
    Route::post('centagate/request-centoken', [CentagateController::class, 'getCentoken']);
    Route::post('centagate/register-fido', [CentagateController::class, 'registerFido']);
    Route::post('centagate/auth-fido', [CentagateController::class, 'authFido']);
    Route::post('centagate/invalidate', [CentagateController::class, 'invalidateLogin']);

    // CaseFileShareController
    Route::resource('case-file-shares', CaseFileShareController::class)->except(['destroy']);
    Route::post('case-file-shares/filter-investigator', [CaseFileShareController::class, 'filterRole']);
    Route::post('case-file-shares/destroy', [CaseFileShareController::class, 'destroy']);
    Route::post('case-file-shares/expiration-date', [CaseFileShareController::class, 'expire']);
    Route::get('all-case-file-shares', [CaseFileShareController::class, 'allCaseFilesShare']);

    // ReportFileController
    Route::resource('report-files', ReportFileController::class)->except(['show']);
    Route::get('report-files/roles', [ReportFileController::class, 'roles']);

    Route::get('chats/contacts', [ChatController::class, 'contacts']);
    Route::get('chats/contacts/full', [ChatController::class, 'fullcontacts']);
    Route::resource('chats', ChatController::class);

    Route::resource('messages', MessageController::class);

    Route::resource('inquiries', InquiryController::class);

    Route::resource('inquiry-responses', InquiryResponseController::class);

    Route::resource('maintenances', SuperadminMaintenanceController::class);

    Route::resource('menus', MenuController::class);

    // ProfilingController
    Route::resource('profilings', ProfilingController::class);
    Route::get('profilings/{id}/detail', [ProfilingController::class, 'detail']);

    Route::resource('integrated-searches', IntegratedSearchController::class)->except(['update', 'create', 'edit']);
    Route::post('integrated-search/generate-pdf', [IntegratedSearchController::class, 'generatePDF']);
    Route::post('mock-integrated-search', [IntegratedSearchController::class, 'integratedSearchProcess']);
    Route::post('mock-integrated-social-media-search', [IntegratedSearchController::class, 'mockSocialMedia']);
    Route::get('integrated-search/search-result', [IntegratedSearchController::class, 'temporaryProfiling']);
    Route::delete('integrated-search/{id}/search-result', [IntegratedSearchController::class, 'destroyTemporaryProfiling']);
    Route::get('integrated-search/{id}/search-result', [IntegratedSearchController::class, 'fetchProfilingUser']);

    // Data Application
    Route::resource('data-requests', DataApplicationController::class)->except(['show']);
    Route::get('data-requests/in-progress', [DataApplicationController::class, 'inProgress']);
    Route::post('data-requests/approval', [DataApplicationController::class, 'approval']);
    Route::get('data-requests/case-files', [DataApplicationController::class, 'fetchCaseFile']);
    Route::post('data-requests/case-files', [DataApplicationController::class, 'linkToCaseFile']);

    Route::resource('data-requests-history', DataApplicationHistoryController::class);

    Route::put('notifications-update/{id}', [NotificationController::class, 'notificationUpdate']);
    Route::get('notifications/paginate', [NotificationController::class, 'indexPaginated']);

    Route::resource('notifications', NotificationController::class)->except(['show']);

    // UserManualController (Superadmin)
    Route::resource('user-manuals', SuperadminUserManualController::class);

    // UserManualController
    Route::get('user-manuals-all', [UserManualController::class, 'index']);

    // NoteController
    Route::resource('notes', NoteController::class);
    Route::get('latest-notes/{id}', [NoteController::class, 'latestNotes']);
});