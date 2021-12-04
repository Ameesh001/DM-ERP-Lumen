<?php

/**
 * Darulmadinah Api
Author: Muhammad Usama (it.dev7) 
 * 
 * This is a City API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\City;
use Illuminate\Http\Request;
use App\Utilities\Utilities;

class CityApi extends Controller
{

    /**
     * Constructor
     */
    private $mdlName;
             
    public function __construct()
    {
        $this->mdlName = new City();
    }

    private $select_columns = ['id', 'city_name', 'city_desc', 'countries_id', 'state_id', 'region_id', 'organization_id', 'is_enable'];

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
     * Add City.
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
        $obj = City::create($post_arr);
        
        $data = [ 'id' => $obj->id ];
        
        $response = Utilities::buildSuccessResponse(10000, "City successfully created.", $data);
        
        return response()->json($response, 201);
    }

    /**
     * Update City.
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
        $result_set = City::find($request->id);
        
        $status = 200;
        $response = [];
        
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "City not found.");
        } else {
            
            $post_arr = $request->all();
           
            $obj = $result_set->update($post_arr);

            if ($obj) {
                $data = [ 'id' => $result_set->id ];
                $response = Utilities::buildSuccessResponse(10001, "City successfully updated.", $data);
            } 
        }
        
        return response()->json($response, $status);
    }
    
    /**
     * Activate/De-Activate City.
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
        
        $result_set = City::find($request->id);
        $status = 200;
        $response = [];

        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "City not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $data = ['id' => $result_set->id ];
                $actMsg = $request->is_enable == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "City successfully $actMsg.", $data);
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Delete City.
     *
     * @param $id 'ID' of City to delete. (required)
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
        
        $result_set = City::find($request->id);
        
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "City not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "City successfully deleted.");
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Get one City.
     *
     * @param $id 'ID' of City to return (required)
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
        
        $result_set = City::with('Country', 'State', 'Region')->where('id', $request->id)->first($select);
        
        $status = 200;
        $response = [];

        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "City not found.");
        } else {
            
            $data_set = $result_set->toArray();
  
            $dataResult['data_set']             = $data_set;
  
            $response = Utilities::buildSuccessResponse(10005, "City Data.", $dataResult);
        }
        
        return response()->json($response, $status);
    }

    /**
     * Fetch list of City by searching with optional filters..
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
        
        
        if($request->city_name) {   
            $whereData[] = ['city_name', 'LIKE', "%{$request->city_name}%"];
        }
        if($request->city_desc) {   
            $whereData[] = ['city_desc', 'LIKE', "%{$request->city_desc}%"];
        }
        if($request->countries_id) {   
            $whereData[] = ['countries_id', $request->countries_id];
        }
        if($request->state_id) {   
            $whereData[] = ['state_id', $request->state_id];
        }
        if($request->region_id) {   
            $whereData[] = ['region_id', $request->region_id];
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
        $data_city_id        = $request->data_p_city_id;
        
        $total_record_q = City::where($whereData)
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
                    $query->orWhere('region_id', '=', $value);
                }
            });
        }
        
        if(!empty($data_city_id)){
            $total_record_q->where(function($query) use ($data_city_id) {
                foreach ($data_city_id as $key => $value) {
                    $query->orWhere('id', '=', $value);
                }
            });
        }
        
        $total_record = $total_record_q->count();
        
        $orderBy =  $this->mdlName->getOrderColumn($request->order_by);
        
        $orderType = $request->order_type ?? Constant::OrderType;
        
        $queryObj = City::with('Country', 'State', 'Region')
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
                    $query->orWhere('region_id', '=', $value);
                }
            });
        }
        
        if(!empty($data_city_id)){
            $queryObj->where(function($query) use ($data_city_id) {
                foreach ($data_city_id as $key => $value) {
                    $query->orWhere('id', '=', $value);
                }
            });
        }
        
        $queryObj->active()
            ->orderBy($orderBy, $orderType)
            ->offset($skip)
            ->limit($pageSize);
        
        $data_set = $queryObj->get($select);
        
        
        $data_result = [];
        $status = 200;
        $data_result['data_list'] = $data_set->toArray();
        $data_result['total_record'] = $total_record;
        
        $response = Utilities::buildSuccessResponse(10004, "City List.", $data_result);

        return response()->json($response, $status); 
    }
    
    /**
     * Fetch list of City for selectboc/dropdown by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCities(Request $request, $id = null)
    {
        $this->validate($request, $this->mdlName->rules($request, Constant::RequestType['GET_ALL']), $this->mdlName->messages($request, Constant::RequestType['GET_ALL']));
        
       
        $select =  $this->select_columns;
        
        if($request->fields){
            $select = $request->fields;
        }
        $whereData = array();
        
        
        if($request->city_name) {   
            $whereData[] = ['city_name', 'LIKE', "%{$request->city_name}%"];
        }
        if($request->city_desc) {   
            $whereData[] = ['city_desc', 'LIKE', "%{$request->city_desc}%"];
        }
        if($request->countries_id) {   
            $whereData[] = ['countries_id', $request->countries_id];
        }
        if($request->organization_id) {   
            $whereData[] = ['organization_id', $request->organization_id];
        }
        if($request->state_id) {   
            $whereData[] = ['state_id', $request->state_id];
        }
        
        if ($request->region_id) {
            if(strlen($request->region_id) > 1){
               $region_id_arr = explode(",", $request->region_id);
            }else{
                $whereData[] = ['region_id', $request->region_id];
            }
        }
        
        if($request->id) {   
            $whereData[] = ['id', $request->id];
        }
        if($request->is_enable != null) {
            $whereData[] = ['is_enable', $request->is_enable];
        }
        
        
        Utilities::set_all_data_permission_request_parameters($request);
        $data_org_id         = $request->data_p_org_id;
        $data_country_id     = $request->data_p_country_id;
        $data_state_id       = $request->data_p_state_id;
        $data_region_id      = $request->data_p_region_id;
        $data_city_id        = $request->data_p_city_id;
        
        
        $queryObj = City::with('Country', 'State', 'Region')
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
                    $query->orWhere('region_id', '=', $value);
                }
            });
        }
        
        if(!empty($data_city_id)){
            $queryObj->where(function($query) use ($data_city_id) {
                foreach ($data_city_id as $key => $value) {
                    $query->orWhere('id', '=', $value);
                }
            });
        }
        
        
        if(!empty($region_id_arr)){
           $queryObj->whereIn('region_id', $region_id_arr);
        }
        
        $result_set = $queryObj->get($select);
        
        $data_result = [];
        $status = 200;
        $data_result['city'] = $result_set->toArray();
        
        $response = Utilities::buildSuccessResponse(10004, "City List.", $data_result);

        return response()->json($response, $status); 
    }
}
