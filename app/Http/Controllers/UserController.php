<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Responses\ApiResponse;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(StoreUserRequest $request):JsonResponse
    {
        try {
            /* validar los datos */
            $newUser = $request->validated();

            /* encriptar el password ya validado */
            $newUser['password'] = Hash::make($newUser['password']);

            /* crear usuario */
            $user = User::create($newUser);

            /* comprobar si NO se creó exitosamente */
            if (!$user) {
                return ApiResponse::Error('Registration Failed');
            }

            $userData = [
                'idUsuario' => $user->id,
                'nombreCompleto' => $user->name,
                'correo' => $user->email,
                'rol_id' => $user->rol_id,
                'rolDescripcion' => $user->rols->name ?? 'Sin rol asignado',
            ];

            /* Estructurar la respuesta */
            $data = [
                'user' => $userData,
                // 'token' => $token, // incluir el token (pausado)
            ];

            /* devolver los datos */
            return ApiResponse::Success('Registration Successfully', $data, 201);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function login(Request $request)
    {
        try {

            $loginData = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string', 'min:8'],
            ]);
           $user = User::where('email', $loginData['email'])->first();
           if(!$user){
               return ApiResponse::NotFound('Email Failed',[],200);
           }
           if (!Hash::check($loginData['password'], $user->password)) {
               return ApiResponse::NotFound('Password  Failed',[],200);
           }

            $role = $user->rols ? $user->rols->name : 'Sin rol asignado';
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'rolDescripcion' => $role,
            ];
            return ApiResponse::Success('Login Successfully', $userData, 201);

        } catch (\Error $e) {

            return ApiResponse::Error('An error occurred', null, 500);
        }
    }

    public function listUsers() {
        try {

            $users = User::where('active', 1)->with('rols')->get();


            if (!$users) {
                return ApiResponse::Error('Users not found');
            }

            $userList = $users->map(function ($user) {
                return [
                    'name' => $user->name,
                    'email' => $user->email,
                    'rolDescripcion' => $user->rols->name ?? 'No role assigned',
                    'active' => $user->active,
                    'password' => $user->password,
                    'rol_id' => $user->rols->id,
                ];
            });

            return ApiResponse::Success('Users List', $userList, 200);
        } catch (\Exception $e) {
            return ApiResponse::Error($e->getMessage());
        }
    }

    public function profile(Request $request)
    {
        try {
            /* Obtener el token */
            // $token = $request->bearerToken();

            $data = [
                'user' => $request->user(),
                //'token' => $token,
                //'isAdmin' => $request->user()->isAdmin,
            ];

            return ApiResponse::Success('User Profile', $data);
        } catch (\Error $e){
            return ApiResponse::Error($e->getMessage());
        }
    }
}
