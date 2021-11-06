<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InvestmentsDetail;

class InvestmentsDetailsController extends Controller
{
    //
    public function store(Request $request)
    {
        $data=InvestmentsDetails::create([
           'user_id'=>$request->user_id,
           'payment_account_id'=>$request->payment_account_id ,
           'amount'=>$request->balance 
        ]);
        return response()->json($data);
    }
}

