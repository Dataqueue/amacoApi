<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturnDetail extends Model
{
    use HasFactory;

    protected $table = "purchase_returns_details";

    protected $fillable = [
        'prd_id',	
        'pr_id',	
        'total_amount',	
        'quotation_no',	
        'po_number',	
        'analyse_id',	
        'product_id',	
        'purchase_price',	
        'description',	
        'quantity',	
        'margin',	
        'sell_price',	
        'created_at',	
        'updated_at',	
        'remark',	
        'file_img_url',	
        'product_description',	
        'unit_of_measure',
    ];
    public function product_purchaseReturn()
    {
        return $this->hasMany(Product::class, 'product_id','id');
    }
}
