<?php

/**
 * Darulmadinah Api
Author: Muhammad Usama (it.dev7) 
 * 
 * This is a CampusSection API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\CampusSection;
use App\Models\StudentAdmission;
use App\Models\Options;
use Illuminate\Http\Request;
use App\Utilities\Utilities;

class CampusSectionApi extends Controller
{

    /**
     * Constructor
     */
    private $mdlName;
             
    public function __construct()
    {
        $this->mdlName = new CampusSection();
    }
    
    private $select_columns  = [
        'id', 
        // 'organization_id', 
        // 'countries_id', 
        // 'state_id', 
        // 'region_id',
        // 'city_id', 
        'campus_id', 
        'class_id',  
        'session_id', 
        'section_id',
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
     * Add CampusSection.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
             
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        
        $this->mdlName->filterColumns($request);
        
         Utilities::defaultAddAttributes($request, $request->data_user_id);
            
        $post_arr = $request->all();
        //$post_arr['organization_id'] =  1;
        $obj = CampusSection::create($post_arr);
        
        $data = [ 'id' => $obj->id ];
        
        $response = Utilities::buildSuccessResponse(10000, "CampusSection successfully created.", $data);
        
        return response()->json($response, 201);
    }

    /**
     * Update CampusSection.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        
        $this->mdlName->filterColumns($request);
        
        Utilities::defaultUpdateAttributes($request, $request->data_user_id);
//
        $result_set = CampusSection::find($request->id);
        
        $status = 200;
        $response = [];
        
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "CampusSection not found.");
        } else {
            
            $post_arr = $request->all();
           
            $obj = $result_set->update($post_arr);

            if ($obj) {
                $data = [ 'id' => $result_set->id ];
                $response = Utilities::buildSuccessResponse(10001, "CampusSection successfully updated.", $data);
            } 
        }
        
        return response()->json($response, $status);
    }
    
    /**
     * Activate/De-Activate CampusSection.
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
        
        $result_set = CampusSection::find($request->id);
        $status = 200;
        $response = [];

        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "CampusSection not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $data = ['id' => $result_set->id ];
                $actMsg = $request->is_enable == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "CampusSection successfully $actMsg.", $data);
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Delete CampusSection.
     *
     * @param $id 'ID' of CampusSection to delete. (required)
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
        
        $result_set = CampusSection::find($request->id);
        
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "CampusSection not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "CampusSection successfully deleted.");
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Get one CampusSection.
     *
     * @param $id 'ID' of CampusSection to return (required)
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
        
        $result_set = CampusSection::with('Campus', 'Class', 'Session', 'Section')->where('id', $request->id)->first($select);
        
        $status = 200;
        $response = [];

        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "CampusSection not found.");
        } else {
            
            $data_set = $result_set->toArray();
  
            $dataResult['data_set']             = $data_set;
  
            $response = Utilities::buildSuccessResponse(10005, "CampusSection Data.", $dataResult);
        }
        
        return response()->json($response, $status);
    }

    /**
     * Fetch list of CampusSection by searching with optional filters..
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
        
        
       
        // if($request->countries_id) {   
        //     $whereData[] = ['countries_id', $request->countries_id];
        // }
        // if($request->state_id) {   
        //     $whereData[] = ['state_id', $request->state_id];
        // }
        // if($request->region_id) {   
        //     $whereData[] = ['region_id', $request->region_id];
        // }
        
        // if($request->city_id) {   
        //     $whereData[] = ['city_id', $request->city_id];
        // }
        
        if($request->campus_id) {   
            $whereData[] = ['campus_id', $request->campus_id];
        }

        if($request->data_campus_id !== "null") {   
            $whereData[] = ['campus_id', $request->data_campus_id];
        }
        
        if($request->class_id) {   
            $whereData[] = ['class_id', $request->class_id];
        }
        
        if($request->session_id) {   
            $whereData[] = ['session_id', $request->session_id];
        }
        
        if($request->section_id) {   
            $whereData[] = ['section_id', $request->section_id];
        }
           
        
        if(isset($request->is_enable)) {   
            $whereData[] = ['is_enable', $request->is_enable];
        }

        if($request->organization_id) {   
            $whereData[] = ['organization_id', $request->organization_id];
        }
        
        $total_record = CampusSection::where($whereData)
            ->active()
            ->count();
        
        $orderBy =  $this->mdlName->getOrderColumn($request->order_by);
        
        $orderType = $request->order_type ?? Constant::OrderType;
        
        $data_set = CampusSection::with('Campus', 'Class', 'Session', 'Section')
            ->where($whereData)
            ->active()
            ->orderBy($orderBy, $orderType)
            ->offset($skip)
            ->limit($pageSize)
            ->get($select);
        
        
        $data_result = [];
        $status = 200;
        $data_result['data_list'] = $data_set->toArray();
        $data_result['total_record'] = $total_record;
        
        $response = Utilities::buildSuccessResponse(10004, "CampusSection List.", $data_result);

        return response()->json($response, $status); 
    }
    
    /**
     * Fetch list of CampusSection for selectboc/dropdown by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCampusSection(Request $request, $id = null)
    {
        $this->validate($request, $this->mdlName->rules($request, Constant::RequestType['GET_ALL']), $this->mdlName->messages($request, Constant::RequestType['GET_ALL']));
        
       
        $select =  $this->select_columns;
        
        if($request->fields){
            $select = $request->fields;
        }
        $whereData = array();

        
        $whereData[] = ['is_enable', 1];    


        if($request->data_campus_id) {   
            $whereData[] = ['campus_id', $request->data_campus_id];
        }
        if($request->class_id) {   
            $whereData[] = ['class_id', $request->class_id];
        }

        if($request->data_session_id) {   
            $whereData[] = ['session_id', $request->data_session_id];
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
 
        if($request->id) {   
            $whereData[] = ['id', $request->id];
        }
        if($request->is_enable != null) {
            $whereData[] = ['is_enable', $request->is_enable];
        }

        if($request->organization_id) {   
            $whereData[] = ['organization_id', $request->organization_id];
        }
        
        $data_set = CampusSection::with('Campus', 'Class','Session', 'Section')
            ->where($whereData)
            ->active()
            ->get($select);
        
        
        $data_result = [];
        $status = 200;
        $data_result['campus'] = $data_set->toArray();
        
        $response = Utilities::buildSuccessResponse(10004, "CampusSection List.", $data_result);

        return response()->json($response, $status); 
    }


    public function get_campus_section(Request $request, $id = null)
    {
        $whereData = array();
        $whereData[] = ['is_enable', 1];
        if($request->data_campus_id) {   
            $whereData[] = ['campus_id', $request->data_campus_id];
        }

        if($request->campus_class_id) {   
            $whereData[] = ['class_id', $request->campus_class_id];
        }

        $data_set_std = StudentAdmission::where($whereData)
            // ->active()
            ->get('*');


        if($request->campus_session_id) {   
            $whereData[] = ['session_id', $request->campus_session_id];
        }
      
        
        
        // return response()->json($whereData, 200);
        // exit;
        
        $data_set = CampusSection::with('Section')
            ->where($whereData)
            // ->active()
            ->get('*');
        
        
        $data_result = [];
        $status = 200;
        $data_result['data_list'] = $data_set->toArray();
        $data_result['std_list'] = $data_set_std->toArray();
        
        $response = Utilities::buildSuccessResponse(10004, "CampusSection List.", $data_result);

        return response()->json($response, $status); 
    }

    public function get_campus_student(Request $request, $id = null)
    {
        $whereData = array();
        $whereData[] = ['is_enable', 1];
        if($request->data_campus_id) {   
            $whereData[] = ['campus_id', $request->data_campus_id];
        }

        if($request->campus_class_id) {   
            $whereData[] = ['class_id', $request->campus_class_id];
        }

        if($request->class_id) {   
            $whereData[] = ['class_id', $request->class_id];
        }

        if($request->campus_session_id) {   
            $whereData[] = ['session_id', $request->campus_session_id];
        }

        if($request->data_session_id) {   
            $whereData[] = ['session_id', $request->data_session_id];
        }
      
        $data_set_std = StudentAdmission::where($whereData)->get('*');
        
        $data_result = [];
        $status = 200;
        $data_result['std_list'] = $data_set_std->toArray();
        $response = Utilities::buildSuccessResponse(10004, "CampusSection List.", $data_result);
        return response()->json($response, $status); 
    }


    public function get_single_student(Request $request, $id = null)
    {
        $whereData = array();
        $whereData[] = ['is_enable', 1];
        if($request->student_id) {   
            $whereData[] = ['id', $request->student_id];
        }
       
        $data_set_std_one = StudentAdmission::where($whereData)->first();
        
        $data_result = [];
        $status = 200;
        $data_result['std_list_one'] = $data_set_std_one->toArray();
        $response = Utilities::buildSuccessResponse(10004, "Single Student List.", $data_result);
        return response()->json($response, $status); 
    }

    public function get_progress_list(Request $request)
    {
        $whereData = array();
        $whereData[] = ['is_enable', 1];
        if($request->option_type) {   
            $whereData[] = ['option_type', $request->option_type];
        }
        if($request->org_ids) {   
            $whereData[] = ['organization_id', $request->org_ids];
        }
        $get_progress_list = Options::where($whereData)->get();
        $data_result = [];
        $status = 200;
        $data_result['option_list'] = $get_progress_list->toArray();
        $response = Utilities::buildSuccessResponse(10004, "Options  List.", $data_result);
        return response()->json($response, $status); 
    }

    public function get_campus_student_by_section_id(Request $request, $id = null)
    {
        $whereData = array();
        $whereData[] = ['is_enable', 1];
        if($request->data_campus_id) {   
            $whereData[] = ['campus_id', $request->data_campus_id];
        }
        if($request->class_id) {   
            $whereData[] = ['class_id', $request->class_id];
        }
        if($request->campus_session_id) {   
            $whereData[] = ['session_id', $request->campus_session_id];
        }
        if($request->data_session_id) {   
            $whereData[] = ['session_id', $request->data_session_id];
        }
        if($request->section_id) {   
            $whereData[] = ['section_id', $request->section_id];
        }
      
        $data_set_std = StudentAdmission::where($whereData)->get('*');
        
        $data_result = [];
        $status = 200;
        $data_result['std_list'] = $data_set_std->toArray();
        $response = Utilities::buildSuccessResponse(10004, "Campus Student List.", $data_result);
        return response()->json($response, $status); 
    }


}
