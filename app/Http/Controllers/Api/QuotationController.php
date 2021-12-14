<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\QuotationDetail;
use Illuminate\Http\Request;
use App\Models\DeliveryNote;
use App\Models\DeliveryNoteDetail;
use App\Models\CompanyBank;

use App\Models\notes;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use Exception;
use Illuminate\Support\Facades\File;

class QuotationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    // public function checkDeliveredProductQuantity($quotation_detail)
    // {
    //     $product_quantity = $quotation_detail->product->quantity;
    //     $product_id = $quotation_detail->product->id;
    //     $quotation_id = $quotation_detail->quotation->id;
    //     $deliveryNote = DeliveryNote::where('quotation_id',$quotation_id)->firstOrFail();
    //     $res = $deliveryNote->deliveryNoteDetail ?? $deliveryNote->deliveryNoteDetail->map(function($deliveryNoteD, $this->product_quantity,$this->product_id){
    //         if($deliveryNoteD->product_id == $product_id){
    //             if($product_quantity - $deliveryNoteD->quantity != 0){
    //                 return true;
    //             }else{
    //                 return false;
    //             }
    //         }
    //     });
    //     // dd($quotation_detail);
    //     return $res;
    // }

    public function getCurrentYear()
    {
        return substr(date('Y'), 2);
    }

    public function getCurrentMonth()
    {
        return date('m');
    }

    public function getLastQuotationNo()
    {
        $quotation = Quotation::where('transaction_type', 'sale')
            ->latest('created_at')->first();
        if ($quotation) {
            $latest_quotation_no = $quotation->quotation_no ? $quotation->quotation_no : 0;
            return ($latest_quotation_no);
        } else {
            return ('AMC-QT-' . $this->getCurrentYear() . '-' . $this->getCurrentMonth() . sprintf("%02d", 0));
        }
    }

    public function getLastPONo()
    {
        $quotation = Quotation::where('transaction_type', 'purchase')
            ->latest('created_at')->first();
        if ($quotation) {
            $latest_po_number = $quotation->po_number ? $quotation->po_number : 0;
            return ($latest_po_number);
        } else {
            return ('AMC-PO-' . $this->getCurrentYear() . '-' . $this->getCurrentMonth() . sprintf("%02d", 0));
        }
    }

    public function getLastSONo()
    {
        $quotation = Quotation::where('transaction_type', 'sale')
            ->latest('created_at')->first();
        if ($quotation) {
            $latest_sales_order_number = $quotation->sales_order_number ? $quotation->sales_order_number : 0;
            return ($latest_sales_order_number);
        } else {
            return ('ASON-' . $this->getCurrentYear() . '-' . sprintf("%04d", 0));
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
            return ('AMC-QT-' . $current_year . '-' . $current_month  . sprintf("%02d", 1));
        } else {
            if ($current_month != $last_month) {
                return ('AMC-QT-' . $current_year . '-' . $current_month  . sprintf("%02d", 1));
            } else {
                if (((int)substr($this->getLastQuotationNo(), 12) < 99)) {
                    return ('AMC-QT-' . $current_year . '-' . $current_month . sprintf("%02d", ((int)substr($this->getLastQuotationNo(), 12)) + 1));
                } else {
                    return ('AMC-QT-' . $current_year . '-' . $current_month . sprintf("%03d", ((int)substr($this->getLastQuotationNo(), 12)) + 1));
                }
            }
        }
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


    public function getPONo()
    {
        $latest_po_number = $this->getLastPONo();
        $last_year = substr($latest_po_number, 7, 2);
        $last_month = substr($latest_po_number, 10, 2);
        $current_year = $this->getCurrentYear();
        $current_month = $this->getCurrentMonth();
        if ($current_year != $last_year) {
            return ('AMC-PO-' . $current_year . '-' . $current_month  . sprintf("%02d", 1));
        } else {
            if ($current_month != $last_month) {
                return ('AMC-PO-' . $current_year . '-' . $current_month  . sprintf("%02d", 1));
            } else {
                if (((int)substr($this->getLastPONo(), 12) < 99)) {
                    return ('AMC-PO-' . $current_year . '-' . $current_month . sprintf("%02d", ((int)substr($this->getLastPONo(), 12)) + 1));
                } else {
                    return ('AMC-PO-' . $current_year . '-' . $current_month . sprintf("%03d", ((int)substr($this->getLastPONo(), 12)) + 1));
                }
            }
        }
    }

    public function getSalesOrderNumber()
    {
        $latest_sales_order_number = $this->getLastSONo();
        $last_year = substr($latest_sales_order_number, 5, 2);
        $current_year = $this->getCurrentYear();
        // dd([$last_year, $current_year]);
        if ($current_year != $last_year) {
            return ('ASON-' . $current_year . '-' . sprintf("%04d", 1));
        } else {
            return ('ASON-' . $current_year . '-' . sprintf("%04d", ((int)substr($this->getLastSONo(), 9)) + 1));
        }
    }

    public function index() // Purchase List
    {
        $quotations = Quotation::where(['status' => 'New', 'transaction_type' => 'purchase'])
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('invoices')
                    ->whereRaw('invoices.quotation_id = quotations.id');
            })->orderBy('created_at', 'DESC')
            ->get();
        // $quotations = Quotation::where('status','=','New')->orderBy('created_at','DESC')->get();
        $quotations_data = [
            $quotations->map(
                function ($quotation) {
                    $data = [
                        'id' => $quotation->id,
                        'po_number' => $quotation->po_number,
                        'created_at' => $quotation->created_at,
                        'updated_at' => $quotation->updated_at,
                        'status' => $quotation->status,
                        'total_value' => $quotation->total_value,
                        'party_id' => $quotation->party_id,
                        "contact_id" => $quotation->contact_id,
                        "contact" => $quotation->contact,
                        "party" => $quotation->party,
                        // "partyDivision"=>$quotation->partyDivision,
                        "vat_in_value" => $quotation->vat_in_value,
                        "net_amount" => $quotation->net_amount,
                        "transaction_type" => $quotation->transaction_type,
                        'discount_in_p' => $quotation->discount_in_p,
                        'ps_date' => $quotation->ps_date,
                        'quotation_details' => $quotation->quotationDetail->map(function ($quotation_detail) {
                            $quotation_detail = QuotationDetail::where('id', '=', $quotation_detail->id)->first();
                            return [
                                "id" => $quotation_detail['id'],
                                "created_at" => $quotation_detail->created_at,
                                "updated_at" => $quotation_detail->updated_at,
                                "product_id" => $quotation_detail->product_id,
                                "product" => array($quotation_detail->product),
                                "description" => $quotation_detail->description,
                                "quantity" => $quotation_detail->quantity,
                                "total_amount" => $quotation_detail->total_amount,
                                "analyse_id" => $quotation_detail->analyse_id,
                                "purchase_price" => $quotation_detail->purchase_price,
                                "amaco_description" => $quotation_detail->descriptionss,
                                "margin" => $quotation_detail->margin,
                                "sell_price" => $quotation_detail->sell_price,
                                "remark" => $quotation_detail->remark,
                            ];
                        }),
                    ];
                    return $data;
                }
            ),
        ];
        return response()->json($quotations_data[0], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     */

    public function store(Request $request)
    {
        
        
        // $rfqId = null;
        $rfqId = $request->rfq_id ? $request->rfq_id :null;
        $parentId = null;
        if($request['parent_id']){
            $parentId = $request['parent_id'];

        }



        // try {
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
                'contact_id' => $request['contact_id']?$request['contact_id']:null,
                'transaction_type' => $request['transaction_type'],
                'ps_date' => $request['ps_date'],  // ? $request['ps_date'] : Carbon::now()
                'sign' => $request['sign'],  // ? $request['ps_date'] : Carbon::now()
                'bank_id' => (int)$request['bank_id'],  // ? $request['ps_date'] : Carbon::now()
                'subject' => $request['subject']?$request['subject']:null,  // ? $request['ps_date'] : Carbon::now()
                'rfq_no' => $request['rfq_no']?$request['rfq_no']:null,  // ? $request['ps_date'] : Carbon::now()
                
            ];

            if ($request->transaction_type === 'sale') {
                if ($request['parent_id']) {
                    $datas['quotation_no'] = $this->revisedQuotationNo($request['quotation_no']);
                
                }else{
                    $datas['quotation_no'] = $this->getQuotationNo();
                }
            } elseif ($request->transaction_type === 'purchase') {
                $datas['po_number'] = $this->getPONo();
            } else {
                $datas['quotation_no'] = null;
                $datas['po_number'] = null;
            }

            $quotation = Quotation::create($datas);
           


            global $quotation_id;
            $quotation_id = $quotation->id;
            
            if ($request->transaction_type === 'purchase') {
                foreach ($request['quotation_details'] as $key => $quotation_detail) {
                    QuotationDetail::create([
                        'quotation_id' => $quotation_id,
                        'total_amount' => $quotation_detail['total_amount'],
                        'analyse_id' => null,
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
            } else {
                $index = 0;
                while ($request['quotation_detail' . $index] != null) {
                    $quotation_detail = (array) json_decode($request['quotation_detail' . $index], true);
                    $filePath = null;
                    if ($request->file('file' . $index)) {
                        $filePath = $request->file('file' . $index)->move('quotation/quotation_detail/' . $quotation_id);
                    }
                    QuotationDetail::create([
                        'quotation_id' => $quotation_id,
                        'total_amount' => $quotation_detail['total_amount'],
                        'analyse_id' => null,
                        'product_id' => $quotation_detail['product_id']?$quotation_detail['product_id']:null,
                        'purchase_price' => $quotation_detail['purchase_price'],
                        'description' => $quotation_detail['description'],
                        'unit_of_measure' => $quotation_detail['unit_of_measure']?$quotation_detail['unit_of_measure']:"",
                        'product_description' => $quotation_detail['descriptionss']?$quotation_detail['descriptionss']:"",
                        'quantity' => $quotation_detail['quantity'],
                        'margin' => $quotation_detail['margin'],
                        'sell_price' => $quotation_detail['sell_price'],
                        'remark' => $quotation_detail['remark'],
                        // "amaco_description" => $quotation_detail['descriptionss'],
                        'file_img_url' => $filePath,
                    ]);
                    $index++;
                }
               
                $note_detail = json_decode($request->notes, true);
                foreach ($note_detail as $div) {
                   
                notes::create([
                    'quotation_id' => $quotation_id,
                    'notes' => $div['note'], 
                    
        
                ]); 
            }
            
            }

            if ($request['parent_id']) {
                $tempQuotaion = Quotation::where('id', $request['parent_id'])->first();
                if ($tempQuotaion) {
                    $tempQuotaion->update(['is_revised' => 1]);
                }
            }
           
           
           
           
            

            // return response()->json($res);
        // } catch (Exception $e) {
        //     return response()->json($request, 201);
        // }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Quotation  $quotation
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $quotation = Quotation::where('id', $id)->first();
        $data = [
            "id" => $quotation->id,
            'quotation_no' => $quotation->quotation_no,
            "party_id" => $quotation->party_id,
            "rfq_id" => $quotation->rfq_id || null,
            "status" => $quotation->status,
            "total_value" => $quotation->total_value,
            "discount_in_p" => $quotation->discount_in_p,
            "vat_in_value" => $quotation->vat_in_value,
            "net_amount" => $quotation->net_amount,
            "created_at" => $quotation->created_at,
            "updated_at" => $quotation->updated_at,
            "validity" => $quotation->validity,
            "payment_terms" => $quotation->payment_terms,
            "warranty" => $quotation->warranty,
            "delivery_time" => $quotation->delivery_time,
            "inco_terms" => $quotation->inco_terms,
            "po_number" => $quotation->po_number,
            "transaction_type" => $quotation->transaction_type,
            "ps_date" => $quotation->ps_date,
            "sales_order_number" => $quotation->sales_order_number,
            "contact" => $quotation->contact,
            "party" => $quotation->party,
            "partyDivision" => $quotation->party->partyDivision->map(function($payment){
                return $payment->partyDivision;
            }),
            "rfq" => $quotation->rfq,
            "is_revised" => $quotation->is_revised,
            "sign" => $quotation->sign,
            "notes" => $quotation->notes,
            "bank" => $quotation->bank,
            "currency_type" => $quotation->currency_type,
            "freight_type" => $quotation->freight_type,
            "subject" => $quotation->subject,
            "rfq_no" => $quotation->rfq_no,

            "quotation_details" => $quotation->quotationDetail->map(function ($quotation_detail) {
                $filePath = $quotation_detail->file_img_url ? $quotation_detail->file_img_url : '';
                $urlPath = $filePath ? url($filePath) : null;
                return [
                    "id" => $quotation_detail->id,
                    "total_amount" => $quotation_detail->total_amount,
                    "analyse_id" => $quotation_detail->analyse_id,
                    "product_id" => $quotation_detail->product_id,
                    // "descriptionss" => $quotation_detail->product->description,
                    "descriptionss" => $quotation_detail->product_description,
                    "amaco_description" => $quotation_detail->amaco_description,
                    "product" => $quotation_detail->product,
                    // "partyDivision" => $quotation_detail->partyDivision,
                    "product_price_list" => $quotation_detail->product? $quotation_detail->product->productPrice->map(function ($productP) {
                        return [
                            'price' => $productP->price,
                            'firm_name' => $productP->party->firm_name
                        ];
                    }):null,
                    
                    // "product_price_list" => $quotation_detail->product->productPrice,
                    "purchase_price" => $quotation_detail->purchase_price,
                    "description" => $quotation_detail->description,
                    "quantity" => $quotation_detail->quantity,
                    "cost_qty"  => (float)$quotation_detail->purchase_price*(int)$quotation_detail->quantity,
                    'unit_of_measure' => $quotation_detail->unit_of_measure,
                    // "delivered_quantity"=> $quotation_detail->quantity,
                    "delivered_quantity" => $quotation_detail->getDeliveredQuantity($quotation_detail),
                    "balance" => (int)$quotation_detail->quantity - (int)$quotation_detail->getDeliveredQuantity($quotation_detail),
                    "margin" => $quotation_detail->margin,
                    "sell_price" => $quotation_detail->sell_price,
                    "remark" => $quotation_detail->remark,
                    "file" => $urlPath,
                    "created_at" => $quotation_detail->created_at,
                    "updated_at" => $quotation_detail->updated_at,
                   
                  
                ];
            })
        ];

        return response()->json([
            $data
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Quotation  $quotation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        // return $request;
        $quotation = Quotation::where("id", $request->id)->first();
        $data = $request->all();
        if ($request->transaction_type !== 'purchase') {
        $quotation->update([
            'po_number' => $request->po_number,
            // 'status' => $request->status,
            'total_value' => $request->total_value,
            'party_id' => $request->party_id,
            'contact_id' => $request->contact_id,
            'vat_in_value' => $request->vat_in_value,
            'net_amount' => $request->net_amount,
            'transaction_type' => $request->transaction_type,
            'discount_in_p' => $request->discount_in_p,
            'ps_date' => $request->ps_date,
            'sign' => $request->sign,
            'rfq_no' => $request->rfq_no,
            'subject' => $request->subject,
            'bank_id'=> (int)$request->bank_id,
            'validity' => $request['validity'],
            'payment_terms' => $request['payment_terms'],
            'warranty' => $request['warranty'],
            'currency_type' => $request['currency_type'],
            'delivery_time' => $request['delivery_time'],
            'inco_terms' => $request['inco_terms'],
            // 'sales_order_number' => $data['sales_order_number'],
        ]);
        $index = 0;
        while ($request['quotation_detail' . $index] != null) {
            $quotation_detail = (array) json_decode($request['quotation_detail' . $index], true);
            $filePath = null;
            if ($request->file('file' . $index)) {
                $filePath = $request->file('file' . $index)->move('quotation/quotation_detail/' . $request->id);
            }
            $quotationDetail = QuotationDetail::where([
                'id' => $quotation_detail['id'],
                // 'quotation_id' => $request->id
            ])->first();
            if ($quotationDetail) {
                if (File::exists(public_path($quotationDetail->file_img_url))) {

                    File::delete(public_path($quotationDetail->file_img_url));
                }
                $quotationDetail->update([
                    'total_amount' => $quotation_detail['total_amount'],
                    'analyse_id' => $quotation_detail['analyse_id'],
                    'product_id' => $quotation_detail['product_id'],
                    'purchase_price' => $quotation_detail['purchase_price'],
                    'description' => $quotation_detail['description'],
                    'quantity' => $quotation_detail['quantity'],
                    'margin' => $quotation_detail['margin'],
                    'sell_price' => $quotation_detail['sell_price'],
                    'unit_of_measure' => $quotation_detail['unit_of_measure'],
                    'product_description' => $quotation_detail['descriptionss']?$quotation_detail['descriptionss']:"",
                    'remark' => $quotation_detail['remark'],
                    'file_img_url' => $filePath,

                ]);


                $res=notes::where('quotation_id',$quotation->id)->delete();
                $note_detail = json_decode($request->notes, true);

                foreach ($note_detail as $div) {
           
                notes::create([
                'quotation_id' => $quotation->id,
                'notes' => $div['notes'], 

                ]); 
                }
            } else {
                QuotationDetail::create([
                    'quotation_id' => $quotation->id,
                    'total_amount' => $quotation_detail['total_amount'],
                    // 'analyse_id' => $quotation_detail['analyse_id'],
                    'product_id' => $quotation_detail['product_id'],
                    'purchase_price' => $quotation_detail['purchase_price'],
                    'description' => $quotation_detail['description'],
                    'quantity' => $quotation_detail['quantity'],
                    'margin' => $quotation_detail['margin'],
                    'sell_price' => $quotation_detail['sell_price'],
                    'product_description' => $quotation_detail['descriptionss']?$quotation_detail['descriptionss']:"",
                    'unit_of_measure' => $quotation_detail['unit_of_measure'],
                    'remark' => $quotation_detail['remark'],
                    'file_img_url' => $filePath,

                ]);
            }
            $index++;
        }
        return response()->json("hello");
        }
        else
        {
            $quotation->update([
                // 'status' => $request->status,
                'total_value' => $request->total_value,
                'vat_in_value' => $request->vat_in_value,
                'party_id' => $request->party_id,
                'contact_id' => $request->contact_id,
                'freight_type' => $request->freight,
                'payment_terms' => $request->payment_terms,
                'currency_type' => $request->currency_type,
                'delivery_time' => $request->delivery_time,
                'inco_terms' => $request->inco_terms,
                'net_amount' => $request->net_amount,
                'transaction_type' => $request->transaction_type,
                'discount_in_p' => $request->discount_in_p,
                'ps_date'=>$request->ps_date,
                
                // 'sales_order_number' => $data['sales_order_number'],
            ]);
            $index = 0;
            while ($request['quotation_details'] != null) {
                $quotation_detail = (array) json_decode(json_encode($request['quotation_details'], true));
                foreach ($request['quotation_details'] as $key => $quotation_detail) {
                
                $quotationDetail = QuotationDetail::where([
                    'id' => $quotation_detail['id'],
                    // 'quotation_id' => $request->id
                ])->first();
                if ($quotationDetail) {
                   
                    $quotationDetail->update([
                        'total_amount' => $quotation_detail['total_amount'],
                        'product_id' => $quotation_detail['product_id'],
                        'purchase_price' => $quotation_detail['purchase_price'],
                        'description' => $quotation_detail['product']?$quotation_detail['product']:$quotation_detail['description'],
                        'quantity' => $quotation_detail['quantity'],
                        'margin' => $quotation_detail['margin'],
                        'sell_price' => $quotation_detail['sell_price'],
                        'remark' => $quotation_detail['remark'],
                        'product_description' => $quotation_detail['descriptionss']?$quotation_detail['descriptionss']:"",
                        'unit_of_measure' => $quotation_detail['unit_of_measure'],
    
                    ]);
                } else {
                    QuotationDetail::create([
                        'quotation_id' => $quotation->id,
                        'total_amount' => $quotation_detail['total_amount'],
                        // 'analyse_id' => $quotation_detail['analyse_id'],
                        'product_id' => $quotation_detail['product_id']?$quotation_detail['product_id']:null,
                        'purchase_price' => $quotation_detail['purchase_price'],
                        'description' => $quotation_detail['product'],
                        'quantity' => $quotation_detail['quantity'],
                        'margin' => $quotation_detail['margin'],
                        'sell_price' => $quotation_detail['sell_price'],
                        'unit_of_measure' => $quotation_detail['unit_of_measure'],
                        'product_description' => $quotation_detail['descriptionss']?$quotation_detail['descriptionss']:null,
                       
    
                    ]);
                   
      
        
                    }
                $index++;
               
                
        
               
    
               
      
        }
        return response()->json($request);
    }
}
        
    
    }

    public function updateQuotation(Request $request, $id)
    {

        // add validation

        // $validator = Validator::make($request->all(), [
        //     'title' => 'unique:quotations'
        // ]);
        // if ($validator->fails()) {
        //     return response()->json(['msg' => 'P.O.Number is already exists'],201);
        // }

        // new validation logic for po_number

        $unique_po_no = Quotation::where('po_number', $request->po_number)->first();
        $data = $request->all();
        $quotation = Quotation::where("id", $id)->firstOrFail();
        $filePath = null;
        if ($request->file('file')) {
            $filePath = $request->file('file')->move("quotation/filePath",  $request->file('file')->getClientOriginalName());
        }
        if ($request->po_number) {

            if (isset($unique_po_no)) {
                return response()->json(['msg' => 'P.O.Number is exsits']);
            }

            $data['sales_order_number'] = $this->getSalesOrderNumber();
            $quotation->update([
                'status' => $data['status'],
                'sales_order_number' => $data['sales_order_number'],
                'po_number' => $data['po_number'],
                'file' => $filePath,
                
            ]);
        } else {
            $quotation->update([
                'status' => $data['status'],
            ]);
        }



        return response()->json($quotation);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Quotation  $quotation
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $quotation = Quotation::where('id', $id)->first();

        $res = $quotation->delete();
        if ($res) {
            return (['msg' => 'Quotation' . ' ' . $quotation->id . ' is successfully deleted']);
        }
    }
    public function destroy_details($id)
    {
        $quotation = QuotationDetail::where('id', $id)->first();

        $res = $quotation->delete();
        if ($res) {
            return (['msg' => 'Quotation' . ' ' . $quotation->id . ' is successfully deleted']);
        }
    }

    public function invoice_list()
    {
        $quotations = Quotation::where('status', '=', 'po')->orderBy('created_at', 'DESC')->get();
        $quotations_data = [
            $quotations->map(
                function ($quotation) {
                    return [
                        'id' => $quotation->id,
                        'created_at' => $quotation->created_at,
                        'updated_at' => $quotation->updated_at,
                        'status' => $quotation->status,
                        'total_value' => $quotation->total_value,
                        'party_id' => $quotation->party_id,
                        'quotation_no' => $quotation->quotation_no,
                        "vat_in_value" => $quotation->vat_in_value,
                        "net_amount" => $quotation->net_amount,
                        'discount_in_p' => $quotation['discount_in_p'],
                        "party" => $quotation->party,
                        'quotation_details' => $quotation->quotationDetail->map(function ($quotation_detail) {
                            $quotation_detail = QuotationDetail::where('id', '=', $quotation_detail->id)->first();
                            return [
                                "id" => $quotation_detail['id'],
                                "created_at" => $quotation_detail->created_at,
                                "updated_at" => $quotation_detail->updated_at,
                                "product_id" => $quotation_detail->product_id,
                                "product" => array($quotation_detail->product),
                                "description" => $quotation_detail->description,
                                "quantity" => $quotation_detail->quantity,
                                "total_amount" => $quotation_detail->total_amount,
                                "analyse_id" => $quotation_detail->analyse_id,
                                "purchase_price" => $quotation_detail->purchase_price,
                                "margin" => $quotation_detail->margin,
                                "sell_price" => $quotation_detail->sell_price,
                                "remark" => $quotation_detail->remark,
                            ];
                        }),
                    ];
                }
            ),
        ];
        return response()->json($quotations_data[0], 200);
    }

    public function history()
    {
        $quotations = Quotation::whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('invoices')
                ->whereRaw('invoices.quotation_id = quotations.id');
        })->orderBy('created_at', 'DESC')
            //->where('status', '=', 'po')
            ->get();

        return response()->json($quotations);
    }

    public function salesList()
    {
        $quotations = Quotation::where(['status' => 'New', 'transaction_type' => 'sale'])
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('invoices')
                    ->whereRaw('invoices.quotation_id = quotations.id');
            })->orderBy('created_at', 'DESC')
            ->get();
        // $quotations = Quotation::where('status','=','New')->orderBy('created_at','DESC')->get();
        $quotations_data = [
            $quotations->map(
                function ($quotation) {
                    return [
                        'id' => $quotation->id,
                        'quotation_no' => $quotation->quotation_no,
                        'created_at' => $quotation->created_at,
                        'updated_at' => $quotation->updated_at,
                        'status' => $quotation->status,
                        'total_value' => $quotation->total_value,
                        'party_id' => $quotation->party_id,
                        "contact_id" => $quotation->contact_id,
                        "contact" => $quotation->contact,
                        "party" => $quotation->party,
                        "vat_in_value" => $quotation->vat_in_value,
                        "net_amount" => $quotation->net_amount,
                        "transaction_type" => $quotation->transaction_type,
                        'discount_in_p' => $quotation['discount_in_p'],
                        'quotation_details' => $quotation->quotationDetail->map(function ($quotation_detail) {
                            $quotation_detail = QuotationDetail::where('id', '=', $quotation_detail->id)->first();
                            return [
                                "id" => $quotation_detail['id'],
                                "created_at" => $quotation_detail->created_at,
                                "updated_at" => $quotation_detail->updated_at,
                                "product_id" => $quotation_detail->product_id,
                                "product" => array($quotation_detail->product),
                                "description" => $quotation_detail->description,
                                "quantity" => $quotation_detail->quantity,
                                "total_amount" => $quotation_detail->total_amount,
                                "analyse_id" => $quotation_detail->analyse_id,
                                "purchase_price" => $quotation_detail->purchase_price,
                                "margin" => $quotation_detail->margin,
                                "sell_price" => $quotation_detail->sell_price,
                                "remark" => $quotation_detail->remark,
                            ];
                        }),
                    ];
                }
            ),
        ];
        return response()->json($quotations_data[0], 200);
    }

    public function acceptedList()
    {
        $quotations = Quotation::where(['status' => 'accept', 'transaction_type' => 'sale'])
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('invoices')
                    ->whereRaw('invoices.quotation_id = quotations.id');
            })->orderBy('created_at', 'DESC')
            ->get();
        // $quotations = Quotation::where('status','=','New')->orderBy('created_at','DESC')->get();
        $quotations_data = [
            $quotations->map(
                function ($quotation) {
                    return [
                        'id' => $quotation->id,
                        'quotation_no' => $quotation->quotation_no,
                        'created_at' => $quotation->created_at,
                        'updated_at' => $quotation->updated_at,
                        'status' => $quotation->status,
                        'total_value' => $quotation->total_value,
                        'party_id' => $quotation->party_id,
                        "contact_id" => $quotation->contact_id,
                        "contact" => $quotation->contact,
                        "party" => $quotation->party,
                        "vat_in_value" => $quotation->vat_in_value,
                        "net_amount" => $quotation->net_amount,
                        "transaction_type" => $quotation->transaction_type,
                        'discount_in_p' => $quotation['discount_in_p'],
                        'quotation_details' => $quotation->quotationDetail->map(function ($quotation_detail) {
                            $quotation_detail = QuotationDetail::where('id', '=', $quotation_detail->id)->first();
                            // $isDelivered = $this->checkDeliveredProductQuantity($quotation_detail);
                            // dd($isDelivered);
                            return [
                                "id" => $quotation_detail['id'],
                                "created_at" => $quotation_detail->created_at,
                                "updated_at" => $quotation_detail->updated_at,
                                "product_id" => $quotation_detail->product_id,
                                "product" => array($quotation_detail->product),
                                "description" => $quotation_detail->description,
                                "amaco_description" => $quotation_detail->descriptionss,
                                "quantity" => $quotation_detail->quantity,
                                "total_amount" => $quotation_detail->total_amount,
                                "analyse_id" => $quotation_detail->analyse_id,
                                "purchase_price" => $quotation_detail->purchase_price,
                                "margin" => $quotation_detail->margin,
                                "sell_price" => $quotation_detail->sell_price,
                                "remark" => $quotation_detail->remark,
                            ];
                        }),
                    ];
                }
            ),
        ];
        return response()->json($quotations_data[0], 200);
    }

    public function rejectedList()
    {
        $quotations = Quotation::where(['status' => 'reject', 'transaction_type' => 'sale'])
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('invoices')
                    ->whereRaw('invoices.quotation_id = quotations.id');
            })->orderBy('created_at', 'DESC')
            ->get();
        // $quotations = Quotation::where('status','=','New')->orderBy('created_at','DESC')->get();
        $quotations_data = [
            $quotations->map(
                function ($quotation) {
                    return [
                        'id' => $quotation->id,
                        'quotation_no' => $quotation->quotation_no,
                        'created_at' => $quotation->created_at,
                        'updated_at' => $quotation->updated_at,
                        'status' => $quotation->status,
                        'total_value' => $quotation->total_value,
                        'party_id' => $quotation->party_id,
                        "contact_id" => $quotation->contact_id,
                        "contact" => $quotation->contact,
                        "party" => $quotation->party,
                        "vat_in_value" => $quotation->vat_in_value,
                        "net_amount" => $quotation->net_amount,
                        "transaction_type" => $quotation->transaction_type,
                        'discount_in_p' => $quotation['discount_in_p'],
                        'quotation_details' => $quotation->quotationDetail->map(function ($quotation_detail) {
                            $quotation_detail = QuotationDetail::where('id', '=', $quotation_detail->id)->first();
                            return [
                                "id" => $quotation_detail['id'],
                                "created_at" => $quotation_detail->created_at,
                                "updated_at" => $quotation_detail->updated_at,
                                "product_id" => $quotation_detail->product_id,
                                "product" => array($quotation_detail->product),
                                "description" => $quotation_detail->description,
                                "quantity" => $quotation_detail->quantity,
                                "total_amount" => $quotation_detail->total_amount,
                                "analyse_id" => $quotation_detail->analyse_id,
                                "purchase_price" => $quotation_detail->purchase_price,
                                "margin" => $quotation_detail->margin,
                                "sell_price" => $quotation_detail->sell_price,
                                "remark" => $quotation_detail->remark,
                            ];
                        }),
                    ];
                }
            ),
        ];
        return response()->json($quotations_data[0], 200);
    }

    public function deleteFile(QuotationDetail $quotation_detail)
    {
        if (File::exists(public_path($quotation_detail->file_img_url))) {

            File::delete(public_path($quotation_detail->file_img_url));

            $quotation_detail->update([
                'file_img_url' => null
            ]);

            return response()->json(['msg' => "Successfully file is deleted"]);


        }
        return response()->json(['msg' => "There is no file in quotation detail"]);
    }

    public function saleReport(Request $request)
    {
        $reports = Quotation::where('transaction_type','sale')
        ->whereBetween('created_at', [$request->from_date . ' ' . '00:00:00', $request->to_date ? $request->to_date . ' ' . '23:59:59' : now()])->get();

        if($reports){
            $reports->map(
                function ($quotation) {
                    $data = [
                        'id' => $quotation->id,
                        'po_number' => $quotation->po_number,
                        'created_at' => $quotation->created_at,
                        'updated_at' => $quotation->updated_at,
                        'status' => $quotation->status,
                        'total_value' => $quotation->total_value,
                        'party_id' => $quotation->party_id,
                        "contact_id" => $quotation->contact_id,
                        "contact" => $quotation->contact,
                        "party" => $quotation->party,
                        "vat_in_value" => $quotation->vat_in_value,
                        "net_amount" => $quotation->net_amount,
                        "transaction_type" => $quotation->transaction_type,
                        'discount_in_p' => $quotation->discount_in_p,
                        'ps_date' => $quotation->ps_date,
                        'quotation_details' => $quotation->quotationDetail->map(function ($quotation_detail) {
                            $quotation_detail = QuotationDetail::where('id', '=', $quotation_detail->id)->first();
                            return [
                                "id" => $quotation_detail['id'],
                                "created_at" => $quotation_detail->created_at,
                                "updated_at" => $quotation_detail->updated_at,
                                "product_id" => $quotation_detail->product_id,
                                "product" => array($quotation_detail->product),
                                "description" => $quotation_detail->description,
                                "quantity" => $quotation_detail->quantity,
                                "total_amount" => $quotation_detail->total_amount,
                                "analyse_id" => $quotation_detail->analyse_id,
                                "purchase_price" => $quotation_detail->purchase_price,
                                "margin" => $quotation_detail->margin,
                                "sell_price" => $quotation_detail->sell_price,
                                "remark" => $quotation_detail->remark,
                            ];
                        }),
                    ];
                    // return $data;
                }
            );

            return response()->json($reports);
        }
        return response()->json(['msg'=>"There is no report between the given date"],500);
    }
    public function updateQuotestatus(Request $request)
    {
        $unique_po_no = Quotation::where('po_number', $request->po_number)->first();
        $data = $request->all();
        $quotation = Quotation::where("id", $request->id)->firstOrFail();
        $filePath = null;
        if ($request->file('file')) {
            $filePath = $request->file('file')->move("quotate/filePath",  $request->file('file')->getClientOriginalName());
        }
        if ($request->po_number) {

            if (isset($unique_po_no)) {
                return response()->json(['msg' => 'P.O.Number is exsits']);
            }

            $sales_order_number = $this->getSalesOrderNumber();
            $quotation->update([
                'status' => $request->status,
                'sales_order_number' => $sales_order_number,
                'po_number' => $request->po_number,
                'file' => $filePath,
                
            ]);
        } else {
            $quotation->update([
                'status' => $request->status,
            ]);
        }



        return response()->json($quotation);
    }
    public function update_company(Request $request)
    {
        $quotations = Quotation::where('id',$request->id)->update([
            'company_address'=> $request->company_address,

        ]);

    }
    public function  quoteNum(Request $request)
    {
        $current_year = $this->getCurrentYear();
        $current_month = $this->getCurrentMonth();
        $quote_num = Quotation::orderBy('id','DESC')->get('id');
        return ('AMC-QT-' . $current_year . '-' . $current_month  . sprintf("%02d", 1));
    }

}
