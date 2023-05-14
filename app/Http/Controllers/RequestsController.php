<?php

namespace App\Http\Controllers;

use App\Models\Requests;
use Illuminate\Http\Request;
use Validator;
use Exception;
class RequestsController extends Controller
{

    public function get(Request $request)
    {
        try {

            $requests = Requests::all();

        } catch (Exception $e) {
            return response(['status' => false, 'message' => "Get Requests Failed", 'errors' => $e], 400);
        }

        return response(['message' => "Requests fetched Successfully", 'messages' => $requests], 200);
    }


    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_name' => 'required|string',
            'order_number' => 'required|numeric|min:1',
            'price' => 'required|numeric|min:1',
            'description' => 'required|string|min:6'
        ]);

        if ($validator->fails()) {
            return response(['message' => "All Fields are required", 'errors' => $validator->errors()], 400);
        }

        $input = $request->all();
        try {
            $request = Requests::create($input);
        } catch (Exception $e) {
            return response(['status' => false, 'message' => "Add request failed!"], 400);
        }

        return response(['message' => "Request added Successfully", 'data' => $request], 200);
    }

    public function show(Request $request)
    {
        $id = $request->id;
        try {
            $request = Requests::find($id);

        } catch (Exception $e) {
            return response(['status' => false, 'message' => "Get Request Failed", 'errors' => $e], 400);
        }
        return response(['message' => "Request fetched Successfully", 'data' => $request], 200);
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
            Requests::where('id', $request->id)->update(request()->all());
            $Request = Requests::find($request->id);

        } catch (Exception $e) {
            return response(['status' => false, 'message' => $e], 400);
        }


        return response(['message' => "Request Updated Successfully", 'data' => $Request]);
    }

    public function delete(Request $request)
    {
        $id = $request->id;
        try {

            $isDeleted = Requests::where('id', $id)->firstorfail()->delete();

        } catch (Exception $e) {
            return response(['status' => false, 'message' => $e], 400);
        }
        return response(['message' => "Request Deleted Successfully", 'data' => $isDeleted], 200);
    }
}
