<?php

/**
 * Darulmadinah Api
 * 
 * This is a TeacherTimeTable API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\TeacherTimeTable;
use Illuminate\Http\Request;
use App\Utilities\Utilities;
use Illuminate\Support\Facades\DB;

class TeacherTimeTableApi extends Controller
{

    /**
     * Constructor
     */
    private $mdlName;
             
    public function __construct()
    {
        $this->mdlName = new TeacherTimeTable();
    }
    
    private $select_columns  = [
        'id',
        'campus_id',
        'class_id',
        'subject_id',
        'teacher_id',
        'day',
        'class_start_time',
        'class_end_time',
        'break_start_time',
        'break_end_time',
        'is_enable',
        'created_by',
        'created_at' ,
        'updated_by' ,
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
     * Add TeacherTimeTable.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        //$user_id =   Utilities::getUserID_by_keycloak_id($request->keycloak_id);
        
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        
        $this->mdlName->filterColumns($request);
        
         Utilities::defaultAddAttributes($request, 1);
            
        $post_arr = $request->all();
        $obj = TeacherTimeTable::create($post_arr);
        
        $data = [ 'id' => $obj->id ];
        
        $response = Utilities::buildSuccessResponse(10000, "Time assigned Successfully.", $data);
        
        return response()->json($response, 201);
    }

    /**
     * Update TeacherTimeTable.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $user_id =   Utilities::getUserID_by_keycloak_id($request->keycloak_id);
        $mdlName = new TeacherTimeTable();
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        
        $this->mdlName->filterColumns($request);
        
        Utilities::defaultUpdateAttributes($request, $user_id);
//
        $result_set = TeacherTimeTable::find($request->id);
        
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
        
        return response()->json($response, $status);

        
    }
    
    /**
     * Activate/De-Activate TeacherTimeTable.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function patchDisableEnable(Request $request)
    {
 
        Utilities::removeAttributesExcept($request, ["id","is_enable"]);
        
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        
        Utilities::defaultUpdateAttributes($request, 1);
        
        $activate = $request->is_enable == 1 ? Constant::RecordType['ENABLED'] : Constant::RecordType['DISABLED'];
        
        $request->request->add([ 'is_enable' => $activate ]);
        
        $result_set = TeacherTimeTable::find($request->id);
        $status = 200;
        $response = [];

        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Teacher Time Table not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $data = ['id' => $result_set->id ];
                $actMsg = $request->is_enable == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "Time Assign successfully $actMsg.", $data);
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Delete TeacherTimeTable.
     *
     * @param $id 'ID' of TeacherTimeTable to delete. (required)
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
        
        $result_set = TeacherTimeTable::find($request->id);
        
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Time Table not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "Time Table successfully deleted.");
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Get one TeacherTimeTable.
     *
     * @param $id 'ID' of TeacherTimeTable to return (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOne($id, Request $request)
    {
        $mdlList = new TeacherTimeTable();
        $request->request->add([ 'id' => $id ]);
        $this->validate($request, $mdlList->rules($request, Constant::RequestType['GET_ONE']), $mdlList->messages($request, Constant::RequestType['GET_ONE']));
        $select = $this->select_columns;
        $mdlList->filterColumns($request);
        if($request->fields){
            $select = $request->fields;
        }
        $list = TeacherTimeTable::where('id', $request->id)->first($select);
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

    /**
     * Fetch list of TeacherTimeTable by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll(Request $request, $id = null)
    {
        
       
        $this->validate($request, $this->mdlName->rules($request, Constant::RequestType['GET_ALL']),
         $this->mdlName->messages($request, Constant::RequestType['GET_ALL']));
        
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
        
        
        if($request->campus_id) {   
            $whereData[] = ['campus_id', $request->campus_id];
        }
        
        if($request->class_id) {   
            $whereData[] = ['class_id', $request->class_id];
        }
        
        if($request->subject_id) {   
            $whereData[] = ['subject_id', $request->subject_id];
       
        }if($request->teacher_id) {   
            $whereData[] = ['teacher_id', $request->teacher_id];
        }

        if(isset($request->day)) {   
            $whereData[] = ['day', $request->day];
        }

        if(isset($request->class_start_time)) {   
            $whereData[] = ['class_start_time', $request->class_start_time];
        }

        if(isset($request->class_end_time)) {   
            $whereData[] = ['class_end_time', $request->class_end_time];
        }
        
        if(isset($request->break_start_time)) {   
            $whereData[] = ['break_start_time', $request->break_start_time];
        }
        if(isset($request->break_end_time)) {   
            $whereData[] = ['break_end_time', $request->break_end_time];
        }
        
        if(isset($request->is_enable)) {   
            $whereData[] = ['is_enable', $request->is_enable];
        }



        $mdlList = new TeacherTimeTable();
        
        
        $total_record = TeacherTimeTable::where($whereData)->active()->count();
        $orderBy =  $mdlList->getOrderColumn($request->order_by);
        $orderType = $request->order_type ?? Constant::OrderType;
        
        $queryObj = TeacherTimeTable::with('Campus','TeacherName','Class','Subject');
        $queryObj->where($whereData);
        $queryObj->active();
        $queryObj->orderBy($orderBy, $orderType);
        $queryObj->offset($skip);
        $queryObj->limit($pageSize);
        $list = $queryObj->get($select);
        $status = 200;
        // $data_result = new StateResponse();
        // $data_result->setState($list->toArray());

        $data_result = array("list" => $list->toArray());
        $data_result['total_record'] = $total_record;
        $response = Utilities::buildSuccessResponse(10004, "Time Table List.", $data_result);
        return response()->json($response, $status);  

        //return response()->json(TeacherTimeTable::all());
    }
    
    /**
     * Fetch list of TeacherTimeTable for selectboc/dropdown by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTeacherTimeTable(Request $request, $id = null)
    {
        $this->validate($request, $this->mdlName->rules($request, Constant::RequestType['GET_ALL']), $this->mdlName->messages($request, Constant::RequestType['GET_ALL']));
        
       
        $select =  $this->select_columns;
        
        if($request->fields){
            $select = $request->fields;
        }
        $whereData = array();
        
        
        if($request->campus_id) {   
            $whereData[] = ['campus_id', $request->campus_id];
        }
        
        if($request->class_id) {   
            $whereData[] = ['class_id', $request->class_id];
        }
        
        if($request->subject_id) {   
            $whereData[] = ['subject_id', $request->subject_id];
       
        }if($request->teacher_id) {   
            $whereData[] = ['teacher_id', $request->teacher_id];
        }

        if(isset($request->day)) {   
            $whereData[] = ['day', $request->day];
        }

        if(isset($request->class_start_time)) {   
            $whereData[] = ['class_start_time', $request->class_start_time];
        }

        if(isset($request->class_end_time)) {   
            $whereData[] = ['class_end_time', $request->class_end_time];
        }
        
        if(isset($request->break_start_time)) {   
            $whereData[] = ['break_start_time', $request->break_start_time];
        }
        if(isset($request->break_end_time)) {   
            $whereData[] = ['break_end_time', $request->break_end_time];
        }
        
        if(isset($request->is_enable)) {   
            $whereData[] = ['is_enable', $request->is_enable];
        }
        
        $data_set = TeacherTimeTable::where($whereData)
           ->where($whereData)
            ->active()
            ->get($select);
        
        
        $data_result = [];
        $status = 200;
        $data_result['campus'] = $data_set->toArray();
        
        $response = Utilities::buildSuccessResponse(10004, "TeacherTimeTable List.", $data_result);

        return response()->json($response, $status); 
    }
}
