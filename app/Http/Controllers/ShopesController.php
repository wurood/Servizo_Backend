<?php

namespace App\Http\Controllers;

use App\Models\Shopes;
use Illuminate\Http\Request;
use Validator;
use Exception;
class ShopesController extends Controller
{

    public function get(Request $request)
    {
        try {

            $shopes = Shopes::all();

        } catch (Exception $e) {
            return response(['status' => false, 'message' => "Get Shopes Failed", 'errors' => $e], 400);
        }

        return response(['message' => "Shopes fetched Successfully", 'messages' => $shopes], 200);
    }


    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'city' => 'required|string',
            'facebook_link' => 'required|string|min:20',
            'instagram_link' => 'required|string|min:20',
            'description' => 'required|string|min:6'
        ]);

        if ($validator->fails()) {
            return response(['message' => "All Fields are required", 'errors' => $validator->errors()], 400);
        }

        $input = $request->all();
        try {
            $shope = Shopes::create($input);
        } catch (Exception $e) {
            return response(['status' => false, 'message' => "Add Shope failed!", 'errors' => $e], 400);
        }

        return response(['message' => "Shope added Successfully", 'data' => $shope], 200);
    }

    public function show(Request $request)
    {
        $id = $request->id;
        try {
            $shope= Shopes::find($id);

        } catch (Exception $e) {
            return response(['status' => false, 'message' => "Get Shope Failed", 'errors' => $e], 400);
        }
        return response(['message' => "Shope fetched Successfully", 'data' => $shope], 200);
    }


    public function update(Request $request)
    {
            $validator = Validator::make($request->all(), [
                'id' => 'required'
            ]);

            if ($validator->fails()) {
                return response(['message' => "Validation Errors", 'errors' => $validator->errors()], 400);
            }

        try {
            Shopes::where('id', $request->id)->update(request()->all());
            $shope = Shopes::find($request->id);

        } catch (Exception $e) {
            return response(['status' => false, 'message' => $e], 400);
        }


        return response(['message' => "Shope Updated Successfully", 'data' => $shope]);
    }

    public function delete(Request $request)
    {
        $id = $request->id;
        try {

            $isDeleted = Shopes::where('id', $id)->firstorfail()->delete();

        } catch (Exception $e) {
            return response(['status' => false, 'message' => $e], 400);
        }
        return response(['message' => "Shope Deleted Successfully", 'data' => $isDeleted], 200);
    }
}
