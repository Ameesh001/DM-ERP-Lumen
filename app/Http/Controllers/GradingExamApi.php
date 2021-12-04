<?php

/**
 * Darulmadinah Api
Author: Muhammad Usama (it.dev7) 
 * 
 * This is a GradingExam API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\GradingExam;
use App\Models\GradingType;
use App\Models\GradingRemarks;
use Illuminate\Http\Request;
use App\Utilities\Utilities;
use Illuminate\Support\Facades\DB;

class GradingExamApi extends Controller
{

    /**
     * Constructor
     */
    private $mdlName;
             
    public function __construct()
    {
        $this->mdlName = new GradingExam();
    }
    
    private $select_columns  = [
        'id', 
        'organization_id', 
        'grading_type_id', 
        'grading_remarks_id', 
        'grade_name', 
        'percentage_from', 
        'percentage_end', 
        'desc', 
        
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
     * Add GradingExam.
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
        try
        {
            DB::beginTransaction();
            $obj = GradingExam::create($request->all());
            DB::commit();
            $data = [ 'id' => $obj->id ];
            $response = Utilities::buildSuccessResponse(10000, "Grading Exam successfully created.", $data);
        }
        catch(\Exception $e)
        {
            DB::rollback();
           
            $response = Utilities::buildBaseResponse(10003, $e."Transaction Failed Studnet Registration. ");
        }

        return response()->json($response, 201);
    }

    /**
     * Update GradingExam.
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
        $Record = GradingExam::find($request->id);
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
                $response = Utilities::buildSuccessResponse(10001, "Grading Exam successfully updated.", $data);
            }
        }
        return response()->json($response, $status); 
    }
    
    /**
     * Activate/De-Activate GradingExam.
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
        
        $result_set = GradingExam::find($request->id);
        $status = 200;
        $response = [];

        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "GradingExam not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $data = ['id' => $result_set->id ];
                $actMsg = $request->is_enable == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "GradingExam successfully $actMsg.", $data);
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Delete GradingExam.
     *
     * @param $id 'ID' of GradingExam to delete. (required)
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
        $result_set = GradingExam::find($request->id);
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Grading Exam not found.");
        } else {    
            $obj = $result_set->update($request->all()); 
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "Grading Exam successfully deleted.");
            }
        }
        return response()->json($response, $status);
    }

    /**
     * Get one GradingExam.
     *
     * @param $id 'ID' of GradingExam to return (required)
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
        $result_set = GradingExam::with('GradingType', 'GradingRemarks')->where('id', $request->id)->first();
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Grading Exam not found.");
        } else {
            
            $list = $result_set->toArray();
            $data_set = $list;
            $dataResult['data_set'] = $data_set;
            $response = Utilities::buildSuccessResponse(10005, "Grading Exam Single Data.", $dataResult);
        }
        return response()->json($response, $status);
    }

    /**
     * Fetch list of GradingExam by searching with optional filters..
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
        if($request->grading_type_id) {   
            $whereData[] = ['grading_type_id', $request->grading_type_id];
        }
        if($request->grading_remarks_id) {   
            $whereData[] = ['grading_remarks_id', $request->grading_remarks_id];
        }
        // if($request->student_name) {   
        //     $whereData[] = ['student_name', 'LIKE', "%{$request->student_name}%"];
        // }
        // if($request->father_name) {   
        //     $whereData[] = ['father_name', 'LIKE', "%{$request->father_name}%"];
        // }
        // if($request->dob) {   
        //     $whereData[] = ['dob', $request->dob];
        // }

        // if($request->class_id) {   
        //     $whereData[] = ['class_id', $request->class_id];
        // }


        $total_record_obj = GradingExam::where($whereData)
        ->active();
        
        $total_record =  $total_record_obj->count();
        
        $orderBy =  $this->mdlName->getOrderColumn($request->order_by);
        
        $orderType = $request->order_type ?? Constant::OrderType;
        
        $data_set_obj = GradingExam::with('GradingType','GradingRemarks')
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
        
        $response = Utilities::buildSuccessResponse(10004, "Grading Exam List.", $data_result);

        return response()->json($response, $status); 
    }

    /**
     * Fetch list of Grade type by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGradeType(Request $request, $id = null)
    {
        if($request->organization_id) {   
            $whereData[] = ['organization_id', $request->organization_id];
        }
        $data_set_obj = GradingType::where($whereData);
        $data_set =  $data_set_obj->get();
      
        $data_result = [];
        $status = 200;
        $data_result['list'] = $data_set->toArray();
        $response = Utilities::buildSuccessResponse(10004, "Grading Type List.", $data_result);
        return response()->json($response, $status); 
    }

    /**
     * Fetch list of Grade type by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGradeRemarks(Request $request)
    {
        if($request->type_id) {   
            $whereData[] = ['grading_type_id', $request->type_id];
        }
        $data_set_obj = GradingRemarks::where($whereData);
        $data_set =  $data_set_obj->get();
      
        $data_result = [];
        $status = 200;
        $data_result['list'] = $data_set->toArray();
        $response = Utilities::buildSuccessResponse(10004, "Grading Type List.", $data_result);
        return response()->json($response, $status); 
    }
   
    
    
    
   
}
