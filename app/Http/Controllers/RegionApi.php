<?php

/**
 * Darulmadinah Api
Author: Muhammad Usama (it.dev7) 
 * 
 * This is a Region API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\Region;
use Illuminate\Http\Request;
use App\Utilities\Utilities;

class RegionApi extends Controller
{

    /**
     * Constructor
     */
    private $mdlName;
             
    public function __construct()
    {
        $this->mdlName = new Region();
    }

    private $select_columns = ['id', 'region_name', 'region_desc', 'countries_id', 'state_id', 'organization_id', 'is_enable'];

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
     * Add Region.
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
        
        $post_arr['organization_id'] =  $post_arr['data_org_id'];
        $obj = Region::create($post_arr);
        
        $data = [ 'id' => $obj->id ];
        
        $response = Utilities::buildSuccessResponse(10000, "Region successfully created.", $data);
        
        return response()->json($response, 201);
    }

    /**
     * Update Region.
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
        $result_set = Region::find($request->id);
        
        $status = 200;
        $response = [];
        
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Region not found.");
        } else {
            
            $post_arr = $request->all();
           
            $obj = $result_set->update($post_arr);

            if ($obj) {
                $data = [ 'id' => $result_set->id ];
                $response = Utilities::buildSuccessResponse(10001, "Region successfully updated.", $data);
            } 
        }
        
        return response()->json($response, $status);
    }
    
    /**
     * Activate/De-Activate Region.
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
        
        $result_set = Region::find($request->id);
        $status = 200;
        $response = [];

        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Region not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $data = ['id' => $result_set->id ];
                $actMsg = $request->is_enable == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "Region successfully $actMsg.", $data);
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Delete Region.
     *
     * @param $id 'ID' of Region to delete. (required)
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
        
        $result_set = Region::find($request->id);
        
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Region not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "Region successfully deleted.");
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Get one Region.
     *
     * @param $id 'ID' of Region to return (required)
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
        
        $result_set = Region::with('Country', 'State')->where('id', $request->id)->first($select);
        
        $status = 200;
        $response = [];

        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Region not found.");
        } else {
            
            $data_set = $result_set->toArray();
  
            $dataResult['data_set']             = $data_set;
  
            $response = Utilities::buildSuccessResponse(10005, "Region Data.", $dataResult);
        }
        
        return response()->json($response, $status);
    }

    /**
     * Fetch list of Region by searching with optional filters..
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
        
        
        if($request->region_name) {   
            $whereData[] = ['region_name', 'LIKE', "%{$request->region_name}%"];
        }
        if($request->region_desc) {   
            $whereData[] = ['region_desc', 'LIKE', "%{$request->region_desc}%"];
        }
        if($request->countries_id) {   
            $whereData[] = ['countries_id', $request->countries_id];
        }
        if($request->state_id) {   
            $whereData[] = ['state_id', $request->state_id];
        }
        if($request->organization_id) {   
            $whereData[] = ['organization_id', $request->organization_id];
        }
        if($request->is_enable != null) {
            $whereData[] = ['is_enable', $request->is_enable];
        }
        
        Utilities::set_all_data_permission_request_parameters($request);
        $data_org_id         = $request->data_p_org_id;
        $data_country_id     = $request->data_p_country_id;
        $data_state_id       = $request->data_p_state_id;
        $data_region_id      = $request->data_p_region_id;
        
        
        $total_record_q = Region::where($whereData)
            ->active();
        
        if(!empty($data_org_id)){
            $total_record_q->where(function($query) use ($data_org_id) {
                foreach ($data_org_id as $key => $value) {
                    $query->orWhere('organization_id', '=', $value);
                }
            });
        }
        
        if(!empty($data_country_id)){
            $total_record_q->where(function($query) use ($data_country_id) {
                foreach ($data_country_id as $key => $value) {
                    $query->orWhere('countries_id', '=', $value);
                }
            });
        }
        
        if(!empty($data_state_id)){
            $total_record_q->where(function($query) use ($data_state_id) {
                foreach ($data_state_id as $key => $value) {
                    $query->orWhere('state_id', '=', $value);
                }
            });
        }
        
        if(!empty($data_region_id)){
            $total_record_q->where(function($query) use ($data_region_id) {
                foreach ($data_region_id as $key => $value) {
                    $query->orWhere('id', '=', $value);
                }
            });
        }
        
        $total_record = $total_record_q->count();
        
        $orderBy =  $this->mdlName->getOrderColumn($request->order_by);
        
        $orderType = $request->order_type ?? Constant::OrderType;
        
        $queryObj = Region::with('Country', 'State')
            ->where($whereData);
        
        if(!empty($data_org_id)){
            $queryObj->where(function($query) use ($data_org_id) {
                foreach ($data_org_id as $key => $value) {
                    $query->orWhere('organization_id', '=', $value);
                }
            });
        }
        
        if(!empty($data_country_id)){
            $queryObj->where(function($query) use ($data_country_id) {
                foreach ($data_country_id as $key => $value) {
                    $query->orWhere('countries_id', '=', $value);
                }
            });
        }
        
        if(!empty($data_state_id)){
            $queryObj->where(function($query) use ($data_state_id) {
                foreach ($data_state_id as $key => $value) {
                    $query->orWhere('state_id', '=', $value);
                }
            });
        }
        
        if(!empty($data_region_id)){
            $queryObj->where(function($query) use ($data_region_id) {
                foreach ($data_region_id as $key => $value) {
                    $query->orWhere('id', '=', $value);
                }
            });
        }
        
        
        $data_set = $queryObj->active()
            ->orderBy($orderBy, $orderType)
            ->offset($skip)
            ->limit($pageSize)
            ->get($select);
        
        
        $data_result = [];
        $status = 200;
        $data_result['data_list'] = $data_set->toArray();
        $data_result['total_record'] = $total_record;
        
        $response = Utilities::buildSuccessResponse(10004, "Region List.", $data_result);

        return response()->json($response, $status); 
    }
    
    /**
     * Fetch list of Region for selectbox/dropdown sby searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRegions(Request $request, $id = null)
    {
         
        $select =  $this->select_columns;
        
        if($request->fields){
            $select = $request->fields;
        }
        $whereData = array();
        
        
        if($request->region_name) {   
            $whereData[] = ['region_name', 'LIKE', "%{$request->region_name}%"];
        }
        if($request->region_desc) {   
            $whereData[] = ['region_desc', 'LIKE', "%{$request->region_desc}%"];
        }
        if($request->countries_id) {   
            $whereData[] = ['countries_id', $request->countries_id];
        }
        
        
        if ($request->state_id) {
            if(strlen($request->state_id) > 1){
               $state_id_arr = explode(",", $request->state_id);
            }else{
               $whereData[] = ['state_id', $request->state_id];
            }
        }
        
        if($request->id) {   
            $whereData[] = ['id', $request->id];
        }
        if($request->organization_id) {   
            $whereData[] = ['organization_id', $request->organization_id];
        }
        if($request->is_enable != null) {
            $whereData[] = ['is_enable', $request->is_enable];
        }else{
            $whereData[] = ['is_enable', 1];
        }
        
        Utilities::set_all_data_permission_request_parameters($request);
        $data_org_id         = $request->data_p_org_id;
        $data_country_id     = $request->data_p_country_id;
        $data_state_id       = $request->data_p_state_id;
        $data_region_id      = $request->data_p_region_id;
  
        $queryObj = Region::with('Country', 'State')
            ->where($whereData)
            ->active();
        
        
        if(!empty($data_org_id)){
            $queryObj->where(function($query) use ($data_org_id) {
                foreach ($data_org_id as $key => $value) {
                    $query->orWhere('organization_id', '=', $value);
                }
            });
        }
        
        if(!empty($data_country_id)){
            $queryObj->where(function($query) use ($data_country_id) {
                foreach ($data_country_id as $key => $value) {
                    $query->orWhere('countries_id', '=', $value);
                }
            });
        }
        
        if(!empty($data_state_id)){
            $queryObj->where(function($query) use ($data_state_id) {
                foreach ($data_state_id as $key => $value) {
                    $query->orWhere('state_id', '=', $value);
                }
            });
        }
        
        if(!empty($data_region_id)){
            $queryObj->where(function($query) use ($data_region_id) {
                foreach ($data_region_id as $key => $value) {
                    $query->orWhere('id', '=', $value);
                }
            });
        }
        
        if(!empty($state_id_arr)){
           $queryObj->whereIn('state_id', $state_id_arr);
        }
        
        $result_set = $queryObj->get($select);
        
        $data_result = [];
        $status = 200;
        $data_result['region'] = $result_set->toArray();
        
        $response = Utilities::buildSuccessResponse(10004, "Region List.", $data_result);

        return response()->json($response, $status); 
    }
}
