<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvoiceDetail;
use Illuminate\Http\Request;

class InvoiceDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InvoiceDetail  $invoiceDetail
     * @return \Illuminate\Http\Response
     */
    public function show(InvoiceDetail $invoiceDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\InvoiceDetail  $invoiceDetail
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, InvoiceDetail $invoiceDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InvoiceDetail  $invoiceDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $invoice = InvoiceDetail::where('id', $id)->first();
       
        $res = $invoice->delete();
        
        if ($res) {
            return (['msg' => 'invoice' . ' ' . $invoice->id . ' is successfully deleted']);
        }
    }
    // public function getDelivered_invoice_Quantity(InvoiceDetail $quotation_detail)
    // {
    //     $deliveryNoteDetails = DB::table('delivery_notes')
    //     ->leftJoin('delivery_note_details', 'delivery_note_details.delivery_note_id','=', 'delivery_notes.id')
    //     ->where('delivery_notes.invoice_id',$quotation_detail->invoice_id)
    //     ->where('delivery_note_details.product_id', $quotation_detail->product_id)
    //     ->get();

    //     if($deliveryNoteDetails) {
    //         $totalDeliveryNoteDetail = 0;
    //         foreach ($deliveryNoteDetails as $item) {
    //             $totalDeliveryNoteDetail += intval($item->delivered_quantity);
    //         }
    //         return $totalDeliveryNoteDetail;
    //     }
    //     return 0;
    // }
}
