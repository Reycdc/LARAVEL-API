<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthenticationController extends Controller {
    // buat funtion login mengarah ke database
    // public function login(Request $request) {
    //     try {
    //         $username = "Muhammad Umar Mansyur";
    //         $password = "umar123.";

    //         if($request->username === $username && $request->password === $password) {
    //             return response()->json([
    //                 'status' => true,
    //                 'message' => 'Berhasil login'
    //             ]);
    //         }
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Pengguna tidak diketahui'
    //         ], 401);
    //     } catch (\Throwable $th) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $th->getMessage()
    //         ]);
    //     }
    // }

    public function login(Request $request) 
    {
        try {

            // $request->validate([
            //     'name' => 'required|string|min:8',
            //     'password' => 'required|string',
            // ], [
            //     'name.required' => 'Nama harus diisi!',
            //     'name.min' => 'Nama minimal 8 karakter',
            // ]);

            $credentials = $request->only('name', 'password');

            if ($token = Auth::attempt($credentials)) {
                // $user = Auth::user();
                $user = User::where('name', $request->name)->first();
                $token = $user->createToken('token')->plainTextToken;
                return response()->json([
                    'status' => true,
                    'message' => 'Berhasil login',
                    'data' => $token,
                    'user' => $user
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'Pengguna tidak diketahui'
            ], 401);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    public function me(Request $request)
    {
        try {
            $user = $request->user();
    
            return response()->json([
                'status' => true,
                'message' => 'Berhasil mendapatkan data pengguna',
                'data' => [
                    'user' => $user,
                    'roles' => $user->getRoleNames(), // Mengambil nama-nama role
                ]
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    public function register(Request $request)
    {
        try {
            $username = $request->name;
            $password = $request->password;
            $password = Hash::make($password);
            $email = $request->email;

            if(!$username || !$password || !$email) {
                return response()->json([
                    'status' => false,
                    'message' => 'Username, password, dan email harus diisi!'
                ], 422);
            }

            $data = User::create([
                'name' => $username,
                'password' => $password,
                'email' => $email
            ]);

            $data->assignRole('customer');
            $data->givePermissionTo('manage profile');

            return response()->json([
                'status' => true,
                'message' => 'Berhasil register pengguna',
                'data' => $data,
                'permission' => $data->getAllPermissions(),
                'role' => $data->getRoleNames(),
                'created_at' => $data->created_at,
                'updated_at' => $data->updated_at
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    public function logout(Request $request)
    {
        try {
            Auth::logout();
            return response()->json([
                'status' => true,
                'message' => 'Berhasil logout'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ]);
        }
    }


}