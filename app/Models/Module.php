<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;
    protected $table = "modules";
    protected $fillable = [
        'mod_id',
	    'parent_id',
	    'module_name',
	    'type',
	    'status',
	    'created_at',
	    'updated_at',
    ];
}
