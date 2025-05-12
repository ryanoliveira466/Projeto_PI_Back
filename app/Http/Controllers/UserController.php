<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

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
    public function destroy(string $id)
    {
        try {
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
        return response()->json([
            'success' => true,
            'user' => $request->user()
        ]);
    }


    public function publicIndex()
    {

        try {
            $users = User::select('name', 'email', 'slug')->get();

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



    public function showBySlug($slug)
    {
        try {
            $user = User::select('name', 'email', 'photo')->where('slug', $slug)->firstOrFail();
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
}
