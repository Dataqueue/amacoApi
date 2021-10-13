<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class notes extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }
}
