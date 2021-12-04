<?php

/**
 * Darulmadinah Api
Author: Muhammad Usama (it.dev7) 
 * 
 * This is a ExamSetup API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\ExamSetup;
use App\Models\Organization;
use App\Models\Hierarchy;
use App\Models\Campus;
use App\Models\AssignExamHierarchy;
use App\Models\AssignExamCampus;
use Illuminate\Http\Request;
use App\Utilities\Utilities;
use Illuminate\Support\Facades\DB;

class ExamSetupApi extends Controller
{

    /**
     * Constructor
     */
    private $mdlName;
             
    public function __construct()
    {
        $this->mdlName = new ExamSetup();
    }
    
    private $select_columns  = [
        'id', 
        'organization_id', 
        'exam_type_id', 
        'title',
        'desc',
        'session_id',
        'is_part_final',
       
        
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
     * Add ExamSetup.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        if($request->hierarchy_level == 3)
        {
            if($request->state_id == null || empty($request->state_id))
            {   
                $response = Utilities::buildBaseResponse(10003, "Please Select State", 'error');
                return response()->json($response, 201);
                exit;
            }
        }
        if($request->state_id == ''){
            $request['state_id'] = null;
        }
        $org_list = Organization::find($request->data_org_id);
       
        $request->request->add(['countries_id' => $org_list->countries_id]);
        $isExamSetupWhere = array();    
        $isExamSetupWhere[] = ['hierarchy_level', $request->hierarchy_level];
        $isExamSetupWhere[] = ['organization_id', $request->data_org_id];
        $isExamSetupWhere[] =['exam_type_id',$request->exam_type_id];
        $isExamSetupWhere[] =['session_id',$request->session_id];
        $isExamSetupWhere[] =['countries_id',$org_list->countries_id];
        $isExamSetupWhere[] =['state_id',$request->state_id];
        $isExamSetupWhere[] =['is_enable',1];
        // $isExamSetupWhere[] =['region_id',$request->region_id];
        // $isExamSetupWhere[] =['city_id',$request->city_id];
        // $isExamSetupWhere[] =['campus_id',$request->campus_id];

        $is_exam_setup_q = ExamSetup::where($isExamSetupWhere);

        if($is_exam_setup_q->count()>0){
            $is_exam_setup =  $is_exam_setup_q->first();
            $response = Utilities::buildBaseResponse(10003, "Duplicate Entry on This Level.");
            return response()->json($response, 201);
            exit;
        }

        $isUperExam = array();    
        $isUperExam[] = ['organization_id', $request->data_org_id];
        $isUperExam[] =['exam_type_id',$request->exam_type_id];
        $isUperExam[] =['session_id',$request->session_id];
        $isUperExam[] =['is_enable',1];

        $isUperExam_q = ExamSetup::where($isUperExam);
        $isLowerExam=false;
        if($isUperExam_q->count()>0)
        {
            $isUperExam_list = $isUperExam_q->get();
            foreach($isUperExam_list as $checkUper)
            {
                if($request->hierarchy_level > $checkUper->hierarchy_level)
                {
                    $response = Utilities::buildBaseResponse(10003, "Upper Level Hierarchy Already Generated...!!");
                    return response()->json($response, 201);
                    exit;
                }
            }

            foreach($isUperExam_list as $checkUper)
            {
                if($request->hierarchy_level < $checkUper->hierarchy_level)
                {
                    $isLowerExam=true;
                    $response = Utilities::buildBaseResponse(10003, "Please Select Region Level Hierarchy...!!");
                    return response()->json($response, 201);
                    exit;
                }
            }

        }
        
        $campusWhere = array();
        $campusWhere[] = ['countries_id', $org_list->countries_id];

        if($request->state_id != null || !empty($request->state_id)){
            $campusWhere[] = ['state_id', $request->state_id];

            // if($request->region_id != null || !empty($request->region_id)){
            //     $campusWhere[] = ['region_id', $request->region_id];

            //     if($request->city_id != null || !empty($request->city_id)){
            //         $campusWhere[] = ['city_id', $request->city_id];

            //         if($request->campus_id != null || !empty($request->campus_id)){
            //             $campusWhere[] = ['id', $request->campus_id];
            //         }
            //     }
            // }
        }

        $campus_list = Campus::where($campusWhere)->get();
        
        $user_id =   Utilities::getUserID_by_keycloak_id($request->keycloak_id);
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        $this->mdlName->filterColumns($request);
        Utilities::defaultAddAttributes($request, $user_id);
        // return response()->json($request, 201);
        // exit;
        try
        {
            DB::beginTransaction();
            $obj = ExamSetup::create($request->all());
            $created_dt = date('Y-m-d H:i:s');
           
                foreach($campus_list as $campus)
                {
                    $assing_exam_campus = array();
                    $assing_exam_campus['examination_id']= $obj->id;
                    $assing_exam_campus['campus_id']= $campus->id;
                    $assing_exam_campus['created_by']= $user_id;
                    $assing_exam_campus['created_at']= $created_dt;
                    $AssignExamCampus = AssignExamCampus::create($assing_exam_campus);
                }
            
            DB::commit();
            $data = [ 'id' => $obj->id ];
            $response = Utilities::buildSuccessResponse(10000, "Exam Setup  successfully created.", $data);
        }
        catch(\Exception $e)
        {
            DB::rollback();
            $response = Utilities::buildBaseResponse(10003, $e."Transaction Failed Exam Type. ");
        }
        return response()->json($response, 201);
    }

    /**
     * Update ExamSetup.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $user_id =   Utilities::getUserID_by_keycloak_id($request->keycloak_id);
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        $this->mdlName->filterColumns($request);
        Utilities::defaultUpdateAttributes($request, $user_id);
        $Record = ExamSetup::find($request->id);
        $post_arr = $request->all();
        unset($post_arr['data_campus_id']);
        unset($post_arr['data_organization_id']);
        unset($post_arr['data_session_id']);
        unset($post_arr['data_state_id']);
        unset($post_arr['data_user_id']);
        unset($post_arr['exam_type_id']);
        unset($post_arr['hierarchy_level']);
        unset($post_arr['is_part_final']);
        unset($post_arr['session_id']);
        unset($post_arr['state_id']);
        unset($post_arr['_method']);
        $status = 200;
        // return response()->json($post_arr, $status);
        // exit;
        $response = [];
        if (! $Record) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Record not found.");
        } else {

            $obj = $Record->update($post_arr);
            if ($obj) {
                $data = [ 'id' => $Record->id ];
                $response = Utilities::buildSuccessResponse(10001, "Exam Setup successfully updated.", $data);
            }
        }
        return response()->json($response, $status); 
    }
    
    /**
     * Activate/De-Activate ExamSetup.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function patchDisableEnable(Request $request)
    {
 
        // Utilities::removeAttributesExcept($request, ["id","is_enable"]);
        // $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        // Utilities::defaultUpdateAttributes($request, $request->data_user_id);
        // $activate = $request->is_enable == 1 ? Constant::RecordType['ENABLED'] : Constant::RecordType['DISABLED'];
        // $request->request->add([ 'is_enable' => $activate ]);
        // $result_set = ExamSetup::find($request->id);
        // $status = 200;
        // $response = [];
        // if (!$result_set) {
        //     $status = 404;
        //     $response = Utilities::buildBaseResponse(10003, "ExamSetup not found.");
        // } else {    
        //     $obj = $result_set->update($request->all());
        //     AssignExamCampus::where('examination_id', '=', $obj->id)->update(['is_enable' => 0]);
        //     if ($obj) {
        //         $data = ['id' => $result_set->id ];
        //         $actMsg = $request->is_enable == 1 ? "activated" : "de-activated";
        //         $response = Utilities::buildSuccessResponse(10001, "ExamSetup successfully $actMsg.", $data);
        //     }
        // }
        // return response()->json($response, $status);
    }

    /**
     * Delete ExamSetup.
     *
     * @param $id 'ID' of ExamSetup to delete. (required)
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
        $result_set = ExamSetup::find($request->id);
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Exam Setup not found.");
        } else {    
            $obj = $result_set->update($request->all()); 
            if ($obj) {
                AssignExamCampus::where('examination_id', '=', $request->id)->update(['is_enable' => 2]);
                $response = Utilities::buildBaseResponse(10006, "Exam Setup successfully deleted.");
            }
        }
        return response()->json($response, $status);
    }

    /**
     * Get one ExamSetup.
     *
     * @param $id 'ID' of ExamSetup to return (required)
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
        $result_set = ExamSetup::where('id', $request->id)->first();
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Exam Setup not found.");
        } else {

            // $AssignExamHierarchy = AssignExamHierarchy::where('examination_id',$result_set->id)->first();

            $data_set = $result_set->toArray();
            // $data_set['state_id']= $AssignExamHierarchy->state_id;
            // $data_set['region_id']= $AssignExamHierarchy->region_id;
            // $data_set['city_id']= $AssignExamHierarchy->city_id;
            // $data_set['campus_id']= $AssignExamHierarchy->campus_id;
            $dataResult['data_set'] = $data_set;
            // $dataResult['AssignExamHierarchy'] = $AssignExamHierarchy->toArray();
            $response = Utilities::buildSuccessResponse(10005, "Exam Setup Single Data.", $dataResult);
        }
        return response()->json($response, $status);
    }

    /**
     * Fetch list of ExamSetup by searching with optional filters..
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
        
        if($request->organization_id) {   
            $whereData[] = ['organization_id', $request->organization_id];
        }
        if($request->exam_type_id) {   
            $whereData[] = ['exam_type_id', $request->exam_type_id];
        }

        $total_record_obj = ExamSetup::where($whereData)->active();
        
        $total_record =  $total_record_obj->count();
        
        $orderBy =  $this->mdlName->getOrderColumn($request->order_by);
        
        $orderType = $request->order_type ?? Constant::OrderType;
        
        $data_set_obj = ExamSetup::with('ExamType' ,'Session')->where($whereData)
            ->active()
            ->orderBy($orderBy, $orderType)
            ->offset($skip)
            ->limit($pageSize);
      
        $data_set =  $data_set_obj->get();
        
        $data_result = [];
        $status = 200;
        $data_result['data_list'] = $data_set->toArray();
        $data_result['total_record'] = $total_record;
        
        $response = Utilities::buildSuccessResponse(10004, "Exam Setup List.", $data_result);

        return response()->json($response, $status); 
    }
   
}
