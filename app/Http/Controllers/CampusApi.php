<?php

/**
 * Darulmadinah Api
Author: Muhammad Usama (it.dev7) 
 * 
 * This is a Campus API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\Campus;
use App\Models\Session;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Utilities\Utilities;

class CampusApi extends Controller
{

    /**
     * Constructor
     */
    private $mdlName;
             
    public function __construct()
    {
        $this->mdlName = new Campus();
    }
    
    private $select_columns  = [
        'id', 
        'organization_id', 
        'countries_id', 
        'state_id', 
        'region_id',
        'city_id', 
        'campus_name', 
        'campus_address',
        'campus_email', 
        'campus_cell', 
        'principle_name',
        'principle_cell',
        'principle_email', 
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
     * Add Campus.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
             
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        
        // $post_arr['organization_id'] =  $post_arr['data_org_id'];
        $this->mdlName->filterColumns($request);
        
         Utilities::defaultAddAttributes($request, $request->data_user_id);
            
        $post_arr = $request->all();
        // $post_arr['organization_id'] =  $post_arr['data_org_id'];
        $obj = Campus::create($post_arr);
        
        $data = [ 'id' => $obj->id ];
        
        $response = Utilities::buildSuccessResponse(10000, "Campus successfully created.", $data);
        
        return response()->json($response, 201);
    }

    /**
     * Update Campus.
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
        $result_set = Campus::find($request->id);
        
        $status = 200;
        $response = [];
        
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Campus not found.");
        } else {
            
            $post_arr = $request->all();
           
            $obj = $result_set->update($post_arr);

            if ($obj) {
                $data = [ 'id' => $result_set->id ];
                $response = Utilities::buildSuccessResponse(10001, "Campus successfully updated.", $data);
            } 
        }
        
        return response()->json($response, $status);
    }
    
    /**
     * Activate/De-Activate Campus.
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
        
        $result_set = Campus::find($request->id);
        $status = 200;
        $response = [];

        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Campus not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $data = ['id' => $result_set->id ];
                $actMsg = $request->is_enable == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "Campus successfully $actMsg.", $data);
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Delete Campus.
     *
     * @param $id 'ID' of Campus to delete. (required)
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
        
        $result_set = Campus::find($request->id);
        
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Campus not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "Campus successfully deleted.");
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Get one Campus.
     *
     * @param $id 'ID' of Campus to return (required)
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
        
        $result_set = Campus::with('Country', 'State', 'Region', 'City')->where('id', $request->id)->first($select);
        
        $status = 200;
        $response = [];

        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Campus not found.");
        } else {
            
            $data_set = $result_set->toArray();
  
            $dataResult['data_set']             = $data_set;
  
            $response = Utilities::buildSuccessResponse(10005, "Campus Data.", $dataResult);
        }
        
        return response()->json($response, $status);
    }

    /**
     * Fetch list of Campus by searching with optional filters..
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
        
        
        if($request->campus_name) {   
            $whereData[] = ['campus_name', 'LIKE', "%{$request->campus_name}%"];
        }
        if($request->campus_address) {   
            $whereData[] = ['campus_address', 'LIKE', "%{$request->campus_address}%"];
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
        
        if($request->city_id) {   
            $whereData[] = ['city_id', $request->city_id];
        }

        if($request->organization_id) {   
            $whereData[] = ['organization_id', $request->organization_id];
        }
                
        if($request->campus_email) {   
            $whereData[] = ['campus_email', $request->campus_email];
        }
        
        
        if($request->campus_cell) {   
            $whereData[] = ['campus_cell', $request->campus_cell];
        }
        
        
        if($request->principle_name) {   
            $whereData[] = ['principle_name', $request->principle_name];
        }
        
        if($request->principle_cell) {   
            $whereData[] = ['principle_cell', $request->principle_cell];
        }
        
        
        if($request->principle_email) {   
            $whereData[] = ['principle_email', $request->principle_email];
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
        $data_campus_id        = $request->data_p_campus_id;
        
        
        $total_record_q = Campus::where($whereData);
        
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
                    $query->orWhere('city_id', '=', $value);
                }
            });
        }
        
        if(!empty($data_campus_id)){
            $total_record_q->where(function($query) use ($data_campus_id) {
                foreach ($data_campus_id as $key => $value) {
                    $query->orWhere('id', '=', $value);
                }
            });
        }
        
        $total_record  = $total_record_q->active()
            ->count();
        
        $orderBy =  $this->mdlName->getOrderColumn($request->order_by);
        
        $orderType = $request->order_type ?? Constant::OrderType;
        
        $queryObj = Campus::with('Country', 'State', 'Region', 'City')
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
                    $query->orWhere('city_id', '=', $value);
                }
            });
        }
        
        if(!empty($data_campus_id)){
            $queryObj->where(function($query) use ($data_campus_id) {
                foreach ($data_campus_id as $key => $value) {
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
        
        $response = Utilities::buildSuccessResponse(10004, "Campus List.", $data_result);

        return response()->json($response, $status); 
    }
    
    /**
     * Fetch list of Campus for selectboc/dropdown by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCampus(Request $request, $id = null)
    {
        $this->validate($request, $this->mdlName->rules($request, Constant::RequestType['GET_ALL']), $this->mdlName->messages($request, Constant::RequestType['GET_ALL']));
        
       
        $select =  $this->select_columns;
        
        if($request->fields){
            $select = $request->fields;
        }
        $whereData = array();
        
        
        if($request->campus_name) {   
            $whereData[] = ['campus_name', 'LIKE', "%{$request->campus_name}%"];
        }
        if($request->campus_address) {   
            $whereData[] = ['campus_address', 'LIKE', "%{$request->campus_address}%"];
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
        if ($request->city_id) {
            if(strlen($request->city_id) > 1){
               $city_id_arr = explode(",", $request->city_id);
            }else{
               $whereData[] = ['city_id', $request->city_id];
            }
        }
        
        if($request->campus_email) {   
            $whereData[] = ['campus_email', $request->campus_email];
        }
        
        
        if($request->campus_cell) {   
            $whereData[] = ['campus_cell', $request->campus_cell];
        }
        
        
        if($request->principle_name) {   
            $whereData[] = ['principle_name', $request->principle_name];
        }
        
        if($request->principle_cell) {   
            $whereData[] = ['principle_cell', $request->principle_cell];
        }
        
        
        if($request->principle_email) {   
            $whereData[] = ['principle_email', $request->principle_email];
        }
        
        
        if($request->campus_id) {   
            $whereData[] = ['id', $request->campus_id];
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
        $data_campus_id      = $request->data_p_campus_id;
        
        $queryObj = Campus::with('Country', 'State', 'Region')
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
                    $query->orWhere('city_id', '=', $value);
                }
            });
        }
        
        if(!empty($data_campus_id)){
            $queryObj->where(function($query) use ($data_campus_id) {
                foreach ($data_campus_id as $key => $value) {
                    $query->orWhere('id', '=', $value);
                }
            });
        }
        
        if(!empty($city_id_arr)){
           $queryObj->whereIn('city_id', $city_id_arr);
        }
        
        $result_set = $queryObj->get($select);
        
        
        $data_result = [];
        $status = 200;
        $data_result['campus'] = $result_set->toArray();
        
        $response = Utilities::buildSuccessResponse(10004, "Campus List.", $data_result);

        return response()->json($response, $status); 
    }
    
    
    /**
     * Fetch list of Campus for selectboc/dropdown by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCampusWithSessionOrg(Request $request, $id = null)
    {
        
        $data_result = [];
        
        $this->validate($request, $this->mdlName->rules($request, Constant::RequestType['GET_ALL']), $this->mdlName->messages($request, Constant::RequestType['GET_ALL']));
        
       
        $select =  $this->select_columns;
        
        if($request->fields){
            $select = $request->fields;
        }
        $whereData = array();
        
        
        if($request->campus_name) {   
            $whereData[] = ['campus_name', 'LIKE', "%{$request->campus_name}%"];
        }
        if($request->campus_address) {   
            $whereData[] = ['campus_address', 'LIKE', "%{$request->campus_address}%"];
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
        if ($request->city_id) {
            if(strlen($request->city_id) > 1){
               $city_id_arr = explode(",", $request->city_id);
            }else{
               $whereData[] = ['city_id', $request->city_id];
            }
        }
        
        if($request->campus_email) {   
            $whereData[] = ['campus_email', $request->campus_email];
        }
        
        
        if($request->campus_cell) {   
            $whereData[] = ['campus_cell', $request->campus_cell];
        }
        
        
        if($request->principle_name) {   
            $whereData[] = ['principle_name', $request->principle_name];
        }
        
        if($request->principle_cell) {   
            $whereData[] = ['principle_cell', $request->principle_cell];
        }
        
        
        if($request->principle_email) {   
            $whereData[] = ['principle_email', $request->principle_email];
        }
        
        
        if($request->campus_id) {   
            $whereData[] = ['id', $request->campus_id];
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
        $data_campus_id      = $request->data_p_campus_id;
        
        $queryObj = Campus::with('Country', 'State', 'Region')
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
                    $query->orWhere('city_id', '=', $value);
                }
            });
        }
        
        if(!empty($data_campus_id)){
            $queryObj->where(function($query) use ($data_campus_id) {
                foreach ($data_campus_id as $key => $value) {
                    $query->orWhere('id', '=', $value);
                }
            });
        }
        
        if(!empty($city_id_arr)){
           $queryObj->whereIn('city_id', $city_id_arr);
        }
        
        $campus_result_set = $queryObj->get($select);
        
        $campus_data = $campus_result_set->toArray();
        
        if(!empty($campus_data)){
            
            if($request->countries_id) {   
                $whereSessionData[] = ['campus_id', $request->campus_id];
            }
            if($request->id) {   
                $whereSessionData[] = ['id', $request->id];
            }

            if($request->session_id) {   
                $whereSessionData[] = ['id', $request->session_id];
            }

            if($request->organization_id) {   
                $whereSessionData[] = ['organization_id', $request->organization_id];
            }

            if($request->is_enable != null) {
                $whereSessionData[] = ['is_enable', $request->is_enable];
            }
            else{
                $whereSessionData[] = ['is_enable', 1];
            }


            $session_result = Session::where($whereSessionData)
                        ->active()
                        ->get(); 
            
            $session_data = $session_result->toArray();
            
            
            if(!empty($session_data)){
               
                $data_result['session'] = $session_data;
                
                $org_result_set = Organization::where('id', $campus_data[0]['organization_id'])->active()->get();
                $list = $org_result_set->toArray()[0];
                
                if(!empty($list)){
                    
                    $org_data_set = $list;
                    
                    unset($org_data_set['org_logo']);

                    $data_result['organaization']        = $org_data_set;
                    $data_result['affiliation_board_id'] = $org_data_set['affiliation_board_id'];
                    $data_result['org_logo']             = $list['org_logo'];

                    $getImg       = url('app/organization_file/');
                    $getImgPublic = '';
                    if (strpos($getImg, 'public') !== false) {
                        $getImgPublic = str_replace('public', 'storage', $getImg);
                    }else{
                        $getImgPublic = url('storage/app/organization_file/');
                    }
                    $data_result['logo_path']    = $getImgPublic . '/'. $list['org_logo'].'?'.rand();
                    $data_result['$getImg']      = $getImgPublic;
                }
               

                
                
            }
        }
        
        
        $status = 200;
        
        $data_result['campus']  = $campus_data;
        
   
        $response = Utilities::buildSuccessResponse(10004, "Campus with Session And Organization List.", $data_result);

        return response()->json($response, $status); 
    }
    
    
    
}
