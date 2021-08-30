<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Requests\v1\PostRequest;
use App\Http\Resources\v1\PostResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use function PHPUnit\Framework\isNull;

class PostController extends Controller
{

  public function __construct()
  {
    $this->middleware('auth:api', ['except' => ['index', 'show']]);
  }
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    return PostResource::collection(Post::latest()->paginate());
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(PostRequest $request)
  {
    $request->validated();

    $user = Auth::user();

    $post = new Post();
    $post->user()->associate($user);
    $url_image = $this->upload($request->file('image'));
    $post->image = $url_image;
    $post->title = $request->input('title');
    $post->description = $request->input('description');

    $res = $post->save();

    if ($res) {
      return (response()->json(['message' => 'Post creado satisfactoriamente...'], 201));
    }
    return (response()->json(['message' => 'Error al crear el Post...'], 500));
  }

  private function upload($image)
  {
    $path_info = pathinfo($image->getClientOriginalName());
    $post_path = 'image/post';

    $rename = uniqid() . '.' . $path_info['extension'];
    $image->move(public_path() . "/$post_path", $rename);

    return ("$post_path/$rename");
  }

  /**
   * Display the specified resource.
   *
   * @param  \App\Models\Post  $post
   * @return \Illuminate\Http\Response
   */
  public function show(Post $post)
  {
    return new PostResource($post);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \App\Models\Post  $post
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, Post $post)
  {
    Validator::make($request->all(), [
      'title' => 'max:70',
      'image' => 'image|max:1024',
      'description' => 'max:2000',
    ])->validate();

    if (Auth::id() !== $post->user->id) {
      return response()->json(['message', 'Tu no tienes permiso para actualizar...'], 403);
    }

    if (!empty($request->input('title'))) {
      echo $post;
      $post->title = $request->input('title');
    }

    if (!empty($request->input('description'))) {
      $post->description = $request->input('description');
    }

    if (!empty($request->input('image'))) {
      $url_image = $this->upload($request->file('image'));
      $post->image = $url_image;
    }

    $res = $post->save();

    if ($res) {
      return (response()->json(['message' => 'Post actualizado satisfactoriamente...']));
    }
    return (response()->json(['message' => 'Error al actualizar el Post...'], 500));
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  \App\Models\Post  $post
   * @return \Illuminate\Http\Response
   */
  public function destroy(Post $post)
  {
    $res = $post->delete();
    if ($res) {
      return (response()->json(['message' => 'Post eliminado satisfactoriamente...']));
    }
    return (response()->json(['message' => 'Error al eliminar el Post...'], 500));
  }
}
