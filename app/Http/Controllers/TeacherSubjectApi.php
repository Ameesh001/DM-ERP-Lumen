<?php

/**
 * Darulmadinah Api
 * 
 * This is a TeacherSubAssign API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\TeacherSubAssign;
use Illuminate\Http\Request;
use App\Utilities\Utilities;

class TeacherSubjectApi extends Controller
{

    /**
     * Constructor
     */
    private $mdlName;
             
    public function __construct()
    {
        $this->mdlName = new TeacherSubAssign();
    }
    
    private $select_columns  = [
        'id',
        'teacher_id',
        'campus_id',
        'class_id',
        'subject_id',
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
     * Add TeacherSubAssign.
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
            
        $post_arr = $request->all();
        $obj = TeacherSubAssign::create($post_arr);
        
        $data = [ 'id' => $obj->id ];
        
        $response = Utilities::buildSuccessResponse(10000, "Teacher assigned Successfully.", $data);
        
        return response()->json($response, 201);
    }

    /**
     * Update TeacherSubAssign.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $user_id =   Utilities::getUserID_by_keycloak_id($request->keycloak_id);
        $mdlName = new TeacherSubAssign();
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        
        $this->mdlName->filterColumns($request);
        
        Utilities::defaultUpdateAttributes($request, $user_id);
//
        $result_set = TeacherSubAssign::find($request->id);
        
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
     * Activate/De-Activate TeacherSubAssign.
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
        
        $result_set = TeacherSubAssign::find($request->id);
        $status = 200;
        $response = [];

        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "TeacherAssign not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $data = ['id' => $result_set->id ];
                $actMsg = $request->is_enable == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "TeacherAssign successfully $actMsg.", $data);
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Delete TeacherSubAssign.
     *
     * @param $id 'ID' of TeacherSubAssign to delete. (required)
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
        
        $result_set = TeacherSubAssign::find($request->id);
        
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "TeacherAssign not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "TeacherAssign successfully deleted.");
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Get one TeacherSubAssign.
     *
     * @param $id 'ID' of TeacherSubAssign to return (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOne($id, Request $request)
    {
        $mdlList = new TeacherSubAssign();
        $request->request->add([ 'id' => $id ]);
        $this->validate($request, $mdlList->rules($request, Constant::RequestType['GET_ONE']), $mdlList->messages($request, Constant::RequestType['GET_ONE']));
        $select = $this->select_columns;
        $mdlList->filterColumns($request);
        if($request->fields){
            $select = $request->fields;
        }
        $list = TeacherSubAssign::where('id', $request->id)->first($select);
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
     * Fetch list of TeacherSubAssign by searching with optional filters..
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
        
        
       
        if($request->teacher_id) {   
            $whereData[] = ['teacher_id', $request->teacher_id];
        }
        if($request->campus_id) {   
            $whereData[] = ['campus_id', $request->campus_id];
        }
        if($request->class_id) {   
            $whereData[] = ['class_id', $request->class_id];
        }
        
        if($request->subject_id) {   
            $whereData[] = ['subject_id', $request->subject_id];
        }
        
        if(isset($request->is_enable)) {   
            $whereData[] = ['is_enable', $request->is_enable];
        }
        



        $mdlList = new TeacherSubAssign();
        
        
        $total_record = TeacherSubAssign::where($whereData)->active()->count();
        $orderBy =  $mdlList->getOrderColumn($request->order_by);
        $orderType = $request->order_type ?? Constant::OrderType;
        
        $queryObj = TeacherSubAssign::with('Campus','TeacherName','Class','Subject');
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
        $response = Utilities::buildSuccessResponse(10004, "Subject List.", $data_result);
        return response()->json($response, $status);  

        //return response()->json(TeacherSubAssign::all());
    }
    
    /**
     * Fetch list of TeacherSubAssign for selectboc/dropdown by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTeacherSubAssign(Request $request, $id = null)
    {
        $this->validate($request, $this->mdlName->rules($request, Constant::RequestType['GET_ALL']), $this->mdlName->messages($request, Constant::RequestType['GET_ALL']));
        
       
        $select =  $this->select_columns;
        
        if($request->fields){
            $select = $request->fields;
        }
        $whereData = array();
        
        
        if($request->teacher_id) {   
            $whereData[] = ['teacher_id', $request->teacher_id];
        }
        if($request->campus_id) {   
            $whereData[] = ['campus_id', $request->campus_id];
        }
        if($request->class_id) {   
            $whereData[] = ['class_id', $request->class_id];
        }
        
        if($request->subject_id) {   
            $whereData[] = ['subject_id', $request->subject_id];
        }
//        
        if($request->id) {   
            $whereData[] = ['id', $request->id];
        }
        if($request->is_enable != null) {
            $whereData[] = ['is_enable', $request->is_enable];
        }
        
        $data_set = TeacherSubAssign::where($whereData)
           ->where($whereData)
            ->active()
            ->get($select);
        
        
        $data_result = [];
        $status = 200;
        $data_result['campus'] = $data_set->toArray();
        
        $response = Utilities::buildSuccessResponse(10004, "TeacherSubAssign List.", $data_result);

        return response()->json($response, $status); 
    }
}
