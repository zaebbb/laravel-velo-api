<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|Response|object
     */
    public function index(Request $request)
    {
        $token = $request->header('authorization');
        $searchUserToken = User::where('bearerToken','=',"$token")->get();
        if(count($searchUserToken) === 0){
            return response([
                'status' => false,
                'message' => 'access denied'
            ], 403)
                ->setStatusCode(403, 'access denied');
        }
        if($searchUserToken[0]->name !== 'admin'){
            return response([
                'status' => false,
                'message' => 'access denied'
            ], 403)
                ->setStatusCode(403, 'access denied');
        }
        $users = User::all();
        if(count($users) === 0){
            return response([
                'status' => false,
                'message' => 'users not found'
            ], 404)
                ->setStatusCode(404, 'users not found');
        }

        return response([
            'status' => true,
            'message' => $users
        ], 200)
            ->setStatusCode(200, 'users found');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $name = $request->name;
        $email = $request->email;
        $password = $request->password;

        $errors = [];

        if(empty($name)){
            $errors[] = array('name_required' => 'Name required');
        }
        if(empty($email)){
            $errors[] = array('email_required' => 'Email required');
        }
        if(empty($password)){
            $errors[] = array('password_required' => 'Password required');
        }

        if(strlen($password) < 5){
            $errors[] = array('password_length' => 'Password min length 5 symbols');
        }

        if(count($errors) !== 0){
            return response([
                'status' => false,
                'errors' => $errors
            ], 201)
                ->setStatusCode(400, 'Bad request');
        }

        $searchNameUser = User::where('name','=',"$name")->get();
        $searchEmailUser = User::where('email','=',"$email")->get();

        if(count($searchNameUser) !== 0){
            return response([
                'status' => false,
                'message' => 'user exists'
            ], 400)
                ->setStatusCode(400, 'name exists');
        }
        if(count($searchEmailUser) !== 0){
            return response([
                'status' => false,
                'message' => 'email exists'
            ], 400)
                ->setStatusCode(400, 'email exists');
        }

        $generatePassword = password_hash($password, PASSWORD_DEFAULT);

        $createUser = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $generatePassword,
            'bearerToken' => ''
        ]);

        $generateToken = Str::random(60) . $createUser->id;

        $createUser->update([
            'bearerToken' => $generateToken
        ]);

        return response([
            'status' => true,
            'user_id' => $createUser->id
        ], 201)
            ->setStatusCode(201, 'created successful')
            ->header('authorization', $generateToken);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return Response
     */
    public function show(User $user, Request $request, $id)
    {
        $token = $request->header('authorization');
        $searchUser = User::where('bearerToken','=',"$token")->get();
        if(count($searchUser) === 0){
            return response([
                'status' => false,
                'message' => 'access denied'
            ], 403)
                ->setStatusCode(403, 'access denied');
        }
        $user = User::find($id);
        if($user === null){
            return response([
                'status' => false,
                'message' => 'user not found'
            ], 404)
                ->setStatusCode(404, 'user not found');
        }

        return response([
            'status' => true,
            'message' => $user
        ], 200)
            ->header('authorization', "$token")
            ->setStatusCode(200, 'user found');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return Response
     */
    public function update(Request $request, User $user, $id)
    {
        $token = $request->header('authorization');
        $searchUser = User::where('bearerToken','=',"$token")->get();
        if(count($searchUser) === 0){
            return response([
                'status' => false,
                'message' => 'access denied'
            ], 403)
                ->setStatusCode(403, 'access denied');
        }

        if($searchUser[0]->name !== 'admin' && $id !== substr($token, 60)){
            return response([
                'status' => false,
                'message' => 'access denied user'
            ], 403)
                ->setStatusCode(403, 'access denied user');
        }

        $name = $request->name;
        $email = $request->email;
        $password = $request->password;

        $errors = [];

        if(empty($name)){
            $errors[] = array('name_required' => 'Name required');
        }
        if(empty($email)){
            $errors[] = array('email_required' => 'Email required');
        }
        if(empty($password)){
            $errors[] = array('password_required' => 'Password required');
        }

        if(count($errors) !== 0){
            return response([
                'status' => false,
                'errors' => $errors
            ], 400)
                ->setStatusCode(400, 'errors validation');
        }

        $userUpdated = User::find($id);

        if($userUpdated === null){
            return response([
                'status' => false,
                'message' => 'user not found'
            ], 404)
                ->setStatusCode(404, 'user not found');
        }

        if($userUpdated->name === 'admin'){
            return response([
                'status' => false,
                'message' => 'admin not updated'
            ], 403)
                ->setStatusCode(403, 'admin not updated');
        }

        $generateToken = Str::random(60) . $id;
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $userUpdated->update([
            'name' => $name,
            'email' => $email,
            'password' => $passwordHash,
            'bearerToken' => $generateToken
        ]);

        return response([
            'status' => true,
            'user' => $userUpdated
        ], 201)
            ->setStatusCode(201, 'updated successful')
            ->header('header', $generateToken);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\User $user
     * @param $id
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|Response|object
     */
    public function destroy(User $user, $id, Request $request)
    {
        $token = $request->header('authorization');
        $searchUser = User::where('bearerToken','=',$token)->get();
        if(count($searchUser) === 0){
            return response([
                'status' => false,
                'message' => 'access denied'
            ], 403)
                ->setStatusCode(403, 'access denied');
        }

        if($id != substr($token, 60) && $searchUser[0]->name != 'admin'){
            return response([
                'status' => false,
                'message' => 'access denied user'
            ], 403)
                ->setStatusCode(403, 'access denied user');
        }

        $delUser = User::find($id);

        if($delUser === null){
            return response([
                'status' => false,
                'message' => 'user not found'
            ], 404)
                ->setStatusCode(404, 'user not found');
        }

        if($delUser->name === 'admin'){
            return response([
                'status' => false,
                'message' => 'admin not deleted'
            ], 403)
                ->setStatusCode(403, 'admin not deleted');
        }

        $delUser->delete();

        return response([
            'status' => false,
            'message' => 'delete successful'
        ], 200)
            ->setStatusCode(200, 'delete successful')
            ->header('authorization', $token);
    }
}
