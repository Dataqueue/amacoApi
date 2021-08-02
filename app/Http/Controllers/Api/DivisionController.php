<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Division;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    //
    public function show(Division $div)
    {
        return response()->json(array($div));
    }
}
