<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Expense;
use App\Models\Receipt;
use App\Models\PaymentAccount;
use App\Models\AdvancePayment;
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
        PaymentAccount::create([
            'div_id'=> $party->id,
            'name'=>$party->name,
            'balance'=>$party->opening_bal,
            'type'=>'division',


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
       
        
        $division = PaymentAccount::where('id',$id)->get();
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
                $divEopenbalance=Expense::where('is_paid',1)->where('utilize_div_id',$item['id'])->sum('amount'); 
                $accountSum=PaymentAccount::where('id',$item['id'])->sum('balance');
                $recievedby=AdvancePayment::where('received_by',$item['id'])->sum('amount');
                $divRopenbalance=Receipt::where('div_id',$item['id'])->sum('paid_amount');
                $item['name']=$item->name;
                $item['id']=$item->id;
                $item['balance'] = $recievedby;
                return $item;
            }
            
           else
           {
            
            $item['name']=$item->name;
            $item['id']=$item->id;
            $item['balance'] =0.00;
            return $item;

           }
        
    });
    return response()->json($datas);
    }
}
