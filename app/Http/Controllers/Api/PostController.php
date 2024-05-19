<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

//import model posts
use App\Models\Post;
//import PostResource
use App\Http\Resources\PostResource;
//import facades storage
use Illuminate\Support\Facades\Storage;
//import validator
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //get all posts
        $posts = Post::orderBy('title', 'asc')->paginate(5);

        //return collection of posts as a resource
        return new PostResource(true, 'List Data Post', $posts);
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
        //define validation rule
        $validator = Validator::make($request->all(), [
            'image'  => 'required|image|mimes:png,jpg,jpeg,gif,svg|max:2048',
            'title'  => 'required|string',
            'content' => 'required',
        ]);

        //check if validator fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //upload image
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        //create post
        $post = Post::create([
            'image' => $image->hashName(),
            'title' => $request->title,
            'content' => $request->content,
        ]);

        //return response
        return new PostResource(true, 'Data Berhasil Di Input', $post);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //find post by ID
        $post = Post::find($id);

        //return single post as a resource
        return new PostResource(true, 'Detail Data Post', $post);
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
    public function update(Request $request, $id)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'content' => 'required'
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //find post ID
        $post = Post::find($id);

        //check image is not empty
        if ($request->hasFile('image')) {

            //uplaod image
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            //delete old image
            Storage::delete('public/posts' . basename($post->image));

            //update post with new image
            $post->update([
                'image' => $image->hashName(),
                'title' => $request->title,
                'content' => $request->content
            ]);
        } else {
            //update post without image
            $post->update([
                'title' => $request->title,
                'content' => $request->content
            ]);
        }
        //return response
        return new PostResource(true, 'Data Post berhasil di update', $post);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //find post by ID
        $post = Post::find($id);

        //delete image
        Storage::delete('public/posts/' . basename($post->image));

        //delete post
        $post->delete();

        //return response
        return new PostResource(true, 'data post berhasil dihapus', null);
    }
}
