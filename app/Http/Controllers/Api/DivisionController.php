<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\division;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    //
    public function show(Division $div)
    {
        $data = [
            'id' => $div->id,
            'name' => $div->name,
        ];

        return response()->json($data, 200);
    }
}
