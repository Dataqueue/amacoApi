<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Receipt;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $allReceipt = Receipt::all();

        $allReceipt->map(function ($receipt){
            return $receipt->party;
        });

        return response()->json($allReceipt, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->json()->all();
        if ($request->file('file')) {
            $filePath = $request->file('file')->move('');
        }

        // $receipt = Receipt::create(["party_id",$data['party_id'],
        // "payment_mode",$data['payment_mode'],
        // "file",$data['file'],
        // "paid_amount",$data['paid_amount'],
        // "paid_date",$data['paid_date'],
        // "div_id",$data['div_id'],
        // "bank_id",$data['bank_id'],
    // ]);

        return response()->json($request->party_id, 200);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Receipt  $receipt
     * @return \Illuminate\Http\Response
     */
    public function show(Receipt $receipt)
    {
        return response()->json($receipt, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Receipt  $receipt
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Receipt $receipt)
    {
        $receipt->update($request->all());

        return response()->json($receipt, 200);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Receipt  $receipt
     * @return \Illuminate\Http\Response
     */
    public function destroy(Receipt $receipt)
    {
        $receipt->delete();

        return response()->json(['msg'=>"Permanently deleted"], 200);
    }
}
