<?php

/**
 * Darulmadinah Api
Author: Muhammad Usama (it.dev7) 
 * 
 * This is a ClassAssign API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\ClassAssign;
use App\Models\AuthUser;
use App\Models\TeacherSubAssign;
use Illuminate\Http\Request;
use App\Utilities\Utilities;

class ClassAssignApi extends Controller
{

    /**
     * Constructor
     */
    private $mdlName;
             
    public function __construct()
    {
        $this->mdlName = new ClassAssign();
    }
    
    private $select_columns  = [
        'id', 
        // 'organization_id', 
        // 'countries_id', 
        // 'state_id', 
        // 'region_id',
        // 'city_id', 
        'campus_id', 
        'session_id', 
        'class_id', 
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
     * Add ClassAssign.
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
        $obj = ClassAssign::create($post_arr);
        
        $data = [ 'id' => $obj->id ];
        
        $response = Utilities::buildSuccessResponse(10000, "Campus Class successfully created.", $data);
        
        return response()->json($response, 201);
    }

    /**
     * Update ClassAssign.
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
        $result_set = ClassAssign::find($request->id);
        
        $status = 200;
        $response = [];
        
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Campus Class not found.");
        } else {
            
            $post_arr = $request->all();
           
            $obj = $result_set->update($post_arr);

            if ($obj) {
                $data = [ 'id' => $result_set->id ];
                $response = Utilities::buildSuccessResponse(10001, "Campus Class successfully updated.", $data);
            } 
        }
        
        return response()->json($response, $status);
    }
    
    /**
     * Activate/De-Activate ClassAssign.
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
        
        $result_set = ClassAssign::find($request->id);
        $status = 200;
        $response = [];

        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Campus Class not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $data = ['id' => $result_set->id ];
                $actMsg = $request->is_enable == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "Campus Class successfully $actMsg.", $data);
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Delete .
     *
     * @param $id 'ID' of  to delete. (required)
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
        
        $result_set = ClassAssign::find($request->id);
        
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Campus Class not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "Campus Class successfully deleted.");
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Get one ClassAssign.
     *
     * @param $id 'ID' of ClassAssign to return (required)
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
        
        $result_set = ClassAssign::with('Country', 'State', 'Region', 'City', 'Campus' ,'Session' ,'Classes')->where('id', $request->id)->first($select);
        
        $status = 200;
        $response = [];

        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Campus Class not found.");
        } else {
            
            $data_set = $result_set->toArray();
  
            $dataResult['data_set']             = $data_set;
  
            $response = Utilities::buildSuccessResponse(10005, "Campus Class Data.", $dataResult);
        }
        
        return response()->json($response, $status);
    }

    /**
     * Fetch list of ClassAssign by searching with optional filters..
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

        if($request->data_session_id) {   
            $whereData[] = ['session_id', $request->data_session_id];
        }
       
        if($request->countries_id) {   
            $whereData[] = ['countries_id', $request->countries_id];
        }
        if($request->state_id) {   
            $whereData[] = ['state_id', $request->state_id];
        }
        if($request->region_id) {   
            $whereData[] = ['region_id', $request->region_id];
        }
        
        if($request->city_id) {   
            $whereData[] = ['city_id', $request->city_id];
        }
        
        if($request->campus_id) {   
            $whereData[] = ['campus_id', $request->campus_id];
        }
        
        if($request->session_id) {   
            $whereData[] = ['session_id', $request->session_id];
        }
        if($request->class_id) {   
            $whereData[] = ['class_id', $request->class_id];
        }
           
        
        if($request->is_enable != null) {
            $whereData[] = ['is_enable', $request->is_enable];
        }
        
        $total_record = ClassAssign::where($whereData)
            ->active()
            ->count();
        
        $orderBy =  $this->mdlName->getOrderColumn($request->order_by);
        
        $orderType = $request->order_type ?? Constant::OrderType;
        
        $data_set = ClassAssign::with('Country', 'State', 'Region', 'City', 'Campus' , 'Session','Classes')
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
        
        $response = Utilities::buildSuccessResponse(10004, "Campus Class List.", $data_result);

        return response()->json($response, $status); 
    }
    
    /**
     * Fetch list of ClassAssign for selectboc/dropdown by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClassAssign(Request $request, $id = null)
    {
        $user_list = AuthUser::where('keycloak_id', $request->keycloak_id)->first();
        
        if($user_list->is_teacher == 1)
        {
            $whereDataTeacher = array();
            $whereDataTeacher[] = ['is_enable', 1];
            $whereDataTeacher[] = ['teacher_id', $user_list->id];

            if($request->campus_id) {   
                $whereDataTeacher[] = ['campus_id', $request->campus_id];
            }
    
            if($request->data_campus_id) {   
                $whereDataTeacher[] = ['campus_id', $request->data_campus_id];
            }
            $data_set = TeacherSubAssign::with('Classes')
                ->where($whereDataTeacher)
                ->active()
                ->get();
        }
        else
        {
            $this->validate($request, $this->mdlName->rules($request, Constant::RequestType['GET_ALL']), $this->mdlName->messages($request, Constant::RequestType['GET_ALL']));
            $select =  $this->select_columns;
            
            if($request->fields){
                $select = $request->fields;
            }
            $whereData = array();
            if($request->countries_id) {   
                $whereData[] = ['countries_id', $request->countries_id];
            }
            if($request->state_id) {   
                $whereData[] = ['state_id', $request->state_id];
            }
            if($request->region_id) {   
                $whereData[] = ['region_id', $request->region_id];
            }
            
            if($request->city_id) {   
                $whereData[] = ['city_id', $request->city_id];
            }
            
            if($request->campus_id) {   
                $whereData[] = ['campus_id', $request->campus_id];
            }
            if($request->data_campus_id) {   
                $whereData[] = ['campus_id', $request->data_campus_id];
            }
            if($request->session_id) {   
                $whereData[] = ['session_id', $request->session_id];
            }
            if($request->class_id) {   
                $whereData[] = ['class_id', $request->class_id];
            }
            if($request->id) {   
                $whereData[] = ['id', $request->id];
            }
            if($request->is_enable != null) {
                $whereData[] = ['is_enable', $request->is_enable];
            }
            $whereData[] = ['is_enable', 1];
            $data_set = ClassAssign::with('Country', 'State', 'Region', 'Campus' ,'Session', 'Classes')
                ->where($whereData)
                ->active()
                ->get($select);
        }
        

        $data_result = [];
        $status = 200;
        $data_result['campus_class'] = $data_set->toArray();
        
        $response = Utilities::buildSuccessResponse(10004, "Campus Class List.", $data_result);

        return response()->json($response, $status); 
    }
}
