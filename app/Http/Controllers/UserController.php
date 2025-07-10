<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        try {
            $users = User::all();

            return response()->json([
                'success' => true,
                'message' => 'Users listed successfully',
                'usersCount' => $users->count(),
                'users' => $users
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => "Failed to delete user",
                'error' => $error->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate(
                [
                    'name' => 'required',
                    'email' => 'required',
                    'password' => 'required',
                ],
                [
                    'name.required' => 'Field name is required',
                    'email.required' => 'Field email is required',
                    'password.required' => 'Field password is required',
                ]
            );

            $user = User::create([
                'name' => $request['name'],
                'email' => $request['email'],
                'password' => bcrypt($request['password']),
            ]);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to register user',
                'error' => $error->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'user' => $user,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $request->validate(
                [
                    'name' => 'required',
                    'email' => 'required',
                    'password' => 'required',
                ],
                [
                    'name.required' => 'Field name is required',
                    'email.required' => 'Field email is required',
                    'password.required' => 'Field password is required',
                ]
            );

            $user = User::findOrFail($id);
            $user->update([
                'name' => $request['name'],
                'email' => $request['email'],
                'password' => bcrypt($request['password']),
            ]);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
                'error' => $error->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'user' => $user,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $userAuth = $request->user();
        $id = $userAuth->id;

        try {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $user = User::findOrFail($id);
            $user->delete();
            return response()->json([
                'success' => true,
                'message' => "User $user->name deleted successfully",
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => "Failed to delete user",
                'error' => $error->getMessage(),
            ], 500);
        }
    }



    public function my(Request $request)
    {
        try{
            return response()->json([
                'success' => true,
                'message' => 'Success while getting user',
                'user' => $request->user()
            ], 200);
        }
        catch(\Exception $error){
            return response()->json([
                'success' => false,
                'message' => "Failed to get user",
                'error' => $error->getMessage(),
            ], 500);
        }
        
    }


    public function publicIndex()
    {

        try {
            $users = User::select('name', 'email', 'slug', 'photo')->get();

            return response()->json([
                'success' => true,
                'message' => 'Users listed successfully',
                'usersCount' => $users->count(),
                'users' => $users
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => "Failed to delete user",
                'error' => $error->getMessage(),
            ], 500);
        }
    }

    public function publicIndexQuery(Request $request)
    {

        $query = trim($request->input('query', ''));

        // 🔒 If both query and tags are empty, return empty result
        if ($query === '') {
            return response()->json([
                'success' => true,
                'message' => 'No search criteria provided',
                'usersCount' => 0,
                'users' => []
            ], 200);
        }

        try {
            $users = User::select('name', 'email', 'slug', 'photo')
                ->where('name', 'like', '%' . $query . '%')
                ->orWhere('email', 'like', '%' . $query . '%')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Users listed successfully',
                'usersCount' => $users->count(),
                'users' => $users
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => "Failed to delete user",
                'error' => $error->getMessage(),
            ], 500);
        }
    }



    public function showBySlug(Request $request, $slug)
    {
        try {
            $userAuth = $request->user();
            $user = User::select('id', 'name', 'email', 'photo')->where('slug', $slug)->firstOrFail();
            $isFollowing = null;


            if ($userAuth !== null) {
                if (DB::table('followers')
                    ->where('user_id', $userAuth->id)
                    ->where('followed_user_id', $user->id)
                    ->exists()
                ) {
                    $isFollowing = true;
                } else {
                    $isFollowing = false;
                }
            } else {
                $isFollowing = false;
            }


            $user->followed = $isFollowing;

            return response()->json([
                'success' => true,
                'message' => 'User listed successfully',
                'userCount' => $user->count(),
                'user' => $user
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => "Failed to select user by slug",
                'error' => $error->getMessage(),
            ], 500);
        }
    }


    public function listFollowers(Request $request)
    {


        $userAuth = $request->user();
        $id = $userAuth->id;
        try {
            $users = DB::table('followers')
                ->join('users', 'followers.user_id', '=', 'users.id')
                ->select('users.id', 'users.name', 'users.email', 'users.slug', 'users.photo')
                ->where('followed_user_id', '=', $id)
                ->get();


            return response()->json([
                'success' => true,
                'message' => "Success with followed users",
                'result' => $users
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => "Failed to list followed users",
                'error' => $error->getMessage(),
            ], 500);
        }
    }

    public function listFollowing(Request $request)
    {

        $userAuth = $request->user();
        $id = $userAuth->id;
        try {
            $users = DB::table('followers')
                ->join('users', 'followers.followed_user_id', '=', 'users.id')
                ->select('users.id', 'users.name', 'users.email', 'users.slug', 'users.photo')
                ->where('user_id', '=', $id)
                ->get();


            return response()->json([
                'success' => true,
                'message' => "Success with followed users",
                'result' => $users
            ],200);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => "Failed to list following users",
                'error' => $error->getMessage(),
            ], 500);
        }
    }
}
