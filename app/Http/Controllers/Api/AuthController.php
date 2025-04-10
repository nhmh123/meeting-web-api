<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request) {
        $validation = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'success' => false,
                'data' => $validation->errors()->all(),
            ],422);
        }

        try {
            $user = User::where('email', $request->email)->first();
            
            if ($user && password_verify($request->password, $user->password)) {
                $token = $user->createToken('auth_token')->plainTextToken;
                $user['token'] = $token;
                
                return response()->json([
                    'success' => true,
                    'message' => 'Đăng nhập thành công',
                    'data' => $user,
                ]);
            }
            
            throw new \Exception('Tài khoản hoặc mật khẩu không đúng');

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 401);
        }
    }

    public function register(Request $request)
    {

        $validation = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'password' => 'required|min:6|confirmed',
            'email' => 'required|email|unique:users'
        ]);

        if ($validation->fails()) {
            return response()->json([
                'success' => false,
                'data' => $validation->errors()->all(),
            ],422);
        }

        try {
            $user = User::create([
                'name'=>$request->name,
                'email'=>$request->email,
                'password'=>password_hash($request->password, PASSWORD_DEFAULT),
            ]);

            $user['token'] = $user->createToken('auth_token')->plainTextToken; // ['token'=>

            return response()->json([
                'success'=>true,
                'message'=>'Đăng ký thành công',
                'data'=>$user,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function logout(Request $request) {
        try {
            $request->user()->tokens()->delete();
            return response()->json([
                'success'=>true,
                'message'=>'Đăng xuất thành công'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function show(){
        $user = User::where('id',Auth::user()->id)->first();
        return response()->json([
           'success'=>true,
           'message'=>'Lấy thông tin thành công',
            'data'=>$user,  
        ]);
    }

    
}
