<?php

/**
 * Darulmadinah Api
 * 
 * This is a DiscountPolicy API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\AssignDiscountPolicy;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Utilities\Utilities;
use Illuminate\Support\Facades\DB;

class AssignDiscountPolicyApi extends Controller
{

    /**
     * Constructor
     */
    private $mdlName;
             
    public function __construct()
    {
        $this->mdlName = new AssignDiscountPolicy();
    }
    
    private $select_columns  = [
        'id',
        'disc_code', 
        'organization_id', 
        'country_id', 
        'state_id', 
        'region_id', 
        'city_id', 
        'campus_id', 
        'class_id', 
        'admission_code', 
        'is_enable',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_at'
    ];
        
        
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
     * Add DiscountPolicy.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        //$user_id =   Utilities::getUserID_by_keycloak_id($request->keycloak_id);
        $data = []; 
        
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        
        $this->mdlName->filterColumns($request);
        
        Utilities::defaultAddAttributes($request, 1);        

        

        $org_list = Organization::find($request->data_org_id);
       
        $request->request->add(['country_id' => $org_list->countries_id]);

        // if(isset($request->admission_code)) { 

        //     //  $admission_code_n_id = $post_arr['admission_code'];
        //     $admission_code_n_id = $request->admission_code;            
        //     $std_admission_id = substr($admission_code_n_id.'_', 0, strpos($admission_code_n_id, '_'));
        //     $admission_code = substr($admission_code_n_id, strpos($admission_code_n_id, "_") + 1); 
            
        //     $request->request->add(['student_id' => $std_admission_id]);
        //     $request->request->add(['admission_code' => $admission_code]);
        // }

        $post_arr = $request->all();

        if(isset($request->admission_code)) { 

        foreach($post_arr['admission_code'] as $array){    

            // $post_arr['student_id'] = $array['id'];
            // $post_arr['admission_code'] = $array['admission_code']; 

            $admission_code_n_id = $array;            
            $post_arr['student_id'] = substr($admission_code_n_id.'_', 0, strpos($admission_code_n_id, '_'));
            $post_arr['admission_code'] = substr($admission_code_n_id, strpos($admission_code_n_id, "_") + 1); 
            
            
            $obj = AssignDiscountPolicy::create($post_arr);
                    

        }

        $response = Utilities::buildSuccessResponse(10000, "Discount Policy assigned Successfully.", $data);
        

    }
    else{

        $obj = AssignDiscountPolicy::create($post_arr);        
        $data = [ 'id' => $obj->id ];

        $response = Utilities::buildSuccessResponse(10000, "Discount Policy assigned Successfully.", $data);
        

       }
      
       return response()->json($response, 201);


}

    /**
     * Update DiscountPolicy.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $user_id =   Utilities::getUserID_by_keycloak_id($request->keycloak_id);
        $mdlName = new AssignDiscountPolicy();
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        
        $this->mdlName->filterColumns($request);
        
        Utilities::defaultUpdateAttributes($request, $user_id);
//
        $result_set = AssignDiscountPolicy::find($request->id);
        
        $status = 200;
        $response = [];
        
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Record not found.");
        } else {
    
            if(isset($request->admission_code)) { 
    
                //  $admission_code_n_id = $post_arr['admission_code'];
                $admission_code_n_id = $request->admission_code;            
                $std_admission_id = substr($admission_code_n_id.'_', 0, strpos($admission_code_n_id, '_'));
                $admission_code = substr($admission_code_n_id, strpos($admission_code_n_id, "_") + 1); 
                
                $request->request->add(['student_id' => $std_admission_id]);
                $request->request->add(['admission_code' => $admission_code]);
            }
            
            $post_arr = $request->all();
           
            $obj = $result_set->update($post_arr);

            if ($obj) {
                $data = [ 'id' => $result_set->id ];
                $response = Utilities::buildSuccessResponse(10001, "Record successfully updated.", $data);
            } 
        }
        
        return response()->json($response, $status);

        
    }
    
    /**
     * Activate/De-Activate DiscountPolicy.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function patchDisableEnable(Request $request)
    {
 
        Utilities::removeAttributesExcept($request, ["id","is_enable"]);
        
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        
        Utilities::defaultUpdateAttributes($request, 1);
        
        $activate = $request->is_enable == 1 ? Constant::RecordType['ENABLED'] : Constant::RecordType['DISABLED'];
        
        $request->request->add([ 'is_enable' => $activate ]);
        
        $result_set = AssignDiscountPolicy::find($request->id);
        $status = 200;
        $response = [];

        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Discount Policy not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $data = ['id' => $result_set->id ];
                $actMsg = $request->is_enable == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "Discount Policy  Assign successfully $actMsg.", $data);
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Delete DiscountPolicy.
     *
     * @param $id 'ID' of DiscountPolicy to delete. (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id, Request $request)
    {
        $request->request->add([ 'id' => $id ]);
        
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        
        Utilities::defaultDeleteAttributes($request, 1);
        
        $request->request->add([
            'is_enable' => Constant::RecordType['DELETED']
        ]);
        
        $result_set = AssignDiscountPolicy::find($request->id);
        
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Discount Policy not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "Discount Policy successfully deleted.");
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Get one DiscountPolicy.
     *
     * @param $id 'ID' of DiscountPolicy to return (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOne($id, Request $request)
    {
        $mdlList = new AssignDiscountPolicy();
        $request->request->add([ 'id' => $id ]);
        $this->validate($request, $mdlList->rules($request, Constant::RequestType['GET_ONE']), $mdlList->messages($request, Constant::RequestType['GET_ONE']));
        $select = $this->select_columns;
        $mdlList->filterColumns($request);
        if($request->fields){
            $select = $request->fields;
        }
        $list = AssignDiscountPolicy::where('id', $request->id)->first($select);
        $list->Country;
        $list->State;
        $status = 200;
        $response = [];
        if (! $list) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Record not found.");
        } else {
            $dataResult = array("list" => $list->toArray());
            $response = Utilities::buildSuccessResponse(10005, "Data.", $dataResult);
        }
        return response()->json($response, $status);
    }

    /**
     * Fetch list of DiscountPolicy by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll(Request $request, $id = null)
    {
        
       
        $this->validate($request, $this->mdlName->rules($request, Constant::RequestType['GET_ALL']),
         $this->mdlName->messages($request, Constant::RequestType['GET_ALL']));
        
        $pageSize = $request->limit ?? Constant::PageSize;
        
        if($pageSize > Constant::MaxPageSize){
            $pageSize = Constant::MaxPageSize;
        }
        
        $page = $request->page ?? Constant::Page;
        
        $skip = ($page - 1) * $pageSize;
        
        $select =  $this->select_columns;
        
        if($request->fields){
            $select = $request->fields;
        }
        $whereData = array();        
        
        
        if($request->disc_code) {   
            $whereData[] = ['disc_code', $request->disc_code];
        }
        
        if($request->country_id) {   
            $whereData[] = ['country_id', $request->country_id];
       
        }if($request->state_id) {   
            $whereData[] = ['state_id', $request->state_id];
        }

        if(isset($request->region_id)) {   
            $whereData[] = ['region_id', $request->region_id];
        }

        if(isset($request->city_id)) {   
            $whereData[] = ['city_id', $request->city_id];
        }

        if(isset($request->campus_id)) {   
            $whereData[] = ['campus_id', $request->campus_id];
        }
        
        if(isset($request->class_id)) {   
            $whereData[] = ['class_id', $request->class_id];
        }        
        
        if(isset($request->is_enable)) {   
            $whereData[] = ['is_enable', $request->is_enable];
        }

        if(isset($request->admission_code)) {   
            $whereData[] = ['admission_code', $request->admission_code];
        }


        $mdlList = new AssignDiscountPolicy();
        
        
        $total_record = AssignDiscountPolicy::where($whereData)->active()->count();
        $orderBy =  $mdlList->getOrderColumn($request->order_by);
        $orderType = $request->order_type ?? Constant::OrderType;
        
        //$queryObj = AssignDiscountPolicy::with('Class','FeesType','DiscType','FeeStructureDetail');
        $queryObj = AssignDiscountPolicy::with('Country','State','Region','City','Campus','Class','Student');
        $queryObj->where($whereData);
        $queryObj->active();
        $queryObj->orderBy($orderBy, $orderType);
        $queryObj->offset($skip);
        $queryObj->limit($pageSize);
        $list = $queryObj->get();
        $status = 200;
        // $data_result = new StateResponse();
        // $data_result->setState($list->toArray());


        // $list->toArray();




        $data_result = array("list" => $list->toArray());
        $data_result['total_record'] = $total_record;
        $response = Utilities::buildSuccessResponse(10004, "Discount Policy List.", $data_result);
        return response()->json($response, $status);  

        //return response()->json(AssignDiscountPolicy::all());
    }
    
    /**
     * Fetch list of AssignDiscountPolicy for selectboc/dropdown by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllMaster(Request $request, $id = null)
    {
        $this->validate($request, $this->mdlName->rules($request, Constant::RequestType['GET_ALL']), $this->mdlName->messages($request, Constant::RequestType['GET_ALL']));
        
       
        $select =  $this->select_columns;
        
        if($request->fields){
            $select = $request->fields;
        }
        $whereData = array();
        
        
        if($request->disc_code) {   
            $whereData[] = ['disc_code', $request->disc_code];
        }
        
        if($request->country_id) {   
            $whereData[] = ['country_id', $request->country_id];
       
        }if($request->state_id) {   
            $whereData[] = ['state_id', $request->state_id];
        }

        if(isset($request->region_id)) {   
            $whereData[] = ['region_id', $request->region_id];
        }

        if(isset($request->city_id)) {   
            $whereData[] = ['city_id', $request->city_id];
        }

        if(isset($request->campus_id)) {   
            $whereData[] = ['campus_id', $request->campus_id];
        }
        
        if(isset($request->class_id)) {   
            $whereData[] = ['class_id', $request->class_id];
        }        
        
        if(isset($request->is_enable)) {   
            $whereData[] = ['is_enable', $request->is_enable];
        }

        if(isset($request->data_organization_id)) {   
            $whereData[] = ['organization_id', $request->data_organization_id];
        }
        
        $data_set = AssignDiscountPolicy::where($whereData)
           ->where($whereData)
            ->active()
            ->get($select);
        
        
        $data_result = [];
        $status = 200;
        $data_result['campus'] = $data_set->toArray();
        
        $response = Utilities::buildSuccessResponse(10004, "Discount Policy List.", $data_result);

        return response()->json($response, $status); 
    }
}
