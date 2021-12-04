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
use App\Models\ExamMarksRegister;
use App\Models\StudentAdmission;
use App\Models\GradingExam;
use Illuminate\Http\Request;
use App\Utilities\Utilities;
use Illuminate\Support\Facades\DB;

class ExamMarksRegisterApi extends Controller
{

    /**
     * Constructor
     */
    private $mdlName;
             
    public function __construct()
    {
        $this->mdlName = new ExamMarksRegister();
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
        // $assign_exam_subject = $request['arr'];
        // if(!$assign_exam_subject)
        // {
        //     $response = Utilities::buildSuccessResponse(80001, "Subject Not Found...!!.", 'error');
        //     return response()->json($response, 201);
        //     exit;
        // }
        // $req_class_id = $request->class_id;
        // $AssignExamCampus = AssignExamCampus::find($request->exam_type_id);
        // $request->request->add(['examination_id' => $AssignExamCampus->examination_id]);


        // $examination = ExamSetup::find($AssignExamCampus->examination_id);
        // $request->request->add(['session_id' => $examination->session_id]);
       
        // $user_id =   Utilities::getUserID_by_keycloak_id($request->keycloak_id);
        // $this->mdlName->filterColumns($request);
        // $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        // Utilities::defaultAddAttributes($request, $user_id);
        // try
        // {
        //     DB::beginTransaction();
        //     $obj = ExamSetups::create($request->all());
        //     $created_dt = date('Y-m-d H:i:s');
        //     foreach($assign_exam_subject as $m_d)
        //     {   
        //         unset($m_d['subject_name']);
        //         $m_d['exam_setup_id']=$obj->id;
        //         $m_d['class_id']=$req_class_id;
        //         $m_d['created_by']=$user_id;
        //         $m_d['created_at']=$created_dt;
        //         // return response()->json($m_d, 201);
        //         // exit;
        //         $obj_AssignExamSubject = AssignExamSubject::create($m_d);
        //     }
    
        //     DB::commit();
        //     $data = [ 'id' => $obj->id ];
        //     $response = Utilities::buildSuccessResponse(10000, "Exam Subject successfully created.", $data);
        // }
        // catch(\Exception $e)
        // {
        //     DB::rollback();
        //     $response = Utilities::buildBaseResponse(10003, $e."Transaction Failed Exam Subject. ");
        // }
        // return response()->json($response, 201);
    }

