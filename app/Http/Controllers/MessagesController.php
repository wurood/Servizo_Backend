<?php
namespace App\Http\Controllers;

use App\Models\Messages;
use Illuminate\Http\Request;
use Validator;
use Exception;

class MessagesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function get()
    {
        try {

            $messages = Messages::all();

        } catch (Exception $e) {
            return response(['status' => false, 'message' => "Get Messages Failed", 'errors' => $e], 400);
        }


        return response(['message' => "Message fetched Successfully", 'messages' => $messages], 200);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'name' => 'required|string|min:6',
            'message' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response(['message' => "All Fields are required", 'errors' => $validator->errors()], 400);
        }

        $input = $request->all();
        try {
            $message = Messages::create($input);
        } catch (Exception $e) {
            return response(['status' => false, 'message' => "Add message failed!"], 400);
        }

        return response(['message' => "Message added Successfully", 'data' => $message], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Messages  $messages
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $id = $request->id;
        try {
            $message = Messages::find($id);

        } catch (Exception $e) {
            return response(['status' => false, 'message' => "Get Message Failed", 'errors' => $e], 400);
        }
        return response(['message' => "Message fetched Successfully", 'data' => $message], 200);

    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Messages  $messages
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $id = $request->id;
        try {

            $isDeleted = Messages::where('id', $id)->firstorfail()->delete();

        } catch (Exception $e) {
            return response(['status' => false, 'message' => $e], 400);
        }
        return response(['message' => "Message Deleted Successfully", 'data' => $isDeleted], 200);
    }
}
