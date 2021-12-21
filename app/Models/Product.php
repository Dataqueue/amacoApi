<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class Product extends Model
{
    protected $guarded = [];
    use HasFactory;

    public function division()
    {
        return $this->belongsTo(Division::class, 'rfq_id');
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'rfq_id');
    }
    public function product_subcategory()
    {
        // return $this->hasMany(Category::class, 'parent_id','category_id');
        return $this->hasMany(Category::class, 'id','category_id');
    }
   
    public function product_purchase()
    {
        return $this->hasMany(PurchaseReturnDetail::class);
        
    }
    public function product_sales()
    {
        return $this->hasMany(InvoiceDetail::class);
    }
    
    public function rfq()
    {
        return $this->belongsTo(RFQDetails::class, 'rfq_id');
    }
    public function rfq_detail()
    {
        return $this->belongsTo(RFQ::class);
    }
    public function quotaionDetail()
    {
        return $this->belongsTo(QuotationDetail::class);
    }

    public function invoiceDetail()
    {
        return $this->belongsTo(InvoiceDetail::class);
    }
    public function purchaseInvoiceDetail()
    {
        return $this->belongsTo(PurchaseInvoiceDetail::class);
    }

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function productPrice()
    {
        return $this->hasMany(ProductPrice::class, 'product_id', 'id');
    }

    public function deliveryNoteDetail()
    {
        return $this->hasOne(DeliveryNoteDetail::class, 'product_id', 'id');
    }
    public function purchase_sale_Return()
    {
        return $this->hasMany(PurchaseReturnDetail::class);
    }
    public function sales_purchase()
    {
        return $this->hasMany(QuotationDetail::class);
    }
}
