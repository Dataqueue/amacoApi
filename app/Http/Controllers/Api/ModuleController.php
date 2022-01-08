<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Module;


class ModuleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $gdata = Module::where('div_id',$id)->get();
        $gdata = $gdata->whereNull('parent_id');
        $allcategories = Module::where('div_id',$id)->get();
        $rootcategories = $allcategories->whereNull('parent_id')->values();
        self::formatTree($rootcategories,$allcategories);

         return response()->json([
                'status' => 200,
                'all' => $rootcategories,
                'gData' => $gdata,

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

        $data = new Module;
        $data -> module_name = $request->input('module_name');
        $data -> type = $request->input('type');
        $data -> div_id = $request->input('div_id');
        $request->input('parentId');
        if($request->input('parentId')){
            $par = $request->input('parentId');
        }else{
            $par = null;
        }
        $data -> parent_id = $par;

        $data -> status = "Active";
         if($data -> save()){
                return response()->json([
                    'status' => 200,
                    'message' => "Saved Successfully.",
                    'ss' => $request->input('parentId'),
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
        $data = Module::where('mod_id',$id)->get();
         return response()->json([
                    'status' => 200,
                    'data' => $data,
            ]);
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
        Module::where('mod_id',$id)->update(
         array(
                 'module_name' => $request->input('module_name'),
                 'type' => $request->input('type'),
         )
         );
         return response()->json([
                    'status' => 200,
                    'message' => "Updated Successfully.",
            ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = Module::where('mod_id',$id)->delete();
         return response()->json([
                    'status' => 200,
                    'message' => "Deleted Successfully.",
            ]);
    }
}
