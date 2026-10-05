<?php

declare(strict_types=1);

use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

//Route::middleware([
//    'web',
//    'auth:sanctum',
//    InitializeTenancyByDomain::class,
//    PreventAccessFromCentralDomains::class,
//])->group(function () {
//    Route::get('/', function () {
//        return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
//    });
//});

//Route::middleware([
//    'guest',
//    InitializeTenancyByDomain::class,
//    PreventAccessFromTenantDomains::class,
//])->group(function () {
//
//    Route::post('/tenant/register', [RegisteredUserController::class, 'store'])
//        ->name('tenant.register');
//
//    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
//        ->name('tenant.login');
//
//    Route::post('/tenant/forgot-password', [PasswordResetLinkController::class, 'store'])
//        ->name('password.email');
//
//    Route::post('/reset-password', [NewPasswordController::class, 'store'])
//        ->name('tenant.password.store');
//});
//
//
//
//Route::middleware([
//    'auth',
//    InitializeTenancyByDomain::class,
//    PreventAccessFromCentralDomains::class,
//])->group(function () {
//
//    Route::get('/tenant/verify-email/{id}/{hash}', VerifyEmailController::class)
//        ->middleware(['signed', 'throttle:6,1'])
//        ->name('tenant.verification.verify');
//
//    Route::post('/tenant/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
//        ->middleware(['throttle:6,1'])
//        ->name('tenant.verification.send');
//
//    Route::post('/tenant/logout', [AuthenticatedSessionController::class, 'destroy'])
//        ->name('tenant.logout');
//
//});

//Route::middleware(['web',
//    InitializeTenancyByDomain::class,
//    PreventAccessFromCentralDomains::class,
//])->prefix('api')->group(function () {
//
//    Route::get('/logged-in-user', [UserController::class, 'loggedInUser']);
//});
