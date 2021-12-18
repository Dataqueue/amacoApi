<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    use HasFactory;
    protected $table = "purchase_returns";
    protected $fillable = [
        'pr_id',	
        'quotation_no',	
        'quotationr_no',	
        'party_id',	
        'rfq_id',	
        'pr_number',	
        'status',	
        'total_value',	
        'discount_in_p',	
        'vat_in_value',	
        'net_amount',	
        'validity',	
        'payment_terms',	
        'warranty',	
        'delivery_time',	
        'inco_terms',	
        'po_number',	
        'created_at',	
        'updated_at',	
        'contact_id',	
        'transaction_type',	
        'ps_date',	
        'sales_order_number',	
        'is_revised',	
        'parent_id',	
        'sign',	
        'file',	
        'bank_id',	
        'freight_type',	
        'currency_type',	
        'subject',	
        'rfq_no',	
        'company_address',
    ];
    public function sales_purchase_Return()
    {
        return $this->hasMany(PurchaseReturnDetail::class, 'pr_id','pr_id');
    }
    
}
