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

        try {
            $users = Post::all();

            return response()->json([
                'success' => true,
                'message' => 'Users listed successfully',
                'usersCount' => $users->count(),
                'users' => $users
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => "Failed to list user",
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
    public function destroy(string $slug)
    {
        try {
            $post = Post::findOrFail($slug);
            $post->delete();
            return response()->json([
                'success' => true,
                'message' => "User $post->name deleted successfully",
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => "Failed to delete post",
                'error' => $error->getMessage(),
            ], 500);
        }
    }



    //Get all posts from user
    public function myProjects(Request $request)
    {
        try {
            $user = $request->user();
            $projects = Post::select('id', 'name', 'description', 'javascript', 'css', 'html', 'slug', 'photo', 'tags', 'likes', 'views')->where('user_id', $user->id)->get();

            // Fetch liked post IDs for the current user
            $likedPostIds = $user
                ? $user->likedPosts()->pluck('post_id')->toArray()
                : [];

            // Add "liked" flag to each post
            $projects->transform(function ($post) use ($likedPostIds) {
                $post->liked = in_array($post->id, $likedPostIds);
                return $post;
            });

            return response()->json([
                'success' => true,
                'message' => 'Posts from user profile listed successfully',
                'projectsCount' => $projects->count(),
                'projects' => $projects,
                'userSlug' => $user->slug
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => "Failed to select posts of user profile",
                'error' => $error->getMessage(),
            ], 500);
        }
    }



    //Post for edit
    public function myProject(Request $request, $projectSlug)
    {
        try {
            $user = $request->user();
            $project = Post::select('name', 'description', 'javascript', 'css', 'html', 'slug', 'photo', 'tags')->where('user_id', $user->id)->where('slug', $projectSlug)->firstOrFail();
            return response()->json([
                'success' => true,
                'message' => 'Post for editing from user profile listed successfully',
                'projectsCount' => $project->count(),
                'project' => $project,
                'userSlug' => $user->slug,
                'userName' => $user->name,
                'userImage' => $user->photo
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => "Failed to select Post for editing from user profile",
                'error' => $error->getMessage(),
            ], 500);
        }
    }



    //
    public function publicIndex()
    {

        try {
            $posts = Post::with(['user:id,name,slug,photo,email'])
                ->select('user_id', 'name', 'description', 'javascript', 'css', 'html', 'slug', 'photo')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Posts listed successfully',
                'postsCount' => $posts->count(),
                'posts' => $posts
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => "Failed to list posts",
                'error' => $error->getMessage(),
            ], 500);
        }
    }



    //With query
    public function publicIndexQuery(Request $request)
    {
        $query = trim($request->input('query', ''));
        $tags = trim($request->input('tags', ''));
        $tagList = array_filter(array_map('trim', explode(',', $tags)));

        if ($query === '' && empty($tagList)) {
            return response()->json([
                'success' => true,
                'message' => 'No search criteria provided',
                'postsCount' => 0,
                'posts' => []
            ], 200);
        }

        try {
            $user = $request->user(); // May be null if unauthenticated

            $posts = Post::with(['user:id,name,slug,photo,email'])
                ->select('id', 'user_id', 'name', 'description', 'javascript', 'css', 'html', 'slug', 'photo', 'tags', 'likes', 'views')
                ->when($query !== '', function ($q) use ($query) {
                    $q->where('name', 'like', '%' . $query . '%');
                })
                ->when(!empty($tagList), function ($q) use ($tagList) {
                    $q->where(function ($q2) use ($tagList) {
                        foreach ($tagList as $tag) {
                            $q2->orWhere('tags', 'like', '%' . $tag . '%');
                        }
                    });
                })
                ->get();

            // Fetch liked post IDs for the current user
            $likedPostIds = $user
                ? $user->likedPosts()->pluck('post_id')->toArray()
                : [];

            // Add "liked" flag to each post
            $posts->transform(function ($post) use ($likedPostIds) {
                $post->liked = in_array($post->id, $likedPostIds);
                return $post;
            });

            return response()->json([
                'success' => true,
                'message' => 'Posts listed successfully',
                'postsCount' => $posts->count(),
                'posts' => $posts
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => "Failed to list posts",
                'error' => $error->getMessage(),
            ], 500);
        }
    }




    public function userProjects(Request $request)
    {

        $slug = trim($request->input('slug', ''));

        try {
            $userAuth = $request->user(); // May be null if unauthenticated
            $user = User::select('id')->where('slug', $slug)->firstOrFail();;
            $posts = Post::select('id', 'name', 'description', 'javascript', 'css', 'html', 'slug', 'photo', 'tags', 'likes', 'views')->where('user_id', $user->id)->get();

            // Fetch liked post IDs for the current user
            $likedPostIds = $userAuth
                ? $userAuth->likedPosts()->pluck('post_id')->toArray()
                : [];

            // Add "liked" flag to each post
            $posts->transform(function ($post) use ($likedPostIds) {
                $post->liked = in_array($post->id, $likedPostIds);
                return $post;
            });

            return response()->json([
                'success' => true,
                'message' => 'Posts from user listed successfully by slug',
                'projectsCount' => $posts->count(),
                'projects' => $posts,
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => "Failed to select posts of user by slug",
                'error' => $error->getMessage(),
            ], 500);
        }
    }



    public function showBySlug($userSlug, $projectSLug)
    {
        try {
            $user = User::select('id', 'name', 'photo')->where('slug', $userSlug)->firstOrFail();;
            $project = Post::select('name', 'description', 'javascript', 'css', 'html', 'photo')->where('slug', $projectSLug)->where('user_id', $user->id)->firstOrFail();
            return response()->json([
                'success' => true,
                'message' => 'Post from user listed successfully by slug',
                'projectCount' => $project->count(),
                'project' => $project,
                'userName' => $user->name,
                'userImage' => $user->photo
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => "Failed to select post of user by slug",
                'error' => $error->getMessage(),
            ], 500);
        }
    }
}
