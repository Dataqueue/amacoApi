<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employees extends Model
{
    use HasFactory;
    protected $table = "employee";
    protected $fillable = [
        'emp_id',
        'emp_no',
        'name',
        'contact_number',
        'present_address',
        'email',
        'designation',
        'grosssalary',
        'bsalary',
        'hrasalary',
        'tasalary',
        'div_id',
        'passport_number',
        'passport_exp_date',
        'iqama_exp_date',
        'file',
        'date_of_join',
        'salary_id',
        'status',
        'created_at',
        'updated_at',
    ];
}
