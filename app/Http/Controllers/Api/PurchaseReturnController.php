<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Quotation;
use App\Models\QuotationDetail;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnDetail;
use DB;


class PurchaseReturnController extends Controller
{
    public function index($id){
        
        $data = Quotation::
        where('transaction_type','=','purchase')
        ->where('party_id','=',$id)
        ->get();


        return response()->json([
            'status' => 200,
            'getPurchaseReturnData' => $data,
        ]);

    }

    public function getProductsPR($po){

        $data = Quotation::
        where('transaction_type','=','purchase')
        ->where('po_number','=',$po)
        ->get('id');

          $dd = QuotationDetail::
        where('quotation_id','=',$data[0]->id)
        ->get();


        return response()->json([
            'status' => 200,
            'getPData' => $dd,
        ]);
    }


  public function getLastPONo()
    {
        $quotation = PurchaseReturn::
            orderby('pr_id','DESC')->first();
        if ($quotation) {
            $latest_po_number = $quotation->pr_number ? $quotation->pr_number : 0;
            return ($latest_po_number);
        } else {
            return ('AMC-DN-' . $this->getCurrentYear() . '-' . $this->getCurrentMonth() . sprintf("%02d", 0));
        }
    }

    
    public function getCurrentYear()
    {
        return substr(date('Y'), 2);
    }

    public function getCurrentMonth()
    {
        return date('m');
    }


       public function getPONo()
    {
        $latest_po_number = $this->getLastPONo();
        $last_year = substr($latest_po_number, 7, 2);
        $last_month = substr($latest_po_number, 10, 2);
        $current_year = $this->getCurrentYear();
        $current_month = $this->getCurrentMonth();
        if ($current_year != $last_year) {
            return ('AMC-DN-' . $current_year . '-' . $current_month  . sprintf("%02d", 1));
        } 
        else {
            if ($current_month != $last_month) {
                return ('AMC-DN-' . $current_year . '-' . $current_month  . sprintf("%02d", 1));
            } else {
                if (((int)substr($this->getLastPONo(), 12) < 99)) {
                    return ('AMC-DN-' . $current_year . '-' . $current_month . sprintf("%02d", ((int)substr($this->getLastPONo(), 12)) + 1));
                } else {
                    return ('AMC-DN-' . $current_year . '-' . $current_month . sprintf("%03d", ((int)substr($this->getLastPONo(), 12)) + 1));
                }
            }
        }
    }

    public function purchasereturn(Request $request){


        
        // $rfqId = null;
        $rfqId = $request->rfq_id ? $request->rfq_id :null;
        $parentId = null;
        if($request['parent_id']){
            $parentId = $request['parent_id'];

        }



        // // try {
            $datas = [
                'party_id' => $request['party_id'],
                'rfq_id' => $request['rfq_id']?$request['rfq_id']:0,
                'status' => 'New',
                'parent_id' => $parentId,
                'total_value' => $request['total_value'],
                'net_amount' => $request['net_amount'],
                'vat_in_value' => $request['vat_in_value'],
                'discount_in_p' => $request['discount_in_p'],
                'validity' => $request['validity'],
                'payment_terms' => $request['payment_terms'],
                'warranty' => $request['warranty'],
                'currency_type' => $request['currency_type'],
                'freight_type' => $request['freight'],
                'delivery_time' => $request['delivery_time'],
                
                'inco_terms' => $request['inco_terms'],
                // 'pr_number' => $this->getPONo(),
                'contact_id' => $request['contact_id']?$request['contact_id']:null,
                'transaction_type' => $request['transaction_type'],
                'ps_date' => $request['ps_date'],  // ? $request['ps_date'] : Carbon::now()
                'sign' => $request['sign'],  // ? $request['ps_date'] : Carbon::now()
                'bank_id' => (int)$request['bank_id'],  // ? $request['ps_date'] : Carbon::now()
                'subject' => $request['subject']?$request['subject']:null,  // ? $request['ps_date'] : Carbon::now()
                'rfq_no' => $request['rfq_no']?$request['rfq_no']:null,  // ? $request['ps_date'] : Carbon::now()
            ];
 
 
            if ($request['transaction_type'] == "sales") {
                    $datas['quotationr_no'] = $this->getQuotationNo();
            } elseif ($request['transaction_type'] == "purchase") {
                $datas['pr_number'] = $this->getPONo();
            }

            // $datas['pr_number'] = $this->getPONo();
            $quotation = PurchaseReturn::create($datas);
           
            global $quotation_id;
            $quotation_id = $quotation->id;
            
            if ($request->transaction_type === 'purchase') {
                foreach ($request['quotation_details'] as $key => $quotation_detail) {
                    $a = $quotation_detail['po_number'];
                    PurchaseReturnDetail::create([
                        'pr_id' => $quotation_id,
                        'total_amount' => $quotation_detail['total_amount'],
                        'analyse_id' => null,
                        'po_number' => $quotation_detail['po_number'],
                        'product_id' => $quotation_detail['product_id']?$quotation_detail['product_id']:null,
                        'purchase_price' => $quotation_detail['purchase_price'],
                        'description' => $quotation_detail['product_name']?$quotation_detail['product_name']:$quotation_detail['product'],
                        'product_description' => $quotation_detail['description'],
                        'quantity' => $quotation_detail['quantity'],
                        'unit_of_measure' => $quotation_detail['unit_of_measure'],
                        'margin' => $quotation_detail['margin'],
                        'sell_price' => $quotation_detail['sell_price'],
                        'remark' => $quotation_detail['remark'],
                    ]);
                }
            }else{
                 foreach ($request['quotation_details'] as $key => $quotation_detail) {
                    // $a = $quotation_detail['po_number'];
                    PurchaseReturnDetail::create([
                        'pr_id' => $quotation_id,
                        'total_amount' => $quotation_detail['total_amount'],
                        'analyse_id' => null,
                        'invoice_no' => $quotation_detail['invoice_no'],
                        'product_id' => $quotation_detail['product_id']?$quotation_detail['product_id']:null,
                        'purchase_price' => $quotation_detail['purchase_price'],
                        'description' => $quotation_detail['product_name']?$quotation_detail['product_name']:$quotation_detail['product'],
                        'product_description' => $quotation_detail['description'],
                        'quantity' => $quotation_detail['quantity'],
                        'unit_of_measure' => $quotation_detail['unit_of_measure'],
                        'margin' => $quotation_detail['margin'],
                        'sell_price' => $quotation_detail['sell_price'],
                        'remark' => $quotation_detail['remark'],
                    ]);
                }
                
            }

            return response()->json([
            'status' => 200,
            'getReturnParty' =>$request['transaction_type'],
        ]);  
          
    }

