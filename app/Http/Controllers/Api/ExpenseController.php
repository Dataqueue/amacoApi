<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ColumnData;
use App\Models\Expense;
use App\Models\payment_account;
use App\Models\PaymentAccount;
use App\Models\AdvancePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Exception;
use Illuminate\Support\Facades\Storage;


class ExpenseController extends Controller
{
    /**
     * Display a listing of the expenses which are not paid.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
//         $expenses = Expense::join('account_categories','expenses.account_category_id','account_categories.id')->join('divisions','expenses.div_id','divisions.id')->
//         join('divisions as divtable','expenses.utilize_div_id','divtable.id')->select(
//     'divisions.name as paid_from',
//     'divtable.name as paid_towards',
//     'account_categories.name',
//             'expenses.*'
// )->where("status", "new")->orderBy('created_at', 'DESC')->get();
//         $expenses->map(function ($expense) {
//             return $expense->payment_account;
//         });
//         return response()->json($expenses);

$expenses = Expense::join('account_categories','expenses.account_category_id','account_categories.id')->join('payment_accounts','expenses.utilize_div_id','payment_accounts.id')->select(
'payment_accounts.name as paid_from',
'payment_accounts.name as paid_towards',
'account_categories.name',
    'expenses.*'
)->where("status", "new")->orderBy('created_at', 'DESC')->get();
$expenses->map(function ($expense) {
    return $expense->payment_account;
});
return response()->json($expenses);

    }

    // to get all paid expenses
    public function paid()
    {
        $expenses = $expenses = Expense::join('account_categories','expenses.account_category_id','account_categories.id')->join('payment_accounts','expenses.utilize_div_id','payment_accounts.id')->select(
            'payment_accounts.name as paid_from',
            'payment_accounts.name as paid_towards',
            'account_categories.name',
                'expenses.*'
)->where("status", 'verified')->orderBy('created_at', 'DESC')->get();
        $expenses->map(function ($expense) {
            return $expense->payment_account;
        });
        return response()->json($expenses);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $bank_slip_path = null;
        if ($request->file('bank_slip')) {
            $bank_slip_path = $request->file('bank_slip')->move("expenses/bankSlip", $request->file('bank_slip')->getClientOriginalName());
        }

        if ($request->file('file_path')) {
            $filePath = $request->file('file_path')->move("expenses/filePath",  $request->file('file_path')->getClientOriginalName());
        }

        if($request->payment_account_id==="other"){
            $account=PaymentAccount::create([
            'name' => $request->payeename,
            'user_id' => null,
            ]);
            $lastInsertedId= $account->id;
           
        }
        else{
            $lastInsertedId= $request->payment_account_ids;

            
        }
        $data=[];
        $div_id= $request->utilize_div_id;
        $arr=collect($request->payment_account_ids);
      
        



        $sumVal=floatval(0);
        $status=false;
        $amountVal=$request->amount;

        $map = $arr->map(
            function($items) use($request,$sumVal,$status,$amountVal) {
                $pieces = explode(",", $items);
                  $data['id'] = floatval($pieces[0]);
                  
                 

                    
                   

                   
               
                
                 
                  
                  return $data['id'];
                }
            );
            
           
            // $collection = [1,2,3,4,5];
             $demo=$map->toArray();
             $test=implode(',',$demo);
            $expense = Expense::create([
                'created_by' => $request->created_by,
                'paid_date' => $request->paid_date,
                'paid_to' => $request->paid_to?$request->paid_to:' ',
                'amount' => $request->amount,
                'payment_type' => $request->payment_type,
                'check_no' => $request->cheque_no,
                'transaction_id' => $request->transaction_id,
                'payment_account_id' =>$test,
                'description' => $request->description?$request->description:' ',
                'referrence_bill_no' => $request->referrence_bill_no,
                'tax' => $request->tax,
                'status' => $request->status,
                // 'paid_by' => $lastInsertedId,
                'bank_ref_no' => $request->bank_ref_no,
                'bank_id' => $request->bank_id?$request->bank_id:null,
                'bank_slip' => $request->file('bank_slip') ? $bank_slip_path : null,
                // // 'bank_slip' =>  $path ,
                "account_category_id" => $request->account_category_id,
                "company_name" => $request->company_name ? $request->company_name : " ",
                "file_path" => $request->file('file_path')?$filePath:null,
                // "div_id" => $request->div_id,
                "company" => $request->company?$request->company:" ",
                "vatno" => $request->vatno?$request->vatno:" ",
                "inv_no" => $request->inv_no?$request->inv_no:" ",
                "utilize_div_id"=>$request->utilize_div_id?$request->utilize_div_id:" "
    
            ]);
    
            $tempArray = (array) json_decode($request->data, true);
            foreach ($tempArray as $column_data_) {
                $column_data = $column_data_;
    
                $column_type = $column_data['type'];
                if ($column_type != 'file') {
                    $column_data_value = $column_data[$column_type];
                }
                $tempFile = "file" . $column_data['id'];
                if ($request->file($tempFile)) {
                    $column_data_value = $request->file($tempFile)->move('expenses/files', $request->file($tempFile)->getClientOriginalName());
                }
    
    
    
    
                ColumnData::create([
                    "expense_id" => $expense->id,
                    "column_id" => $column_data['id'],
                    "value" => $column_data_value ? $column_data_value : null,
                ]);
            }
            $maps = $arr->map(
                function($items) use($expense,$request,$sumVal,$status,$amountVal) {
                    $pieces = explode(",", $items);
                      $data['id'] = floatval($pieces[0]);
                      
                     
    
                        if(floatval($request->utilize_div_id)!==floatval($pieces[0]))
                        {
                      
                        AdvancePayment::create([
                            "payment_account_id" => $data['id'],
                            "received_by" => $request->utilize_div_id,
                            "amount" => floatval($pieces[2]),
                            "payment_mode" => $request->payment_type,
                            "expense_id" => $expense->id,
                            'received_date' => $request->paid_date,
                        ]); 
                        
                       
    
                       
                        }
                       
    
                       
                   
                    
                     
                      
                      return $data['id'];
                    }
                );
            return response()->json($test);
        }
        // }
    

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Expense  $expense
     * @return \Illuminate\Http\Response
     */
    public function show(Expense $expense)
    {
        $data=[];
        $resultArray = collect(explode(',',$expense->payment_account_id));
        $memebrsInfo = $expense->payment_account_id;
        $map=$resultArray->map(
            function($items,$key) use($data) {
               
                
                $result=Paymentaccount::where('id',floatval($items))->get();
                
                
                return $result;
            }
        );
        // $collection =  collect([explode('.',$memebrsInfo)]);

        // $multiplied = $collection->map(function ($item, $key) {
        //     return floatval($item) * 2;
        // });
        return response()->json([
            $expense,
            $expense->payment_account,
            $expense->column_data->map(function ($item) {
                if (File::exists(public_path($item->value))) {
                    $item['file'] = url($item->value);
                }
                return $item->column;
            }),
            'mapdata'=>$map,
            'img' => $expense->img(),
            'referrenceImgUrl' => $expense->referrenceImg(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Expense  $expense
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Expense $expense)
    {
        // $request['is_paid'] = true;
        $reqdata = $request->all(); 
        // $expense = Expense::findOrfail($request->id);
        if($request->status==="verified")
        {
        $expense= Expense::where('id',$request->id)->update(['status' => $request->status]);
        }
        else
        {
            $expense= Expense::where('id',$request->id)->update(['is_paid' =>1]); 
            
        }
        // $expense->update($request->all());
        
        return response()->json($request->id);


        


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Expense  $expense
     * @return \Illuminate\Http\Response
     * 
     * 
     */
        public function destroy(Expense $expense)
    {
        $expense->delete();
        $res=AdvancePayment::where('expense_id',$expense->id)->delete();
        return response()->json(['msg' => 'Expense ' . $expense . ' has been deleted.']);
    }

    public function expenseUpdate(Request $request, Expense $expense)
    {     
        if ($request->file('file_path')) {
            $filePath = $request->file('file_path')->move("expenses/filePath",  $request->file('file_path')->getClientOriginalName());
            $expenseF=Expense::where('id',$request->id)->update([
                "file_path" => $request->file('file_path') ? $filePath : null,
            ]);
        }
        if ($request->file('bank_slip')) {
            $bank_slip_path = $request->file('bank_slip')->move("expenses/bankSlip", $request->file('bank_slip')->getClientOriginalName());
            $expenseB=Expense::where('id',$request->id)->update([
                "bank_slip" => $request->file('bank_slip') ? $bank_slip_path : null,
            ]);
        }
          $expenseId = Expense::findOrfail($request->id);
         
          if($request->payeename){
            $account=PaymentAccount::create([
            'name' => $user->name,
            'user_id' => null,
            ]);
            return $lastInsertedId= $account->id;
        }
        $data=[];
        $div_id= $request->utilize_div_id;
        $arr=collect($request->payment_account_ids);
      
        



        $sumVal=floatval(0);
        $status=false;
        $amountVal=$request->amount;
        

        $map = $arr->map(
            function($items) use($request,$sumVal,$status,$amountVal) {
                $pieces = explode(",", $items);
                  $data['id'] = floatval($pieces[0]);
                  
                 

                    if(floatval($request->utilize_div_id)!==floatval($pieces[0]))
                    {
                    $res=AdvancePayment::where('expense_id',$request->id)->delete();
                    AdvancePayment::create([
                        "payment_account_id" => $data['id'],
                        "received_by" => $request->utilize_div_id,
                        "amount" => floatval($pieces[2]),
                        "payment_mode" => $request->payment_type,
                        "received_date" => $request->paid_date,
                        "expense_id" => $request->id,
                    ]); 
                    
                   

                   
                    }
                   

                   
               
                
                 
                  
                  return $data['id'];
                }
            );
            
           
            // $collection = [1,2,3,4,5];
             $demo=$map->toArray();
             $test=implode(',',$demo);
          
          $expense= Expense::where('id',$request->id)->update([
            'created_by' => $request->created_by,
            'paid_date' => $request->paid_date,
            'paid_to' => $request->paid_to?$request->paid_to:" ",
            'amount' => $request->amount,
            'payment_type' => $request->payment_type,
            'check_no' => $request->cheque_no,
            'transaction_id' => $request->transaction_id,
            'payment_account_id' => $test,
            'description' => $request->description?$request->description:" ",
            // 'referrence_bill_no' => $request->referrence_bill_no,
            'tax' => $request->tax,
            'status' => $request->status,
            // 'paid_by' => $request->payment_account_id?$request->payment_account_id:null,
            'bank_ref_no' => $request->bank_ref_no,
            // 'bank_slip' => $request->file('bank_slip') ? $bank_slip_path : null,
            // 'bank_slip' =>  $path ,
            "account_category_id" => $request->account_category_id,
            "company_name" => $request->company_name ? $request->company_name : " ",
            "company" => $request->company?$request->company:" ",
                "vatno" => $request->vatno?$request->vatno:" ",
                "inv_no" => $request->inv_no?$request->inv_no:" ",
                "utilize_div_id"=>$request->utilize_div_id?$request->utilize_div_id:" ",
                "div_id" => $request->div_id,
         
            'bank_id' => $request->bank_id?$request->bank_id:null,

        ]);
        
        
        $res=ColumnData::where('expense_id',$request->id)->delete();
        $tempArray = (array) json_decode($request->data, true);
         foreach ($tempArray as $column_data_) {
        $column_data = $column_data_;

        $column_type = $column_data['type'];
            if ($column_type != 'file') {
                $column_data_value = $column_data[$column_type];
            }
            $tempFile = "file" . $column_data['id'];
            if ($request->file($tempFile)) {
                $column_data_value = $request->file($tempFile)->move('expenses/files', $request->file($tempFile)->getClientOriginalName());
            }
            

            ColumnData::create([
                "expense_id" => $request->id,
                "column_id" => $column_data['column_id'],
                "value" => $column_data_value ? $column_data_value : null,
            ]);
            
           
        
        }
        return response()->json($tempArray);
       
        
    }
    public function singleExpense($id)
    {
        $expense = ColumnData::where('expense_id',$id)->join('expenses','ColumnData.expense_id','parties.id')->where('party_id', $party_id)->get();
        
        
        return response()->json([$expense]);
    }
}
