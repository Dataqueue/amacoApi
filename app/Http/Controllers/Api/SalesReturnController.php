<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Quotation;
use App\Models\PurchaseReturn;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\PurchaseReturnDetail;


class SalesReturnController extends Controller
{

    public function index($id){
        
        $data = Invoice::
        where('party_id','=',$id)
        ->get();
        return response()->json([
            'status' => 200,
            'getPurchaseReturnData' => $data
        ]);

    }

      public function getSalesReturnINV($id){
        $purchaseReturn = PurchaseReturn::
        join('parties','parties.id','purchase_returns.party_id')
        ->where('purchase_returns.transaction_type','sales')
        ->where('purchase_returns.pr_id','=',$id)
        ->orderBy('purchase_returns.created_at', 'DESC')
        ->get();

        $returnItems = PurchaseReturnDetail::
        where('pr_id','=',$id)
        ->get();

        return response()->json([
            'status' => 200,
            'getReturnParty' => $purchaseReturn,
            'getReturnItems' => $returnItems
        ]);    
    }

    public function getProductsSR($iv){
         $data = Invoice::
        where('invoice_no','=',$iv)
        ->get('id');

          $dd = InvoiceDetail::
        where('invoice_id','=',$data[0]->id)
        ->get();


        return response()->json([
            'status' => 200,
            'getPData' => $dd,
        ]);
    }



    public function SalesReturnTableData(){
        $quotations = PurchaseReturn::
        join('parties','parties.id','purchase_returns.party_id')
        ->where("transaction_type",'sales')
        ->orderBy('purchase_returns.created_at', 'DESC')
        ->get();


        return response()->json($quotations, 200);
    }


    public function getSalesFormData(){
        $getCust = Quotation::
        join('parties','parties.id','quotations.party_id')
        ->where('transaction_type','=','sale')
        ->get();


        return response()->json([
            'status' => 200,
            'getCust' => $getCust
        ]);



    }
}
