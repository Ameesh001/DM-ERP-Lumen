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
use App\Models\ExamSetups;

use App\Models\Organization;
use App\Models\Hierarchy;
use App\Models\Campus;
use App\Models\AssignExamHierarchy;
use App\Models\AssignExamCampus;
use App\Models\AssignExamSubject;
use App\Models\CampusSubject;
use Illuminate\Http\Request;
use App\Utilities\Utilities;
use Illuminate\Support\Facades\DB;

class AssignExamSubjectApi extends Controller
{

    /**
     * Constructor
     */
    private $mdlName;
             
    public function __construct()
    {
        $this->mdlName = new ExamSetups();
    }
    
    private $select_columns  = [];
        
        
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
        $assign_exam_subject = $request['arr'];
        if(!$assign_exam_subject)
        {
            $response = Utilities::buildSuccessResponse(80001, "Subject Not Found...!!.", 'error');
            return response()->json($response, 201);
            exit;
        }
        $req_class_id = $request->class_id;
        $AssignExamCampus = AssignExamCampus::find($request->exam_type_id);
        $request->request->add(['examination_id' => $AssignExamCampus->examination_id]);


        $examination = ExamSetup::find($AssignExamCampus->examination_id);
        $request->request->add(['session_id' => $examination->session_id]);
       
