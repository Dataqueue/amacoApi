<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Party;
use App\Models\Division;
use App\Models\PaymentAccount;
use App\Models\AdvancePayment;
use App\Models\Receipt;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class MasterAccountController extends Controller
{
    public function getInvoiceData($div_id,  $to_date, $from_date = null)
    {
        $temp = new Collection();
        $temp = Expense::join('payment_accounts','expenses.payment_account_id','payment_accounts.id')->join('divisions','expenses.div_id','divisions.id')->where('is_paid',1)->select('divisions.name as div_name','payment_accounts.name as nick_name','expenses.*')->where('div_id', $div_id)->whereBetween('expenses.created_at', [$from_date . ' ' . '00:00:00', $to_date . ' ' . '23:59:59'])->get();
        return $temp;
    }

    public function getReceiptData($div_id,  $to_date, $from_date = null)
    {
        $temp = new Collection();
        $temp = Receipt::join('divisions','receipts.div_id','divisions.id')->select('divisions.name as div_name','receipts.*')->where('div_id', $div_id)->whereBetween('receipts.created_at', [$from_date . ' ' . '00:00:00', $to_date . ' ' . '23:59:59'])->get();
        return $temp;
    }


    public function masterStatement(Request $request)
    {
        $div = Division::where('id', intval($request['div_id']))->first();
        $total_div=Division::where('id',$div->id)->sum('opening_bal');
        if (!$div) {
            return response('No division exists by this id', 400);
        }

        // -----------------------------------
        $divOpeningBalance = floatval($div->opening_bal);
        $divEopenbalance=Expense::where('is_paid',1)->where('div_id',$div->id)->whereDate('created_at','<=' ,date('Y-m-d H:i:s', strtotime($request->from_date)))->sum('amount');
        $divRopenbalance=Receipt::where('div_id',$div->id)->whereDate('created_at','<=' ,date('Y-m-d H:i:s', strtotime($request->from_date)))->sum('paid_amount');

        $oldInvoiceCollection = $this->getInvoiceData($div->id, $request['from_date']);
        $oldReceiptCollection = $this->getReceiptData($div->id, $request['from_date']);
        $oldData = $oldInvoiceCollection->concat($oldReceiptCollection);
        if (!$oldData) {
            return response()->json(['msg' => "There are no entries between" . $request['from_date'] . " to " . $request['from_date']], 400);
        }
        $oldData = $oldData->sortBy('created_at');

        foreach ($oldData as $key => $item) {
            if ($item->amount) {
                $divOpeningBalance += floatVal($item['amount']);
            }

            if ($item->paid_amount) {
                $divOpeningBalance -= floatVal($item['paid_amount']);
            }
        }
        // ------------------------------------


        $invoiceCollection = $this->getInvoiceData($div->id, $request['to_date'], $request['from_date']);

        $receiptCollection = $this->getReceiptData($div->id, $request['to_date'], $request['from_date']);
        $data = $invoiceCollection->concat($receiptCollection);
        $data = $data->sortBy('created_at');

        $data && ( $datas['data'] = $data->map(function ($item)  {
            if ($item->amount) {
                $item['div_name']=$item->div_name;
                $item['user_name']=$item->nick_name;
                $item['date'] = $item->created_at;
                $item['code_no'] = $item->invoice_no;
                $item['description'] = $item->description;
                 $item['credit'] = floatval(str_replace(",","",$item->amount));
                $item['po_number'] = $item->po_number;
                $item['credit_days'] = floatval($item->credit_days);
                $item['debit'] = null;
                return [ $item ];
            }

            if ($item->paid_amount) {
                $item['div_name']=$item->div_name;
                $item['date'] = $item->created_at;
                $item['code_no'] = $item->receipt_no;
                $item['description'] = "Received";
                $item['debit'] = floatval(str_replace(",","",$item->paid_amount));
                $item['po_number'] = $item->po_number;
                $item['credit_days'] = floatval($item->credit_days);
                $item['credit'] = null;
                return [$item];

            }
        }));

        !$data && $datas['data'] = null;
        $datas['opening_balance'] = $divRopenbalance-$divEopenbalance+$total_div;
        $datas['firm_name'] = $div->firm_name;
        $datas['credit_days'] = $div->credit_days;
        $datas['from_date'] = $request['from_date'];
        $datas['to_date'] = $request['to_date'];

        return response()->json([$datas]);
    }

    public function allAccountmasterStatement(Request $request)
    {
        $invoiceCollection = new Collection();
        $total_div=Paymentaccount::where('type','division')->sum('balance');
        // $total_div=Division::sum('opening_bal');
        if($request->from_date){
            $invoiceCollection = Expense::join('payment_accounts','expenses.utilize_div_id','payment_accounts.id')->join('account_categories','account_categories.id','expenses.account_category_id')->where('status','verified')->where('payment_accounts.type','division')->select('payment_accounts.name as div_name','payment_accounts.name as nick_name','account_categories.name as cat_name','expenses.utilize_div_id as divid','expenses.utilize_div_id as expense_type','expenses.*')->whereBetween('expenses.created_at', [$request->from_date . ' ' . '00:00:00', $request->to_date ? $request->to_date . ' ' . '23:59:59' : now()])->get();

            $divEopenbalance=Expense::where('status','verified')->whereDate('created_at','<=' ,date('Y-m-d H:i:s', strtotime($request->from_date)))->sum('amount');
        }else{
            $invoiceCollection = Expense::all();
           
        }

        $receiptCollection = new Collection();
        if($request->from_date){
            $receiptCollection = Receipt::join('payment_accounts','receipts.div_id','payment_accounts.id')->join('parties','parties.id','receipts.party_id')->select('payment_accounts.name as div_name','receipts.*','parties.firm_name as paid_to','payment_accounts.name as receipt_type')->whereBetween('receipts.created_at', [$request->from_date . ' ' . '00:00:00', $request->to_date ? $request->to_date. ' ' . '23:59:59' : now()])->get();
            $divRopenbalance=Receipt::whereDate('created_at','<=' ,date('Y-m-d H:i:s', strtotime($request->from_date)))->sum('paid_amount');
        }else{
            $receiptCollection = Receipt::all();
           

        }
        $advanceCollection = new Collection();
        $advance=AdvancePayment::whereBetween('advance_payments.created_at', [$request->from_date . ' ' . '00:00:00', $request->to_date ? $request->to_date. ' ' . '23:59:59' : now()])->get();
        $advanceData=$advance->filter(function($obj){
            if($obj->paymentAccount->type=="division" && $obj->receivedBy->type=="personal")
                {
            return $obj;
                }
         if($obj->receivedBy->type=="division" && $obj->paymentAccount->type=="personal"){
            return $obj;
        }
        });
        //     $obj["status"]='advance_type';
        //     $obj["paidBy"]=$obj->paymentAccount->name;
        //     $obj["receivedBy"]=$obj->receivedBy->name;
        //     $obj["paidByType"]=$obj->paymentAccount->type;
        //     $obj["receivedByType"]=$obj->receivedBy->type;
        //     $obj["advance_amount"]=$obj->amount;
        //     $obj->paymentAccount;
        //     $obj->receivedBy;
           

        //     return $obj;
        // });

        // $data =$advanceData;
        $data = $invoiceCollection->concat($receiptCollection)->concat($advanceData);
        

        $data && ($datas['data'] = $data->map(function ($item) {
            // if ($item->amount) {
            if ($item->expense_type) {
                $item['div_name']=$item->div_name;
                $item['user_name']=$item->nick_name;
                $item['div_id']=$item->divid;
                $item['date'] = $item->created_at;
                $item['code_no'] = $item->invoice_no;
                $item['description'] = $item->description;
                $item['paid_to'] = $item->paid_to;
                $item['cat_name'] = $item->cat_name;
                $item['credit'] = floatval(str_replace(",","",$item->amount));
                $item['po_number'] = $item->po_number;
                $item['debit'] = null;
                // $item['credit_days'] = floatval($item->credit_days);
                return [$item];
            }

            if ($item->receipt_type) {
            // if ($item->paid_amount) {
                $item['div_name']=$item->div_name;
                $item['date'] = $item->created_at;
                $item['code_no'] = $item->receipt_no;
                $item['paid_to'] = $item->paid_to;
                $item['description'] = $item->narration;
                $item['cat_name'] = 'Received';
                $item['debit'] = floatval(str_replace(",","",$item->paid_amount));
                $item['po_number'] = $item->po_number;
                $item['credit'] = null;
                // $item['credit_days'] = floatval($item->credit_days);
                return [$item];
            }
            // if($item->status=="advance_type")
            // {

                if($item->paymentAccount->type=="division" && $item->receivedBy->type=="personal")
                {
                    $item['div_name']=$item->paymentAccount->name;
                    $item['date'] = $item->created_at;
                    $item['code_no'] = " ";
                    $item['paid_to'] = $item->receivedBy->name;
                    $item['description'] = $item->narration;
                    $item['cat_name'] = 'Division';
                    $item['credit'] = floatval(str_replace(",","",$item->amount));
                    $item['po_number'] = " ";
                    $item['debit'] = null;
                    // $item['credit_days'] = floatval($item->credit_days);
                    return [$item];

                }
                if($item->receivedBy->type=="division" && $item->paymentAccount->type=="personal")
                {
                    
                    
                    $item['div_name']=$item->receivedBy->name;
                    $item['date'] = $item->created_at;
                    $item['code_no'] = " ";
                    $item['paid_to'] = $item->paymentAccount->name;
                    $item['description'] = $item->narration;
                    $item['cat_name'] = 'Division';
                    $item['debit'] = floatval(str_replace(",","",$item->amount));
                    $item['po_number'] = " ";
                    $item['credit'] = null;
                    return [$item];
                

                  
                }
               

            // }
          
        
            
           
        }));
        
        $datas['opening_balance'] = $divRopenbalance-$divEopenbalance+$total_div;
        $datas['name'] = "All";
        $datas['from_date'] = $request['from_date'] ? $request['from_date'] : "2021-01-01";
        $datas['to_date'] = $request['to_date'] ? $request['to_date'] : substr(now(), 0, 10);

        return response()->json([$datas]);
    }
}
