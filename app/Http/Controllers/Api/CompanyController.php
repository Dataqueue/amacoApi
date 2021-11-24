<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Config;
use DB;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $company = Company::all();
        $company->map(function($val){
            $val['image1']=$val->Img1();
            $val['image2']=$val->Img2();
            $val['image3']=$val->Img3();
            return $val;
        });
        return response()->json($company);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        if ($request->file('img1')) {
            $img1_name = $request->img1->getClientOriginalName();
            $img1_path = $request->file('img1')->move('company/', $img1_name);
            $data['img1'] = $img1_path;
        }
        if ($request->file('img2')) {
            $img2_name = $request->img2->getClientOriginalName();
            $img2_path = $request->file('img2')->move('company/', $img2_name);
            $data['img2'] = $img2_path;
        }
        if ($request->file('img3')) {
            $img3_name = $request->img3->getClientOriginalName();
            $img3_path = $request->file('img3')->move('company/', $img3_name);
            $data['img3'] = $img3_path;
        }
        $apikey=  \Config::get('example.key');
        $namear = json_decode(file_get_contents('https://translation.googleapis.com/language/translate/v2?key='.$apikey.'&q='.urlencode($request['name']).'&target=ar'));
        $company = Company::create([
            'name'=>$request['name'],
            'email'=>$request['email'],
            'address'=>$request['address'],
            'contact'=>$request['contact'],
            'cr_no'=>$request['cr_no'],
            'po_box'=>$request['po_box'],
            'vat_no'=>$request['vat_no'],
            'fax'=>$request['fax'],
            'website'=>$request['website'],
            'img1'=>$img1_path,
            'img2'=>$img2_path,
            // 'img3'=>$img3_path,
            'arabic_name'=>$namear->data->translations[0]->translatedText,

        ]);
        // if ($company) {

            return response()->json($company);
        // }
        // return response()->json(['msg' => "Error ", 500]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function show(Company $company)
    {
        $company->img1 && $company['img1'] = url($company->img1);
        $company->img2 && $company['img2'] = url($company->img2);
        $company->img3 && $company['img3'] = url($company->img3);
        return response()->json([$company]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Company $company)
    {




        $data = $request->all();
        if ($request->file('img1')) {
            if (File::exists(public_path($company->img1))) {

                File::delete(public_path($company->img1));

                $id->update([
                    'img1' => null
                ]);
            }
            $img1_name = $request['img1']->getClientOriginalName();
            $img1_path = $request->file('img1')->move('company/', $img1_name);
            $data['img1'] = $img1_path;
        }
        if ($request->file('img2')) {
            if (File::exists(public_path($company->img2))) {

                File::delete(public_path($company->img2));

                $id->update([
                    'img2' => null
                ]);
            }
            $img2_name = $request['img2']->getClientOriginalName();
            $img2_path = $request->file('img2')->move('company/', $img2_name);
            $data['img2'] = $img2_path;
        }
        if ($request->file('img3')) {
            if (File::exists(public_path($company->img3))) {

                File::delete(public_path($company->img3));

                $company->update([
                    'img3' => null
                ]);
            }
            $img3_name = $request['img3']->getClientOriginalName();
            $img3_path = $request->file('img3')->move('company/', $img3_name);
            $data['img3'] = $img3_path;
        }

        $company->update($data);

        return response()->json($company);
    }

    public function company_edit(Request $request, Company $company)
    {




        $data = $request->all();
        $id=Company::where('id',$data['id'])->update([
            'name'=>$request['name'],
            'email'=>$request['email'],
            'address'=>$request['address'],
            'contact'=>$request['contact'],
            'cr_no'=>$request['cr_no'],
            'po_box'=>$request['po_box'],
            'vat_no'=>$request['vat_no'],
            'fax'=>$request['fax'],
            'website'=>$request['website'],

        ]);;
        if ($request->file('img1')) {
            // if (File::exists(public_path($company->Img1()))) {

            //     File::delete(public_path($company->Img1()));

            //     $company->update([
            //         'img1' => null
            //     ]);
            // }
            $img1_name = $request['img1']->getClientOriginalName();
            $img1_path = $request->file('img1')->move('company/', $img1_name);
            $data['img1'] = $img1_path;
            $data=Company::where('id',$request['id'])->update([
                "img1" => $request->file('img1') ? $img1_path : null,
            ]);
        }
        if ($request->file('img2')) {
            // if (File::exists(public_path($company->Img2()))) {

            //     File::delete(public_path($company->Img2()));

            //     $company->update([
            //         'img2' => null
            //     ]);
            // }
            $img2_name = $request['img2']->getClientOriginalName();
            $img2_path = $request->file('img2')->move('company/', $img2_name);
            $data['img2'] = $img2_path;
            $data=Company::where('id',$request['id'])->update([
                "img2" => $request->file('img2') ? $img2_path : null,
            ]);
        }
        if ($request->file('img3')) {
            // if (File::exists(public_path($company->Img3()))) {

            //     File::delete(public_path($company->Img3()));

            //     $company->update([
            //         'img3' => null
            //     ]);
            // }
            $img3_name = $request['img3']->getClientOriginalName();
            $img3_path = $request->file('img3')->move('company/', $img3_name);
            $data['img3'] = $img3_path;
            $data=Company::where('id',$request['id'])->update([
                "img3" => $request->file('img3') ? $img3_path : null,
            ]);
        }

        

        return response()->json($id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function destroy(Company $company)
    {
        $company->delete();

        return response()->json(['msg' => "Successfully Delelted"]);
    }
}
