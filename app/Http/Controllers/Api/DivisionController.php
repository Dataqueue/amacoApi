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
    public function store(Division $div)
    {
       

        $party = Division::create([
            'name' => ucwords(trans($request->name)),
            'opening_bal' => (string) $request->opening_balance,
            
            
            
        ]);
        return response()->json("success");
    }

}
