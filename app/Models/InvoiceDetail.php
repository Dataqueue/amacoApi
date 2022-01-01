<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InvoiceDetail extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }

    public function quotationDetail()
    {
        return $this->hasOne(QuotationDetail::class, 'id', 'quotation_detail_id');
    }
    public function product_invoice()
    {
        return $this->hasMany(Product::class, 'id','product_id');
    }
    public function getDelivered_invoice_Quantity(InvoiceDetail $quotation_detail)
    {
        $deliveryNoteDetails = DB::table('delivery_notes')
        ->leftJoin('delivery_note_details', 'delivery_note_details.delivery_note_id','=', 'delivery_notes.id')
        ->where('delivery_notes.invoice_id',$quotation_detail->invoice_id)
        ->where('delivery_note_details.product_id', $quotation_detail->product_id)
        ->get();

        if($deliveryNoteDetails) {
            $totalDeliveryNoteDetail = 0;
            foreach ($deliveryNoteDetails as $item) {
                $totalDeliveryNoteDetail += intval($item->delivered_quantity);
            }
            return $totalDeliveryNoteDetail;
        }
        return 0;
    }
}
