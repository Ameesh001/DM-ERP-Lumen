<?php

/**
 * Darulmadinah Api
Author: Muhammad Usama (it.dev7) 
 * 
 * This is a CampusFee API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\CampusFee;
use Illuminate\Http\Request;
use App\Utilities\Utilities;

class CampusFeeApi extends Controller
{

    /**
     * Constructor
     */
    private $mdlName;
             
    public function __construct()
    {
        $this->mdlName = new CampusFee();
    }
    
    private $select_columns  = [
        'id', 
        // 'organization_id', 
        // 'countries_id', 
        // 'state_id', 
        // 'region_id',
       // 'city_id', 
        'campus_id', 
        'class_id', 
        'fee_type_id', 
        'fee_amount', 
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
     * Add CampusFee.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
             
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        
        $this->mdlName->filterColumns($request);
        
         Utilities::defaultAddAttributes($request, $request->data_user_id);
            
        $post_arr = $request->all();
        //$post_arr['organization_id'] =  1;
        $obj = CampusFee::create($post_arr);
        
        $data = [ 'id' => $obj->id ];
        
        $response = Utilities::buildSuccessResponse(10000, "CampusFee successfully created.", $data);
        
        return response()->json($response, 201);
    }

    /**
     * Update CampusFee.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        
        $this->mdlName->filterColumns($request);
        
        Utilities::defaultUpdateAttributes($request, $request->data_user_id);
//
        $result_set = CampusFee::find($request->id);
        
        $status = 200;
        $response = [];
        
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "CampusFee not found.");
        } else {
            
            $post_arr = $request->all();
           
            $obj = $result_set->update($post_arr);

            if ($obj) {
                $data = [ 'id' => $result_set->id ];
                $response = Utilities::buildSuccessResponse(10001, "CampusFee successfully updated.", $data);
            } 
        }
        
        return response()->json($response, $status);
    }
    
    /**
     * Activate/De-Activate CampusFee.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function patchDisableEnable(Request $request)
    {
 
        Utilities::removeAttributesExcept($request, ["id","is_enable"]);
        
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        
        Utilities::defaultUpdateAttributes($request, $request->data_user_id);
        
        $activate = $request->is_enable == 1 ? Constant::RecordType['ENABLED'] : Constant::RecordType['DISABLED'];
        
        $request->request->add([ 'is_enable' => $activate ]);
        
        $result_set = CampusFee::find($request->id);
        $status = 200;
        $response = [];

        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "CampusFee not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $data = ['id' => $result_set->id ];
                $actMsg = $request->is_enable == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "CampusFee successfully $actMsg.", $data);
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Delete CampusFee.
     *
     * @param $id 'ID' of CampusFee to delete. (required)
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
        
        $result_set = CampusFee::find($request->id);
        
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "CampusFee not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "CampusFee successfully deleted.");
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Get one CampusFee.
     *
     * @param $id 'ID' of CampusFee to return (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOne($id, Request $request)
    {
        $request->request->add([ 'id' => $id ]);
        
        $this->validate($request, $this->mdlName->rules($request, Constant::RequestType['GET_ONE']), $this->mdlName->messages($request, Constant::RequestType['GET_ONE']));
        
        $select = $this->select_columns;

        $this->mdlName->filterColumns($request);
        
        if($request->fields){
            $select = $request->fields;
        }
        
        $result_set = CampusFee::with('Campus', 'Class', 'Feetype')->where('id', $request->id)->first($select);
        
        $status = 200;
        $response = [];

        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "CampusFee not found.");
        } else {
            
            $data_set = $result_set->toArray();
  
            $dataResult['data_set']             = $data_set;
  
            $response = Utilities::buildSuccessResponse(10005, "CampusFee Data.", $dataResult);
        }
        
        return response()->json($response, $status);
    }

    /**
     * Fetch list of CampusFee by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll(Request $request, $id = null)
    {
        $this->validate($request, $this->mdlName->rules($request, Constant::RequestType['GET_ALL']), $this->mdlName->messages($request, Constant::RequestType['GET_ALL']));
        
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
        
        
       
        // if($request->countries_id) {   
        //     $whereData[] = ['countries_id', $request->countries_id];
        // }
        // if($request->state_id) {   
        //     $whereData[] = ['state_id', $request->state_id];
        // }
        // if($request->region_id) {   
        //     $whereData[] = ['region_id', $request->region_id];
        // }
        
        // if($request->city_id) {   
        //     $whereData[] = ['city_id', $request->city_id];
        // }
        
        if($request->campus_id) {   
            $whereData[] = ['campus_id', $request->campus_id];
        }
        
        if($request->class_id) {   
            $whereData[] = ['class_id', $request->class_id];
        }
        
        if($request->fee_type_id) {   
            $whereData[] = ['fee_type_id', $request->fee_type_id];
        }
           
        
        if($request->is_enable != null) {
            $whereData[] = ['is_enable', $request->is_enable];
        }
        
        $total_record = CampusFee::where($whereData)
            ->active()
            ->count();
        
        $orderBy =  $this->mdlName->getOrderColumn($request->order_by);
        
        $orderType = $request->order_type ?? Constant::OrderType;
        
        $data_set = CampusFee::with('Campus', 'Class', 'Feetype')
            ->where($whereData)
            ->active()
            ->orderBy($orderBy, $orderType)
            ->offset($skip)
            ->limit($pageSize)
            ->get($select);
        
        
        $data_result = [];
        $status = 200;
        $data_result['data_list'] = $data_set->toArray();
        $data_result['total_record'] = $total_record;
        
        $response = Utilities::buildSuccessResponse(10004, "CampusFee List.", $data_result);

        return response()->json($response, $status); 
    }
    
    /**
     * Fetch list of CampusFee for selectboc/dropdown by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCampusFee(Request $request, $id = null)
    {
        $this->validate($request, $this->mdlName->rules($request, Constant::RequestType['GET_ALL']), $this->mdlName->messages($request, Constant::RequestType['GET_ALL']));
        
       
        $select =  $this->select_columns;
        
        if($request->fields){
            $select = $request->fields;
        }
        $whereData = array();
        
        
        if($request->countries_id) {   
            $whereData[] = ['countries_id', $request->countries_id];
        }
        if($request->state_id) {   
            $whereData[] = ['state_id', $request->state_id];
        }
        if($request->region_id) {   
            $whereData[] = ['region_id', $request->region_id];
        }
        
        if($request->city_id) {   
            $whereData[] = ['city_id', $request->city_id];
        }
        
        if($request->class_id) {   
            $whereData[] = ['class_id', $request->class_id];
        }
        
        if($request->campus_id) {   
            $whereData[] = ['campus_id', $request->campus_id];
        }
        
        if($request->fee_type_id) {   
            $whereData[] = ['fee_type_id', $request->fee_type_id];
        }
         
 
        if($request->id) {   
            $whereData[] = ['id', $request->id];
        }
        if($request->is_enable != null) {
            $whereData[] = ['is_enable', $request->is_enable];
        }
        
        $data_set = CampusFee::with( 'Campus','Class', 'Feetype')
            ->where($whereData)
            ->active()
            ->get($select);
        
        
        $data_result = [];
        $status = 200;
        $data_result['campus'] = $data_set->toArray();
        
        $response = Utilities::buildSuccessResponse(10004, "CampusFee List.", $data_result);

        return response()->json($response, $status); 
    }
}
