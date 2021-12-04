<?php

/**
 * Darulmadinah Api
Author: Muhammad Usama (it.dev7) 
 * 
 * This is a StudentAdmission API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\StudentAdmission;
use App\Models\StdInterviewTest;
use Illuminate\Http\Request;
use App\Utilities\Utilities;
use Illuminate\Support\Facades\DB;

class StudentAdmissionAPi extends Controller
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
        'std_registration_id',
        'registration_code',
        'admission_code',
        'gr_no',
        'session_id', 
        'admission_date', 
        'admission_month', 
        'joinning_date', 
        'batch', 
        'organization_id', 
        'campus_id', 
        'class_id', 
        'section_id', 
        'student_name',
        'father_name',
        'gender',
        'dob',
        'father_nic',
        'mother_nic',
        'home_cell_no',
        'father_cell_no', 
        'mother_cell_no',
        'home_address',
        'place_of_birth',
        'blood_group',
        'religion', 
        'nationality',
        'caste',
        'community',
        'is_physically_fit',
        'school_last_attended',
        'grade',
        'native_language',
        'other_language',
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
     * Add StudentAdmission.
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
    
            $obj = StudentAdmission::create($post_arr);

            $result_set = DB::table('student_registration')->where('id','=' , $obj->id)->get('*')->toArray();
            
            $folder = storage_path('app/student_registration_profile/');
            if($post_arr['student_img']){
                $img = explode(";base64,", $post_arr['student_img']);
                $img_aux = explode("image/", $img[0]);
                $img_base64 = base64_decode($img[1]);

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
     * Update StudentAdmission.
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
        $result_set = StudentAdmission::find($request->id);
        
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
     * Activate/De-Activate StudentAdmission.
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
        
        $result_set = StudentAdmission::find($request->id);
        $status = 200;
        $response = [];

        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "StudentAdmission not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $data = ['id' => $result_set->id ];
                $actMsg = $request->is_enable == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "StudentAdmission successfully $actMsg.", $data);
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Delete StudentAdmission.
     *
     * @param $id 'ID' of StudentAdmission to delete. (required)
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
        
        $result_set = StudentAdmission::find($request->id);
        
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "StudentAdmission not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "StudentAdmission successfully deleted.");
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Get one StudentAdmission.
     *
     * @param $id 'ID' of StudentAdmission to return (required)
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
        
        $result_set = StudentAdmission::with('Class', 'Session' , 'Campus')->where('id', $request->id)->first($select);
        
        $status = 200;
        $response = [];

        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "StudentAdmission not found.");
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
     * Fetch list of StudentAdmission by searching with optional filters..
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
        
        if($request->campus_id) {   
            $whereData[] = ['campus_id', $request->campus_id];
        }
        
        if($request->session_id) {   
            $whereData[] = ['session_id', $request->session_id];
        }
        
        if($request->section_id) {   
            $whereData[] = ['section_id', $request->section_id];
        }
        
        if($request->organization_id) {   
            $whereData[] = ['organization_id', $request->organization_id];
        }
        if($request->data_organization_id) {   
            $whereData[] = ['organization_id', $request->data_organization_id];
        }
       
        if($request->registration_code) {   
            $whereData[] = ['registration_code', $request->registration_code];
        }
        
        if($request->admission_code) {   
            $whereData[] = ['admission_code', $request->admission_code];
        }
        
        if($request->first_admission_code) {   
            $whereData[] = ['admission_code', $request->first_admission_code];
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

        if($request->father_cell_no) {   
            $whereData[] = ['father_cell_no', $request->father_cell_no];
        }
        
        
        if($request->first_class_id) {   
            $whereData[] = ['class_id', $request->first_class_id];
        }
        
        if($request->class_id) {   
            $whereData[] = ['class_id', $request->class_id];
        }
        
        
        
        $whereData[] = ['is_enable', 1];
       
        $total_record_obj = StudentAdmission::where($whereData);
        
        $total_record =  $total_record_obj->count();
        
        $orderBy =  $this->mdlName->getOrderColumn($request->order_by);
        
        $orderType = $request->order_type ?? Constant::OrderType;
        
        
         
        $data_set_obj = StudentAdmission::with('Class', 'Session', 'Section')
            ->where($whereData)
            ->orderBy($orderBy, $orderType)
            ->offset($skip)
            ->limit($pageSize);
      
        $data_set =  $data_set_obj->get($select);
        
        $data_result = [];
        $status = 200;
        $data_result['data_list'] = $data_set->toArray();
        $data_result['total_record'] = $total_record;
        
        $response = Utilities::buildSuccessResponse(10004, "Student Admission List.", $data_result);

        return response()->json($response, $status); 
    }
  
   
}
