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
use App\Models\Hierarchy;
use Illuminate\Http\Request;
use App\Utilities\Utilities;
use Illuminate\Support\Facades\DB;
class HierarchyApi extends Controller
{

    /**
     * Constructor
     */
    private $mdlName;
             
    public function __construct()
    {
        $this->mdlName = new Hierarchy();
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
     * Fetch Hierarchy list..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll(Request $request, $id = null)
    {
        $whereData = array(); 
        $total_record_obj_q = Hierarchy::where($whereData);
        $data_result = [];
        $status = 200;
        if($total_record_obj_q->count()>0)
        {
            $data_set = $total_record_obj_q->get();
            $data_result['data_list'] = $data_set->toArray();
        }
        else
        {
            $data_result['data_list'] = 'No Record Found..!!';
        }
        
        $response = Utilities::buildSuccessResponse(10004, "Hierarchy List.", $data_result);
        return response()->json($response, $status); 
    }

 
   
}
