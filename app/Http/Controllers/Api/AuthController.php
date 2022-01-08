<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\PermissionDenied;



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
        $division_data=DB::table('user_divisions')->join('divisions','divisions.id','user_divisions.div_id')->where(['user_divisions.u_id'=>Auth::user()->id])->get();

         if(Auth::user()->role->name=="SA"){
            $divs = DB::table('divisions')->get();

            $type = DB::table('divisions')->get(['divisions.id','divisions.name']);
        }
        else{
            $divs = DB::table('user_divisions')->join('divisions','divisions.id','user_divisions.div_id')->where('user_divisions.u_id',Auth::user()->id)->get();

            $type = DB::table('user_divisions')->join('divisions','divisions.id','user_divisions.div_id')->where('user_divisions.u_id',Auth::user()->id)->get(['divisions.id','divisions.name']);
           }
        $perData = PermissionDenied::where('u_id',Auth::user()->id)->get();

        $var=Auth::user();
        $var['division']=$type;
        $var['permission']=$perData;
        $var['divs']=$divs;
        $data = [
            "accessToken" => $token,
            "user" => $var,
            "role" => Auth::user()->role->name,
            'division' => $type[0]->id,
            'division_data'=>$division_data,
            'permission'=>$perData
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

    

         if(Auth::user()->role->name=="SA"){
            $divs = DB::table('divisions')->get();
            $type = DB::table('divisions')->get(['divisions.id','divisions.name']);
        }
        else{
                    $divs = DB::table('user_divisions')->join('divisions','divisions.id','user_divisions.div_id')->where('user_divisions.u_id',Auth::user()->id)->get();

            $type = DB::table('user_divisions')->join('divisions','divisions.id','user_divisions.div_id')->where('user_divisions.u_id',Auth::user()->id)->get(['divisions.id','divisions.name']);
           }

        $division_data=DB::table('user_divisions')->join('divisions','divisions.id','user_divisions.div_id')->where(['user_divisions.u_id'=>Auth::user()->id])->get();


        $perData = PermissionDenied::where('u_id',Auth::user()->id)->get();
        $var=Auth::user();
        $var['division']=$type;
        $var['divs']=$divs;
        $var['permission']=$perData;
        $data = [
            'division_data'=>$division_data,
            'user' => $var,
            
            'role' => Auth::user()->role->name,
            'division'=>$type[0]->id,
            'permission'=>$perData
            
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
