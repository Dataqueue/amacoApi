<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PermissionDenied;
use App\Models\Module;


class PermissionDeniedController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id,$i)
    {
        $perData = PermissionDenied::where('u_id',$id)->get();

        $gdata = Module::where('div_id',$i)->get();
        $gdata = $gdata->whereNull('parent_id');
        $allcategories = Module::where('div_id',$i)->get();
        $rootcategories = $allcategories->whereNull('parent_id')->values();
        self::formatTree($rootcategories,$allcategories);

         return response()->json([
                'status' => 200,
                'all' => $rootcategories,
                'gData' => $gdata,
                'permission' => $perData,

            ]);
    } 
    public function userPermission($id)
    {
        $perData = PermissionDenied::where('u_id',$id)->get();

         return response()->json([
                'status' => 200,
                'permission' => $perData,

            ]);
    }

    
    private static function formatTree($categories ,$allcategories){
        foreach($categories as $category){
            $category->child = $allcategories -> where('parent_id',$category->mod_id )->values();
            if($category->child->isNotEmpty() ){
                self::formatTree($category->child,$allcategories);
            }
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        if($request->input('status') == "lock"){
            $data = new PermissionDenied;
            $data -> module = $request->input('module');
            $data -> status = $request->input('status');
            $data -> u_id = $request->input('userid');
            $data -> type = $request->input('type');

            if($data -> save()){
                    return response()->json([
                        'status' => 200,
                        'message' => "Locked Successfully.",
                ]);
            }
        }else{

            $d = PermissionDenied::where('module',$request->input('module'))->where('u_id',$request->input('userid'))->delete();
            return response()->json([
                        'status' => 200,
                        'message' => "Deleted",
                ]);
        }
         
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
