<?php

/**
 * Darulmadinah Api
 * 
 * This is a DiscountPolicy API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\DiscountPolicy;
use Illuminate\Http\Request;
use App\Utilities\Utilities;
use Illuminate\Support\Facades\DB;

class DiscountPolicyApi extends Controller
{

    /**
     * Constructor
     */
    private $mdlName;
             
    public function __construct()
    {
        $this->mdlName = new DiscountPolicy();
    }
    
    private $select_columns  = [
        'id', 
        'disc_code', 
        'discount_type', 
        'fees_type_id', 
        'disc_percentage', 
        'condition', 
        'discription', 
        'disc_from_date', 
        'disc_end_date', 
        'disc_is_new_addmission',
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
        
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        
        $this->mdlName->filterColumns($request);
        
         Utilities::defaultAddAttributes($request, 1);
            
        $post_arr = $request->all();
        $obj = DiscountPolicy::create($post_arr);
        
        $data = [ 'id' => $obj->id ];
        
        $response = Utilities::buildSuccessResponse(10000, "Discount Policy assigned Successfully.", $data);
        
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
        $mdlName = new DiscountPolicy();
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        
        $this->mdlName->filterColumns($request);
        
        Utilities::defaultUpdateAttributes($request, $user_id);
//
        $result_set = DiscountPolicy::find($request->id);
        
        $status = 200;
        $response = [];
        
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Record not found.");
        } else {
            
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
        
        $result_set = DiscountPolicy::find($request->id);
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
        
        $result_set = DiscountPolicy::find($request->id);
        
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
        $mdlList = new DiscountPolicy();
        $request->request->add([ 'id' => $id ]);
        $this->validate($request, $mdlList->rules($request, Constant::RequestType['GET_ONE']), $mdlList->messages($request, Constant::RequestType['GET_ONE']));
        $select = $this->select_columns;
        $mdlList->filterColumns($request);
        if($request->fields){
            $select = $request->fields;
        }
        $list = DiscountPolicy::where('id', $request->id)->first($select);
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
        
        if($request->discount_type) {   
            $whereData[] = ['discount_type', $request->discount_type];
       
        }if($request->fees_type_id) {   
            $whereData[] = ['fees_type_id', $request->fees_type_id];
        }

        if(isset($request->disc_percentage)) {   
            $whereData[] = ['disc_percentage', $request->disc_percentage];
        }

        if(isset($request->discription)) {   
            $whereData[] = ['discription', $request->discription];
        }

        if(isset($request->disc_from_date)) {   
            $whereData[] = ['disc_from_date', $request->disc_from_date];
        }
        
        if(isset($request->disc_end_date)) {   
            $whereData[] = ['disc_end_date', $request->disc_end_date];
        }
        if(isset($request->disc_is_new_addmission)) {   
            $whereData[] = ['disc_is_new_addmission', $request->disc_is_new_addmission];
        }
        
        if(isset($request->is_enable)) {   
            $whereData[] = ['is_enable', $request->is_enable];
        }

        if(isset($request->data_organization_id)) {   
            $whereData[] = ['organization_id', $request->data_organization_id];
        }



        $mdlList = new DiscountPolicy();
        
        
        $total_record = DiscountPolicy::where($whereData)->active()->count();
        $orderBy =  $mdlList->getOrderColumn($request->order_by);
        $orderType = $request->order_type ?? Constant::OrderType;
        
        //$queryObj = DiscountPolicy::with('Class','FeesType','DiscType','FeeStructureDetail');
        $queryObj = DiscountPolicy::with('Class','FeesType','DiscType','FeeStructureDetail');
        $queryObj->where($whereData);
        $queryObj->active();
        $queryObj->orderBy($orderBy, $orderType);
        $queryObj->offset($skip);
        $queryObj->limit($pageSize);
        $list = $queryObj->get($select);
        $status = 200;
        // $data_result = new StateResponse();
        // $data_result->setState($list->toArray());

        $data_result = array("list" => $list->toArray());
        $data_result['total_record'] = $total_record;
        $response = Utilities::buildSuccessResponse(10004, "Discount Policy List.", $data_result);
        return response()->json($response, $status);  

        //return response()->json(DiscountPolicy::all());
    }
    
    /**
     * Fetch list of DiscountPolicy for selectboc/dropdown by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDiscountPolicy(Request $request, $id = null)
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
        
        if($request->discount_type) {   
            $whereData[] = ['discount_type', $request->discount_type];
       
        }if($request->fees_type_id) {   
            $whereData[] = ['fees_type_id', $request->fees_type_id];
        }

        if(isset($request->disc_percentage)) {   
            $whereData[] = ['disc_percentage', $request->disc_percentage];
        }

        if(isset($request->discription)) {   
            $whereData[] = ['discription', $request->discription];
        }

        if(isset($request->disc_from_date)) {   
            $whereData[] = ['disc_from_date', $request->disc_from_date];
        }
        
        if(isset($request->disc_end_date)) {   
            $whereData[] = ['disc_end_date', $request->disc_end_date];
        }
        if(isset($request->disc_is_new_addmission)) {   
            $whereData[] = ['disc_is_new_addmission', $request->disc_is_new_addmission];
        }
        
        if(isset($request->is_enable)) {   
            $whereData[] = ['is_enable', $request->is_enable];
        }

        if(isset($request->data_organization_id)) {   
            $whereData[] = ['organization_id', $request->data_organization_id];
        }
        
        $data_set = DiscountPolicy::where($whereData)
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
