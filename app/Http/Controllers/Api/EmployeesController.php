<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employees;


class EmployeesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
         $data = Employees::
        join('divisions','divisions.id','employee.div_id')
         ->orderby('emp_id','DESC')
         ->select('employee.name as e_name','divisions.name as div_name','divisions.*','employee.*')
         ->get();

          
        return response()->json([
                            'status' => 200,
                            'getData' => $data,
                    ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = new Employees;
         $data -> name = $request->input('prefix') . " " . $request->input('name');
         $data -> contact_number = $request->input('contact_number');
         $data -> emp_no = $this -> getEmpNo();
         $data -> email = $request->input('email');
         $data -> present_address = $request->input('present_address');
         $data -> grosssalary = $request->input('grosssalary');
         $data -> bsalary = $request->input('bsalary');
         $data -> hrasalary = $request->input('hrasalary');
         $data -> tasalary = $request->input('tasalary');
         $data -> div_id = $request->input('div_id');

        $fpath = NULL;
        if($request->hasFile('file')){
            $request->file('file');
            $fname =  $request->file('file','name');
            $fpath = $request->file('file')->move('uploadedFiles/',$fname.'.jpg');
        }
         $data -> file = $fpath;
         $data -> passport_number = $request->input('passport_number');
         $data -> designation = $request->input('designation');
         $data -> passport_exp_date = $request->input('passport_exp_date');
         $data -> iqama_exp_date = $request->input('iqama_exp_date');
         $data -> date_of_join = $request->input('date_of_join');
         $data -> status = "Working";
         if($data -> save()){
                return response()->json([
                    'status' => 200,
                    'message' => "Employee Saved Successfully.",
            ]);
        }else{

            
            $emp_no = $this -> getEmpNo();

                return response()->json([
                'status' => 402,
                'message' => $emp_no,
            ]);
        } 
    }

    public function getEmpNo(){

        $emp = Employees::orderby('emp_no','DESC')->get('emp_no');

        try {
             $emp = $emp[0]->emp_no;
            $no = substr($emp,7);
            $no++;
            $empno = "AMC-EM-".str_pad($no, 3, '0', STR_PAD_LEFT);
            return $empno;
        } catch (\Throwable $th) {
             $no = 000;
            $no++; 
            $empno = "AMC-EM-".str_pad($no, 3, '0', STR_PAD_LEFT);
            return $empno;
        }
        // if($emp[0]->emp_no){
        //     $emp = $emp[0]->emp_no;
        //     $no = substr($emp,7);
        //     $no++;
        //     $empno = "AMC-EM-".str_pad($no, 3, '0', STR_PAD_LEFT);
        //     return $empno;
        // }else{
        //     $no = 000;
        // $no++; 
        // $empno = "AMC-EM-".str_pad($no, 3, '0', STR_PAD_LEFT);
        //     return $empno;
        // }

        

        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {

        
        $fpath = $request->input('file');
        // Employees::where('emp_id',$request->id)->update(array('name'=>$request['name']));
        if($request->hasFile('file')){
            $request->file('file');
            $fname =  $request->file('file','name');
            $fpath = $request->file('file')->move('uploadedFiles/',$fname.'.jpg');
        }
        Employees::where('emp_id',$request->id)->update(
         array(
                 'name' => $request->input('name'),
                 'emp_no' => $request->input('emp_no'),
                 'contact_number' => $request->input('contact_number'),
                 'email' => $request->input('email'),
                 'file' => $fpath,
                 'present_address' => $request->input('present_address'),
                 'passport_number' => $request->input('passport_number'),
                 'designation' => $request->input('designation'),
                 'bsalary' => $request->input('bsalary'),
                 'hrasalary' => $request->input('hrasalary'),
                 'tasalary' => $request->input('tasalary'),
                 'grosssalary' => $request->input('grosssalary'),
                 'passport_exp_date' => $request->input('passport_exp_date'),
                 'iqama_exp_date' => $request->input('iqama_exp_date'),
                 'date_of_join' => $request->input('date_of_join'),
                 
              )
         );

        return response()->json([
                'status' => 402,
                'message' => $request['name'],
            ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $d = Employees::
        where('emp_id','=',$id)
        ->delete();
         return response()->json([
                'status' => 402,
                'message' => "Deleted.",
            ]);
    }
}
