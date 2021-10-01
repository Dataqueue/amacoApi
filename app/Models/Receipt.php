<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class Receipt extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function party()
    {
        return $this->hasOne(Party::class, 'id','party_id');
    }
    public function paymentaccount()
    {
        return $this->hasOne(PaymentAccount::class, 'id','receiver');
    }
    
    public function division()
    {
        return $this->hasOne(Receipts::class, 'id','party_id');
    }
    public function referrenceImg()
    {
        $path = $this->file;
        if (File::exists(public_path($this->file))) {
            return url($path);
        }
        return "No file Uploaded";

    }
}