    /**
     * Update ExamSetup.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        // return response()->json($request, 200); 
        // exit;
        $exam_marks_register = $request['arr'];
        $user_id =   Utilities::getUserID_by_keycloak_id($request->keycloak_id);
        $status = 200;
        try
        {
            DB::beginTransaction();
            $created_dt = date('Y-m-d H:i:s');
            $line_no=0;
            foreach($exam_marks_register as $m_d)
            {  $line_no++;
                if($m_d['obtain_marks']> $m_d['max_marks'])
                {
                    $response = Utilities::buildBaseResponse(30001, "Line Number : [ ". $line_no. " ] Obtain Mark must be less than Max Marks..!!");
                    return response()->json($response, 200);
                    exit;
                }    
                $result_set = ExamMarksRegister::find($m_d['id']);
               
                if($result_set)
                    {
                        $m_d['percentage'] = round(($m_d['obtain_marks']/$m_d['max_marks']) * ( 100),2);
                       
                        if($m_d['exam_attendance']==1)
                        {
                            $GradingExamWhere= array();
                            $GradingExamWhere[] = ['organization_id', $request->data_organization_id];
                            $GradingExamWhere[] = ['percentage_from', '<=' ,  $m_d['percentage']];
                            $GradingExamWhere[] = ['percentage_end', '>=', $m_d['percentage']];
                            $GradingExamWhere[] = ['grading_type_id', $m_d['grading_type_id']];
                            $GradingExamWhere[] = ['is_enable',1];
                            $GradingExam_q = GradingExam::where($GradingExamWhere);
                            if($GradingExam_q->count() == 0)
                            {
                                $response = Utilities::buildBaseResponse(30001, "Admission Code : ". $m_d['admission_code']." Exam Grading not found..!!");
                                return response()->json($response, 200);
                                exit;
                            }

                            $GradingExam = $GradingExam_q->first();
                            $m_d['grading_exam_id']=$GradingExam->id;
                        }
                        if($m_d['exam_attendance']==0)
                        {
                            $m_d['obtain_marks']=0;
                        }

                        
                        unset($m_d['father_name']);
                        unset($m_d['student_name']);
                        unset($m_d['admission_code']);
                        unset($m_d['max_marks']);

                        $m_d['updated_by']=$user_id;
                        $m_d['updated_at']=$created_dt;
                        
                       

                        $obj = $result_set->update($m_d);
                        // return response()->json($m_d, $status); 
                        // exit;
                    }
            }
    
            DB::commit();
            $data = [ 'id' =>'id' ];
            $response = Utilities::buildSuccessResponse(10000, "Exam Marks Register successfully Updated.", $data);
        }
        catch(\Exception $e)
        {
            DB::rollback();
            $response = Utilities::buildBaseResponse(30001, $e."Transaction Failed Exam Marks Register. ");
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
        // $request->request->add([ 'id' => $id ]);
        // $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        // Utilities::defaultDeleteAttributes($request, 1);
        // $request->request->add([
        //     'is_enable' => Constant::RecordType['DELETED']
        // ]);
        // $result_set = ExamSetups::find($request->id);
        // $status = 200;
        // $response = [];
        // if (!$result_set) {
        //     $status = 404;
        //     $response = Utilities::buildBaseResponse(10003, "Exam Setup not found.");
        // } else {    
        //     $obj = $result_set->update($request->all()); 
        //     if ($obj) {
        //         AssignExamSubject::where('exam_setup_id', '=', $request->id)->update(['is_enable' => 2]);
        //         $response = Utilities::buildBaseResponse(10006, "Exam Subject successfully deleted.");
        //     }
        // }
        // return response()->json($response, $status);
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
        // $request->request->add([ 'id' => $id ]);
        // $this->validate($request, $this->mdlName->rules($request, Constant::RequestType['GET_ONE']), $this->mdlName->messages($request, Constant::RequestType['GET_ONE']));
        // $this->mdlName->filterColumns($request);
        // if($request->fields){
        //     $select = $request->fields;
        // }
        // $result_set = ExamSetups::where('id', $request->id)->first();
        // $status = 200;
        // $response = [];
        // if (!$result_set) {
        //     $status = 404;
        //     $response = Utilities::buildBaseResponse(10003, "Exam Subject not found.");
        // } else {

        //     $AssignExamSubject_q = AssignExamSubject::with('Subject')->where('exam_setup_id',$result_set->id);
        //     $AssignExamSubject_count = $AssignExamSubject_q->count();
        //     $AssignExamSubject = $AssignExamSubject_q->get();
        //     $data_set = $result_set->toArray();
        //     $campus_subject_where = array();
        //     $campus_subject_where[] = ['campus_id', $result_set->campus_id];
        //     $campus_subject_where[] = ['class_id', $result_set->class_id];
        //     $campus_subject_q = CampusSubject::where($campus_subject_where);
            
        //     $campus_subject_count = $campus_subject_q->count();
        //     $campus_subject = $campus_subject_q->get();

           
           

        //     if($campus_subject_count > $AssignExamSubject_count )
        //     {
        //         foreach($campus_subject as $_c)
        //         {
        //             $_new_check= array();
        //             $_new_check[] = ['exam_setup_id', $result_set->id];
        //             $_new_check[] = ['class_id', $_c->class_id];
        //             $_new_check[] = ['subject_id', $_c->subject_id];

                  
        //             $_new_check_q = AssignExamSubject::where($_new_check);

        //             if($_new_check_q->count() == 0){
        //                 $created_dt = date('Y-m-d H:i:s');
        //                 $new_entry= array();
        //                 $new_entry['exam_setup_id'] = $result_set->id;
        //                 $new_entry['class_id']=$_c->class_id;
        //                 $new_entry['subject_id']=$_c->subject_id;
        //                 $new_entry['max_marks']=0;
        //                 $new_entry['passing_marks']=0;
        //                 // $new_entry['created_by']=$user_id;
        //                 $new_entry['created_at']=$created_dt;
        //                 // return response()->json($new_entry, $status);
        //                 // exit;
        //                 $obj_AssignExamSubject = AssignExamSubject::create($new_entry);
        //             }
        //         }
        //     }
        //     $AssignExamSubject = AssignExamSubject::with('Subject')->where('exam_setup_id',$result_set->id)->get();
            
        //     $dataResult['data_set_child'] = $AssignExamSubject->toArray();
        //     $dataResult['data_set'] = $data_set;
           
        //     $response = Utilities::buildSuccessResponse(10005, "Exam Subject Single Data.", $dataResult);
        // }
        // return response()->json($response, $status);
    }

    /**
     * Fetch list of ExamSetup by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll(Request $request, $id = null)
    {
       
        // $this->validate($request, $this->mdlName->rules($request, Constant::RequestType['GET_ALL']), $this->mdlName->messages($request, Constant::RequestType['GET_ALL']));
       
        // $pageSize = $request->limit ?? Constant::PageSize;
        
        // if($pageSize > Constant::MaxPageSize){
        //     $pageSize = Constant::MaxPageSize;
        // }
        
        // $page = $request->page ?? Constant::Page;
        
        // $skip = ($page - 1) * $pageSize;
        
        // $select =  $this->select_columns;
        
        // if($request->fields){
        //     $select = $request->fields;
        // }
        // $whereData = array();
        
        // if($request->data_campus_id) {   
        //     $whereData[] = ['campus_id', $request->data_campus_id];
        // }
        // if($request->session_id) {   
        //     $whereData[] = ['session_id', $request->session_id];
        // }

        // if($request->class_id) {   
        //     $whereData[] = ['class_id', $request->class_id];
        // }
        // if($request->assign_exam_campus_id) {   
        //     $whereData[] = ['assign_exam_campus_id', $request->assign_exam_campus_id];
        // }

       
        // $total_record_obj = ExamSetups::where($whereData)->active();
        
        // $total_record =  $total_record_obj->count();
      
        // $orderBy =  $this->mdlName->getOrderColumn($request->order_by);
        
        // $orderType = $request->order_type ?? Constant::OrderType;
        
        // $data_set_obj = ExamSetups::with('ExamSetup', 'Class', 'Session')
        //     ->where($whereData)
        //     ->active()
        //     ->orderBy($orderBy, $orderType)
        //     ->offset($skip)
        //     ->limit($pageSize);
      
        // $data_set =  $data_set_obj->get();
        
        // $data_result = [];
        // $status = 200;
        // $data_result['data_list'] = $data_set->toArray();
        // $data_result['total_record'] = $total_record;
        
        // $response = Utilities::buildSuccessResponse(10004, "Exam Setups List.", $data_result);

        // return response()->json($response, $status); 
    }


    public function get_register_marks_std(Request $request)
    {
        $user_id =   Utilities::getUserID_by_keycloak_id($request->keycloak_id);
        Utilities::defaultAddAttributes($request, $user_id);

        $AssignExamCampus = AssignExamCampus::find($request->assign_exam_campus_id);

        if(!$AssignExamCampus)
        {
            $response = Utilities::buildSuccessResponse(70001, "AssignExamCampus Not Found...!!.", 'error');
            return response()->json($response, 201);
            exit;
        }
      
        $request->request->add(['examination_id' => $AssignExamCampus->examination_id]);
    
        $exam_setup_where = array();
        $exam_setup_where[] = ['examination_id', $AssignExamCampus->examination_id];
        $exam_setup_where[] = ['campus_id', $request->data_campus_id];
        $exam_setup_where[] = ['class_id', $request->class_id];
        $exam_setup_where[] = ['session_id', $request->data_session_id];

        $exam_setup_q = ExamSetups::where($exam_setup_where);
        if($exam_setup_q->count()==0)
        {
            $response = Utilities::buildSuccessResponse(70001, "ExamSetups Not Found...!!.", 'error');
            return response()->json($response, 201);
            exit;
        }
        $exam_setup =  $exam_setup_q->first();

        $request->request->add(['exam_setup_id' => $exam_setup->id]);

        //================= Get Student List =============
        $student_where = array();
        $student_where[] = ['campus_id', $request->data_campus_id];
        $student_where[] = ['session_id', $request->data_session_id];
        $student_where[] = ['class_id', $request->class_id];
        $student_where[] = ['section_id', $request->section_id];
        $student_where[] = ['is_enable', 1];

        $std_list_q = StudentAdmission::where($student_where);
        if($std_list_q->count()==0)
        {
            $response = Utilities::buildSuccessResponse(70001, "Student Not Found...!!.", 'error');
            return response()->json($response, 201);
            exit;
        }
        $std_list = $std_list_q->get();
        //================ END Student list ====================================

        $AssignExamSubject_q = AssignExamSubject::where('id',$request->assign_exam_subject_id);

        if($AssignExamSubject_q->count() == 0 )
        {
            $response = Utilities::buildSuccessResponse(70001, "Subject ID Not Found..!!", 'error');
            return response()->json($response, 201);
            exit;
        }
        $AssignExamSubject = $AssignExamSubject_q->first();

        // return response()->json($std_list, 201);
        // exit;
        //====Check exam_marks_register_where exists Then Create New Entry =========================
        foreach($std_list as $students)
        {
            $exam_marks_register_where = array();
            $exam_marks_register_where[] = ['examination_id', $request->examination_id];
            $exam_marks_register_where[] = ['exam_setup_id', $request->exam_setup_id];
            $exam_marks_register_where[] = ['organization_id', $request->data_organization_id];
            $exam_marks_register_where[] = ['std_admission_id', $students->id];
            $exam_marks_register_where[] = ['admission_code', $students->admission_code];
            $exam_marks_register_where[] = ['session_id', $request->data_session_id];
            $exam_marks_register_where[] = ['class_id', $request->class_id];
            $exam_marks_register_where[] = ['section_id', $request->section_id];
            $exam_marks_register_where[] = ['assign_exam_subject_id', $request->assign_exam_subject_id];
            $ExamMarksRegister_q = ExamMarksRegister::with('Student', 'AssignExamSubject')->where($exam_marks_register_where);

            if($ExamMarksRegister_q->count() == 0 )
            {
                $new_entry_ = $request->all();
                $new_entry_['campus_id'] = $new_entry_['data_campus_id'];
                $new_entry_['organization_id'] = $new_entry_['data_organization_id'];
                $new_entry_['session_id'] = $new_entry_['data_session_id'];
                $new_entry_['std_admission_id'] = $students->id;
                $new_entry_['admission_code'] = $students->admission_code;
                $new_entry_['gr_no'] = $students->gr_no;
                $new_entry_['grading_exam_id'] = null;
                $new_entry_['obtain_marks'] = 0;
                $new_entry_['percentage'] = 0;
                $new_entry_['exam_attendance'] = 1;
                $new_entry_['subject_id'] =  $AssignExamSubject->subject_id;
                unset($new_entry_['data_campus_id']);
                unset($new_entry_['data_organization_id']);
                unset($new_entry_['data_session_id']);
                unset($new_entry_['data_user_id']);
                unset($new_entry_['keycloak_id']);
               $crated_new =  ExamMarksRegister::create($new_entry_);
            }
        }
        //==== ------END---Check exam_marks_register_where exists Then Create New Entry =========================

        $exam_marks_register_where = array();
        $exam_marks_register_where[] = ['examination_id', $request->examination_id];
        $exam_marks_register_where[] = ['exam_setup_id', $request->exam_setup_id];
        $exam_marks_register_where[] = ['organization_id', $request->data_organization_id];
        $exam_marks_register_where[] = ['session_id', $request->data_session_id];
        $exam_marks_register_where[] = ['class_id', $request->class_id];
        $exam_marks_register_where[] = ['section_id', $request->section_id];
        $exam_marks_register_where[] = ['assign_exam_subject_id', $request->assign_exam_subject_id];
        $ExamMarksRegister_q = ExamMarksRegister::with('Student', 'AssignExamSubject')->where($exam_marks_register_where);
        


        //===========Check exam_marks_register_where exists =================
        // $exam_marks_register_where = array();
        // $exam_marks_register_where[] = ['examination_id', $request->examination_id];
        // $exam_marks_register_where[] = ['exam_setup_id', $request->exam_setup_id];
        // $exam_marks_register_where[] = ['organization_id', $request->data_organization_id];
        // $exam_marks_register_where[] = ['session_id', $request->data_session_id];
        // $exam_marks_register_where[] = ['class_id', $request->class_id];
        // $exam_marks_register_where[] = ['section_id', $request->section_id];
        // $exam_marks_register_where[] = ['assign_exam_subject_id', $request->assign_exam_subject_id];
        // $ExamMarksRegister_q = ExamMarksRegister::with('Student', 'AssignExamSubject')->where($exam_marks_register_where);
        //=========== ---END --Check exam_marks_register_where exists =================

       
       
        //==============Insert Exam Marks Register ==============
        // if($ExamMarksRegister_q->count() == 0 )
        // {
        //     foreach($std_list as $students)
        //     {
        //         $new_entry_ = $request->all();
        //         $new_entry_['campus_id'] = $new_entry_['data_campus_id'];
        //         $new_entry_['organization_id'] = $new_entry_['data_organization_id'];
        //         $new_entry_['session_id'] = $new_entry_['data_session_id'];
        //         $new_entry_['std_admission_id'] = $students->id;
        //         $new_entry_['admission_code'] = $students->admission_code;
        //         $new_entry_['gr_no'] = $students->gr_no;
        //         $new_entry_['grading_exam_id'] = null;
        //         $new_entry_['obtain_marks'] = 0;
        //         $new_entry_['percentage'] = 0;
        //         $new_entry_['exam_attendance'] = 1;
        //         $new_entry_['subject_id'] =  $AssignExamSubject->subject_id;
        //         unset($new_entry_['data_campus_id']);
        //         unset($new_entry_['data_organization_id']);
        //         unset($new_entry_['data_session_id']);
        //         unset($new_entry_['data_user_id']);
        //         unset($new_entry_['keycloak_id']);
        //         return response()->json($new_entry_, 200); 
        //         exit;
        //         $crated_new =  ExamMarksRegister::create($new_entry_);
        //     }
        //     $ExamMarksRegister_q = ExamMarksRegister::with('Student', 'AssignExamSubject')->where($exam_marks_register_where);
        // }
        //==============----END--- Insert Exam Marks Register ==============

        if($ExamMarksRegister_q->count()== 0)
        {
            $response = Utilities::buildSuccessResponse(70001, "Please Search Again..!!", 'error');
            return response()->json($response, 201);
            exit;
        }
        $ExamMarksRegister = $ExamMarksRegister_q->get();
        $data_result = [];
        $status = 200;
        $data_result['list'] = $ExamMarksRegister->toArray();
        $response = Utilities::buildSuccessResponse(10004, "Exam Marks Register List List.", $data_result);
        return response()->json($response, $status); 
    }



    public function get_student_register_marks(Request $request)
    {   

         //================= Get Student List =============
         $student_where = array();
         $student_where[] = ['campus_id', $request->data_campus_id];
         $student_where[] = ['session_id', $request->data_session_id];
         $student_where[] = ['class_id', $request->class_id];
         $student_where[] = ['section_id', $request->section_id];
         $student_where[] = ['admission_code',$request->admission_code];
         $student_where[] = ['is_enable', 1];
        
        
         $std_list_q = StudentAdmission::where($student_where);
         if($std_list_q->count() == 0)
         {
             $response = Utilities::buildSuccessResponse(70001, "Student Not Found...!!.", 'error');
             return response()->json($response, 201);
             exit;
         }
         $std_list = $std_list_q->first();
        
         //================ END Student list ====================================

        

        $user_id =   Utilities::getUserID_by_keycloak_id($request->keycloak_id);
        Utilities::defaultAddAttributes($request, $user_id);

        $AssignExamCampus = AssignExamCampus::find($request->assign_exam_campus_id);
        // $AssignExamCampus = AssignExamCampus::find('111');

        if(!$AssignExamCampus)
        {
            $response = Utilities::buildSuccessResponse(70001, "AssignExamCampus Not Found...!!.", 'error');
            return response()->json($response, 201);
            exit;
        }

        $request->request->add(['examination_id' => $AssignExamCampus->examination_id]);
        $exam_setup_where = array();
        $exam_setup_where[] = ['examination_id', $AssignExamCampus->examination_id];
        $exam_setup_where[] = ['campus_id', $request->data_campus_id];
        $exam_setup_where[] = ['class_id', $request->class_id];
        $exam_setup_where[] = ['session_id', $request->data_session_id];

        $exam_setup_q = ExamSetups::where($exam_setup_where);

        if($exam_setup_q->count()==0)
        {
            $response = Utilities::buildSuccessResponse(70001, "ExamSetups Not Found...!!.", 'error');
            return response()->json($response, 201);
            exit;
        }
        $exam_setup =  $exam_setup_q->first();
        
        $request->request->add(['exam_setup_id' => $exam_setup->id]);

       
        $AssignExamSubjectWhere = array();
        $AssignExamSubjectWhere[] = ['exam_setup_id', $request->exam_setup_id]; 
        $AssignExamSubjectWhere[] = ['class_id', $request->class_id]; 

        $AssignExamSubject_q = AssignExamSubject::where($AssignExamSubjectWhere);
        
        if($AssignExamSubject_q->count()==0)
        {
            $response = Utilities::buildSuccessResponse(70001, "AssignExamSubject Not Found...!!.", 'error');
            return response()->json($response, 201);
            exit;
        }
        $AssignExamSubject = $AssignExamSubject_q->get();

        // return response()->json($AssignExamSubject, 201);
        // exit;

        foreach($AssignExamSubject as $exam_subject)
        {   
            $where_exam_std = array();
            $where_exam_std[] = ['assign_exam_subject_id', $exam_subject->id];
            $where_exam_std[] = ['std_admission_id', $std_list->id];
            $where_exam_std[] = ['class_id', $request->class_id];
            $where_exam_std[] =['section_id', $request->section_id];
            $ExamMarksRegister_q = ExamMarksRegister::where($where_exam_std);

            if($ExamMarksRegister_q->count() == 0)
            {
                $new_entry_ = $request->all();
                $new_entry_['campus_id'] = $new_entry_['data_campus_id'];
                $new_entry_['organization_id'] = $new_entry_['data_organization_id'];
                $new_entry_['session_id'] = $new_entry_['data_session_id'];
                $new_entry_['std_admission_id'] = $std_list->id;
                $new_entry_['admission_code'] = $std_list->admission_code;
                $new_entry_['gr_no'] = $std_list->gr_no;
                $new_entry_['grading_exam_id'] = null;
                $new_entry_['obtain_marks'] = 0;
                $new_entry_['percentage'] = 0;
                $new_entry_['assign_exam_subject_id'] = $exam_subject->id;
                $new_entry_['exam_attendance'] = 1;
                $new_entry_['subject_id'] = $exam_subject->subject_id;
                unset($new_entry_['data_campus_id']);
                unset($new_entry_['data_organization_id']);
                unset($new_entry_['data_session_id']);
                unset($new_entry_['data_user_id']);
                unset($new_entry_['keycloak_id']);
                // return response()->json($new_entry_, 200); 
                // exit;
                $crated_new =  ExamMarksRegister::create($new_entry_);
            }
        }

        // return response()->json($request, 201);
        // exit;
        //===========Check exam_marks_register_where exists =================
        $exam_marks_register_where = array();
        $exam_marks_register_where[] = ['examination_id', $request->examination_id];
        $exam_marks_register_where[] = ['exam_setup_id', $request->exam_setup_id];
        $exam_marks_register_where[] = ['organization_id', $request->data_organization_id];
        $exam_marks_register_where[] = ['session_id', $request->data_session_id];
        $exam_marks_register_where[] = ['class_id', $request->class_id];
        $exam_marks_register_where[] = ['section_id', $request->section_id];
        $exam_marks_register_where[] = ['admission_code', $request->admission_code];
        $ExamMarksRegister_q = ExamMarksRegister::with('Student', 'AssignExamSubject', 'Subject' ,'Class', 'Section')
                                                ->where($exam_marks_register_where);
        //=========== ---END --Check exam_marks_register_where exists =================

        if($ExamMarksRegister_q->count()== 0)
        {
            $response = Utilities::buildSuccessResponse(70001, "Please Search Again..!!", 'error');
            return response()->json($response, 201);
            exit;
        }

        $ExamMarksRegister = $ExamMarksRegister_q->get();
        $data_result = [];
        $status = 200;
        $data_result['list'] = $ExamMarksRegister->toArray();
        $response = Utilities::buildSuccessResponse(10004, "Student Exam Marks Register List.", $data_result);
        return response()->json($response, $status); 
    }
   
}
