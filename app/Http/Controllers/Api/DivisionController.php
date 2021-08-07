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
        $division = Division::where('id',$request->id)->get();
        $division->update([
            'name' => $request->name,
            'opening_bal' => $request->opening_bal,
            // 'contact_id' => $request->contact_id,
        ]);
        // return $contact;
        // return response()->json([$request->json()->all()]);
    }
    public function singleDivision($id)
    {
       
        
        $division = Division::where('id',$id)->get();
        return response()->json($division);
    }

}
