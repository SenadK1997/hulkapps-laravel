<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register(Request $request)
    {
        $validator = $this->validateRegistration($request->all());

        $user = $this->createUser($validator->validated());

        return $this->respondWithMessageAndData('User successfully registered', $user, 201);
    }

    public function login(Request $request)
    {
        $validator = $this->validateLogin($request->all());
        if (!$token = auth()->attempt($validator->validated())) {
            return $this->respondWithError('Unauthorized', 401);
        }
        return $this->createNewTokenResponse($token);
    }
    public function logout()
    {
        auth()->logout();
        
        return response()->json([
            'message' => 'User logged out'
        ], 201);
    }
    public function profile()
    {
        return response()->json(auth()->user());
    }

    private function validateRegistration(array $data)
    {
        return Validator::make($data, [
            'name' => 'required',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed|min:6'
        ]);
    }

    private function createUser(array $data)
    {
        return User::create(array_merge($data, ['password' => bcrypt($data['password'])]));
    }

    private function validateLogin(array $data)
    {
        return Validator::make($data, [
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);
    }

    private function createNewTokenResponse($token)
    {
        return $this->respondWithJson([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
            'user' => auth()->user(),
        ]);
    }

    private function respondWithError($message, $statusCode)
    {
        return response()->json(['error' => $message], $statusCode);
    }

    private function respondWithMessageAndData($message, $data, $statusCode)
    {
        return response()->json(['message' => $message, 'data' => $data], $statusCode);
    }

    private function respondWithJson($data, $statusCode = 200)
    {
        return response()->json($data, $statusCode);
    }
}
