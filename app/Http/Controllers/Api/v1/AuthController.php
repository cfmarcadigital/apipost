<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:api', ['except' => ['login', 'register']]);
  }

  public function login(Request $request)
  {
    /*$validator = Validator::make($request->all(), [
      'email' => 'required|email',
      'password' => 'required|string|min:8',
    ]);*/

    $credentials = request(['email', 'password']);

    /*if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }*/

    if (!$token = auth()->attempt($credentials)) {
      return response()->json(['error' => 'Unauthorized'], 401);
    }

    return $this->respondWithToken($token);
    //return view('home');
  }

  public function registro()
  {
    return view('register');
  }


  public function register(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string|between:5,50',
      'email' => 'required|string|email|max:100|unique:users',
      'password' => 'required|string|confirmed|min:8',
    ]);

    if ($validator->fails()) {
      return response()->json($validator->errors()->toJson(), 400);
    }

    $user = User::create(array_merge(
      $validator->validated(),
      ['password' => bcrypt($request->password)]
    ));

    return response()->json([
      'message' => 'Usuario registrado satisfactoriamente',
      'user' => $user
    ], 201);


    /*$user2 = new AuthController();
    $user2->login($request);
    return view('home');*/
  }

  public function logout()
  {
    auth()->logout();

    return response()->json(['message' => 'Usuario finalizo sesion correctamente...']);
  }

  public function refresh()
  {
    return $this->createNewToken(auth()->refresh());
  }

  public function userProfile()
  {
    return response()->json(auth()->user());
  }

  protected function createNewToken($token)
  {
    return response()->json([
      'access_token' => $token,
      'token_type' => 'bearer',
      'expires_in' => auth()->factory()->getTTL() * 60,
      'user' => auth()->user()
    ]);
  }

  protected function respondWithToken($token)
  {
    return response()->json([
      'access_token' => $token,
      'token_type' => 'bearer',
      'expires_in' => auth()->factory()->getTTL() * 60,
    ]);
  }
}
