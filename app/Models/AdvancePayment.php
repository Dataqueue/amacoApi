<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class AdvancePayment extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function paymentAccount()
    {
        return $this->hasMany(PaymentAccount::class, 'id','payment_account_id');
    }
   
    public function referrenceImg()
    {
        $path = $this->file_path;
        if (File::exists(public_path($this->file_path))) {
            return url($path);
        }
        return "No file Uploaded";

    }
}
