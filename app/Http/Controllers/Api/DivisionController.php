<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Division;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    //


    public function index()
    {
        $div = Division::all();
        return response()->json($div);
    }


    public function show(Division $div)
    {
        return response()->json(array($div));
    }
    public function store(Request $request)
    {
       
        $data = $request->json()->all();
        $party = Division::create([
            'name' => $request->name,
            'opening_bal' => (string) $request->opening_balance,
        
            
            
        ]);
        return response()->json([$data]);
    }

    public function update(Request $request, Division $div)
    {
        $div->update($request->all());
        // return $contact;
    }
    public function singleDivision($id)
    {
       
        
        $division = Division::where('id',$id)->get();
        return response()->json($division);
    }

}
