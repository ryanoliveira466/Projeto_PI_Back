<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });



// PROTECTED routes
Route::middleware('auth:sanctum')->group(function () {

    //Notice: GET /user/{user} will catch everything under /user/*.
    //When you request /user/my, Laravel thinks "my" is a {user} parameter!
    //It tries to find a User with ID = "my" — but "my" is not a number — so everything breaks!
    //THAT'S why it only works when you remove Route::resource().

    Route::get('/user/my', [UserController::class, 'my']);
    Route::post('/user/update', [AuthController::class, 'updateUser']);
    Route::post('/user/change-password', [AuthController::class, 'changePassword']);
    Route::post('/user/post-content', [AuthController::class, 'postContent']);
    Route::get('/user/myProjects', [PostController::class, 'myProjects']);
    Route::get('/user/myProject/{projectSlug}', [PostController::class, 'myProject']);
    Route::post('/user/update-content/{projectSlug}', [AuthController::class, 'updateContent']);
});

// TEST RESTFUL Commands
Route::resource('/user', UserController::class);

// PUBLIC routes
Route::post('/register', [AuthController::class, 'register']);
Route::get('/users/public', [UserController::class, 'publicIndex']); //Search bar user
Route::get('/user/slug/{slug}', [UserController::class, 'showBySlug']); //Profile user public link for each

Route::get('/projects/public', [PostController::class, 'publicIndex']); //Search bar projects
Route::get('/projects/user/{slug}', [PostController::class, 'userProjects']); //Projects of the user public on member page
Route::get('/project/slug/{userSlug}/{projectSlug}', [PostController::class, 'showBySlug']); //Projects user public link for each




// EMAIL
Route::get('/email/verify/{id}/{hash}', function ($id, $hash) {
    $user = User::findOrFail($id);

    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        abort(403, 'Invalid verification link.');
    }

    if (! $user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        event(new Verified($user));
        return redirect('http://127.0.0.1:5501/email-verified-success.html'); // Redirect to your frontend
    }

})->middleware('signed')->name('verification.verify');



// Request password reset link
Route::post('/forgot-password', function (Request $request) {

    $email = (string) $request->input('email');
    $email = preg_replace('/\s+/', '', trim($email));
    if ($request->email !== $email) {
        return response()->json([
            'message' => 'Email must not contain spaces',
        ], 400);
    }

    try {
        $request->validate(
            [
                'email' => 'required|email'
            ],
            [
                'email.required' => 'The email field is required',
                'email.email' => 'The email must be a valid email address',
            ]
        );

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json([
                'success' => true,
                'message' => __($status)
            ])
            : response()->json([
                'success' => false,
                'message' => __($status),
                'error' => __($status),
            ], 400);
    } catch (\Exception $error) {
        return response()->json([
            'success' => false,
            'message' => 'Error while sending email',
            'error' => $error->getMessage(),
        ], 500);
    }
});

// Reset password using token
Route::post('/reset-password', function (Request $request) {

    //Trim is auto for email and name

    $email = (string) $request->input('email');
    $email = preg_replace('/\s+/', '', trim($email));
    if ($request->email !== $email) {
        return response()->json([
            'message' => 'Email must not contain spaces',
        ], 400);
    }

    $password = (string) $request->input('password');
    $password = preg_replace('/\s+/', '', trim($password));
    if ((string) $request->input('password') !== $password) {
        return response()->json([
            'message' => 'Password must not contain spaces',
        ], 400);
    }

    try {
        $request->validate(
            [
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|confirmed',
            ],
            [
                'token.required' => 'The token field is required',
                'email.required' => 'The email field is required',
                'email.email' => 'The email must be a valid email address',
                'password.required' => 'The password field is required',
                'password.confirmed' => 'Passwords must match'

            ]
        );

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json([
                'success' => true,
                'message' => __($status)
            ])
            : response()->json([
                'success' => false,
                'message' => __($status),
                'error' => __($status),
            ], 400);
    } catch (\Exception $error) {
        return response()->json([
            'success' => false,
            'message' => 'Error while resetting password',
            'error' => $error->getMessage(),
        ], 500);
    }
});

