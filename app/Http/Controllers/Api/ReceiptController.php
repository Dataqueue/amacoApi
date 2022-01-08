<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Receipt;
use App\Models\PaymentAccount;
use App\Models\AdvancePayment;
use Illuminate\Support\Facades\File;

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
        $allReceipt = Receipt::join('payment_accounts','receipts.div_id','payment_accounts.id')->select(
            'payment_accounts.name as div_name',
            'receipts.*'
        )->get();

        $allReceipt->map(function ($receipt){
            $receipt['credit']=$receipt->paid_amount;
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
         $filePath=null;
        if ($request->file('file')) {
            $filePath = $request->file('file')->move("receipts/", $request->file('file')->getClientOriginalName());

           
        }
        
        $receipt = Receipt::create(["party_id" => $request->party_id,
        "payment_mode" => $request->payment_mode,
        "narration" => $request->narration?$request->narration:null,
        "file" => $filePath,
        "paid_amount" => $request->paid_amount,
        "paid_date" => $request->paid_date,
        "div_id" => $request->div_id,
        "user_id" => $request->user_id?$request->user_id:0,
        "division_id" => $request->division_id?$request->division_id:0,
        "bank_id" => $request->bank_id,
        "sender" => $request->sender,
        "receiver" => $request->receiver,
    ]);
    if ($receipt->id) {
        $receipt->update(['voucher_no' => 'AMC-'.'TR-'.'RV-'.date('y').'-' . sprintf('%05d', $receipt->id)]);
    }
    if($request->payment_mode=="cash")
    {
        $res=AdvancePayment::create([
            'payment_account_id' => $request->div_id,
            'received_by' =>$request->receiver,
            'payment_mode' => $request->payment_mode,
            'amount' => $request->paid_amount,
            "received_date" => $request->paid_date,
        ]);
    }

        return response()->json($request, 200);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Receipt  $receipt
     * @return \Illuminate\Http\Response
     */
    public function show(Receipt $receipt)
    {
       $receipt['referrenceImgUrl'] =$receipt->referrenceImg();
        return response()->json([$receipt],200);
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
        // $data = $request->json()->all();
        $receipt = Receipt::findOrFail($request->id);
        // $filePath=null;
        // if ($request->file('file')) {
        //     $filePath = $request->file('file')->move("receipts/", $request->file('file')->getClientOriginalName());

           
        // }
        $receipt->update([
            'party_id' => $request->party_id,
            'paid_amount' => $request->paid_amount,
            'div_id' => $request->div_id,
            "user_id" => $request->user_id?$request->user_id:0,
            "division_id" => $request->division_id?$request->division_id:0,
            'narration' => $request->narration?$request->narration:null,
            'check_no' => $request->check_no,
            'bank_id' => $request->bank_id,
            // 'file' => $filePath,
        
            
            // 'contact_id' => $request->contact_id,
        ]);
        return response()->json($receipt);
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

    public function singleReceipt($id)
    {
        $receipt = Receipt::where('receipts.id',$id)->join('divisions','receipts.div_id','divisions.id')->select(
            'divisions.name as div_name',
            'receipts.*'
        )->get();
        
            return [

                $receipt->map(function($accountCategory){
                if (File::exists(public_path($accountCategory->file))) {
                    $accountCategory['file'] = url($accountCategory->file);
                }
                }),
               
                'img' => $accountCategory->img(),
                'referrenceImgUrl' => $accountCategory->referrenceImg(),
           
                // 'sub_categories' => $this->subCategory($accountCategory->id),
            ];
     
        
        return response()->json([$receipt]);
    }
    public function updateReceipt(Request $request)
    {
        $receipt = Receipt::findOrFail($request->id);
        $filePath=null;
        if ($request->file('file')) {
            $filePath = $request->file('file')->move("receipts/", $request->file('file')->getClientOriginalName());
            $receipt->update([
                'file' => $filePath,
            
                
                // 'contact_id' => $request->contact_id,
            ]);
           
        }
        $receipt->update([
            'party_id' => $request->party_id,
            "payment_mode" => $request->payment_mode,
            "narration" => $request->narration,
            'paid_amount' => $request->paid_amount,
            'div_id' => $request->div_id,
            'narration' => $request->narration,
            'check_no' => $request->check_no,
            'bank_id' => $request->bank_id,
            "sender" => $request->sender,
            "receiver" => $request->receiver==" "?0:$request->receiver,
            "paid_date" => $request->paid_date,
            // 'file' => $filePath,
        
            
            // 'contact_id' => $request->contact_id,
        ]);
        return response()->json(['referrenceImgUrl' => $receipt->referrenceImg()]);
    }
}
