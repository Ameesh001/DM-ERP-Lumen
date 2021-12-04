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
use Illuminate\Http\Request;
use App\Utilities\Utilities;
use App\Models\Workflow;
use App\Models\WorkflowDetail;
use App\Models\WorkflowAssignedHierarchy;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;

class WorkflowApi extends Controller
{

    /**
     * Constructor
     */
    private $mdlName;
             
    public function __construct()
    {
        $this->mdlName = new Workflow();
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
        $wf_master_detail = $request['arr'];
        foreach($wf_master_detail as $key => $val)
        {
            $rules_[$key.'.assigned_role_id']  = 'required';
            // $rules_[$key.'.std_registration_id']             = 'required';
            // $rules_[$key.'.obtained_marks']     = 'required|digits_between:1,3';
            // $rules_[$key.'.final_result_id']    = 'required|digits_between:1,3';
            // $rules_[$key.'.test_date']          = 'date';
            // $rules_[$key.'.test_remarks']       = 'min:3|max:100';
            // $rules_[$key.'.is_interview']       = 'digits_between:1,3';
            // $rules_[$key.'.interview_remarks']  = 'min:3|max:100';
            // $rules_[$key.'.interview_date']     = 'date';
            // $rules_[$key.'.is_nazra_test']      = 'digits_between:1,3';
            // $rules_[$key.'.nazra_current_lesson']   = 'min:3|max:100';
            // $rules_[$key.'.nazra_date']             = 'date';
            // $rules_[$key.'.nazra_remarks']          = 'min:3|max:100';
        }   
        
        
        $this->validate($request, $rules_);

        $org_list = Organization::find($request->data_org_id);
        $wf_assigned_hierarchy['countries_id'] = $org_list->countries_id;
        if(!$request->is_checkbox)
        {
            $wf_assigned_hierarchy['state_id'] = $request['state_id'];
            $wf_assigned_hierarchy['region_id'] = $request['region_id'];
            $wf_assigned_hierarchy['city_id'] = $request['city_id'];
            $wf_assigned_hierarchy['campus_id'] = $request['campus_id'];
        }

        $user_id =   Utilities::getUserID_by_keycloak_id($request->keycloak_id);
        Utilities::defaultAddAttributes($request, $user_id);
        $req_org = $request->all();
        
       
        $this->mdlName->filterColumns($request);


        $wf_master = $request->all();
        unset($wf_master['data_user_id']);
        unset($wf_master['data_org_id']);
        // return response()->json($wf_master, 201);
        // exit;
        // unset($wf_master['arr']);

    

        $wf_master['wf_level'] = count($wf_master_detail);
       
        // $wf_master['organization_id'] = $request->organization_id;
        // $wf_master = $request->all();
       


        // $this->validate($wf_master, $this->mdlName->rules($wf_master), $this->mdlName->messages($wf_master));
        try
        {
            DB::beginTransaction();
           
                $obj = Workflow::create($wf_master);
                $wf_level=0;
                $created_dt = date('Y-m-d H:i:s');
                foreach($wf_master_detail as $wf_d)
                {   
                    $wf_level++;
                    if($wf_d['amount']==""){$wf_d['amount']=0;}
                    $wf_d['wf_id']=$obj->id;
                    $wf_d['wf_level']=$wf_level;
                    $wf_d['created_by']=$user_id;
                    $wf_d['created_at']=$created_dt;
                    // return response()->json($wf_d, 201);
                    // exit;
                    $obj_WorkflowDetail = WorkflowDetail::create($wf_d);
                }
                $wf_assigned_hierarchy['wf_id']=$obj->id;
                $wf_assigned_hierarchy['created_by']=$user_id;
                $wf_assigned_hierarchy['created_at']= $created_dt;
                $wf_a_h = WorkflowAssignedHierarchy::create($wf_assigned_hierarchy);

            $data = [ 'id' => $obj->id];
            DB::commit();
            $response = Utilities::buildSuccessResponse(10000, "Workflow Setup successfully created.", $data);
        }
        catch(\Exception $e)
        {
            DB::rollback();
            $response = Utilities::buildBaseResponse(10003, $e."Transaction Failed Workflow Setup. ");
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
        Utilities::removeAttributesExcept($request, ["id","is_enable","keycloak_id"]);
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        $user_id =   Utilities::getUserID_by_keycloak_id($request->keycloak_id);
        Utilities::defaultUpdateAttributes($request, $user_id);
        $activate = $request->is_enable == 1 ? Constant::RecordType['ENABLED'] : Constant::RecordType['DISABLED'];
        $request->request->add([ 'is_enable' => $activate ]);
        $result_set = Workflow::find($request->id);
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Workflow not found.");
        } else {
            $obj = $result_set->update($request->all());
            if ($obj) {
                $data = ['id' => $result_set->id ];
                $actMsg = $request->is_enable == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "Workflow successfully $actMsg.", $data);
            }
        } 
        return response()->json($response, $status);
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
        
