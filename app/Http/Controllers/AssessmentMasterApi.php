<?php

/**
 * Darulmadinah Api
Author: Muhammad Usama (it.dev7) 
 * 
 * This is a AssessmentMaster API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\AssessmentMaster;
use Illuminate\Http\Request;
use App\Utilities\Utilities;
use Illuminate\Support\Facades\DB;

class AssessmentMasterApi extends Controller
{

    /**
     * Constructor
     */
    private $mdlName;
             
    public function __construct()
    {
        $this->mdlName = new AssessmentMaster();
    }
    
    private $select_columns  = [
        'id', 
        'assessment_category_id', 
        'assessment_type_id', 
        'title', 
        'assessment_remarks', 
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
     * Add AssessmentMaster.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
       
        $user_id =   Utilities::getUserID_by_keycloak_id($request->keycloak_id);
        $this->mdlName->filterColumns($request);
       
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
      
        Utilities::defaultAddAttributes($request, $user_id);
        try
        {
            DB::beginTransaction();
            $obj = AssessmentMaster::create($request->all());
            DB::commit();
            $data = [ 'id' => $obj->id ];
            $response = Utilities::buildSuccessResponse(10000, "Assessment Master successfully created.", $data);
        }
        catch(\Exception $e)
        {
            DB::rollback();
           
            $response = Utilities::buildBaseResponse(10003, $e."Transaction Failed Assessment Master. ");
        }

        return response()->json($response, 201);
    }

    /**
     * Update AssessmentMaster.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $user_id =   Utilities::getUserID_by_keycloak_id($request->keycloak_id);
        $this->mdlName->filterColumns($request);
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        Utilities::defaultUpdateAttributes($request, $user_id);
        $Record = AssessmentMaster::find($request->id);
        $post_arr = $request->all();
        $status = 200;
        $response = [];
        if (! $Record) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Record not found.");
        } else {

            $obj = $Record->update($post_arr);
            if ($obj) {
                $data = [ 'id' => $Record->id ];
                $response = Utilities::buildSuccessResponse(10001, "Assessment Master successfully updated.", $data);
            }
        }
        return response()->json($response, $status); 
    }
    
    /**
     * Activate/De-Activate AssessmentMaster.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function patchDisableEnable(Request $request)
    {
        $user_id =   Utilities::getUserID_by_keycloak_id($request->keycloak_id);
        Utilities::removeAttributesExcept($request, ["id","is_enable"]);
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        Utilities::defaultUpdateAttributes($request, $user_id);
        $activate = $request->is_enable == 1 ? Constant::RecordType['ENABLED'] : Constant::RecordType['DISABLED'];
        $request->request->add([ 'is_enable' => $activate ]);
        $result_set = AssessmentMaster::find($request->id);
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "AssessmentMaster not found.");
        } else {    
            $obj = $result_set->update($request->all());
            if ($obj) {
                $data = ['id' => $result_set->id ];
                $actMsg = $request->is_enable == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "AssessmentMaster successfully $actMsg.", $data);
            }
        }
        return response()->json($response, $status);
    }

    /**
     * Delete AssessmentMaster.
     *
     * @param $id 'ID' of AssessmentMaster to delete. (required)
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
        $result_set = AssessmentMaster::find($request->id);
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Assessment Master not found.");
        } else {    
            $obj = $result_set->update($request->all()); 
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "Assessment Master successfully deleted.");
            }
        }
        return response()->json($response, $status);
    }

    /**
     * Get one AssessmentMaster.
     *
     * @param $id 'ID' of AssessmentMaster to return (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOne($id, Request $request)
    {
        $request->request->add([ 'id' => $id ]);
        $this->validate($request, $this->mdlName->rules($request, Constant::RequestType['GET_ONE']), $this->mdlName->messages($request, Constant::RequestType['GET_ONE']));
        $this->mdlName->filterColumns($request);
        if($request->fields){
            $select = $request->fields;
        }
        $result_set = AssessmentMaster::with('AssessmentType' , 'AssessmentCategory')->where('id', $request->id)->first();
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Assessment Master not found.");
        } else {
            
            $list = $result_set->toArray();
            $data_set = $list;
            $dataResult['data_set'] = $data_set;
            $response = Utilities::buildSuccessResponse(10005, "Assessment Master Single Data.", $dataResult);
        }
        return response()->json($response, $status);
    }

    /**
     * Fetch list of AssessmentMaster by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll(Request $request, $id = null)
    {
        // return response()->json($request, 200); 
        // exit;
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
        
        // if($request->organization_id) {   
        //     $whereData[] = ['organization_id', $request->organization_id];
        // }
        if($request->assessment_category_id) {   
            $whereData[] = ['assessment_category_id', $request->assessment_category_id];
        }
        if($request->assessment_type_id) {   
            $whereData[] = ['assessment_type_id', $request->assessment_type_id];
        }

        if($request->title) {   
            $whereData[] = ['title', 'LIKE', "%{$request->title}%"];
        }

        if($request->assessment_remarks) {   
            $whereData[] = ['assessment_remarks', 'LIKE', "%{$request->assessment_remarks}%"];
        }

        if($request->is_enable != null) {
            $whereData[] = ['is_enable', $request->is_enable];
        }

        $whereOrgCheck = array();
        $whereOrgCheck[] = ['organization_id', $request->organization_id];

        $total_record_obj = AssessmentMaster::with('AssessmentType' , 'AssessmentCategory')->where($whereData)->active();
        
        $total_record_obj->whereHas('AssessmentType', function($q) use ($whereOrgCheck){
            $q->where($whereOrgCheck);
           
        });

        $total_record =  $total_record_obj->count();
        
        $orderBy =  $this->mdlName->getOrderColumn($request->order_by);
        
        $orderType = $request->order_type ?? Constant::OrderType;
        
        $data_set_obj = AssessmentMaster::with('AssessmentType' , 'AssessmentCategory')->where($whereData)
            ->active()
            ->orderBy($orderBy, $orderType)
            ->offset($skip)
            ->limit($pageSize);
        
        $data_set_obj->whereHas('AssessmentType', function($q) use ($whereOrgCheck){
            $q->where($whereOrgCheck);
        });
        
        $data_set =  $data_set_obj->get();
        
        $data_result = [];
        $status = 200;
        $data_result['data_list'] = $data_set->toArray();
        $data_result['total_record'] = $total_record;
        
        $response = Utilities::buildSuccessResponse(10004, "Assessment Master List.", $data_result);

        return response()->json($response, $status); 
    }

    /**
     * Fetch list of Grade type by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAssessmentMaster(Request $request, $id = null)
    {
        if($request->id) {   
            $whereData[] = ['assessment_type_id', $request->id];
        }
        $whereData[] = ['is_enable', 1];
        $data_set_obj = AssessmentMaster::with('AssessmentType' , 'AssessmentCategory')->where($whereData);
        $data_set =  $data_set_obj->get();
      
        $data_result = [];
        $status = 200;
        $data_result['list'] = $data_set->toArray();
        $response = Utilities::buildSuccessResponse(10004, "Assessment Master List.", $data_result);
        return response()->json($response, $status); 
    }

    
   
}
