<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $login = $request->login;
        $password = $request->password;

        $searchLogin = User::where('name','=',$login)->get();

        if(count($searchLogin) === 0) {
            return response([
                'status' => false,
                'message' => 'user not found'
            ], 404)
                ->setStatusCode(404, 'user not found');
        }

        if(!password_verify($password, $searchLogin[0]->password)){
            return response([
                'status' => false,
                'message' => 'login or password incorrect'
            ], 404)
                ->setStatusCode(404, 'login or password incorrect');
        }

        $token = Str::random(60) . $searchLogin[0]->id;

        $searchLogin[0]->update([
            'bearerToken' => $token
        ]);

        return response([
            'status' => true,
            'message' => "account open",
            'token' => $token
        ], 200)
            ->setStatusCode(200, 'open account')
            ->header('authorization', $token);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $token = $request->header('authorization');
        $searchUser = User::where('bearerToken','=',$token)->get();
        if(count($searchUser) === 0){
            return response([
                'status' => false,
                'message' => 'unauthorized'
            ], 402)
                ->setStatusCode(402, 'unauthorized');
        }

        return response([
            'status' => true,
            'message' => 'login exit'
        ], 200)
            ->setStatusCode(200, 'login exit')
            ->header('authorization', '');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }
}
