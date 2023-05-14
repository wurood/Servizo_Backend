<?php
namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response(['message' => "User Name And Password Are Required", 'errors' => $validator->errors()], 400);
        }
        try {
            $credentials = $request->only('email', 'password');

            $token = Auth::attempt($credentials);
            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized',
                ], 401);
            }

            $user = Auth::user();
        } catch (Exception $e) {
            return response(['status' => false, 'message' => "Login failed", 'errors' => $e], 400);
        }
        return response()->json([
            'status' => 'success',
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);

    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'address' => 'required|string|max:255',
            'phone_number' => 'required|numeric',
            'type' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response(['message' => "Validation Errors", 'errors' => $validator->errors()], 400);
        }
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'address' => $request->address,
                'phone_number' => $request->phone_number,
                'type' => $request->type,

            ]);
        } catch (Exception $e) {
            return response(['status' => false, 'message' => "Registration failed", 'errors' => $e], 400);
        }

        $token = Auth::login($user);
        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorization' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

    public function get(Request $request)
    {
        $user = $request->user();
        $id = $user->id;
        try {
            $user = User::find($id);

        } catch (\Exception $e) {
            return response(['status' => false, 'message' => "Get User Failed", 'errors' => $e], 400);
        }
        return response()->json([
            'status' => 'User Fetched Successfully',
            'user' => $user,
        ]);
    }

    public function update(Request $request)
    {
        if ($request->name != null) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|min:3|max:50|unique:users',
                'id' => 'required'

            ]);

            if ($validator->fails()) {
                return response(['message' => "Validation Errors", 'errors' => $validator->errors()], 400);
            }
        }
        try {
            User::where('id', $request->id)->update(request()->all());
            $user = User::find($request->id);

        } catch (Exception $e) {
            return response(['status' => false, 'message' => $e], 400);
        }


        return response(['message' => "User Updated Successfully", 'data' => $user]);
    }

    public function delete(Request $request)
    {
        $dataReq = $request->all();
        $id = $dataReq['id'];
        $data = User::where([['id', '=', $id]])->select('name')->get();
        try {

            $isDeleted = User::where('id', $id)->firstorfail()->delete();

        } catch (Exception $e) {
            return response(['status' => false, 'message' => $e], 400);
        }

        return response()->json(['message' => "User Deleted Successfully", 'data' => $isDeleted], 200);


    }

}
