<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expenses;
use Illuminate\Http\Request;

class ProfitLoss extends Controller
{
    //
    public function profitLoss(Request $request)
    {
            $res=Expense::join('account_categories','expense.account_category_id','account_categories.id')->get();
            return response()->json($res);
    }
}