     public function revisedQuotationNo($quotationNo)
    {
        if(strlen($quotationNo) > 14){
            $revisedQuotation =  substr($quotationNo, 0,14)."-REV-".sprintf("%02d",((int)substr($quotationNo, 19))+1);
            return $revisedQuotation;
        }else{
            $revisedQuotation =  $quotationNo. "-REV-" . sprintf("%02d", 1);
            return $revisedQuotation;
        }
    }
     public function getQuotationNo()
    {
        $latest_quotation_no = $this->getLastQuotationNo();
        $last_year = substr($latest_quotation_no, 7, 2);
        $last_month = substr($latest_quotation_no, 10, 2);
        $current_year = $this->getCurrentYear();
        $current_month = $this->getCurrentMonth();
        if ($current_year != $last_year) {
            return ('AMC-CN-' . $current_year . '-' . $current_month  . sprintf("%02d", 1));
        } else {
            if ($current_month != $last_month) {
                 
                return ('AMC-CN-' . $current_year . '-' . $current_month  . sprintf("%02d", 1));
            } else {
               
                if (((int)substr($this->getLastQuotationNo(), 12) < 99)) {
                    return ('AMC-CN-' . $current_year . '-' . $current_month . sprintf("%02d", ((int)substr($this->getLastQuotationNo(), 12)) + 1));
                } else {
                    return ('AMC-CN-' . $current_year . '-' . $current_month . sprintf("%03d", ((int)substr($this->getLastQuotationNo(), 12)) + 1));
                }
            }
        }
    }
    
    public function getLastQuotationNo()
    {
        $quotation = PurchaseReturn::where('transaction_type', 'sales')
            ->latest('created_at')->first();


            if ($quotation) {
            $latest_quotation_no = $quotation->quotationr_no ? $quotation->quotationr_no : 0;
            return ($latest_quotation_no);
        } else {
            return ('AMC-CN-' . $this->getCurrentYear() . '-' . $this->getCurrentMonth() . sprintf("%02d", 0));
        }
    }

    public function purchaseReturnTableData(){
        $quotations = PurchaseReturn::
        join('parties','parties.id','purchase_returns.party_id')
        ->where("transaction_type",'purchase')
        ->orderBy('purchase_returns.created_at', 'DESC')
        ->get();


        return response()->json($quotations, 200);
    }

    public function getPurchaseReturnINV($id){
        $purchaseReturn = PurchaseReturn::
        join('parties','parties.id','purchase_returns.party_id')
        ->where('purchase_returns.transaction_type','purchase')
        ->where('purchase_returns.pr_id','=',$id)
        ->orderBy('purchase_returns.created_at', 'DESC')
        ->get();

        $returnItems = PurchaseReturnDetail::
        where('pr_id','=',$id)
        ->get();

        return response()->json([
            'status' => 200,
            'getReturnParty' => $purchaseReturn,
            'getReturnItems' => $returnItems
        ]);    
    }

    public function deletepurchasereturn($id){

        $purchaseR = PurchaseReturn::where('pr_id','=',$id)->delete();
        $purchaseRD = PurchaseReturnDetail::where('pr_id','=',$id)->delete();
         return response()->json([
            'status' => 200,
            'getReturnParty' => "Deleted"
        ]);  




    }

}



