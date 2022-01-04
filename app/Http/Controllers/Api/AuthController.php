<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        
        $credentials = request(['email', 'password']);

        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        // if(Auth::user()->role->name=="SA")
        // {
        //     $type=1;
        // }
        // else{
        //     $count = DB::table('user_divisions')->join('divisions','divisions.id','user_divisions.div_id')->where(['user_divisions.u_id'=>Auth::user()->id,'divisions.id'=>3])->count();
        //     if($count>0)
        //     {
        //         $type=2;
        //     }
        //     else {
        //         $type=1;
        //     }
        // }
        $data = [
            "accessToken" => $token,
            "user" => Auth::user(),
            "role" => Auth::user()->role->name,
            // 'division' => $type,
        ];
        // return $this->respondWithToken($token);
        return response()->json($data);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        if(Auth::user()->role->name=="SA")
        {
            $type=1;
        }
        else{
            $count = DB::table('user_divisions')->join('divisions','divisions.id','user_divisions.div_id')->where(['user_divisions.u_id'=>Auth::user()->id,'divisions.id'=>3])->count();
            if($count>0)
            {
                $type=3;
            }
            else {
                $type=1;
            }
        }

        $var=Auth::user();
        $var['division']=$type;
        $data = [
            'user' => $var,
            
            'role' => Auth::user()->role->name,
            // 'division'=>$type
            
        ];
        return response()->json($data);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        Auth::logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(Auth::refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'accessToken' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ]);
    }
}
