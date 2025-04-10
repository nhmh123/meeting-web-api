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
                'errors' => $validation->errors(),
            ]);
        }

        try {
            if(Auth::attempt(['email'=> $request->email,'password'=> $request->password])) {    
                $user = Auth::user();
                return response()->json([
                    'success'=>true,
                    'message'=>'Đăng nhập thành công',
                    'data'=>$user,
                    'token'=>$user->createToken('auth_token')->plainTextToken,
                ]);
            }else{
                throw new \Exception('Tài khoản hoặc mật khẩu không đúng');
            }

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ]);
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
                'errors' => $validation->errors(),
            ]);
        }

        try {
            $user = User::create([
                'name'=>$request->name,
                'email'=>$request->email,
                'password'=>password_hash($request->password, PASSWORD_DEFAULT),
            ]);

            return response()->json([
                'success'=>true,
                'message'=>'Đăng ký thành công',
                'data'=>$user,
                'token'=>$user->createToken('auth_token')->plainTextToken,
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
            if(Auth::check()){
                $request->user()->tokens()->delete();
                Auth::logout();
                return response()->json([
                    'success'=>true,
                    'message'=>'Đăng xuất thành công'
                ]);
            }else{
                throw new \Exception('Chưa xác thực');
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ]);
        }
    }
}
