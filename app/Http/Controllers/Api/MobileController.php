<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccountCategory;
use App\Models\Division;
use App\Models\PaymentAccount;
use App\Models\CompanyBank;
use App\Models\Employees;
use App\Models\Party;
use App\Models\Expense;
use App\Models\Receipt;
use App\Models\AdvancePayment;



class MobileController extends Controller
{
    
    public function getMCat(){
        $gdata = AccountCategory::get()->whereNull('parent_id')->values();
        // $gdata = $gdata->whereNull('parent_id');
        $allcategories = AccountCategory::get()->values();
        $rootcategories = $allcategories->whereNull('parent_id');
        self::formatTree($rootcategories,$allcategories);


        $div = Division::all();
        $payment_accounts = PaymentAccount::all();
        $bank = CompanyBank::select('id as value','company_banks.*')->get();
        collect($bank)->map(function ($bank)  {
            $bank['label'] = $bank['name'] . ' - '.$bank['iban_no'];
            return $bank;
        });
        
         $employees = Employees::
            join('divisions','divisions.id','employee.div_id')
            ->orderby('emp_id','DESC')
            ->select('employee.name as label','employee.emp_id as value','divisions.name as div_name','divisions.*','employee.*')
            ->get();

         $vendors = Party::where('party_type', '=', 'Vendor')->orWhere('party_type', '=', 'both')
            ->select('id as value', 'firm_name as label', 'contact')
            ->get();
            // ->toArray();
            $vendors->map(function($payment){
                return $payment->partyDivision;
            });

            
        $divEopenbalance=Expense::where('is_paid',1)->sum('amount');
        $divRopenbalance=Receipt::sum('paid_amount');
        
        $division = PaymentAccount::get();
        $datas=$division->map(function ($item) {
            if($item['div_id'])
            {
                $divEopenbalance=Expense::where('is_paid',1)->where('utilize_div_id',$item['id'])->sum('amount'); 
                $accountSum=PaymentAccount::where('id',$item['id'])->sum('balance');
                $recievedby=AdvancePayment::where('received_by',$item['id'])->sum('amount');
                $paidby=AdvancePayment::where('payment_account_id',$item['id'])->sum('amount');
                $divRopenbalance=Receipt::where('div_id',$item['id'])->sum('paid_amount');
                $item['name']=$item->name;
                $item['id']=$item->id;
                $item['item']=$item->name . ' - ' . $item->type.' - ' .$item->balance;
                $item['balance'] = ($accountSum+$divRopenbalance+$recievedby)-($paidby+$divEopenbalance);
                
                return $item;
            }
            
           else
           {
            $accountSum=PaymentAccount::where('id',$item['id'])->sum('balance');
            $recievedby=AdvancePayment::where('received_by',$item['id'])->sum('amount');
            $paidby=AdvancePayment::where('payment_account_id',$item['id'])->sum('amount');
            $paid_date=AdvancePayment::where('received_by',$item['id'])->orderBy('received_date','DESC')->first('received_date');
            $item['date']=!empty($paid_date->received_date)?$paid_date->received_date:$item->created_at;
            $item['name']=$item->name;
            $item['id']=$item->id;
            $item['item']=$item->name . ' - ' . $item->type.' - ' .$item->balance;
            $item['balance'] =$accountSum+$recievedby-$paidby;
            return $item;

           }
        
    });

        
        return response()->json([
                'getAllCat' => $rootcategories->values(),
                'getCat' => $gdata,
                'division' => $div,
                'payment_account' => $payment_accounts,
                'banks' => $bank,
                'employees' => $employees,
                'vendors' => $vendors,
                'paidAccount' => $datas,
                'status' => 200,
            ]);
    }

    private static function formatTree($categories ,$allcategories){
        foreach($categories as $category){
            $category->children = $allcategories -> where('parent_id',$category->id )->values();
            if($category->children->isNotEmpty() ){
                self::formatTree($category->children,$allcategories);
            }
        }
    }


    public function storeExpence(Request $request){

        $bank_slip_path = null;
        $ref_bill_path = null;
        if (hasFile($request->file('bank_slip'))) {
            $bank_slip_path = $request->file('bank_slip')->move("expenses/bankSlip", $request->file('bank_slip')->getClientOriginalName());
        }
        if (hasFile($request->file('file_path'))) {
            $ref_bill_path = $request->file('file_path')->move("expenses/bankSlip", $request->file('file_path')->getClientOriginalName());
        }

       

               $expense = Expense::create([
                'bank_slip' => $bank_slip_path, 
                'file_path' => $ref_bill_path, 
                'paid_by' => $request->paid_by, 
                'referrence_bill_no' => $request->referrence_bill_no, 
                'paid_date' => $request->paid_date, 
                'paid_to' => $request->paid_to, 
                'amount' => $request->amount, 
                'payment_type' => $request->payment_type, 
               
               
                'check_no' => $request->check_no, 
                'transaction_id' => $request->transaction_id, 
                // 'payment_account_id',$request->payment_account_id, 
                'description' => $request->description, 
                // 'is_paid' => $request->is_paid, 
                'tax' => $request->tax, 
                'status' => 'new', 
                'bank_ref_no' => $request->bank_ref_no, 
                'bank_slip' => $request->bank_slip, 
                
                
                
                
                'account_category_id' => $request->account_category_id, 
                'company_name' => $request->company_name, 
                'file_path' => $request->file_path, 
                'div_id' => $request->div_id, 
                'company' => $request->company, 
                'vatno' => $request->vatno, 
                'inv_no' => $request->inv_no,
                'utilize_div_id' => $request->utilize_div_id, 
                'bank_id' => $request->bank_id, 
                'voucher_no' => $request->voucher_no, 
                'vendor_id' => $request->vendor_id, 
                'employee_id' => $request->employee_id,
            ]);
            
            return $request->amount;
    
    }


}
