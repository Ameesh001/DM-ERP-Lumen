<?php

/**
 * Darulmadinah Api
Author: Muhammad Usama (it.dev7) 
 * 
 * This is a Affiliation API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\Affiliation;
use Illuminate\Http\Request;
use App\Utilities\Utilities;

class AffiliationApi extends Controller
{

    /**
     * Constructor
     */
    private $mdlAffiliation;
             
    public function __construct()
    {
        $this->mdlAffiliation = new Affiliation();
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
     * Add Affiliation.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        $this->validate($request, $this->mdlAffiliation->rules($request), $this->mdlAffiliation->messages($request));
        
        $this->mdlAffiliation->filterColumns($request);
        
        Utilities::defaultAddAttributes($request, $request->data_user_id);
        
        $obj = Affiliation::create($request->all());
        
        $data = [ 'id' => $obj->id ];
        
        $response = Utilities::buildSuccessResponse(10000, "Affiliation successfully created.", $data);
        
        return response()->json($response, 201);
    }

    /**
     * Update Affiliation.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $this->validate($request, $this->mdlAffiliation->rules($request), $this->mdlAffiliation->messages($request));
        
        $this->mdlAffiliation->filterColumns($request);
        
        Utilities::defaultUpdateAttributes($request, $request->data_user_id);

        $result_set = Affiliation::find($request->id);
        
        $status = 200;
        $response = [];
        
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Affiliation not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $data = [ 'id' => $result_set->id ];
                $response = Utilities::buildSuccessResponse(10001, "Affiliation successfully updated.", $data);
            }
        }
        
        return response()->json($response, $status);
    }
    
    /**
     * Activate/De-Activate Affiliation.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function patchDisableEnable(Request $request)
    {
        Utilities::removeAttributesExcept($request, ["id","activate"]);
        
        $this->validate($request, $this->mdlAffiliation->rules($request), $this->mdlAffiliation->messages($request));
        
        Utilities::defaultUpdateAttributes($request, $request->data_user_id);
        
        $activate = $request->activate == 1 ? Constant::RecordType['ENABLED'] : Constant::RecordType['DISABLED'];
        
        $request->request->add([ 'is_enable' => $activate ]);
        
        $result_set = Affiliation::find($request->id);
        $status = 200;
        $response = [];

        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Affiliation not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $data = ['id' => $result_set->id ];
                $actMsg = $request->activate == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "Affiliation successfully $actMsg.", $data);
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Delete Affiliation.
     *
     * @param $id 'ID' of Affiliation to delete. (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id, Request $request)
    {
        $request->request->add([ 'id' => $id ]);
        
        $this->validate($request, $this->mdlAffiliation->rules($request), $this->mdlAffiliation->messages($request));
        
        Utilities::defaultDeleteAttributes($request, 1);
        
        $request->request->add([
            'is_enable' => Constant::RecordType['DELETED']
        ]);
        
        $result_set = Affiliation::find($request->id);
        
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Affiliation not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "Affiliation successfully deleted.");
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Get one Affiliation.
     *
     * @param $id 'ID' of Affiliation to return (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOne($id, Request $request)
    {
        $request->request->add([ 'id' => $id ]);
        
        $this->validate($request, $this->mdlAffiliation->rules($request, Constant::RequestType['GET_ONE']), $this->mdlAffiliation->messages($request, Constant::RequestType['GET_ONE']));
        
        $select = $this->select_columns;

        $this->mdlAffiliation->filterColumns($request);
        
        if($request->fields){
            $select = $request->fields;
        }
        
        $result_set = Affiliation::where('id', $request->id)->first($select);
        
        $status = 200;
        $response = [];

        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Affiliation not found.");
        } else {
            $dataResult = array("country" => $result_set->toArray());
            $response = Utilities::buildSuccessResponse(10005, "Affiliation Data.", $dataResult);
        }
        
        return response()->json($response, $status);
    }

    /**
     * Fetch list of Affiliation by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll(Request $request, $id = null)
    {
        $this->validate($request, $this->mdlAffiliation->rules($request, Constant::RequestType['GET_ALL']), $this->mdlAffiliation->messages($request, Constant::RequestType['GET_ALL']));
        
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
        if($request->is_enable != null) {
            $whereData[] = ['is_enable', $request->is_enable];
        }
        
        $total_record = Affiliation::where($whereData)
            ->active()
            ->count();
        
        
        $orderBy =  $request->order_by ?? Constant::OrderBy;
        $orderType = $request->order_type ?? Constant::OrderType;
        
        $data_set = Affiliation::where($whereData)
            ->active()
            ->orderBy($orderBy, $orderType)
            ->offset($skip)
            ->limit($pageSize)
            ->get($select);
        
        
        $data_result = [];
        $status = 200;
        $data_result['data_list'] = $data_set->toArray();
        $data_result['total_record'] = $total_record;
        
        $response = Utilities::buildSuccessResponse(10004, "Affiliation List.", $data_result);

        return response()->json($response, $status); 
    }
    
    /**
     * Fetch list of Affiliation by searching with optional filters for dropdown/selectbox list
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllData(Request $request, $id = null)
    {
        
        $select = '*';
        
        if($request->fields){
            $select = $request->fields;
        }
        
        $whereData = array();
        
        if($request->is_enable != null) {
            $whereData[] = ['is_enable', $request->is_enable];
        }else{
            $whereData[] = ['is_enable', 1];
        }
        
        if($request->id != null) {
            $whereData[] = ['id', $request->id];
        }
        
        $data_set = Affiliation::where($whereData)
            ->active()
            ->get($select);
        
        $data_result = [];
        
        $status = 200;
        $data_result['affiliation'] = $data_set->toArray();
        
        $response = Utilities::buildSuccessResponse(10004, "Affiliation List.", $data_result);

        return response()->json($response, $status); 
    }
    
    
}


