<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use Illuminate\Http\Request;
use App\Models\Product;
use DB;
use Config;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCurrentYear()
    {
        return substr(date('Y'), 2);
    }

    public function getCurrentMonth()
    {
        return date('m');
    }

    public function getLastInvoiceNo()
    {
        $invoice = Invoice::latest('created_at')->first();
        if ($invoice) {
            $latest_invoice_no = $invoice->invoice_no ? $invoice->invoice_no : 0;
            return ($latest_invoice_no);
        } 
        // else {
        //     return ('AMC-INV-' . $this->getCurrentYear() . '-' . sprintf("%02d", 0));
        // }
    }

    public function getInvoiceNo()
    {
        $latest_invoice_no = $this->getLastInvoiceNo();
        $last_year = substr($latest_invoice_no, 6, 2);
        $last_month = substr($latest_invoice_no, 11, 2);
        $current_year = $this->getCurrentYear();
        $current_month = $this->getCurrentMonth();
        // dd([$last_year, $current_year]);
        // if ($current_year != $last_year) {
        //     return ('AMC-INV-' . $current_year . '-'. $current_month  . sprintf("%02d", 1));
        // } else {
        //     if($current_month != $last_month){
        //         return ('AMC-INV-' . $current_year . '-'. $current_month . sprintf("%02d", 1));
        //     }else{
        //         if((int)substr($this->getLastInvoiceNo(), 13) < 99){
                    return ('AMC-INV-' . $current_year . '-'. $current_month .  sprintf("%02d", ((int)substr($latest_invoice_no, 13)) + 1));
                    // return ('AMC-INV-' . $current_year . '-'. $current_month . sprintf("%02d", ((int)substr($this->getLastInvoiceNo(), 13)) + 1));
            //     }else{
            //         return ('AMC-INV-' . $current_year . '-'. $current_month . sprintf("%03d", $latest_invoice_no + 1));
            //         // return ('AMC-INV-' . $current_year . '-'. $current_month . sprintf("%03d", ((int)substr($this->getLastInvoiceNo(), 13)) + 1));
            //     }
            // }
        // }
    }
    public function index()
    {
        $invoices = Invoice::where('status','!=','Delivered')
        ->orderBy('created_at','DESC')->get();
        // $result=$invoices->party;
        $invoices->map(function ($invoice) {
               
            // $invoice->payment_account;
           return $invoice->party;
       });
        return $invoices;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->json()->all();
        // dd($data);
        // dd($request->vat_in_value);
        // dd($request->vat_in_value);
        $data['invoice_no'] = $this->getInvoiceNo();
        $data['issue_date'] = $request['ps_date'];
        $data['status'] = "New";
        $data['quotation_id'] = $request['quotation_id'];
        $data['po_number'] = $request['po_number'];
        $data['total_value'] = $request['total_value'];
        $data['discount_in_percentage'] = $request['discount_in_percentage'];
        $data['vat_in_value'] = $request['vat_in_value'];
        $data['grand_total'] = $request['grand_total'];
        $invoice = Invoice::create([
            'invoice_no' => $data['invoice_no'],
            'po_number' => $data['po_number'],
            'issue_date' => $data['issue_date'],
            'status' => $data['status'],
            'quotation_id' => $data['quotation_id'],
            'total_value' => $data['total_value'],
            'discount_in_percentage' => $data['discount_in_percentage'],
            'vat_in_value' => $data['vat_in_value'],
            'grand_total' => $data['grand_total'],
            'delivery_no' => null,
            'party_id' => $request['party_id']
        ]);

        global $_invoice_id;
        $_invoice_id = $invoice['id'];
            $invoice_details=$request['invoice_details'];
        foreach($invoice_details as $invoice_detail) {
            $apikey=  \Config::get('example.key');
            // $json = json_decode(file_get_contents($path), true);
            $arDescription = $invoice_detail['id']?null:json_decode(file_get_contents('https://translation.googleapis.com/language/translate/v2?key='.$apikey.'&q='.urlencode($invoice_detail['product']).'&target=ar'));
            if(!$invoice_detail['productId'])
            {
               $product=Product::create([
                    'name'=> $invoice_detail['product']
                ]);
            }
            $_invoice_detail = InvoiceDetail::create([
                'quotation_detail_id' => $invoice_detail['id']?$invoice_detail['id']:null,
                'product_id' => $invoice_detail['productId']?$invoice_detail['productId']:$product->id,
                'sell_price' => $invoice_detail['sell_price'],
                'quantity' => $invoice_detail['quantity'],
                'total_amount' => $invoice_detail['total_amount'],
                'unit_of_measure' => $invoice_detail['unit_of_measure'],
                'description' => $invoice_detail['description']?$invoice_detail['description']:$invoice_detail['product'],
                'arabic_description' => $invoice_detail['arabic_description']?$invoice_detail['arabic_description']:$arDescription->data->translations[0]->translatedText,
                'invoice_id' => $_invoice_id,
                'purchase_price' => $invoice_detail['purchase_price']?$invoice_detail['purchase_price']:null,
                // 'product_name' => $invoice_detail['product']?$invoice_detail['product']:null,
                // 'unit_of_measure' => $invoice_detail['unit_of_measure']?$invoice_detail['unit_of_measure']:null,
            ]);
        }
        // return 'success';
        return response()->json($invoice_details);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function show(Invoice $invoice)
    {
        return [
            $invoice,
            $invoice->party,
            $invoice->quotation,
            //$invoice->quotation->quotationDetail,
            $invoice->invoiceDetail->map(function ($invoice_detail){
                return [
                    $invoice_detail['margin']=((((float)$invoice_detail->sell_price)-((float)$invoice_detail->purchase_price))/((float)
                    
                    $invoice_detail->purchase_price))*100,
                    $invoice_detail['delivered_quantity']=$invoice_detail->getDelivered_invoice_Quantity($invoice_detail),
                    $invoice_detail['balance'] = (int)$invoice_detail->quantity - (int)$invoice_detail->getDelivered_invoice_Quantity($invoice_detail),
                    $invoice_detail->quotationDetail,
                    $invoice_detail->product
                ];
            }),
            // $invoice->invoiceDetail->map(function ($invoice_detail){
            //     return [
            //         $invoice_detail->quotationDetail,
            //     ];
            // }),

            // $product['name_ar'] = file_get_contents('https://api.mymemory.translated.net/get?q=helloworld!&langpair=en|ar');

        ];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */

    public function getCurrentDeliveryYear()
    {
        return substr(date('Y'), 2);
    }

    public function getLastDeliveryNo()
    {
        $invoice = Invoice::latest('created_at')->first();
        if ($invoice) {
            $latest_delivery_no = $invoice->delivery_no ? $invoice->delivery_no : 0;
            return ($latest_delivery_no);
        } else {
            return ('AMDLV-' . $this->getCurrentDeliveryYear() . '-' . sprintf("%04d", 0));
        }
    }

    public function getDeliveryNo()
    {
        $latest_delivery_no = $this->getLastDeliveryNo();
        $last_year = substr($latest_delivery_no, 6, 2);
        $current_year = $this->getCurrentDeliveryYear();
        // dd([$last_year, $current_year]);
        if ($current_year != $last_year) {
            return ('AMDLV-' . $current_year . '-' . sprintf("%04d", 1));
        } else {
            return ('AMDLV-' . $current_year . '-' . sprintf("%04d", ((int)substr($this->getLastDeliveryNo(), 9)) + 1));
        }
    }

    public function update(Request $request, Invoice $invoice)
    {
        $data = $request->all();
        $data['status'] = 'Delivered';
        $data['delivery_no'] = $this->getDeliveryNo();
        $invoice->update($data);
        return $invoice;
    }
    public function Invoiceupdate(Request $request, Invoice $invoice)
    {
        $apikey=  \Config::get('example.key');
        $invoice = Invoice::where('id',$request->id)->first();
        
        
       
        $invoice->update([
            // 'invoice_no' => $request->invoice_no,
            'po_number' => $request->po_number,
            'issue_date' => $request->ps_date,
            // 'status' => $request->status,
            // 'quotation_id' => $request->quotation_id,
            'total_value' => $request->total_value,
            'discount_in_percentage' => $request->discount_in_percentage,
            'vat_in_value' => $request->vat_in_value,
            'grand_total' => $request->grand_total,
            'delivery_no' => null,
            'party_id' => $request->party_id,
            // 'contact_id' => $request->contact_id
        ]);
        $temp = json_decode($request['invoice_details'], true);
        $i = 0;
        foreach ((array) $temp as $invoice_detail) {
           
           
                
                $invoiceDetail = InvoiceDetail::where('id', $invoice_detail['id'])->first();
                if($invoiceDetail)
                {
                  
                    if(!$invoice_detail['product_id'])
                    {
                    $product_exist=Product::where('name','=',$invoice_detail['product'])->first();
                        if(!$product_exist){
                       $product=Product::create([
                            'name'=> $invoice_detail['product']
                        ]);
                        }
                        else
                        {
                            $product=null;
                        }  
                    }
                    $arDescription = json_decode(file_get_contents('https://translation.googleapis.com/language/translate/v2?key='.$apikey.'&q='.urlencode($invoice_detail['description']).'&target=ar'));
                    $invoiceDetail->update([
                        // 'quotation_detail_id' => $invoice_detail['id']?$invoice_detail['id']:null,
                        'product_id' => $invoice_detail['product_id']?$invoice_detail['product_id']:($product?$product->id:null),
                        'sell_price' => $invoice_detail['sell_price'],
                        'quantity' => $invoice_detail['quantity'],
                        'total_amount' => $invoice_detail['total_amount'],
                        'unit_of_measure' => $invoice_detail['unit_of_measure'],
                        'description' => $invoice_detail['description']?$invoice_detail['description']:$invoice_detail['product'],
                        'arabic_description' => $invoice_detail['arabic_description']?$invoice_detail['arabic_description']:$arDescription->data->translations[0]->translatedText,
                        // 'invoice_id' => $_invoice_id,
                        'purchase_price' => $invoice_detail['purchase_price']?$invoice_detail['purchase_price']:null,
                       
    
                    ]);

                }
                else{
                   
                    
                    if(!$invoice_detail['product_id'])
                    {
                    $product_exist=Product::where('name','=',$invoice_detail['product'])->first();
                        if(!$product_exist){
                       $product=Product::create([
                            'name'=> $invoice_detail['product']
                        ]);
                     }
                        else{
                           $product=null; 
                        }
                    
                    }
                    $arDescription = json_decode(file_get_contents('https://translation.googleapis.com/language/translate/v2?key='.$apikey.'&q='.urlencode($invoice_detail['description']).'&target=ar'));
                    InvoiceDetail::create([
                       
                            'quotation_detail_id' => $invoice['quotation_id']?$invoice['quotation_id']:null,
                            'product_id' => $invoice_detail['product_id']?$invoice_detail['product_id']:($product?$product->id:null),
                            'sell_price' => $invoice_detail['sell_price'],
                            'quantity' => $invoice_detail['quantity'],
                            'total_amount' => $invoice_detail['total_amount'],
                            'unit_of_measure' => $invoice_detail['unit_of_measure'],
                            'description' => $invoice_detail['description']?$invoice_detail['description']:$invoice_detail['product'],
                            'arabic_description' => $invoice_detail['arabic_description']?$invoice_detail['arabic_description']:$arDescription->data->translations[0]->translatedText,
                            'invoice_id' => $request['id'],
                            'purchase_price' => $invoice_detail['purchase_price']?$invoice_detail['purchase_price']:null,
                           
        
                      
                
                    ]);
                }

            
                $i++;
               
                
            }
            
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function destroy(Invoice $invoice)
    {
        return ($invoice->delete());
    }

    public function history()
    {
        $invoices = Invoice::where('status', '=', 'Delivered')
        ->orderBy('created_at', 'DESC')->get();
        return response()->json($invoices);
    }
    public function salesTax(Invoice $invoice)
    {
        $invoices = Invoice::get();
        $invoices->map(function($val){
            
            return $val->party;
        });
        return $invoices;
    }
    
}