        $result_set = Workflow::find($request->id);
       
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Workflow not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "Workflow successfully deleted.");
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
        
        $total_record_obj_q = Workflow::with('Module', 'Module_name')->where($whereData);

        
        if($total_record_obj_q->count()==0){
            $response = Utilities::buildSuccessResponse(10004, "Record Not Found..!!", 'Info');
            return response()->json($response, $status); 
            exit;
        }
        $data_set = $total_record_obj_q->get();

        $whereDetail = array();
        $whereDetail[] = ['is_enable', 1];
        $whereDetail[] = ['wf_id', $data_set['0']->id];
        $data_set_detail = WorkflowDetail::with('auth_role')->where($whereDetail)->get();

        $whereHierarchy = array();
        $whereHierarchy[] = ['is_enable', 1];
        $whereHierarchy[] = ['wf_id', $data_set['0']->id];
        $data_set_hierarchy = WorkflowAssignedHierarchy::with('Country', 'State','Region', 'City', 'Campus')
                                                        ->where($whereHierarchy)->get();


      
        $data_result = []; 

        $status = 200;
        $data_result['data_list'] = $data_set->toArray();
        $data_result['data_list_detail'] = $data_set_detail->toArray();
        $data_result['data_list_hierarchy'] = $data_set_hierarchy->toArray();
        $response = Utilities::buildSuccessResponse(10004, "Workflow List.", $data_result);
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
        $whereDataModule = array();
       
        if($request->wf_name) {   
            $whereData[] = ['wf_name', 'LIKE', "%{$request->wf_name}%"];
        }

        if($request->wf_desc) {   
            $whereData[] = ['wf_desc', 'LIKE', "%{$request->wf_desc}%"];
        }
        if($request->wf_from_date) {   
            $whereData[] = ['wf_from_date',$request->wf_from_date];
        }
        if($request->wf_end_date) {   
            $whereData[] = ['wf_end_date',$request->wf_end_date];
        }

        if($request->module_id) {   
            $whereDataModule[] = ['id',$request->module_id];
        }

        if($request->doc_type_id) {   
            $whereDataModule[] = ['id',$request->doc_type_id];
        }
        
        $total_record_obj_q = Workflow::with('Module', 'Module_name')->where($whereData)->active();

        $total_record_obj_q->whereHas('Module', function($q) use ($whereDataModule){
            $q->where($whereDataModule);
           
        });


        $total_record =  $total_record_obj_q->count();
        
        $orderBy =  $this->mdlName->getOrderColumn($request->order_by);
        
        $orderType = $request->order_type ?? Constant::OrderType;
        
       
        $data_set_q = Workflow::with('Module', 'Module_name')->where($whereData)->active();

        $data_set_q->whereHas('Module', function($q) use ($whereDataModule){
                    $q->where($whereDataModule);
        });  

        $data_set_q->active()
            ->orderBy($orderBy, $orderType)
            ->offset($skip)
            ->limit($pageSize);

        $data_set = $data_set_q->get();
        $data_result = [];
        $status = 200;
        $data_result['data_list'] = $data_set->toArray();
        $data_result['total_record'] = $total_record;
        
        $response = Utilities::buildSuccessResponse(10004, "Workflow List.", $data_result);

        return response()->json($response, $status); 
    }


 
   
}
