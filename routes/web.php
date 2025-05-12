<?php


use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/test-email', function () {
    $details = [
        'title' => 'Test Email from Laravel',
        'body' => 'This is a test email sent via Mailtrap in Laravel 10.'
    ];

    Mail::raw($details['body'], function ($message) {
        $message->to('test@example.com')
            ->subject('Test Mail from Laravel');
    });

    return 'Test email sent (check your Mailtrap inbox)';
});


// Sanctum CSRF endpoint (always in web)
Route::get('/sanctum/csrf-cookie', function () {
    return response()->noContent();
});


//Cookies
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
