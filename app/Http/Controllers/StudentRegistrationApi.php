<?php

/**
 * Darulmadinah Api
Author: Muhammad Usama (it.dev7) 
 * 
 * This is a StudentRegistration API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\StudentRegistration;
use App\Models\StdInterviewTest;
use App\Models\CampusSeatingCapacity;
use App\Models\NewAdmissionPolicy;
use App\Models\Campus;
use App\Models\StudentAdmission;
use App\Models\GenMonthlyVoucher;
use Illuminate\Http\Request;
use App\Utilities\Utilities;
use Illuminate\Support\Facades\DB;

class StudentRegistrationAPi extends Controller
{

    /**
     * Constructor
     */
    private $mdlName;
             
    public function __construct()
    {
        $this->mdlName = new StudentRegistration();
    }
    
    private $select_columns  = [
        'id', 
        
        'section_id', 
        'organization_id', 
        'nationality_id', 
        'campus_id', 
        'session_id', 
        'class_id', 
        'admission_type_id', 
        'registration_code', 
        'registration_date', 
        'first_name', 
        'last_name', 
        'full_name', 
        'address', 
        'dob', 
        'religion', 
        'father_name', 
        'father_nic', 
        'email', 
        'phone_no', 
        'father_cell_no', 
        'mother_cell_no', 
        'father_occupation', 
        'prev_school', 
        'reason_for_leaving', 
        'student_age', 
        'gender', 
        'is_required_test', 
        'test_date', 
        'test_time', 
        'student_img', 
        'comments', 
        
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
     * Add StudentRegistration.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {

        // $html  = '';
        // $html .= '<div class="container">';
        // $html .= '<h2>Testing for Workflow</h2>';
        // $html .= '<p>Darulmadinah Schooling System</p><hr>';
        // $html .= '<span>Student Registration : '. $request->first_name.'</span><br>
        //             <span>Link: <a href="https://stage-dmsms-client.dibaadm.com/" target="_blank">https://stage-dmsms-client.dibaadm.com/</a> </span><br><hr>
        //             <span>Note: Kindly no reply here.</span>
        //          </div>';

        // $param['html']     = $html;
        // $param['to']       = 'it.dev50@dawateislami.net';
        // $param['to_name']  = 'Naveed';
        // $sendMail = Utilities::sendMail($param);  


        /*
        $std_hirarcy = Campus::find($request->campus_id);
        if(!$std_hirarcy)
        {
            $response = Utilities::buildBaseResponse(70001, "Campus not Found...!!", 'Info');
            return response()->json($response, 200);
            exit; 
        }
        $ageRangeWhere = array();
        $ageRangeWhere[] = ['dob_from', '<=', $request->dob];
        $ageRangeWhere[] = ['dob_end', '>=', $request->dob];
        // $ageRangeWhere[] = ['class_id', '=', $request->class_id];
        $ageRangeWhere[] = ['is_enable', '=',1];
        $flag_find=false;

        //=============Checking for Campus Level =============================
        $result_set_q = NewAdmissionPolicy::with('Class')->where($ageRangeWhere)->where('campus_id',$std_hirarcy->id);
        if($result_set_q->count() > 0)
        { $flag_find=true; }
        //================END Campus Level =====================


        //============checkig for City Level =======================
        if(!$flag_find){
            $result_set_q = NewAdmissionPolicy::with('Class')->where($ageRangeWhere)
                                ->where('city_id',$std_hirarcy->city_id)
                                ->whereNull('campus_id');
        }
        if($result_set_q->count() > 0)
        { $flag_find=true; }
        //===================END City level =======================


        //============checkig for Region Level =======================
        if(!$flag_find){
            $result_set_q = NewAdmissionPolicy::with('Class')->where($ageRangeWhere)
                                ->where('region_id',$std_hirarcy->region_id)
                                ->whereNull('city_id')
                                ->whereNull('campus_id');
        }
        if($result_set_q->count() > 0)
        { $flag_find=true; }
        //===================END Region level =======================


        //============checkig for state Level =======================
        if(!$flag_find){
            $result_set_q = NewAdmissionPolicy::with('Class')->where($ageRangeWhere)
                                ->where('state_id',$std_hirarcy->state_id)
                                ->whereNull('region_id')
                                ->whereNull('city_id')
                                ->whereNull('campus_id');
        }
        if($result_set_q->count() > 0)
        { $flag_find=true; }
        //===================END state level =======================


        //============checkig for countries Level =======================
        if(!$flag_find){
            $result_set_q = NewAdmissionPolicy::with('Class')->where($ageRangeWhere)
                                ->where('countries_id',$std_hirarcy->countries_id)
                                ->whereNull('state_id')
                                ->whereNull('region_id')
                                ->whereNull('city_id')
                                ->whereNull('campus_id');
        }
        if($result_set_q->count() > 0)
        { $flag_find=true; }
        //===================END countries level =======================
        $from_date;
        $end_date;
        $diff_end;
        $noDays_end;
        $noDays_from;
        $is_workflow=true;

        if(!$flag_find)
        {   
            $is_workflow=false;
            //=== No class Found ==============
            //=== Campus level work flow start here
            $request->request->add(['is_wf_required' => 1]);
            $wf_instert =  $request->all();
            unset($wf_instert);
            $wf_instert['wf_name']='Student Registration';

            $response = Utilities::buildBaseResponse(70001, "Student is not eligible in any class. Directly Workflow Start", 'Info');
            return response()->json($response, 200);
            exit;
        }
        else
        {
           
            $result_set = $result_set_q->get();
            foreach($result_set as $_value)
            {
                if($request->class_id == $_value->class_id)
                {
                    //No Work Flow required...
                    $is_workflow=false;
                }
                else
                {
                    $from_date = $_value->dob_from;
                    $end_date = $_value->dob_end;
                    $dateOfBirth =$request->dob;
                    $diff_end = date_diff(date_create($dateOfBirth), date_create($end_date));
                    $noDays_end = $diff_end->format('%a');

                    $diff_from = date_diff(date_create($dateOfBirth), date_create($from_date));
                    $noDays_from = $diff_from->format('%a');

                    // return response()->json(' No of Days Form : '. $noDays_from . ' END : '. $noDays_end, 200);
                    // exit;

                }
            }

            if($is_workflow)
            {
                $request->request->add(['is_wf_required' => 1]);
                //run workflow
                if($noDays_end<=90 || $noDays_from >= 90)
                {
                    //campus level workflow
                }
                else
                {
                    //Region Level Workflow
                }
            }

        }
        */

       
        //===================Add NEW Registration ==========================
        $user_id =   Utilities::getUserID_by_keycloak_id($request->keycloak_id);
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        
        
        
        $this->mdlName->filterColumns($request);

       
         Utilities::defaultAddAttributes($request, $user_id);
         
         $pre_fix =  Utilities::get_country_org_by_campus_id($request->campus_id);
         $_session = DB::table('session')->where('id','=' , $request->session_id)->get('*')->toArray();
         $curr_session = substr($_session['0']->start_year, 2);
        $reg_code_prefix='';
        
        $reg_code_prefix =  $pre_fix['0']->short_code; 
        $post_arr = $request->all();
        $post_arr['first_name'] = ucwords($post_arr['first_name']);
        $post_arr['last_name'] = ucwords($post_arr['last_name']);
        $post_arr['father_name'] = ucwords($post_arr['father_name']);

        $post_arr['reg_code_prefix']=$reg_code_prefix.$curr_session;
        $post_arr['organization_id']=$pre_fix['0']->organization_id;
       
       
        $dateOfBirth = $post_arr['dob'];
        $today = date("Y-m-d");
        $diff = date_diff(date_create($dateOfBirth), date_create($today));
        $noDays = $diff->format('%a');
        $post_arr['student_age']= round($noDays/365, 2);

            
        $post_arr['full_name'] = $post_arr['first_name'] . " " . $post_arr['last_name'];

        

        try
        {
            DB::beginTransaction();
    
            $obj = StudentRegistration::create($post_arr);

            $result_set = DB::table('student_registration')->where('id','=' , $obj->id)->get('*')->toArray();
            
            $folder = storage_path('app/student_registration_profile/');
            if(!empty($post_arr['student_img'])){
                $img = explode(";base64,", $post_arr['student_img']);
                $img_aux = explode("image/", $img[0]);
                $img_base64 = base64_decode($img[1]);
                // $extension = explode('/', mime_content_type($request['attendance_img']))[1];
                $image_name =  $result_set['0']->registration_code. '.png';

                file_put_contents($folder . $image_name, $img_base64);
                $post_arr['student_img'] = $image_name;
                $data = [ 
                    'id' => $obj->id ,
                    'student_img' => $result_set['0']->registration_code,
                ];
                DB::table('student_registration')->where('id', $obj->id)->update(['student_img' => $image_name]);
            }else{
                $data = [ 'id' => $obj->id];
            }

            DB::commit();
            $response = Utilities::buildSuccessResponse(10000, "Student Registration successfully created.", $data);
        }
        catch(\Exception $e)
        {
            DB::rollback();
            $response = Utilities::buildBaseResponse(10003, "Transaction Failed Studnet Registration. ");
        }

        return response()->json($response, 201);
    }

    /**
     * Update StudentRegistration.
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
//
        $result_set = StudentRegistration::find($request->id);
        
        $status = 200;
        $response = [];
        
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Student Registration not found.");
        } else {
            
            $post_arr = $request->all();
           
            $folder = storage_path('app/student_registration_profile/');
            
            if( !empty($post_arr['student_img']) ){
                $img = explode(";base64,", $post_arr['student_img']);
                $img_aux = explode("image/", $img[0]);
                $img_base64 = base64_decode($img[1]);
                $image_name = $result_set->registration_code . '.png';
                file_put_contents($folder . $image_name, $img_base64);             
                $post_arr['student_img'] = $image_name;
            }else{
                unset($post_arr['student_img']);
            }
            unset($post_arr['session_id']);
            // return response()->json($post_arr, $status);
            // exit;
            $obj = $result_set->update($post_arr);

            if ($obj) {
                $data = [ 'id' => $result_set->id ];
                $response = Utilities::buildSuccessResponse(10001, "Student Update Registration successfully updated.", $data);
            } 
        }
        
        return response()->json($response, $status);
    }
    
    /**
     * Activate/De-Activate StudentRegistration.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function patchDisableEnable(Request $request)
    {
        
        $std_profile = StudentAdmission::where('std_registration_id', $request->id);
        if($std_profile->count()>0)
        {
            $status = 201;
            $response = Utilities::buildBaseResponse(70001, "Student Profile Created.. Your are not allowed to update..!!");
            return response()->json($response, $status);
            exit;
        }

        Utilities::removeAttributesExcept($request, ["id","is_enable"]);
        
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        
        Utilities::defaultUpdateAttributes($request, $request->data_user_id);
        
        $activate = $request->is_enable == 1 ? Constant::RecordType['ENABLED'] : Constant::RecordType['DISABLED'];
        
        $request->request->add([ 'is_enable' => $activate ]);
        
        $result_set = StudentRegistration::find($request->id);
        $status = 200;
        $response = [];

        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "StudentRegistration not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $data = ['id' => $result_set->id ];
                $actMsg = $request->is_enable == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "StudentRegistration successfully $actMsg.", $data);
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Delete StudentRegistration.
     *
     * @param $id 'ID' of StudentRegistration to delete. (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id, Request $request)
    {
        $request->request->add([ 'id' => $id ]);

        $std_profile = StudentAdmission::where('std_registration_id', $request->id);
        if($std_profile->count()>0)
        {
            $status = 201;
            $response = Utilities::buildBaseResponse(70001, "Student Profile Created.. Your are not allowed to update..!!");
            return response()->json($response, $status);
            exit;
        }

        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        
        Utilities::defaultDeleteAttributes($request, 1);
        
        $request->request->add([
            'is_enable' => Constant::RecordType['DELETED']
        ]);
        
        $result_set = StudentRegistration::find($request->id);
        
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "StudentRegistration not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "StudentRegistration successfully deleted.");
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Get one StudentRegistration.
     *
     * @param $id 'ID' of StudentRegistration to return (required)
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
        
        $result_set = StudentRegistration::with('Class', 'Session' , 'Campus')->where('id', $request->id)->first($select);
        
        $status = 200;
        $response = [];

        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "StudentRegistration not found.");
        } else {
            
            $list = $result_set->toArray();
            
            $data_set = $list;
            
            unset($data_set['student_img']);
            
            $dataResult['data_set']             = $data_set;
            $dataResult['student_img']             = $list['student_img'];
            
            $getImg       = url('app/student_registration_profile/');
            $getImgPublic = '';
            if (strpos($getImg, 'public') !== false) {
                $getImgPublic = str_replace('public', 'storage', $getImg);
            }else{
                $getImgPublic = url('storage/app/student_registration_profile/');
            }
            $dataResult['logo_path']    = $list['student_img'] ? $getImgPublic . '/'. $list['student_img'].'?'.rand() : null;
            $dataResult['$getImg']      = $getImgPublic;
                 
        
            $response = Utilities::buildSuccessResponse(10005, "Student Single Registration Data.", $dataResult);
        }
        
        return response()->json($response, $status);
    }

    /**
     * Fetch list of StudentRegistration by searching with optional filters..
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
       
        if($request->registration_date) {   
            $whereData[] = ['registration_date', $request->registration_date];
        }

        if($request->registration_code) {   
            $whereData[] = ['registration_code', $request->registration_code];
        }
        
        if($request->full_name) {   
            $whereData[] = ['full_name', 'LIKE', "%{$request->full_name}%"];
        }

        if($request->father_name) {   
            $whereData[] = ['father_name', 'LIKE', "%{$request->father_name}%"];
        }

        if($request->dob) {   
            $whereData[] = ['dob', $request->dob];
        }

        if($request->father_cell_no) {   
            $whereData[] = ['father_cell_no', $request->father_cell_no];
        }
        
        
        if($request->class_id) {   
            $whereData[] = ['class_id', $request->class_id];
        }
        
        if($request->is_enable) {   
            $whereData[] = ['is_enable', $request->is_enable];
        }
                
        


        $total_record_obj = StudentRegistration::where($whereData)
        ->active();
        
        $total_record =  $total_record_obj->count();
        
        $orderBy =  $this->mdlName->getOrderColumn($request->order_by);
        
        $orderType = $request->order_type ?? Constant::OrderType;
        
        $data_set_obj = StudentRegistration::with('Class')
            ->where($whereData)
            ->active()
            ->orderBy($orderBy, $orderType)
            ->offset($skip)
            ->limit($pageSize);
      
        $data_set =  $data_set_obj->get($select);
        
        $data_result = [];
        $status = 200;
        $data_result['data_list'] = $data_set->toArray();
        $data_result['total_record'] = $total_record;
        
        $response = Utilities::buildSuccessResponse(10004, "Student Registration List.", $data_result);

        return response()->json($response, $status); 
    }
    /**
     * Fetch list of Regisration Recetip Note by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function regNote(Request $request, $id = null)
    {   
        $whereData = array();
        $whereData[] = ['is_enable', 1];
        $whereData[] = ['note_type', $request->note_type];
        if($request->type)
        {
            $whereData[] = ['type', 2];
        }else{
            $whereData[] = ['type', 1];
        } 
          
        if($request->organization_id) {   
            $whereData[] = ['organization_id', $request->organization_id];
        }  
        
        if($request->data_organization_id) {   
            $whereData[] = ['organization_id', $request->data_organization_id];
        }  
        
        $data_set = DB::table('reg_rept_note')->where($whereData)->orderBy('sort_no', 'asc')->get('*');
        $status=200;
        return response()->json($data_set, $status); 
    }
    
    /**
     * Fetch list of StudentRegistration by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStdWithInterviewTest(Request $request, $id = null)
    {
       
        $this->validate($request, $this->mdlName->rules($request, Constant::RequestType['GET_ALL']), $this->mdlName->messages($request, Constant::RequestType['GET_ALL']));
       
        $select =  ['std_registration_interview_test.*', 'student_registration.registration_code', 'student_registration.id as std_registration_id'];
        
        if($request->fields){
            $select = $request->fields;
        }
        $whereData = array();
        
        if($request->organization_id) {   
            $whereData[] = ['student_registration.organization_id', $request->organization_id];
        }
        
        if($request->campus_id) {   
            $whereData[] = ['student_registration.campus_id', $request->campus_id];
        }
        
        if($request->data_session_id) {   
            $whereData[] = ['student_registration.session_id', $request->data_session_id];
        }
       
        if($request->registration_date) {   
            $whereData[] = ['student_registration.registration_date', $request->registration_date];
        }

        if($request->registration_code) {   
            $whereData[] = ['student_registration.registration_code', $request->registration_code];
        }

       
        if($request->full_name) {   
            $whereData[] = ['student_registration.full_name', 'LIKE', "%{$request->full_name}%"];
        }

        if($request->father_name) {   
            $whereData[] = ['student_registration.father_name', 'LIKE', "%{$request->father_name}%"];
        }

        if($request->dob) {   
            $whereData[] = ['student_registration.dob', $request->dob];
        }

        if($request->father_cell_no) {   
            $whereData[] = ['student_registration.father_cell_no', $request->father_cell_no];
        }
   
        if(!empty($request->class_id)) {   
            $whereData[] = ['student_registration.class_id', $request->class_id];
        }
              
        if(!empty($request->entry_status)) {   
            $whereData[] = ['student_registration.entry_status', $request->entry_status];
        }
              
        
        $whereData[] = ['student_registration.is_enable', 1];
        
        
        
        $data_set_obj = StudentRegistration::where($whereData);
        $data_set_obj->leftjoin('std_registration_interview_test', function($join)
        {
          $join->on('std_registration_interview_test.std_registration_id', '=', 'student_registration.id');
        });
 
//        $value >= 1 && $value <= 10)
        
        if(!empty($request->from_date)) { 
            
            $data_set_obj->where( 'student_registration.registration_date', '>=', "$request->from_date");
        }
        
        if(!empty($request->to_date)){
            $data_set_obj->where( 'student_registration.registration_date', '<=', "$request->to_date");
        }
        
        $data_set_obj->where('std_registration_interview_test.is_seat_alloted','=', 0);
        
        $data_set =  $data_set_obj->get($select);
        
//        return response()->json($data_set_obj, 200);
//        exit;
        
        $data_result = [];
        $status = 200;
        $data_result['data_list'] = $data_set->toArray();
        
        
        
        $response = Utilities::buildSuccessResponse(10004, "Student Registration List.", $data_result);

        return response()->json($response, $status); 
    }
    
    public function getFinalResultList(Request $request, $id = null)
    {
       
        $result_set = DB::table('std_final_results')->where('is_enable','=', 1)->get('*')->toArray();
         
        $data_result = [];
        $status = 200;
        $data_result['list'] = $result_set;
//        return response()->json($data_result, 200); 
//        $response = Utilities::buildSuccessResponse(10004, "Final Result List.", $data_result);

        return response()->json($data_result, 200); 
    }
    
    
    public function updateStdTestInterview(Request $request)
    {   
        $postArr = $request->all();
//        
        $user_id = $postArr['data_user_id'];
         
        $postArr = $request->except(array_keys($request->query()));
      
        $rules_ = [];        
        $stdids_arr = [];
        $registration_code_arr = [];
        foreach($postArr as $key => $val)
        {
            $rules_[$key.'.registration_code']  = 'required';
            $rules_[$key.'.std_registration_id']             = 'required';
            $rules_[$key.'.obtained_marks']     = 'required|digits_between:1,3';
            $rules_[$key.'.final_result_id']    = 'required|digits_between:1,3';
            $rules_[$key.'.test_date']          = 'date';
            $rules_[$key.'.test_remarks']       = 'min:3|max:100';
            $rules_[$key.'.is_interview']       = 'digits_between:1,3';
            $rules_[$key.'.interview_remarks']  = 'min:3|max:100';
            $rules_[$key.'.interview_date']     = 'date';
            $rules_[$key.'.is_nazra_test']      = 'digits_between:1,3';
            $rules_[$key.'.nazra_current_lesson']   = 'min:3|max:100';
            $rules_[$key.'.nazra_date']             = 'date';
            $rules_[$key.'.nazra_remarks']          = 'min:3|max:100';
            
            if(!empty($val['std_registration_id'])){
                $stdids_arr[$val['std_registration_id']] = $val['std_registration_id'];
                $registration_code_arr[$val['std_registration_id']] = $val['registration_code'];
            }
            
        }   
        
        
        $this->validate($request, $rules_);
                
        try{
        DB::beginTransaction();
            /*
             * Your DB code
             * */
            foreach($postArr as $key => $val)
            {
                $matchThese['std_registration_id'] = $val['std_registration_id'];
                $matchThese['registration_code']   = $val['registration_code'];
                $matchThese['is_enable']           = 1;
                $matchThese['is_seat_alloted']     = 0;

                $obj = StdInterviewTest::updateOrCreate($matchThese, $val);
            }

            DB::commit();
//
//
            $status = 200;
            $response = Utilities::buildSuccessResponse(10001, "Student Intrerview successfully updated.", null);
//
        }catch(\Exception $e){
            DB::rollback();
           $status = 404;
           $response = Utilities::buildBaseResponse(10003, $e."Transaction Failed Something Wrong in Transaction. ");
        }      
        
        return response()->json($response, $status);
    }



    
    /**
     * Get one StudentRegistration.
     *
     * @param $id 'ID' of StudentRegistration to return (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function check_age_student(Request $request)
    {
        $std_hirarcy = Campus::find($request->data_campus_id);

        if(!$std_hirarcy)
        {
            $response = Utilities::buildBaseResponse(70001, "Campus not Found...!!", 'Info');
            return response()->json($response, 200);
            exit; 
        }
        
       
        $ageRangeWhere = array();
       
        $ageRangeWhere[] = ['dob_from', '<=', $request->dob];
        $ageRangeWhere[] = ['dob_end', '>=', $request->dob];
        $ageRangeWhere[] = ['is_enable', '=',1];

        $flag_find=false;

        //=============Checking for Campus Level =============================
        $result_set_q = NewAdmissionPolicy::with('Class')->where($ageRangeWhere)->where('campus_id',$std_hirarcy->id);
        if($result_set_q->count() > 0)
        { $flag_find=true; }
        //================END Campus Level =====================


        //============checkig for City Level =======================
        if(!$flag_find){
            $result_set_q = NewAdmissionPolicy::with('Class')->where($ageRangeWhere)
                                ->where('city_id',$std_hirarcy->city_id)
                                ->whereNull('campus_id');
        }
        if($result_set_q->count() > 0)
        { $flag_find=true; }
        //===================END City level =======================


        //============checkig for Region Level =======================
        if(!$flag_find){
            $result_set_q = NewAdmissionPolicy::with('Class')->where($ageRangeWhere)
                                ->where('region_id',$std_hirarcy->region_id)
                                ->whereNull('city_id')
                                ->whereNull('campus_id');
        }
        if($result_set_q->count() > 0)
        { $flag_find=true; }
        //===================END Region level =======================


        //============checkig for state Level =======================
        if(!$flag_find){
            $result_set_q = NewAdmissionPolicy::with('Class')->where($ageRangeWhere)
                                ->where('state_id',$std_hirarcy->state_id)
                                ->whereNull('region_id')
                                ->whereNull('city_id')
                                ->whereNull('campus_id');
        }
        if($result_set_q->count() > 0)
        { $flag_find=true; }
        //===================END state level =======================


        //============checkig for countries Level =======================
        if(!$flag_find){
            $result_set_q = NewAdmissionPolicy::with('Class')->where($ageRangeWhere)
                                ->where('countries_id',$std_hirarcy->countries_id)
                                ->whereNull('state_id')
                                ->whereNull('region_id')
                                ->whereNull('city_id')
                                ->whereNull('campus_id');
        }
        if($result_set_q->count() > 0)
        { $flag_find=true; }
        //===================END countries level =======================

        if(!$flag_find)
        {
            $response = Utilities::buildBaseResponse(70001, "Student is not eligible in any class.", 'Info');
            return response()->json($response, 200);
            exit;
        }

        $result_set = $result_set_q->get();
        $allow_class ='Student age Eligible for Class ';
        foreach($result_set as $_value)
        {
            $allow_class .= $_value->class->class_name . ',';
        }

        $data_result = [];
        $status = 200;
        $data_result['data_list'] = $result_set->toArray();
        $data_result['class_list'] = $allow_class;
        $response = Utilities::buildSuccessResponse(10005, "Age Range Policy", $data_result);
        
        return response()->json($response, $status);
    }


    public function getOnevalidation($id, Request $request)
    {
        
        $request->request->add([ 'id' => $id ]);
        
        $this->validate($request, $this->mdlName->rules($request, Constant::RequestType['GET_ONE']), $this->mdlName->messages($request, Constant::RequestType['GET_ONE']));
        
        $std_profile = StudentAdmission::where('std_registration_id', $request->id);

        if($std_profile->count()>0)
        {
            $status = 201;
            $response = Utilities::buildBaseResponse(70001, "Student Profile Created.. Your are not allowed to update..!!");
            return response()->json($response, $status);
            exit;
        }

        $select = $this->select_columns;

        $this->mdlName->filterColumns($request);
        
        if($request->fields){
            $select = $request->fields;
        }
        
        $result_set = StudentRegistration::with('Class', 'Session' , 'Campus')->where('id', $request->id)->first($select);
        
        $status = 200;
        $response = [];

        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "StudentRegistration not found.");
        } else {
            
            $list = $result_set->toArray();
            
            $data_set = $list;
            
            unset($data_set['student_img']);
            
            $dataResult['data_set']             = $data_set;
            $dataResult['student_img']             = $list['student_img'];
            
            $getImg       = url('app/student_registration_profile/');
            $getImgPublic = '';
            if (strpos($getImg, 'public') !== false) {
                $getImgPublic = str_replace('public', 'storage', $getImg);
            }else{
                $getImgPublic = url('storage/app/student_registration_profile/');
            }
            $dataResult['logo_path']    = $list['student_img'] ? $getImgPublic . '/'. $list['student_img'].'?'.rand() : null;
            $dataResult['$getImg']      = $getImgPublic;
                 
        
            $response = Utilities::buildSuccessResponse(10005, "Student Single Registration Data.", $dataResult);
        }
        
        return response()->json($response, $status);
    }


    public function check_arrears(Request $request)
    {

        $msg='';
        $total_ = 0;
        if($request->father_nic){

            $whereDataStd[] = ['student_admission.father_nic', $request->father_nic];
            $whereData[] = ['fee_slip_master.payment_status', 0];
            $total_record_obj_q = GenMonthlyVoucher::with('stdAdmission')->where($whereData);
    
            $total_record_obj_q->whereHas('stdAdmission', function($q) use ($whereDataStd){
                $q->where($whereDataStd);   
            });     
                
            $result_set = $total_record_obj_q->get()->toArray();
         
            foreach($result_set as $arrears)        
            {   
                $msg .= 'Admission No : ' . $arrears['admission_code'];
                $msg .= '<br> Student Name : ' . $arrears['std_admission']['student_name'];
                $msg .= '<br> Payable Amount : ' . $arrears['total_payable_amount'];   
                $total_ += $arrears['total_payable_amount'];
            }
            $data_result = [];
            $status = 200;

            $msg .= '<br> Total Arrears : ' . $total_;
            $data_result['msg'] = $msg;
            $data_result['total'] = $total_;
            $response = Utilities::buildSuccessResponse(70001, "List", $data_result);
            return response()->json($response, 200);
                
        } 
   
    }

    public function getInterviewMeritList(Request $request, $id = null)
    {
        $this->validate($request, $this->mdlName->rules($request, Constant::RequestType['GET_ALL']), $this->mdlName->messages($request, Constant::RequestType['GET_ALL']));
       
        $pageSize = $request->limit ?? Constant::PageSize;
        
        if($pageSize > Constant::MaxPageSize){
            $pageSize = Constant::MaxPageSize;
        }
        
        $page = $request->page ?? Constant::Page;

        
        // return response()->json($request->limit, 200); 
        // exit;
        
        $skip = ($page - 1) * $pageSize;

        $select =  ['std_registration_interview_test.*', 'student_registration.registration_code', 'student_registration.id as std_registration_id', 'student_registration.class_id as class_id', 'student_registration.section_id as std_registration_section_id', 'student_registration.full_name as Student_Name'];
        
        if($request->fields){
            $select = $request->fields;
        }
        $whereData = array();
        
        if($request->organization_id) {   
            $whereData[] = ['student_registration.organization_id', $request->organization_id];
        }
        
        if($request->campus_id) {   
            $whereData[] = ['student_registration.campus_id', $request->campus_id];
        }
        
        if($request->data_session_id) {   
            $whereData[] = ['student_registration.session_id', $request->data_session_id];
        }
       
        if($request->registration_date) {   
            $whereData[] = ['student_registration.registration_date', $request->registration_date];
        }        

        if($request->registration_code) {   
            $whereData[] = ['student_registration.registration_code', $request->registration_code];
        }

       
        if($request->full_name) {   
            $whereData[] = ['student_registration.full_name', 'LIKE', "%{$request->full_name}%"];
        }

        if($request->father_name) {   
            $whereData[] = ['student_registration.father_name', 'LIKE', "%{$request->father_name}%"];
        }

        if($request->dob) {   
            $whereData[] = ['student_registration.dob', $request->dob];
        }

        if($request->father_cell_no) {   
            $whereData[] = ['student_registration.father_cell_no', $request->father_cell_no];
        }
   
        if(!empty($request->class_id)) {   
            $whereData[] = ['student_registration.class_id', $request->class_id];
        }
              
        if(!empty($request->entry_status)) {   
            $whereData[] = ['student_registration.entry_status', $request->entry_status];
        }
              
        
        $whereData[] = ['student_registration.is_enable', 1];
        
        
        
        $data_set_obj = StudentRegistration::with('Class','Section')->where($whereData);
        $data_set_obj->rightjoin('std_registration_interview_test', function($join)
        {
          $join->on('std_registration_interview_test.std_registration_id', '=', 'student_registration.id');
               
          

        });
 
//        $value >= 1 && $value <= 10)
        
        if(!empty($request->from_date)) { 
            
            $data_set_obj->where( 'student_registration.registration_date', '>=', "$request->from_date");
        }
        
        if(!empty($request->to_date)){
            $data_set_obj->where( 'student_registration.registration_date', '<=', "$request->to_date");
        }

        if(!empty($request->final_result_id)) {   
            $data_set_obj->where('std_registration_interview_test.final_result_id','=', $request->final_result_id);
        }else{
            // $data_set_obj->whereIn('std_registration_interview_test.final_result_id', ['1','3']);

            $data_set_obj->where(function($query){
                $query->orWhere('std_registration_interview_test.final_result_id', 1);
                $query->orWhere('std_registration_interview_test.final_result_id', 3);
            });
            
        }
        
        $total_record =  $data_set_obj->count();
        $orderBy =  $this->mdlName->getOrderColumn($request->order_by);
        
        $orderType = $request->order_type ?? Constant::OrderType;
        
        
        $data_set_obj->orderBy($orderBy, $orderType);
        $data_set_obj->offset($skip);
        $data_set_obj->limit($pageSize);
        
        $data_set =  $data_set_obj->get($select);        
     
        
        $data_result = [];
        $status = 200;
        $data_result['data_list'] = $data_set->toArray();        
        $data_result['total_record'] = $total_record;       
        
        
        $response = Utilities::buildSuccessResponse(10004, "Student Registration List.", $data_result);

        return response()->json($response, $status); 

    }


    public function updateStdSeatAllot(Request $request)
    { 

        if($request->is_seat_alloted == 1){

            $interview = StdInterviewTest::find($request->id); 

            if($interview->is_seat_alloted==1){
               
                return $this->addSectionalot($request); 

            }

            else{

                return $this->check($request); 
            }            
        }

        if($request->is_seat_alloted == 0){


            $interview = StdInterviewTest::find($request->id); 

            if($interview->is_seat_alloted==0){                         
                
                return $this->addSectionalot($request); 

            }
            else{

                return $this->check($request); 

            }            
        }

    }


    public function check(Request $request)
    {  
       
            if( $request->class_id && $request->data_session_id && $request->section_id && $request->gender) 
            {   
              
                $whereData[] = ['class_id', $request->class_id];              
                $whereData[] = ['section_id', $request->section_id];      
                $whereData[] = ['gender', $request->gender];   
                         
                $whereData[] = ['campus_id', $request->data_campus_id]; 
                $whereData[] = ['is_enable', 1];            
    
                $SeatList = CampusSeatingCapacity::where($whereData)->first('*');

                if($SeatList==null){

                    $response = Utilities::buildBaseResponse(70001, "No seating capacity found.");
                    return response()->json($response, 200); 
                    exit;

                }
                 
                    if($SeatList->count() > 0){
                         
                    $fixed_capacity = $SeatList->fixed_capacity;
                    $reserved_capacity = $SeatList->reserved_capacity;
                    $old_enrolled = $SeatList->old_enrolled_no;
                    $new_student = $SeatList->new_student_no;  
                    
                    $Total =  $reserved_capacity + $old_enrolled + $new_student ;
       
                    $finalCapacity = $fixed_capacity -  $Total;



                    if($request->is_seat_alloted == 1)
                    {

                    if($finalCapacity>0){

                       $result_s = CampusSeatingCapacity::find($SeatList->id);        

                       $post_a['new_student_no']=  $new_student+1;                        
                       
                       $result_s->update($post_a);

                       return $this->addSeatSectionalot($request); 
                       
   
   
                    }
                    else{

                    $response = Utilities::buildBaseResponse(70001, "Capacity is full.");
                    return response()->json($response, 200); 
                    exit;
   
                    }

                }
                    
                if($request->is_seat_alloted == 0){

                    $result_s = CampusSeatingCapacity::find($SeatList->id);        
        
                    $post_a['new_student_no']=  $new_student-1;                        
                    
                    $result_s->update($post_a);
                    
                    return $this->addSeatSectionalot($request); 
                    
        
                }             


               } 

       }   

  
    }


    public function addSeatSectionalot(Request $request){

        $request2 =$request;
        if($request2->std_registration_id){

            $result_set2 = StudentRegistration::find($request2->std_registration_id);              
         

            if (!$result_set2) {
                $status = 404;
                $response = Utilities::buildBaseResponse(10003, "Record not found.");
                return response()->json($response, 200); 
                exit;
            } else {

                $post_arr2['section_id']= $request2->section_id;

                $obj2 = $result_set2->update($post_arr2);              
                
    
                if ($obj2) {

                    Utilities::defaultUpdateAttributes($request, $request->data_user_id);
                    $result_set = StdInterviewTest::find($request->id); 
                    
                    $status = 200;
                    $response = [];
                    
                    if (!$result_set) {
                        $status = 404;
                        $response = Utilities::buildBaseResponse(10003, "Record not found.");
                    } else {
                        
                        $post_arr = $request->all();
                    
                        $obj = $result_set->update($post_arr);

                        if ($obj) {
                            $data = [ 'id' => $result_set->id ];
                            $response = Utilities::buildSuccessResponse(10001, "Record successfully updated.", $data);
                        } 
                    }
                            
                }
            }
          
        }
        
        return response()->json($response, $status);
    }

    public function addSectionalot(Request $request){

        $request2 =$request;
        if($request2->std_registration_id){

            $result_set2 = StudentRegistration::find($request2->std_registration_id);  
            
            $status = 200;
            $response = [];

            if (!$result_set2) {
                $status = 404;
                $response = Utilities::buildBaseResponse(10003, "Record not found.");
            } else {

                $post_arr2['section_id']= $request2->section_id;

                $obj2 = $result_set2->update($post_arr2);              
                
    
                if ($obj2) {
                    $dataSec = [ 'id' => $result_set2->id ];
                    $response = Utilities::buildSuccessResponse(10001, "Section successfully updated.", $dataSec);
                }
            }
          
            return response()->json($response, $status);
        }
    }


    public function getSeatAlloted($id, Request $request)
    {
        $mdlName2 = new StdInterviewTest();
        $request->request->add([ 'id' => $id ]);
        $this->validate($request, $mdlName2->rules($request, Constant::RequestType['GET_ONE']), $mdlName2->messages($request, Constant::RequestType['GET_ONE']));
        $select = $this->select_columns;
        $mdlName2->filterColumns($request);
        if($request->fields){
            $select = $request->fields;
        }
      
        //$list = StdInterviewTest::with('ForSection')->where('id', $request->id)->first('std_registration_id','id','is_seat_alloted');
        $list = StdInterviewTest::with('ForSection')->where('id', $request->id)->first('*');
        
        $list->Country;
        $list->State;
        $status = 200;
        $response = [];
        if (! $list) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Record not found.");
        } else {
            $dataResult = array("list" => $list->toArray());
            $response = Utilities::buildSuccessResponse(10005, "Data.", $dataResult);
        }
        return response()->json($response, $status);

    }

}
