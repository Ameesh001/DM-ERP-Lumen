<?php

/**
 * Darulmadinah Api
Author: Muhammad Usama (it.dev7) 
 * 
 * This is a GenAdmissionVoucher API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use Illuminate\Http\Request;
use App\Utilities\Utilities;
use App\Models\Dropdown;
use Illuminate\Support\Facades\DB;

class DropdownApi extends Controller
{

    /**
     * Constructor
     */
    private $mdlName;
             
    public function __construct()
    {
        $this->mdlName = new Dropdown();
    }
    
    
    private $select_column_dis = [];
    private $select_columns = [];
        
    /**
     * This function is called after validation fails in function $this->validate.
     * 
     * @param Request $request
     * @param array $errors
     * @return \Illuminate\Http\JsonResponse
     */
    
     protected function buildFailedValidationResponse(Request $request, array $errors) {
        $response = Utilities::buildFailedValidationResponse(10000, "Unprocesssable Entity.", $errors);
        return response()->json($response, 400);
    }

    


    /**
     * Add Student Transfer.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {     
        
    }

    /**
     * Update AdmissionVoucher.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {     
    }
    
    /**
     * Activate/De-Activate AdmissionVoucher.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function patchDisableEnable(Request $request)
    {
 
    }

    /**
     * Delete AdmissionVoucher.
     *
     * @param $id 'ID' of GenAdmissionVoucher to delete. (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id, Request $request)
    {   
    }

    /**
     * Get one AdmissionVoucher.
     *
     * @param $id 'ID' of GenAdmissionVoucher to return (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOne($id, Request $request)
    {
       
    }

    /**
     * Fetch list of GenAdmissionVoucher by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll(Request $request, $id = null)
    {
        // $whereData[] = array();
        // if($request->type) {   
        //     $whereData[] = ['type',$request->type];
        // }
        $data_set_q = Dropdown::where('type', $request->type)->active();
        $data_set = $data_set_q->get();
        $data_result = [];
        $status = 200;
        $data_result['dd_list'] = $data_set->toArray();
        $response = Utilities::buildSuccessResponse(10004, "Dropdown List.", $data_result);
        return response()->json($response, $status); 
    }


 
   
}
