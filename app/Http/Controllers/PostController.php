<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }



    //Get all posts from user
    public function myProjects(Request $request)
    {
        try {
            $user = $request->user();
            $projects = Post::select('name', 'description', 'javascript', 'css', 'html', 'slug', 'photo')->where('user_id', $user->id)->get();;
            return response()->json([
                'success' => true,
                'message' => 'Posts from user profile listed successfully',
                'projectsCount' => $projects->count(),
                'projects' => $projects,
                'userSlug' => $user->slug
            ],200);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => "Failed to select posts of user profile",
                'error' => $error->getMessage(),
            ],500);
        }
    }



    //Post for edit
    public function myProject(Request $request, $projectSlug)
    {
        try {
            $user = $request->user();
            $project = Post::select('name', 'description', 'javascript', 'css', 'html', 'slug', 'photo')->where('user_id', $user->id)->where('slug', $projectSlug)->firstOrFail();
            return response()->json([
                'success' => true,
                'message' => 'Post for editing from user profile listed successfully',
                'projectsCount' => $project->count(),
                'project' => $project,
                'userSlug' => $user->slug,
                'userName' => $user->name,
                'userImage' => $user->photo
            ],200);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => "Failed to select Post for editing from user profile",
                'error' => $error->getMessage(),
            ],500);
        }
    }



    public function userProjects($slug)
    {
        try {
            $user = User::select('id')->where('slug', $slug)->firstOrFail();;
            $projects = Post::select('name', 'description', 'javascript', 'css', 'html', 'slug', 'photo')->where('user_id', $user->id)->get();
            return response()->json([
                'success' => true,
                'message' => 'Posts from user listed successfully by slug',
                'projectsCount' => $projects->count(),
                'projects' => $projects
            ],200);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => "Failed to select posts of user by slug",
                'error' => $error->getMessage(),
            ],500);
        }
    }



    public function showBySlug($userSlug, $projectSLug)
    {
        try {
            $user = User::select('id','name','photo')->where('slug', $userSlug)->firstOrFail();;
            $project = Post::select('name', 'description', 'javascript', 'css', 'html' , 'photo')->where('slug', $projectSLug)->where('user_id', $user->id)->firstOrFail();
            return response()->json([
                'success' => true,
                'message' => 'Post from user listed successfully by slug',
                'projectCount' => $project->count(),
                'project' => $project,
                'userName' => $user->name,
                'userImage' => $user->photo
            ],200);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => "Failed to select post of user by slug",
                'error' => $error->getMessage(),
            ],500);
        }
    }

}
