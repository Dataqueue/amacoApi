<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Party;
use App\Models\Division;
use App\Models\Receipt;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class MasterAccountController extends Controller
{
    public function getInvoiceData($div_id,  $to_date, $from_date = null)
    {
        $temp = new Collection();
        $temp = Expense::join('divisions','expenses.div_id','divisions.id')->select('divisions.name as div_name','expenses.*')->where('div_id', $div_id)->whereBetween('expenses.created_at', [$from_date . ' ' . '00:00:00', $to_date . ' ' . '23:59:59'])->get();
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
        if (!$div) {
            return response('No division exists by this id', 400);
        }

        // -----------------------------------
        $divOpeningBalance = floatval($div->opening_bal);

        $oldInvoiceCollection = $this->getInvoiceData($div->id, $request['from_date']);
        $oldReceiptCollection = $this->getReceiptData($div->id, $request['from_date']);
        $oldData = $oldInvoiceCollection->merge($oldReceiptCollection);
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
        $data = $invoiceCollection->merge($receiptCollection);
        $data = $data->sortBy('created_at');

        $data && ( $datas['data'] = $data->map(function ($item)  {
            if ($item->amount) {
                $item['div_name']=$item->div_name;
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
        $datas['opening_balance'] = $divOpeningBalance;
        $datas['firm_name'] = $div->firm_name;
        $datas['credit_days'] = $div->credit_days;
        $datas['from_date'] = $request['from_date'];
        $datas['to_date'] = $request['to_date'];

        return response()->json([$datas]);
    }

    public function allAccountmasterStatement(Request $request)
    {
        $invoiceCollection = new Collection();
        $date="2021-08-09";
        $divEopenbalance=Floatval('0.00');
        $divRopenbalance=Floatval('0.00');
        if($request->from_date){
            $invoiceCollection = Expense::join('divisions','expenses.div_id','divisions.id')->select('divisions.name as div_name','expenses.*')->whereBetween('expenses.created_at', [$request->from_date . ' ' . '00:00:00', $request->to_date ? $request->to_date . ' ' . '23:59:59' : now()])->get();
            $divEopenbalance=$invoiceCollection->where('created_at', '<=', $request->from_date. ' ' . '00:00:00')->sum(['amount']);
        }else{
            $invoiceCollection = Expense::all();
            $divEopenbalance=Expense::where('created_at', '<=', $request->from_date)->sum('expenses.amount');
        }

        $receiptCollection = new Collection();
        if($request->from_date){
            $receiptCollection = Receipt::join('divisions','receipts.div_id','divisions.id')->select('divisions.name as div_name','receipts.*')->whereBetween('receipts.created_at', [$request->from_date . ' ' . '00:00:00', $request->to_date ? $request->to_date. ' ' . '23:59:59' : now()])->get();
            $divRopenbalance=$invoiceCollection->where('created_at', '<=',$request->from_date. ' ' . '00:00:00')->sum(['paid_amount']);
        }else{
            $receiptCollection = Receipt::all();
            $divRopenbalance=Receipt::where('created_at', '<=', $date)->sum('receipts.paid_amount');

        }

        $data = $invoiceCollection->merge($receiptCollection);
        $data = $data->sortBy('created_at');

        $data && ($datas['data'] = $data->map(function ($item) {
            if ($item->amount) {
                $item['div_name']=$item->div_name;
                $item['date'] = $item->created_at;
                $item['code_no'] = $item->invoice_no;
                $item['description'] = $item->description;
                $item['credit'] = floatval(str_replace(",","",$item->amount));
                $item['po_number'] = $item->po_number;
                $item['debit'] = null;
                // $item['credit_days'] = floatval($item->credit_days);
                return [$item];
            }

            if ($item->paid_amount) {
                $item['div_name']=$item->div_name;
                $item['date'] = $item->created_at;
                $item['code_no'] = $item->receipt_no;
                $item['description'] = "Received";
                $item['debit'] = floatval(str_replace(",","",$item->paid_amount));
                $item['po_number'] = $item->po_number;
                $item['credit'] = null;
                // $item['credit_days'] = floatval($item->credit_days);
                return [$item];
            }
        }));
        $datas['opening_balance'] = $divEopenbalance;
        $datas['name'] = "All";
        $datas['from_date'] = $request['from_date'] ? $request['from_date'] : "2021-01-01";
        $datas['to_date'] = $request['to_date'] ? $request['to_date'] : substr(now(), 0, 10);

        return response()->json([$datas]);
    }
}
