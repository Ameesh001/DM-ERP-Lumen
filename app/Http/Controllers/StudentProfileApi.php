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
use App\Models\StudentAdmission;
use App\Models\CampusSection;
use App\Models\StudentStatus;
use Illuminate\Http\Request;
use App\Utilities\Utilities;
use Illuminate\Support\Facades\DB;

class StudentProfileAPi extends Controller
{

    /**
     * Constructor
     */
    private $mdlName;
             
    public function __construct()
    {
        $this->mdlName = new StudentAdmission();
    }
    
    private $select_columns  = [
        'id', 
        'organization_id', 
        'nationality', 
        'campus_id', 
        'session_id', 
        'class_id', 
        // 'admission_type_id', 
        'admission_code', 
        'admission_date', 
        'student_name', 
        // 'last_name', 
        // 'full_name', 
        'home_address', 
        'dob', 
        'religion', 
        'father_name', 
        'father_nic', 
        // 'email', 
        // 'phone_no', 
        'father_cell_no', 
        'mother_cell_no', 
        // 'father_occupation', 
        // 'prev_school', 
        // 'reason_for_leaving', 
        // 'student_age', 
        'gender', 
        // 'is_required_test', 
        // 'test_date', 
        // 'test_time', 
        // 'student_img', 
        // 'comments', 
        
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
                $image_name =  $result_set['0']->registration_code. 'png';

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
       
       
        // $this->mdlName->filterColumns($request);
        // $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));

        $user_id =   Utilities::getUserID_by_keycloak_id($request->keycloak_id);
        Utilities::defaultUpdateAttributes($request, $user_id);
        $Record = StudentAdmission::find($request->id);
       
        
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
                $response = Utilities::buildSuccessResponse(10001, "Student Profile successfully updated.", $data);
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
        
        // $select = $this->select_columns;

        $this->mdlName->filterColumns($request);
        
        if($request->fields){
            $select = $request->fields;
        }
        
        $result_set = StudentAdmission::with('Class', 'Session' , 'Campus', 'Section')->where('id', $request->id)->get();
        
        $status = 200;
        $response = [];

        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "StudentAdmission not found.");
        } else {
            
            $sectionWhere[] = ['campus_id',  $result_set['0']->campus_id];
            $sectionWhere[] = ['class_id',  $result_set['0']->class_id];

            $class_section_q = CampusSection::with('Section')->where($sectionWhere)->get();
            $student_status_q = StudentStatus::get();
            
            $list = $result_set->toArray();
            $data_set = $list;
            
            // unset($data_set['student_img']);
            
            $dataResult['data_set']             = $data_set;

            $sectionWhere=[];

            $dataResult['campus_section'] = $class_section_q->toArray();
            $dataResult['student_status'] = $student_status_q->toArray();

            // $dataResult['student_img']             = $list['student_img'];
            
            // $getImg       = url('app/student_registration_profile/');
            // $getImgPublic = '';
            // if (strpos($getImg, 'public') !== false) {
            //     $getImgPublic = str_replace('public', 'storage', $getImg);
            // }else{
            //     $getImgPublic = url('storage/app/student_registration_profile/');
            // }
            // $dataResult['logo_path']    = $list['student_img'] ? $getImgPublic . '/'. $list['student_img'].'?'.rand() : null;
            // $dataResult['$getImg']      = $getImgPublic;
                 
        
            $response = Utilities::buildSuccessResponse(10005, "Student Single Prfile Data.", $dataResult);
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
        if($request->admission_code) {   
            $whereData[] = ['admission_code', $request->admission_code];
        }
        if($request->admission_date) {   
            $whereData[] = ['admission_date', $request->admission_date];
        }
        if($request->student_name) {   
            $whereData[] = ['student_name', 'LIKE', "%{$request->student_name}%"];
        }
        if($request->father_name) {   
            $whereData[] = ['father_name', 'LIKE', "%{$request->father_name}%"];
        }
        if($request->dob) {   
            $whereData[] = ['dob', $request->dob];
        }

        if($request->class_id) {   
            $whereData[] = ['class_id', $request->class_id];
        }

       

        // if($request->father_cell_no) {   
        //     $whereData[] = ['father_cell_no', $request->father_cell_no];
        // }
        
        
        // if($request->class_id) {   
        //     $whereData[] = ['class_id', $request->class_id];
        // }
                
        


        $total_record_obj = StudentAdmission::where($whereData)
        ->active();
        
        $total_record =  $total_record_obj->count();
        
        $orderBy =  $this->mdlName->getOrderColumn($request->order_by);
        
        $orderType = $request->order_type ?? Constant::OrderType;
        
        $data_set_obj = StudentAdmission::with('Class','Section')
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
        
        $response = Utilities::buildSuccessResponse(10004, "Student Profile List.", $data_result);

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
          $join->where('std_registration_interview_test.is_seat_alloted','=', 0);

        });
 
//        $value >= 1 && $value <= 10)
        
        if(!empty($request->from_date)) { 
            
            $data_set_obj->where( 'student_registration.registration_date', '>=', "$request->from_date");
        }
        
        if(!empty($request->to_date)){
            $data_set_obj->where( 'student_registration.registration_date', '<=', "$request->to_date");
        }
        
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
    
    
   
}
