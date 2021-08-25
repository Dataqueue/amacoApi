<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Expense;
use App\Models\Receipt;
use App\Models\PaymentAccount;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    //


    public function index()
    {
        $div = Division::all();
        return response()->json($div);
    }


    public function show(Division $div)
    {
        return response()->json(array($div));
    }
    public function store(Request $request)
    {
       
        $data = $request->json()->all();
        $party = Division::create([
            'name' => $request->name,
            'opening_bal' => (string) $request->opening_balance,
        
            
            
        ]);
        return response()->json([$data]);
    }

    public function update(Request $request, Division $div)
    {
        $division = Division::findOrFail($request->id);
        $division->update([
            'name' => $request->name,
            'opening_bal' => $request->opening_bal,
            // 'contact_id' => $request->contact_id,
        ]);
        // return $contact;
        // return response()->json([$request->json()->all()]);
    }
    public function singleDivision($id)
    {
       
        
        $division = Division::where('id',$id)->get();
        return response()->json($division);
    }
    public function paidDivision()
    {
       
        $divEopenbalance=Expense::where('is_paid',1)->sum('amount');
        $divRopenbalance=Receipt::sum('paid_amount');
        $division = PaymentAccount::get();
        $datas=$division->map(function ($item) {
            if($item['div_id'])
            {
                $divEopenbalance=Expense::where('is_paid',1)->where('div_id',$item['div_id'])->sum('amount'); 
                $divRopenbalance=Receipt::where('div_id',$item['div_id'])->sum('paid_amount');
                $item['name']=$item->name;
                $item['id']=$item->id;
                $item['balance'] = $divRopenbalance-$divEopenbalance+floatval($item->balance);
                return $item;
            }
            if($item['bank_id'])
            {
               
                $item['name']=$item->name;
                $item['id']=$item->id;
                $item['balance'] = $item->balance;
                return $item;
            }
            if($item['user_id'])
            {
               
                $item['name']=$item->name;
                $item['id']=$item->id;
                $item['balance'] = 0.00;
                return $item;
            }
        
    });
    return response()->json($datas);
    }
}
