<?php

/**
 * Darulmadinah Api
Author: Muhammad Usama (it.dev7) 
 * 
 * This is a GenAdmissionVoucher API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\GenAdmissionVoucher;
use App\Models\AdmissionVoucherPosting;
use App\Models\StudentRegistration;
use App\Models\StdInterviewTest;
use App\Models\AssignFeeStructure;
use App\Models\FeeStructureDetail;
use App\Models\AssigndiscountPolicy;
use App\Models\GenAdmissionVoucherDetail;
use App\Models\FeeType;
use App\Models\Campus;
use App\Models\DiscountPolicy;
use App\Models\StudentAdmission;
use App\Models\Session;
use App\Models\CampusSession;
use App\Models\ClassAssign;
use App\Models\RegReptNote;
use App\Models\State;
use App\Models\Region;
use App\Models\City;
use App\Models\StudentLeftRequest;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Utilities\Utilities;
use Illuminate\Support\Facades\DB;

class StudentLeftRequestApi extends Controller
{

    /**
     * Constructor
     */
    private $mdlName;
             
    public function __construct()
    {
        $this->mdlName = new StudentLeftRequest();
    }
    
    
    private $select_column_dis = [];
    private $select_columns = [];
        
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
     * Add Student Transfer.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {    
        // return response()->json($request, 201);
        // exit;
        $user_id =   Utilities::getUserID_by_keycloak_id($request->keycloak_id);
        $this->mdlName->filterColumns($request);
        Utilities::defaultAddAttributes($request, $user_id);
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        try
        {
            DB::beginTransaction();
            $request['request_assigned_to']=1;
            $post_arr = $request->all();
           
            if($post_arr['application_img']){
                unset($post_arr['application_img']);
            }

            if($post_arr['attendance_img']){
                unset($post_arr['attendance_img']);
            }

            

            $obj = StudentLeftRequest::create($post_arr);

            $folder = storage_path('app/left_certificate_form_image/');
            if($request['application_img']){
                $img = explode(";base64,", $request['application_img']);
                $img_aux = explode("image/", $img[0]);
                $img_base64 = base64_decode($img[1]);
                $extension = explode('/', mime_content_type($request['application_img']))[1];
                $image_name = $obj->id. '-'. $obj->std_admission_id. 'application_img.'. $extension;
                file_put_contents($folder . $image_name, $img_base64);
                $request['application_img'] = $image_name;
                DB::table('student_left_request')->where('id', $obj->id)->update(['application_img' => $image_name]);
            }

            if($request['attendance_img']){
                $img = explode(";base64,", $request['attendance_img']);
                $img_aux = explode("image/", $img[0]);
                $img_base64 = base64_decode($img[1]);
                $extension = explode('/', mime_content_type($request['attendance_img']))[1];
                $image_name = $obj->id. '-'. $obj->std_admission_id. 'attendance_img.'. $extension;
                file_put_contents($folder . $image_name, $img_base64);
                $request['attendance_img'] = $image_name;
                DB::table('student_left_request')->where('id', $obj->id)->update(['attendance_img' => $image_name]);
            }
            $data = [ 'id' => $obj->id];
            DB::commit();
            $response = Utilities::buildSuccessResponse(10000, "Student Left Request successfully created.", $data);
        }
        catch(\Exception $e)
        {
            DB::rollback();
            $response = Utilities::buildBaseResponse(10003, $e."Transaction Failed Studnet Left Request. ");
        }
        return response()->json($response, 201);
    }

    /**
     * Update AdmissionVoucher.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {     
        $this->mdlName->filterColumns($request);
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
       
        Utilities::defaultUpdateAttributes($request, $request->data_user_id);
        $Record = StudentLeftRequest::find($request->id);
        $request['request_assigned_to']=1;
        
        $post_arr = $request->all();
        
        $status = 200;
        $response = [];
        if (! $Record) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Record not found.");
        } else {


            if($post_arr['application_img']){
                unset($post_arr['application_img']);
            }else{
                unset($post_arr['application_img']);
            }

            if($post_arr['attendance_img']){
                unset($post_arr['attendance_img']);
            }else{
                unset($post_arr['attendance_img']);
            }


            $folder = storage_path('app/left_certificate_form_image/');
            if($request['application_img']){
                $img = explode(";base64,", $request['application_img']);
                $img_aux = explode("image/", $img[0]);
                $img_base64 = base64_decode($img[1]);
                $extension = explode('/', mime_content_type($request['application_img']))[1];
                $image_name = $Record->id. '-'. $Record->std_admission_id. 'application_img.'. $extension;
                file_put_contents($folder . $image_name, $img_base64);
                $request['application_img'] = $image_name;
                DB::table('student_left_request')->where('id', $Record->id)->update(['application_img' => $image_name]);
            }

            if($request['attendance_img']){
                $img = explode(";base64,", $request['attendance_img']);
                $img_aux = explode("image/", $img[0]);
                $img_base64 = base64_decode($img[1]);
                $extension = explode('/', mime_content_type($request['attendance_img']))[1];
                $image_name = $Record->id. '-'. $Record->std_admission_id. 'attendance_img.'. $extension;
                file_put_contents($folder . $image_name, $img_base64);
                $request['attendance_img'] = $image_name;
                DB::table('student_left_request')->where('id', $Record->id)->update(['attendance_img' => $image_name]);
            }

            $obj = $Record->update($post_arr);
            if ($obj) {
                $data = [ 'id' => $Record->id ];
                $response = Utilities::buildSuccessResponse(10001, "Student Left successfully updated.", $data);
            }
        }
        return response()->json($response, $status);
    }
    
    /**
     * Activate/De-Activate AdmissionVoucher.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function patchDisableEnable(Request $request)
    {
 
    }

    /**
     * Delete AdmissionVoucher.
     *
     * @param $id 'ID' of GenAdmissionVoucher to delete. (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id, Request $request)
    {   
        $user_id =   Utilities::getUserID_by_keycloak_id($request->keycloak_id);
        $request->request->add([ 'id' => $id ]);
        
        // $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        
        Utilities::defaultDeleteAttributes($request, $user_id);
        
        $request->request->add([
            'is_enable' => Constant::RecordType['DELETED']
        ]);
        
        $result_set = StudentLeftRequest::find($request->id);
       
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "StudentLeftRequest not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "StudentLeftRequest successfully deleted.");
            }
        }
        
        return response()->json($response, $status);
       
    }

    /**
     * Get one AdmissionVoucher.
     *
     * @param $id 'ID' of GenAdmissionVoucher to return (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOne($id, Request $request)
    {
       
        $whereData = array();
        $request->request->add([ 'id' => $id ]);
        if($request->id) {   
            $whereData[] = ['id', $request->id];
        }
        
        $total_record_obj_q = StudentLeftRequest::with('Student', 'StduentStatus', 'Campus','Classes')->where($whereData);

        
        if($total_record_obj_q->count()==0){
            $response = Utilities::buildSuccessResponse(10004, "Record Not Found..!!", 'Info');
            return response()->json($response, $status); 
            exit;
        }
        $data_set = $total_record_obj_q->first();

        // $whereCity = array();
        // $whereCity[] = ['is_enable', 1];
        // $whereCity[] = ['id', $data_set->campus2->city_id];
        // $data_set_city = City::where($whereCity)->first();

        $whereCityFrom = array();
        $whereCityFrom[] = ['is_enable', 1];
        $whereCityFrom[] = ['id', $data_set->campus->city_id];
        $data_set_city_from = City::where($whereCityFrom)->first();

      
        $data_result = []; 
        
        $getImg       = url('app/left_certificate_form_image/');
        $getImgPublic = '';
        if (strpos($getImg, 'public') !== false) {
            $getImgPublic = str_replace('public', 'storage', $getImg);
        }else{
            $getImgPublic = url('storage/app/left_certificate_form_image/');
        }
        $data_result['application_img_path']    = $data_set->application_img ? $getImgPublic . '/'. $data_set->application_img.'?'.rand() : null;

        $getImg       = url('app/left_certificate_form_image/');
        $getImgPublic = '';
        if (strpos($getImg, 'public') !== false) {
            $getImgPublic = str_replace('public', 'storage', $getImg);
        }else{
            $getImgPublic = url('storage/app/left_certificate_form_image/');
        }
        $data_result['attendance_img_path']    = $data_set->attendance_img ? $getImgPublic . '/'. $data_set->attendance_img.'?'.rand() : null;
       
        $getImg       = url('app/organization_file/certificate-background3.png');
            $getImgPublic = '';
            if (strpos($getImg, 'public') !== false) {
                $getImgPublic = str_replace('public', 'storage', $getImg);
            }else{
                $getImgPublic = url('storage/app/organization_file/certificate-background3.png');
            }

        $data_result['certificate_border_path'] = $getImgPublic;


       
        $status = 200;
        $data_result['data_list'] = $data_set->toArray();
        // $data_result['data_city'] = $data_set_city->toArray();
        $data_result['data_city_from'] = $data_set_city_from->toArray();
        $response = Utilities::buildSuccessResponse(10004, "Student Left Request List.", $data_result);
        return response()->json($response, $status); 
    }

    /**
     * Fetch list of GenAdmissionVoucher by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll(Request $request, $id = null)
    {
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
        $whereDataStd = array();
       
        if($request->data_campus_id) {   
            $whereData[] = ['campus_id', $request->data_campus_id];
        }

        if($request->student_name) {   
            $whereDataStd[] = ['student_name', 'LIKE', "%{$request->student_name}%"];
        }
        if($request->gr_no) {   
            $whereDataStd[] = ['gr_no',$request->gr_no];
        }
        if($request->admission_code) {   
            $whereDataStd[] = ['admission_code',$request->admission_code];
        }


        
        $total_record_obj_q = StudentLeftRequest::with('Student', 'StduentStatus', 'Campus'  ,'Classes')->where($whereData)->active();

        $total_record_obj_q->whereHas('Student', function($q) use ($whereDataStd){
            $q->where($whereDataStd);
           
        });


        $total_record =  $total_record_obj_q->count();
        
        $orderBy =  $this->mdlName->getOrderColumn($request->order_by);
        
        $orderType = $request->order_type ?? Constant::OrderType;
        
       
        $data_set_q = StudentLeftRequest::with('Student', 'StduentStatus', 'Campus','Classes', 'Users')->where($whereData)->active();

        $data_set_q->whereHas('Student', function($q) use ($whereDataStd){
                    $q->where($whereDataStd);
        });    
                                        
        $data_set = $data_set_q->get();
        $data_result = [];
        $status = 200;
        $data_result['data_list'] = $data_set->toArray();
        $data_result['total_record'] = $total_record;
        
        $response = Utilities::buildSuccessResponse(10004, "Student Left Request List.", $data_result);

        return response()->json($response, $status); 
    }

    public function get_state_list_org(Request $request, $id = null)
    {
        $whereData = array();
        $whereData[] = ['is_enable', 1];
        if($request->data_campus_id) {   
            $whereData[] = ['id', $request->org_id];
        }
        $data_set_org = Organization::where($whereData)->first();

        $data_set_state = State::where('countries_id',$data_set_org->countries_id )->get();

        $data_result = [];
        $status = 200;
        $data_result['data_list'] = $data_set_state->toArray();
        $response = Utilities::buildSuccessResponse(10004, "State List.", $data_result);
        return response()->json($response, $status); 
    }

    public function get_region_list_org(Request $request, $id = null)
    {
        $whereData = array();
        $whereData[] = ['is_enable', 1];
        if($request->state_id) {   
            $whereData[] = ['state_id', $request->state_id];
        }
        $data_set_region = Region::where($whereData)->get();

        $data_result = [];
        $status = 200;
        $data_result['data_list'] = $data_set_region->toArray();
        $response = Utilities::buildSuccessResponse(10004, "Region List.", $data_result);
        return response()->json($response, $status); 
    }

    public function get_city_list_org(Request $request, $id = null)
    {
        $whereData = array();
        $whereData[] = ['is_enable', 1];
        if($request->region_id) {   
            $whereData[] = ['region_id', $request->region_id];
        }
        $data_set_city = City::where($whereData)->get();

        $data_result = [];
        $status = 200;
        $data_result['data_list'] = $data_set_city->toArray();
        $response = Utilities::buildSuccessResponse(10004, "City List.", $data_result);
        return response()->json($response, $status); 
    }

    public function get_campus_list_org(Request $request, $id = null)
    {
        $whereData = array();
        $whereData[] = ['is_enable', 1];
        if($request->city_id) {   
            $whereData[] = ['city_id', $request->city_id];
        }

        if($request->data_campus_id) {   
            $whereData[] = ['id','!=', $request->data_campus_id];
        }
        $data_set_campus = Campus::where($whereData)->get();

        $data_result = [];
        $status = 200;
        $data_result['data_list'] = $data_set_campus->toArray();
        $response = Utilities::buildSuccessResponse(10004, "Campus List.", $data_result);
        return response()->json($response, $status); 
    }

    public function get_campus_session_list_org(Request $request, $id = null)
    {
        $whereData = array();
        $whereData[] = ['is_enable', 1];
        if($request->campus_id) {   
            $whereData[] = ['campus_id', $request->campus_id];
        }

        $data_set_session = CampusSession::with('Session')->where($whereData)->get();

        $data_result = [];
        $status = 200;
        $data_result['data_list'] = $data_set_session->toArray();
        $response = Utilities::buildSuccessResponse(10004, "Campus Session List.", $data_result);
        return response()->json($response, $status); 
    }

    public function get_campus_class_list_org(Request $request, $id = null)
    {
        $whereData = array();
        $whereData[] = ['is_enable', 1];
        if($request->_campus_id) {   
            $whereData[] = ['campus_id', $request->_campus_id];
        }
        if($request->session_id) {   
            $whereData[] = ['session_id', $request->session_id];
        }

        $data_set_class = ClassAssign::with('Classes')->where($whereData)->get();

        $data_result = [];
        $status = 200;
        $data_result['data_list'] = $data_set_class->toArray();
        $response = Utilities::buildSuccessResponse(10004, "Campus Class List.", $data_result);
        return response()->json($response, $status); 
    }
 
   
}
