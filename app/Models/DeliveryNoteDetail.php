<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryNoteDetail extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function deliveryNote()
    {
        return $this->belongsTo(DeliveryNote::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }


    // functions

    // to get total delivered quantity
    public function getTotalDeliveredQuantity($val)
    {
        // $totalDeliveryNoteDetail = 0;
        // if(isset($val)) {
        //     foreach ((array)$val as $item) {
        //         $totalDeliveryNoteDetail += intval($item['delivered_quantity']);
               
        //     }
        //     return $totalDeliveryNoteDetail;
        // }
        // return 0;
        return $val->toArray();
    }

    // there is no need for this
    // public function getBalanceQuantity($totalQuantity = 0, $totalDeliveredQuantity = 0)
    // {
    //     return ($totalQuantity - $totalDeliveredQuantity);
    // }

    public function showDeliveredNoteDetail($id)
    {
        $delivery_notes_detail = DeliveryNoteDetail::where('id',$id)->first();

        $totalDeliveryNoteDetails = DeliveryNoteDetail::where([
            'delivery_note_id' => $delivery_notes_detail->delivery_note_id,
            'product_id' => $delivery_notes_detail->product_id,
        ])->get();

        if($delivery_notes_detail->quotation_id)
        {
        $quotationDetail = QuotationDetail::where([
            'quotation_id' => $delivery_notes_detail->deliveryNote->quotation_id,
            'product_id' => $delivery_notes_detail->product_id,
        ])->firstOrFail();
        // return [$quotationDetail];
        }
        if($delivery_notes_detail->invoice_id)
        {
       
            $quotationDetail = InvoiceDetail::where([
            'invoice_id' => $delivery_notes_detail->deliveryNote->invoice_id,
            'product_id' => $delivery_notes_detail->product_id,
        ])->firstOrFail();
        // return [$quotationDetail];
        }

        // $totalDeliveredQuantity = $this->getTotalDeliveredQuantity($totalDeliveryNoteDetails);
        $totalDeliveredQuantity =$this->getTotalDeliveredQuantity($delivery_notes_detail);
        // if(isset($totalDeliveredQuantity)){
        //     $totalDeliveredQuantityExceptCurrentValue = $totalDeliveredQuantity - intval($delivery_notes_detail->delivered_quantity) ;
        // }else{
        //     $totalDeliveredQuantityExceptCurrentValue = 0;
        // }

        $data = [
            // "total_quantity" => $quotationDetail->quantity, //$totalQuantity =
            // // "total_delivered_quantity" => $totalDeliveredQuantity,
            // "total_delivered_quantity" => $totalDeliveredQuantityExceptCurrentValue,
            // "delivering_quantity" => $delivery_notes_detail->delivered_quantity,
            // "delivery_notes_detail" => $delivery_notes_detail,
            // "product" => array($delivery_notes_detail->product),
            "NEWARRY" =>  $totalDeliveredQuantity,
            // "quotation" => $delivery_notes_detail->deliveryNote->quotation,
            // "delivery_note" => $delivery_notes_detail->deliveryNote,
            // "party" => $delivery_notes_detail->deliveryNote->quotation->party,
            // 'balance_quantity' => $this->getBalanceQuantity($totalQuantity, $totalDeliveredQuantity), //not required anymore
        ];

        return [$data];

       
    }
}

