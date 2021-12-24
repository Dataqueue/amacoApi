<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionDenied extends Model
{
    use HasFactory;

    protected $table = "permission_denied";
    protected $fillable = [
            'pd_id',
        	'u_id',
        	'module',
        	'type',
        	'status',
        	'created_at',
        	'updated_at',
    ];
}
