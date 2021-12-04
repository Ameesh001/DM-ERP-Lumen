<?php

/**
 * Performance system API
 * This is a Country API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\Country;
use App\Models\State;
use App\Response\CountryResponse;
use Illuminate\Http\Request;
use App\Utilities\Utilities;

class CountryApi extends Controller
{

    /**
     * Constructor
     */
    public function __construct()
    {

    }

    private $select_columns = ['id', 'lang_id', 'lang_name' ,'country_name as name', 'country_full_name as full_name', 'dialing_code as dial_code', 'short_code', 'is_enable as activate'];

    /**
     * This fucntion is called after validation fails in function $this->validate.
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
     * Add Country.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addCountry(Request $request)
    {
        
        $mdlCountry = new Country();
        
        $this->validate($request, $mdlCountry->rules($request), $mdlCountry->messages($request));
        
        $mdlCountry->filterColumns($request);
        
        Utilities::defaultAddAttributes($request, $request->data_user_id);
         $postArr = $request->all();
        $postArr['country_name'] = trim($postArr['country_name']);
        $postArr['country_full_name'] = trim($postArr['country_full_name']);
        $postArr['dialing_code'] = trim($postArr['dialing_code']);
        $postArr['short_code'] = trim($postArr['short_code']);
        $idstring="";
        $namestring="";
        foreach($postArr['lang_id'] as $array){    
            $idstring .= $array['id'].",";
            $namestring .= $array['name'].","; 
        }
        if($idstring){
            $idstring = rtrim($idstring, ',');
        }
        if($namestring){
            $namestring = rtrim($namestring, ',');
        }
        $postArr['lang_id'] = json_encode($postArr['lang_id'], true);
        $postArr['lang_name'] = $namestring;

        $obj = Country::create($postArr);
        
        $data = [ 'id' => $obj->id ];
        
        $response = Utilities::buildSuccessResponse(10000, "Country successfully created.", $data);
        
        return response()->json($response, 201);
    }

    /**
     * Update Country.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCountry(Request $request)
    {
        $mdlCountry = new Country();
        
        $this->validate($request, $mdlCountry->rules($request), $mdlCountry->messages($request));
        
        $mdlCountry->filterColumns($request);
        
        Utilities::defaultAddAttributes($request, $request->data_user_id);

        $country = Country::find($request->id);
        $postArr = $request->all();
        $postArr['country_name'] = trim($postArr['country_name']);
        $postArr['country_full_name'] = trim($postArr['country_full_name']);
        $postArr['dialing_code'] = trim($postArr['dialing_code']);
        $postArr['short_code'] = trim($postArr['short_code']);
        $status = 200;
        $response = [];
        $idstring="";
        $namestring="";
        if(!empty($postArr['lang_id'])){
            foreach($postArr['lang_id'] as $array){    
                $idstring .= $array['id'].",";
                $namestring .= $array['name'].","; 
            }
        }
        if($idstring){
            $idstring = rtrim($idstring, ',');
        }
        if($namestring){
            $namestring = rtrim($namestring, ',');
        }
        $postArr['lang_id'] = json_encode($postArr['lang_id'], true);
        $postArr['lang_name'] = $namestring;
        if (! $country) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Country not found.");
        } else {
           
            $obj = $country->update($postArr);
            
            if ($obj) {
                $data = [ 'id' => $country->id ];
                $response = Utilities::buildSuccessResponse(10001, "Country successfully updated.", $data);
            }
        }
        
        return response()->json($response, $status);
    }
    
    /**
     * Activate/De-Activate Country.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function patchDisableEnableCountry(Request $request)
    {
        $mdlCountry = new Country();

        $whereData[] = ['countries_id', $request->id];
        $whereData[] = ['is_enable', 1];
        $state = State::where($whereData)->first();
        if($state)
        {
            if($request->activate == 0)
            {
                $status = 200;
                $response = Utilities::buildBaseResponse(30001," Country Has Child State...");
                return response()->json($response, $status);
            } 
        }

        Utilities::removeAttributesExcept($request, ["id","activate"]);
        
        $this->validate($request, $mdlCountry->rules($request), $mdlCountry->messages($request));
        
        Utilities::defaultAddAttributes($request, $request->data_user_id);
        
        $activate = $request->activate == 1 ? Constant::RecordType['ENABLED'] : Constant::RecordType['DISABLED'];
        
        $request->request->add([ 'is_enable' => $activate ]);
        
        
        $country = Country::find($request->id);
        $status = 200;
        $response = [];

        if (! $country) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Country not found.");
        } else {
            
            $obj = $country->update($request->all());
            
            if ($obj) {
                $data = ['id' => $country->id ];
                // $data = ['state_id' => $state->id];
                $actMsg = $request->activate == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "Country successfully $actMsg.", $data);
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Delete Country.
     *
     * @param $id 'ID' of Country to delete. (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCountry($id, Request $request)
    {
        $mdlCountry = new Country();

        $whereData[] = ['countries_id', $request->id];
        $whereData[] = ['is_enable', 1];
        $state = State::where($whereData)->first();
        if($state)
        {
            $status = 200;
            $response = Utilities::buildBaseResponse(30001," Country Has Child State...");
            return response()->json($response, $status);
        }
        $request->request->add([ 'id' => $id ]);
        
        $this->validate($request, $mdlCountry->rules($request), $mdlCountry->messages($request));
        
        Utilities::defaultDeleteAttributes($request, 1);
        
        $request->request->add([
            'is_enable' => Constant::RecordType['DELETED']
        ]);
        
        $country = Country::find($request->id);
        
        $status = 200;
        $response = [];
        if (! $country) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Country not found.");
        } else {
            
            $obj = $country->update($request->all());
            
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "Country successfully deleted.");
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Get one Country.
     *
     * @param $id 'ID' of Country to return (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOneCountry($id, Request $request)
    {
        $mdlCountry = new Country();
        
        $request->request->add([ 'id' => $id ]);
        
        $this->validate($request, $mdlCountry->rules($request, Constant::RequestType['GET_ONE']), $mdlCountry->messages($request, Constant::RequestType['GET_ONE']));
        
        $select = $this->select_columns;

        $mdlCountry->filterColumns($request);
        
        if($request->fields){
            $select = $request->fields;
        }
        
        $country = Country::where('id', $request->id)->first($select);
        
        $country['lang_id'] = json_decode($country['lang_id'], true); 
        
        $status = 200;
        $response = [];

        if (! $country) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Country not found.");
        } else {
            $dataResult = array("country" => $country->toArray());
            $response = Utilities::buildSuccessResponse(10005, "Country Data.", $dataResult);
        }
        
        return response()->json($response, $status);
    }

    /**
     * Fetch list of Country by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllCountry(Request $request)
    {
        $mdlCountry = new Country();
        
        $this->validate($request, $mdlCountry->rules($request, Constant::RequestType['GET_ALL']), $mdlCountry->messages($request, Constant::RequestType['GET_ALL']));
        
        $pageSize = $request->limit ?? Constant::PageSize;
        if($pageSize > Constant::MaxPageSize){
            $pageSize = Constant::PageSize;
        }
        $page = $request->page ?? Constant::Page;
        $skip = ($page - 1) * $pageSize;

        
        $select = $this->select_columns;

        $mdlCountry->filterColumns($request);
        
        if($request->fields){
            $select = $request->fields;
        }
        $whereData = array();
        
        if($request->name) {   
            $whereData[] = ['country_name', 'LIKE', "%{$request->name}%"];
        }
        if($request->full_name) {   
            $whereData[] = ['country_full_name', 'LIKE', "%{$request->full_name}%"];
        }
        if($request->dial_code) {   
            $whereData[] = ['dialing_code', 'LIKE', "%{$request->dial_code}%"];
        }
        if($request->short_code) {   
            $whereData[] = ['short_code', 'LIKE', "%{$request->short_code}%"];
        }
        if($request->activate != null) {
            $whereData[] = ['is_enable', $request->activate];
        }
        
        Utilities::set_all_data_permission_request_parameters($request);
        $data_org_id         = $request->data_p_org_id;
        $data_country_id     = $request->data_p_country_id;
        
        
        $total_record_q = Country::with('OrganizationCountry')->where($whereData)->active();
        
        if(!empty($data_org_id)){
            $total_record_q->whereHas('OrganizationCountry', function ($q) use ($data_org_id, $data_country_id) {

               if (!empty($data_org_id)) {
                   $q->where(function($query) use ($data_org_id) {
                       foreach ($data_org_id as $key => $value) {
                           $query->orWhere('id', '=', $value);
                       }
                   });
               }

           });
        }
            
       
        if(!empty($data_country_id)){
            $total_record_q->where(function($query) use ($data_country_id) {
                foreach ($data_country_id as $key => $value) {
                    $query->orWhere('id', '=', $value);
                }
            });
        }
        
        
        $total_record = $total_record_q->count();
        
        
        $orderBy =  $mdlCountry->getOrderColumn($request->order_by);
        $orderType = $request->order_type ?? Constant::OrderType;
        
        $countries_q = Country::with('OrganizationCountry')->where($whereData)
            ->active()
            ->orderBy($orderBy, $orderType)
            ->offset($skip)
            ->limit($pageSize);
        
        if(!empty($data_org_id)){
            $countries_q->whereHas('OrganizationCountry', function ($q) use ($data_org_id, $data_country_id) {

                if (!empty($data_org_id)) {
                    $q->where(function($query) use ($data_org_id) {
                        foreach ($data_org_id as $key => $value) {
                            $query->orWhere('id', '=', $value);
                        }
                    });
                }

            });
        }
            
       
        if(!empty($data_country_id)){
            $countries_q->where(function($query) use ($data_country_id) {
                foreach ($data_country_id as $key => $value) {
                    $query->orWhere('id', '=', $value);
                }
            });
        }
        
        $countries  =  $countries_q->get($select);
        
        $status = 200;
        
//        $data_result = new CountryResponse();
//        $data_result->setCountries($countries->toArray());
        
        $data_result['countries'] = $countries->toArray();
        $data_result['total_record'] = $total_record;
        $response = Utilities::buildSuccessResponse(10004, "Country List.", $data_result);
        
        return response()->json($response, $status);   
    }
    
    
    /**
     * Fetch list of Country by searching with optional filters for dropdown/selectbox list
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllData(Request $request)
    {
       
        $whereData = array();
        
        if($request->name) {   
            $whereData[] = ['country_name', 'LIKE', "%{$request->name}%"];
        }
        if($request->full_name) {   
            $whereData[] = ['country_full_name', 'LIKE', "%{$request->full_name}%"];
        }
        if($request->dial_code) {   
            $whereData[] = ['dialing_code', 'LIKE', "%{$request->dial_code}%"];
        }
        if($request->short_code) {   
            $whereData[] = ['short_code', 'LIKE', "%{$request->short_code}%"];
        }
        
        if($request->is_enable != null) {
            $whereData[] = ['is_enable', $request->is_enable];
        }else{
            $whereData[] = ['is_enable', 1];
        }
        
        if($request->id != null) {
            $whereData[] = ['id', $request->id];
        }
        
        $select = $this->select_columns;
         
        if($request->fields){
            $select = $request->fields;
        }
        
        
        Utilities::set_all_data_permission_request_parameters($request);
        $data_org_id         = $request->data_p_org_id;
        $data_country_id     = $request->data_p_country_id;
        
        
        $countries_q = Country::with('OrganizationCountry')->where($whereData)
            ->active();
        
        if(!empty($data_org_id)){
            $countries_q->whereHas('OrganizationCountry', function ($q) use ($data_org_id, $data_country_id) {

                if (!empty($data_org_id)) {
                    $q->where(function($query) use ($data_org_id) {
                        foreach ($data_org_id as $key => $value) {
                            $query->orWhere('id', '=', $value);
                        }
                    });
                }

            });
        }
            
       
        if(!empty($data_country_id)){
            $countries_q->where(function($query) use ($data_country_id) {
                foreach ($data_country_id as $key => $value) {
                    $query->orWhere('id', '=', $value);
                }
            });
        }
        
         $countries =   $countries_q->get($select);
        
        $status = 200;

        $data_result['countries'] = $countries->toArray();
        
        $response = Utilities::buildSuccessResponse(10004, "Country List", $data_result);
        
        return response()->json($response, $status);   
    }
}
