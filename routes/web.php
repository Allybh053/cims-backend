<?php

use App\Http\Controllers\MailController;
use App\Mail\FirstTimeMail;
use App\Mail\TestMail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/pingconnection', function () {

    $clientIp = request()->ip();

    \Log::debug($clientIp);
    $headers = [
        'X-Forwarded-For',
        'X-Real-IP',
        'CF-Connecting-IP',
        'X-ProxyUser-IP'
    ];

    foreach ($headers as $header) {
        if ($ip = request()->header($header)) {
               $clientIp = $ip;
        }
    }

    \Log::debug("==============");
    \Log::debug($clientIp);

    echo "connection success";
});

Route::get('/', function () {
    echo 'Cybercrime Integrated Management System (CIMS)';
    // return ['Laravel' => app()->version()];

////    $contents = File::get(base_path('app/Dummy/twitter.json'));
////    $contents = File::get(base_path('app/Dummy/fb_json_sample.json'));
////
////    $data = json_decode($contents, true);
//
//    $data = '{"status":"SUCCESS","request_id":"d7023053-4024-4e4e-9fc5-0305a3a216ae","query":"scam","message":"Request accepted"}';
//
//    $dataJson = json_decode($data, true);
//
//    dd($dataJson['request_id']);

});

require __DIR__.'/auth.php';


// Route::get('/testmail', [MailController::class, 'sendMail']);

 Route::get('/first-time-mail', function () {
     $user = User::find(9);
     Mail::to($user)->send(new FirstTimeMail($user));
 });