        $user_id =   Utilities::getUserID_by_keycloak_id($request->keycloak_id);
        $this->mdlName->filterColumns($request);
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        Utilities::defaultAddAttributes($request, $user_id);
        try
        {
            DB::beginTransaction();
            $obj = ExamSetups::create($request->all());
            $created_dt = date('Y-m-d H:i:s');
            foreach($assign_exam_subject as $m_d)
            {   
                unset($m_d['subject_name']);
                $m_d['exam_setup_id']=$obj->id;
                $m_d['class_id']=$req_class_id;
                $m_d['created_by']=$user_id;
                $m_d['created_at']=$created_dt;
                // return response()->json($m_d, 201);
                // exit;
                $obj_AssignExamSubject = AssignExamSubject::create($m_d);
            }
    
            DB::commit();
            $data = [ 'id' => $obj->id ];
            $response = Utilities::buildSuccessResponse(10000, "Exam Subject successfully created.", $data);
        }
        catch(\Exception $e)
        {
            DB::rollback();
            $response = Utilities::buildBaseResponse(10003, $e."Transaction Failed Exam Subject. ");
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
        $assign_exam_subject = $request['arr'];
        $user_id =   Utilities::getUserID_by_keycloak_id($request->keycloak_id);
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        $status = 200;
        try
        {
            DB::beginTransaction();
            $created_dt = date('Y-m-d H:i:s');
            foreach($assign_exam_subject as $m_d)
            {   
                
                $result_set = AssignExamSubject::find($m_d['id']);
               
                if($result_set)
                    {
                        unset($m_d['subject_name']);
                        unset($m_d['subject_id']);
                        $m_d['updated_by']=$user_id;
                        $m_d['updated_at']=$created_dt;
                       
                        $obj = $result_set->update($m_d);
                        // return response()->json($m_d, $status); 
                        // exit;
                    }
            }
    
            DB::commit();
            $data = [ 'id' =>'id' ];
            $response = Utilities::buildSuccessResponse(10000, "Exam Subject successfully Updated.", $data);
        }
        catch(\Exception $e)
        {
            DB::rollback();
            $response = Utilities::buildBaseResponse(10003, $e."Transaction Failed Exam Subject. ");
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
        $result_set = ExamSetups::find($request->id);
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Exam Setup not found.");
        } else {    
            $obj = $result_set->update($request->all()); 
            if ($obj) {
                AssignExamSubject::where('exam_setup_id', '=', $request->id)->update(['is_enable' => 2]);
                $response = Utilities::buildBaseResponse(10006, "Exam Subject successfully deleted.");
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
        $result_set = ExamSetups::where('id', $request->id)->first();
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Exam Subject not found.");
        } else {

            $AssignExamSubject_q = AssignExamSubject::with('Subject' ,'GradingType')->where('exam_setup_id',$result_set->id);
            $AssignExamSubject_count = $AssignExamSubject_q->count();
            $AssignExamSubject = $AssignExamSubject_q->get();
            $data_set = $result_set->toArray();
            $campus_subject_where = array();
            $campus_subject_where[] = ['campus_id', $result_set->campus_id];
            $campus_subject_where[] = ['class_id', $result_set->class_id];
            $campus_subject_q = CampusSubject::where($campus_subject_where);
            
            $campus_subject_count = $campus_subject_q->count();
            $campus_subject = $campus_subject_q->get();

           
           

            if($campus_subject_count > $AssignExamSubject_count )
            {
                foreach($campus_subject as $_c)
                {
                    $_new_check= array();
                    $_new_check[] = ['exam_setup_id', $result_set->id];
                    $_new_check[] = ['class_id', $_c->class_id];
                    $_new_check[] = ['subject_id', $_c->subject_id];

                  
                    $_new_check_q = AssignExamSubject::where($_new_check);

                    if($_new_check_q->count() == 0){
                        $created_dt = date('Y-m-d H:i:s');
                        $new_entry= array();
                        $new_entry['exam_setup_id'] = $result_set->id;
                        $new_entry['class_id']=$_c->class_id;
                        $new_entry['subject_id']=$_c->subject_id;
                        $new_entry['max_marks']=0;
                        $new_entry['passing_marks']=0;
                        // $new_entry['created_by']=$user_id;
                        $new_entry['created_at']=$created_dt;
                        // return response()->json($new_entry, $status);
                        // exit;
                        $obj_AssignExamSubject = AssignExamSubject::create($new_entry);
                    }
                }
            }
            $AssignExamSubject = AssignExamSubject::with('Subject', 'GradingType')->where('exam_setup_id',$result_set->id)->get();
            
            $dataResult['data_set_child'] = $AssignExamSubject->toArray();
            $dataResult['data_set'] = $data_set;
           
            $response = Utilities::buildSuccessResponse(10005, "Exam Subject Single Data.", $dataResult);
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
        
        if($request->data_campus_id) {   
            $whereData[] = ['campus_id', $request->data_campus_id];
        }
        if($request->session_id) {   
            $whereData[] = ['session_id', $request->session_id];
        }

        if($request->class_id) {   
            $whereData[] = ['class_id', $request->class_id];
        }
        if($request->assign_exam_campus_id) {   
            $whereData[] = ['assign_exam_campus_id', $request->assign_exam_campus_id];
        }

       
        $total_record_obj = ExamSetups::where($whereData)->active();
        
        $total_record =  $total_record_obj->count();
      
        $orderBy =  $this->mdlName->getOrderColumn($request->order_by);
        
        $orderType = $request->order_type ?? Constant::OrderType;
        
        $data_set_obj = ExamSetups::with('ExamSetup', 'Class', 'Session')
            ->where($whereData)
            ->active()
            ->orderBy($orderBy, $orderType)
            ->offset($skip)
            ->limit($pageSize);
      
        $data_set =  $data_set_obj->get();
        
        $data_result = [];
        $status = 200;
        $data_result['data_list'] = $data_set->toArray();
        $data_result['total_record'] = $total_record;
        
        $response = Utilities::buildSuccessResponse(10004, "Exam Setups List.", $data_result);

        return response()->json($response, $status); 
    }

    public function exam_type_list_assign(Request $request, $id = null)
    {

        if($request->campus_id) {   
            $whereData[] = ['campus_id', $request->campus_id];
        }
        $whereData[] = ['is_enable',1];
        $data_set_obj = AssignExamCampus::with('Examination')->where($whereData);

        $whereDataStd[] = ['session_id', $request->data_session_id];
        $data_set_obj->whereHas('Examination', function($q) use ($whereDataStd){
            $q->where($whereDataStd);
        });    

        $data_set =  $data_set_obj->get();
      
        $data_result = [];
        $status = 200;
        $data_result['list'] = $data_set->toArray();
        $response = Utilities::buildSuccessResponse(10004, "Exam Type List.", $data_result);
        return response()->json($response, $status); 
        exit;
    }

    public function get_assign_exam_subject(Request $request, $id = null)
    {
        
        if($request->assign_exam_campus_id == null || empty($request->assign_exam_campus_id))
        {
            $status = 404;
            $response = Utilities::buildBaseResponse(70001, "Please Select Exam Title.");
            return response()->json($response, 200); 
            exit;
            
        }
        if($request->class_id == null || empty($request->class_id))
        {
            $status = 404;
            $response = Utilities::buildBaseResponse(70001, "Please Select Class.");
            return response()->json($response, 200); 
            exit;
        }

        $whereExamSetups = array();
        $whereExamSetups[] = ['assign_exam_campus_id',$request->assign_exam_campus_id ];
        $whereExamSetups[] = ['campus_id',$request->campus_id ];
        $whereExamSetups[] = ['class_id',$request->class_id ];
        $whereExamSetups[] = ['session_id',$request->data_session_id ];

        $data_set_obj_q = ExamSetups::where($whereExamSetups);

        if($data_set_obj_q->count() == 0){
            $status = 404;
            $response = Utilities::buildBaseResponse(70001, "Exam Subject Not Assigned..!!");
            return response()->json($response, 200); 
            exit;
        }

        $data_set_obj = $data_set_obj_q->first();

        // return response()->json($data_set_obj->id, 200); 
        // exit;

        $subject_list = AssignExamSubject::with('Subject')->where('exam_setup_id',$data_set_obj->id)->get();
        $data_result = [];
        $status = 200;
        $data_result['list'] = $subject_list->toArray();
        $response = Utilities::buildSuccessResponse(10004, "Exam Subject List.", $data_result);
        return response()->json($response, $status); 
        exit;
    }
   
}
