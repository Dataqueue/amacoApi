<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

class ProfitLossController extends Controller
{
    //
    public function profitLoss(Request $request)
    {
            $res=Expense::join('account_categories','expenses.account_category_id','account_categories.id')->get();
            return response()->json($res);
    }
}
