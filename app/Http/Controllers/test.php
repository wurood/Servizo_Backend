<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\companies;
use Validator;
use Exception;
use App\Models\Cars;
use App\Mail\ContactForm;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register','refresh']]);
    }

    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response(['message' => "User Name And Password Are Required", 'errors' => $validator->errors()], 400);
        }


        try {

            $credentials = $request->only('email', 'password');
            $myTTL = 60; //minutes

            auth()->factory()->setTTL($myTTL);
            $token = Auth::attempt($credentials);
            if (!$token) {
                //add faild to log table
                activity('login')
                    ->withProperties(['message' => "The Email Or Password Is Incorrectg"])
                    ->log('Login Failed by ' . $request->email);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized',
                ], 401);
            }

            $user = Auth::user();
            $userData = $request->user();

            if ($user) {
                activity('login')
                    ->performedOn($user)
                    ->causedBy(Auth::user())
                    ->withProperties(['name' => $userData->name, 'email' => $userData->email])
                    ->log('Login Successful by ' . $user->name);
            }

        } catch (\Exception $e) {
            return response(['status' => false, 'message' => "Login failed", 'errors' => $e], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User Login successfully',
            'user' => $user,
            'authorisation' => [
                'accessToken' => $token,
                'type' => 'bearer',
                'expire_in' => auth()->factory()->getTTL()*60
            ]
        ]);

    }

    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'personal_id' =>'required|string|unique:users|min:9',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'mobile_number' => 'required|min:10|max:10',
            'car_id'       => 'required|string|min:10',
            'car_model'    => 'required|min:4',
            'car_company'  => 'required|string|max:255',
            'car_price'    => 'required|numeric|min:1000|not_in:',
            'engine_size'  => 'required|numeric|min:500|not_in:',
            'years_of_driving'  => 'required|numeric|min:0|not_in:',

        ]);

        if ($validator->fails()) {
            return response(['message' => "Validation Errors", 'errors' => $validator->errors()], 400);
        }

        $input = $request->all();
        $input['password'] = Hash::make($input['password']);

        try {
            $user = User::create($input);

            activity('register')
                ->performedOn($user)
                ->causedBy($user)
                ->withProperties(['email' => $request->email])
                ->log('New User ' . $user->email . ' has been registered');
        } catch (\Exception $e) {
            return response(['status' => false, 'message' => "Registration failed", 'errors' => $e], 400);
        }

        $token = Auth::login($user);
        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user ,
            'authorisation' => [
                'accessToken' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function logout()
    {

        activity('logout')
            ->log('User ' . Auth::user()->name . ' has been Loged Out');

        Auth::logout();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh(Request $request)
    {

        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'refreshToken' => Auth::refresh(),
                'type' => 'bearer',
                'refresh_ttl' => 4600, // 2 hours
            ]
        ]);


    }

    public function index()
    {
        try {

            $users = User::all();

        } catch (\Exception $e) {
            return response(['status' => false, 'message' => "Get Users Failed", 'errors' => $e], 400);
        }

        return response()->json([
            'status' => 'ALL Users Fetched Successfully',
            'users' => $users,
        ]);
    }

    public function show(Request $request)
    {
        $user = $request->user();
        $id = $user->id;
        try {

            $user = User::find($id);
            activity('show')
                ->performedOn($user)
                ->causedBy($user)
                ->log('User ' . $user->name . ' has been Fetched Successful');

        } catch (\Exception $e) {
            return response(['status' => false, 'message' => "Get User Failed", 'errors' => $e], 400);
        }

        return response()->json([
            'status' => 'User Fetched Successfully',
            'user' => $user,
        ]);
    }

    public function showUsers()
    {
        try {

            $users = User::all();
            activity('showUsers')
                ->log('All Users  has been Fetched Successful');

        } catch (\Exception $e) {
            return response(['status' => false, 'message' => "Get Users Failed", 'errors' => $e], 400);
        }

        return response()->json([
            'status' => 'ALL Users Fetched Successfully',
            'users' => $users,
        ]);

    }

    public function showUsersbyCompany(Request $request)
    {
        $companyId = $request->company_id;

        try {
            $data = User::select('users.id', 'users.email', 'users.name')
                ->where('company_id', '=', $companyId)->get();

            $comapny = Companies::find($companyId);

            activity('showUsersbyCompany')
                ->log('Users of company ' . $comapny->name . ' has been Fetched Successful');

        } catch (\Exception $e) {
            return response(['status' => false, 'message' => "Get Users By comapny Failed", 'errors' => $e], 400);
        }


        return response(['message' => "Users Fetched Successfully", 'data' => $comapny]);

    }


    public function add(Request $request)
    {
        $input = $request->all();
        $input['password'] = Hash::make("Admin@paltel");
        try {
            $user = User::create($input);
            activity('add user')
                ->performedOn($user)
                ->causedBy($user)
                ->withProperties(['name' => $request->name])
                ->log('New User ' . $user->name . ' has been created');
        } catch (Exception $e) {
            return response(['status' => false, 'message' => "Username or Email already exist!"], 400);
        }

        return response(['message' => "User Added Successfully", 'data' => $user->id], 200);
    }

    public function update(Request $request)
    {
        if ($request->name != null) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|min:3|max:50|unique:users'
            ]);

            if ($validator->fails()) {
                return response(['message' => "Validation Errors", 'errors' => $validator->errors()], 400);
            }
        }
        try {
            User::where('id', $request->id)->update(request()->all());
            $user = User::find($request->id);

            activity('update user')
                ->performedOn($user)
                ->causedBy($user)
                ->withProperties(['updated data' => $request])
                ->log(' User ' . $user->name . ' has been Updated');

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

            activity('delete user')
                ->log(' User ' . $data[0]->name . ' has been Deleted');

        } catch (Exception $e) {
            return response(['status' => false, 'message' => $e], 400);
        }

        return response()->json(['message' => "User Deleted Successfully", 'data' => $isDeleted], 200);


    }





    public function store(Request $request)
    {
        $dataReq = $request->all();
        $folder_name=$dataReq['personal_id'];
        $request->validate(['images.*' => 'mimes:doc,pdf,docx,zip,jpeg,png,jpg,gif,svg',]);
        try{
            $paths = [];

        if ($request->hasFile('images')) {
            $images = $request->file('images');
            foreach ($images as $image) {
            $filename = $image->getClientOriginalName();
            $path = $image->storeAs("public/$folder_name", $filename);
            $paths[] = $path;
        }

        }
    } catch (Exception $e) {
            return response(['status' => false, 'message' => $e], 400);
            }

            return response()->json(['message' => "Images added Successfully"], 200);
    }

            public function sendEmail(Request $request)
        {
            $data = $request->validate([
                'name' => 'required',
                'email' => 'required|email',
                'message' => 'required',
            ]);

            Mail::to('wuroodj95@gmail.com')->send(new ContactForm($data));

            return response()->json(['message' => 'Email sent successfully']);
        }
