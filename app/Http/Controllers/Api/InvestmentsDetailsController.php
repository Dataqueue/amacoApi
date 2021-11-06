<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InvestmentsDetails;
use App\Models\AdvancePayment;
use App\Models\PaymentAccount;

class InvestmentsDetailsController extends Controller
{
    //
    public function store(Request $request)
    {
     
        $data=InvestmentsDetails::create([
           'user_id'=>$request->user_id,
           'payment_account_id'=>$request->id ,
           'amount'=>$request->balance 
        ]);
        $pay=AdvancePayment::create([
            'narration'=>'Investments',
            'payment_account_id'=>$request->payment_account_id,
            'amount'=>$request->balance,
            'received_by'=>$request->received_by,
            'payment_mode'=>'cash',


        ]);
        return response()->json($data);
    }
}

