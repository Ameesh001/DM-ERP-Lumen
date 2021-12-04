<?php

/**
 * Darulmadinah Api
Author: Muhammad Usama (it.dev7) 
 * 
 * This is a Organization API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\Organization;
//use App\Response\OrganizationResponse;
use Illuminate\Http\Request;
use App\Utilities\Utilities;
use Illuminate\Support\Collection;

class OrganizationApi extends Controller
{

    /**
     * Constructor
     */
    private $mdlOrganization;
             
    public function __construct()
    {
        $this->mdlOrganization = new Organization();
    }

    private $select_columns = ['id', 'org_prefix', 'org_name', 'org_logo', 'countries_id', 'org_address', 'org_contact', 'affiliation_board_id', 'is_enable'];

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
     * Add Organization.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
             
        $this->validate($request, $this->mdlOrganization->rules($request), $this->mdlOrganization->messages($request));
        
        $this->mdlOrganization->filterColumns($request);
        
         Utilities::defaultAddAttributes($request, $request->data_user_id);
            
        $post_arr = $request->all();
        
        $post_arr['id'] = null;
        $folder = storage_path('app/organization_file/');

        
        if($post_arr['org_logo']){
            $img = explode(";base64,", $post_arr['org_logo']);

            $img_aux = explode("image/", $img[0]);

//            $image_type = $img_aux[1];

            $img_base64 = base64_decode($img[1]);

            $image_name = $post_arr['org_prefix'] . '_logo.png';

            file_put_contents($folder . $image_name, $img_base64);
        
            $post_arr['org_logo'] = $image_name;
        }
       

        if($post_arr['affiliation_board_id']){
            $post_arr['affiliation_board_id'] = implode (",", $post_arr['affiliation_board_id']);
        }
        
          
        $obj = Organization::create($post_arr);
        
        $data = [ 'id' => $obj->id ];
        $response = Utilities::buildSuccessResponse(10000, "Organization successfully created.", $data);
        
        return response()->json($response, 201);
    }

    /**
     * Update Organization.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $this->validate($request, $this->mdlOrganization->rules($request), $this->mdlOrganization->messages($request));
        
        $this->mdlOrganization->filterColumns($request);
        
        Utilities::defaultUpdateAttributes($request, $request->data_user_id);
//
        $result_set = Organization::find($request->id);
        
        $status = 200;
        $response = [];
        
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Organization not found.");
        } else {
            
            $post_arr = $request->all();
           
            $folder = storage_path('app/organization_file/');
            
            if( !empty($post_arr['org_logo']) ){
                
                $img = explode(";base64,", $post_arr['org_logo']);
                
                $img_aux = explode("image/", $img[0]);

//                $image_type = $img_aux[1];

                $img_base64 = base64_decode($img[1]);

                $image_name = $post_arr['org_prefix'] . '_logo.png';

                file_put_contents($folder . $image_name, $img_base64);
                
                $post_arr['org_logo'] = $image_name;
            }else{
                unset($post_arr['org_logo']);
            }
            
            
            if($post_arr['affiliation_board_id']){
                $post_arr['affiliation_board_id'] = implode (",", $post_arr['affiliation_board_id']);
            }
            
            
           
            $obj = $result_set->update($post_arr);

            if ($obj) {
                $data = [ 'id' => $result_set->id ];
                $response = Utilities::buildSuccessResponse(10001, "Organization successfully updated.", $data);
            } 
        }
        
        return response()->json($response, $status);
    }
    
    /**
     * Activate/De-Activate Organization.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function patchDisableEnable(Request $request)
    {
//        echo '';
//        print_r($request);
        
        
        Utilities::removeAttributesExcept($request, ["id","is_enable"]);
        
        $this->validate($request, $this->mdlOrganization->rules($request), $this->mdlOrganization->messages($request));
        
        Utilities::defaultUpdateAttributes($request, $request->data_user_id);
        
        $activate = $request->is_enable == 1 ? Constant::RecordType['ENABLED'] : Constant::RecordType['DISABLED'];
        
        $request->request->add([ 'is_enable' => $activate ]);
        
        $result_set = Organization::find($request->id);
        $status = 200;
        $response = [];

        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Organization not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $data = ['id' => $result_set->id ];
                $actMsg = $request->is_enable == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "Organization successfully $actMsg.", $data);
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Delete Organization.
     *
     * @param $id 'ID' of Organization to delete. (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id, Request $request)
    {
        $request->request->add([ 'id' => $id ]);
        
        $this->validate($request, $this->mdlOrganization->rules($request), $this->mdlOrganization->messages($request));
        
        Utilities::defaultDeleteAttributes($request, 1);
        
        $request->request->add([
            'is_enable' => Constant::RecordType['DELETED']
        ]);
        
        $result_set = Organization::find($request->id);
        
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Organization not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "Organization successfully deleted.");
            }
        }
        
        return response()->json($response, $status);
    }
    
    public function deleteByPrefix($id, Request $request)
    {
//        $request->request->add([ 'id' => $id ]);
//        
//        $this->validate($request, $this->mdlOrganization->rules($request), $this->mdlOrganization->messages($request));
//        
//        Utilities::defaultDeleteAttributes($request, 1);
        
//        $request->request->add([
//            'is_enable' => Constant::RecordType['DELETED']
//        ]);
        
        $result_set = Organization::where('org_prefix', '=', $id);
        
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Organization not found.");
        } else {
            
            $obj = $result_set->delete();
            
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "Organization successfully deleted.");
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Get one Organization.
     *
     * @param $id 'ID' of Organization to return (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOne($id, Request $request)
    {
        $request->request->add([ 'id' => $id ]);
        
        $this->validate($request, $this->mdlOrganization->rules($request, Constant::RequestType['GET_ONE']), $this->mdlOrganization->messages($request, Constant::RequestType['GET_ONE']));
        
        $select = $this->select_columns;

        $this->mdlOrganization->filterColumns($request);
        
        if($request->fields){
            $select = $request->fields;
        }
        
        $result_set = Organization::where('id', $request->id)->first($select);
        
        $status = 200;
        $response = [];

        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Organization not found.");
        } else {
            
            $list = $result_set->toArray();
            
            $data_set = $list;
            
            unset($data_set['org_logo']);
            
            $dataResult['data_set']             = $data_set;
            $dataResult['affiliation_board_id'] = $list['affiliation_board_id'];
            $dataResult['org_logo']             = $list['org_logo'];
            
            $getImg       = url('app/organization_file/');
            $getImgPublic = '';
            if (strpos($getImg, 'public') !== false) {
                $getImgPublic = str_replace('public', 'storage', $getImg);
            }else{
                $getImgPublic = url('storage/app/organization_file/');
            }
            $dataResult['logo_path']    = $getImgPublic . '/'. $list['org_logo'].'?'.rand();
            $dataResult['$getImg']      = $getImgPublic;
                 
            $response = Utilities::buildSuccessResponse(10005, "Organization Data.", $dataResult);
        }
        
        return response()->json($response, $status);
    }

    /**
     * Fetch list of Organization by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll(Request $request, $id = null)
    {
        $this->validate($request, $this->mdlOrganization->rules($request, Constant::RequestType['GET_ALL']), $this->mdlOrganization->messages($request, Constant::RequestType['GET_ALL']));
        
        $pageSize = $request->limit ?? Constant::PageSize;
        
        if($pageSize > Constant::MaxPageSize){
            $pageSize = Constant::MaxPageSize;
        }
        
        $page = $request->page ?? Constant::Page;
        
        $skip = ($page - 1) * $pageSize;
        
        $select = '*';
        
        if($request->fields){
            $select = $request->fields;
        }
        $whereData = array();
        
        if($request->org_prefix) {   
            $whereData[] = ['org_prefix', 'LIKE', "%{$request->org_prefix}%"];
        }
        if($request->org_name) {   
            $whereData[] = ['org_name', 'LIKE', "%{$request->org_name}%"];
        }
        if($request->org_address) {   
            $whereData[] = ['org_address', 'LIKE', "%{$request->org_address}%"];
        }
        if($request->org_contact) {   
            $whereData[] = ['org_contact', 'LIKE', "%{$request->org_contact}%"];
        }
        if($request->id) {   
            $whereData[] = ['id', $request->id];
        }
        if($request->is_enable != null) {
            $whereData[] = ['is_enable', $request->is_enable];
        }
        
        Utilities::set_all_data_permission_request_parameters($request);
        $data_org_id       = $request->data_p_org_id;
        
        $total_record_q = Organization::where($whereData)
            ->active();
        
         if(!empty($data_org_id)){
            $total_record_q->where(function($query) use ($data_org_id) {
                foreach ($data_org_id as $key => $value) {
                    $query->orWhere('id', '=', $value);
                }
            });
        }
        
        $total_record = $total_record_q->count();
        
        
        $orderBy =  $request->order_by ?? Constant::OrderBy;
        $orderType = $request->order_type ?? Constant::OrderType;
        
        $data_set_query = Organization::with('Country')->where($whereData)
            ->active();
        
        if(!empty($data_org_id)){
            $data_set_query->where(function($query) use ($data_org_id) {
                foreach ($data_org_id as $key => $value) {
                    $query->orWhere('id', '=', $value);
                }
            });
        }
        
        $data_set = $data_set_query->orderBy($orderBy, $orderType)
            ->offset($skip)
            ->limit($pageSize)
            ->get($select);
        
        
        $data_result = [];
        $status = 200;
        $data_result['data_list'] = $data_set->toArray();
        $data_result['total_record'] = $total_record;
        
        $response = Utilities::buildSuccessResponse(10004, "Organization List.", $data_result);

        return response()->json($response, $status); 
    }

    public function getAllAcitve(Request $request)
    {
       
        $this->validate($request, $this->mdlOrganization->rules($request, Constant::RequestType['GET_ALL']), $this->mdlOrganization->messages($request, Constant::RequestType['GET_ALL']));
      
        $select = '*';
        if($request->fields){
            $select = $request->fields;
        }
        $whereData = array();
        if($request->org_prefix) {   
            $whereData[] = ['org_prefix', 'LIKE', "%{$request->org_prefix}%"];
        }
        if($request->org_name) {   
            $whereData[] = ['org_name', 'LIKE', "%{$request->org_name}%"];
        }
        if($request->org_address) {   
            $whereData[] = ['org_address', 'LIKE', "%{$request->org_address}%"];
        }
        if($request->org_contact) {   
            $whereData[] = ['org_contact', 'LIKE', "%{$request->org_contact}%"];
        }
        if($request->id) {   
            $whereData[] = ['id', $request->id];
        }
       
        if($request->is_enable != null) {
            $whereData[] = ['is_enable', $request->is_enable];
        }else{
            $whereData[] = ['is_enable', 1];
        }
        
        Utilities::set_all_data_permission_request_parameters($request);
        $data_org_id       = $request->data_p_org_id;
        
                
        $data_set_query = Organization::where($whereData);
        
        if(!empty($data_org_id)){
            $data_set_query->where(function($query) use ($data_org_id) {
                foreach ($data_org_id as $key => $value) {
                    $query->orWhere('id', '=', $value);
                }
            });
        }
        
        $data_set = $data_set_query->active()->get($select);
        
        $data_result = [];
        $status = 200;
        $data_result['org_list'] = $data_set->toArray();
        $response = Utilities::buildSuccessResponse(10004, "Organization List.", $data_result);
        return response()->json($response, $status);    
    }
    
    public function getAllCountryOrg(Request $request)
    {
       
        $this->validate($request, $this->mdlOrganization->rules($request, Constant::RequestType['GET_ALL']), $this->mdlOrganization->messages($request, Constant::RequestType['GET_ALL']));
      
        $select = '*';
        
        if($request->fields){
            $select = $request->fields;
        }
        $whereData = array();
        if($request->org_prefix) {   
            $whereData[] = ['org_prefix', 'LIKE', "%{$request->org_prefix}%"];
        }
        if($request->org_name) {   
            $whereData[] = ['org_name', 'LIKE', "%{$request->org_name}%"];
        }
        if($request->org_address) {   
            $whereData[] = ['org_address', 'LIKE', "%{$request->org_address}%"];
        }
        if($request->org_contact) {   
            $whereData[] = ['org_contact', 'LIKE', "%{$request->org_contact}%"];
        }
        
        if ($request->organization_id) {
            if(strlen($request->organization_id) > 1){
               $organization_id_arr = explode(",", $request->organization_id);
            }else{
               $whereData[] = ['id', $request->organization_id];
            }
        }
        
       
        if($request->is_enable != null) {
            $whereData[] = ['is_enable', $request->is_enable];
        }else{
            $whereData[] = ['is_enable', 1];
        }
        
        
        Utilities::set_all_data_permission_request_parameters($request);
        $data_org_id       = $request->data_p_org_id;
        $data_country_id   = $request->data_p_country_id;
        
        
        $queryObj = Organization::with('Country')->where($whereData)
            ->active();
        
        
        
        if(!empty($data_org_id)){
            $queryObj->where(function($query) use ($data_org_id) {
                foreach ($data_org_id as $key => $value) {
                    $query->orWhere('id', '=', $value);
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
        
        
        if(!empty($organization_id_arr)){
           $queryObj->whereIn('id', $organization_id_arr);
        }
        
        $result_set = $queryObj->get($select);
       
        $getCountryList = [];
        foreach ($result_set->toArray() as $key => $value) {
            $getCountryList[$value['country']['id']] = $value['country'];              
        }
        
        $getCountryListArr = [];
        foreach($getCountryList as $key => $value){
            $getCountryListArr[] = $value;    
        }
        
             
        $data_result = [];
        $status = 200;
        $data_result['list'] = $result_set->toArray();
        $data_result['countries'] = $getCountryListArr;
        $response = Utilities::buildSuccessResponse(10004, "Organization List.", $data_result);
        return response()->json($response, $status);    
    }
    
}
