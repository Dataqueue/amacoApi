<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyBank;
use App\Models\PaymentAccount;
use Illuminate\Http\Request;

class CompanyBankController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $bank = CompanyBank::all();
       
        return response()->json($bank);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $bank = CompanyBank::create([
            'name'=> $request->name, 
            'iban_no'=> $request->iban_no, 
            'ac_no'=> $request->ac_no, 
            'bank_address'=> $request->bank_address, 
            
            
        ]);
        // PaymentAccount::create([
        //     'bank_id'=> $bank->id,
        //     'name'=>$bank->name,
        //     'balance'=>$request->balance,
        //     'type'=>'bank',


        // ]);
        return response()->json($bank);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CompanyBank  $companyBank
     * @return \Illuminate\Http\Response
     */
    public function show(CompanyBank $companyBank)
    {
        return response()->json($companyBank);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CompanyBank  $companyBank
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CompanyBank $companyBank)
    {
        $companyBank->update($request->all());

        return response()->json($companyBank);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CompanyBank  $companyBank
     * @return \Illuminate\Http\Response
     */
    public function destroy(CompanyBank $companyBank)
    {
        $companyBank->delete();

        return response()->json(['msg'=>"Successfully Deleted"]);
    }
    public function companybank(CompanyBank $companyBank)
    {
        $companyBank->delete();

        return response()->json(['msg'=>"Successfully Deleted"]);
    }
}
