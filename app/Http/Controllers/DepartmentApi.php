<?php

/**
 * Performance system API
 * This is a Department API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Utilities\Utilities;

class DepartmentApi extends Controller
{

    /**
     * Constructor
     */
    public function __construct()
    {

    }

    private $select_columns = ['id', 'dept_name', 'dept_desc', 'organization_id', 'is_enable as activate'];

    /**
     * This fucntion is called after validation fails in function $this->validate.
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
     * Add Department.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        $user_id =   Utilities::getUserID_by_keycloak_id($request->keycloak_id);
       
        if ($user_id == 0) {
            $response = Utilities::buildSuccessResponse(10003, "User not found.",$request);
            return response()->json($response, 404);
            exit;
        }
    
        $mdlList = new Department();
       
        $this->validate($request, $mdlList->rules($request), $mdlList->messages($request));
        $mdlList->filterColumns($request);

        Utilities::defaultAddAttributes($request, $user_id);
        
       
        $post_arr = $request->all();
        
        $obj = Department::create($post_arr);
        $data = [ 'id' => $obj->id ];
        $response = Utilities::buildSuccessResponse(10000, "Department successfully created.", $data);
        return response()->json($response, 201);
    }

    /**
     * Update Department.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $mdlList = new Department();
        $this->validate($request, $mdlList->rules($request), $mdlList->messages($request));
        $mdlList->filterColumns($request);
        Utilities::defaultUpdateAttributes($request, $request->data_user_id);
        $Department = Department::find($request->id);
        $status = 200;
        $response = [];
        if (! $Department) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Department not found.");
        } else {
            $obj = $Department->update($request->all());
            if ($obj) {
                $data = [ 'id' => $Department->id ];
                $response = Utilities::buildSuccessResponse(10001, "Department successfully updated.", $data);
            }
        }
        return response()->json($response, $status);
    }
    
    /**
     * Activate/De-Activate Department.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function patchDisableEnable(Request $request)
    {
        $mdlList = new Department();
        Utilities::removeAttributesExcept($request, ["id","activate"]);
        $this->validate($request, $mdlList->rules($request), $mdlList->messages($request));
        Utilities::defaultUpdateAttributes($request, $request->data_user_id);
        $activate = $request->activate == 1 ? Constant::RecordType['ENABLED'] : Constant::RecordType['DISABLED'];
        $request->request->add([ 'is_enable' => $activate ]);
        $Department = Department::find($request->id);
        $status = 200;
        $response = [];
        if (! $Department) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Department not found.");
        } else {
            $obj = $Department->update($request->all());
            if ($obj) {
                $data = ['id' => $Department->id ];
                $actMsg = $request->activate == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "Department successfully $actMsg.", $data);
            }
        }
        return response()->json($response, $status);
    }

    /**
     * Delete Department.
     *
     * @param $id 'ID' of Department to delete. (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id, Request $request)
    {
        $mdlList = new Department();
        $request->request->add([ 'id' => $id ]);
        $this->validate($request, $mdlList->rules($request), $mdlList->messages($request));
        Utilities::defaultDeleteAttributes($request, 1);
        $request->request->add([
            'is_enable' => Constant::RecordType['DELETED']
        ]);
        $Department = Department::find($request->id);
        $status = 200;
        $response = [];
        if (! $Department) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Department not found.");
        } else {
            $obj = $Department->update($request->all());
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "Department successfully deleted.");
            }
        }
        return response()->json($response, $status);
    }

    /**
     * Get one Department.
     *
     * @param $id 'ID' of Department to return (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOne($id, Request $request)
    {
        $mdlList = new Department();
        $request->request->add([ 'id' => $id ]);
        $this->validate($request, $mdlList->rules($request, Constant::RequestType['GET_ONE']), $mdlList->messages($request, Constant::RequestType['GET_ONE']));
        $select = $this->select_columns;
        $mdlList->filterColumns($request);
        if($request->fields){
            $select = $request->fields;
        }
        $list = Department::where('id', $request->id)->first($select);
        $list->Organization;
        $status = 200;
        $response = [];
        if (! $list) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Department not found.");
        } else {
            $dataResult = array("list" => $list->toArray());
            $response = Utilities::buildSuccessResponse(10005, "Department Data.", $dataResult);
        }
        return response()->json($response, $status);
    }

    /**
     * Fetch list of Department by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll(Request $request)
    {
        $mdlList = new Department();
        $this->validate($request, $mdlList->rules($request, Constant::RequestType['GET_ALL']), $mdlList->messages($request, Constant::RequestType['GET_ALL']));
        $pageSize = $request->limit ?? Constant::PageSize;
        if($pageSize > Constant::MaxPageSize){
            $pageSize = Constant::PageSize;
        }
        $page = $request->page ?? Constant::Page;
        $skip = ($page - 1) * $pageSize;
        $select = $this->select_columns;
        $mdlList->filterColumns($request);
        if($request->fields){
            $select = $request->fields;
        }
        $whereData = array();
        if($request->organization_id) {   
            $whereData[] = ['organization_id', $request->organization_id];
        }
        if($request->dept_name) {   
            $whereData[] = ['dept_name', 'LIKE', "%{$request->dept_name}%"];
        }
        if($request->dept_desc) {   
            $whereData[] = ['dept_desc', 'LIKE', "%{$request->dept_desc}%"];
        }
        
        if($request->activate != null) {
            $whereData[] = ['is_enable', $request->activate];
        }
        $total_record = Department::where($whereData)->active()->count();
        $orderBy =  $mdlList->getOrderColumn($request->order_by);
        $orderType = $request->order_type ?? Constant::OrderType;
       
       

        $queryObj = Department::with('Organization');
        $queryObj->where($whereData);
       
        $queryObj->active();
        $queryObj->orderBy($orderBy, $orderType);
        $queryObj->offset($skip);
        $queryObj->limit($pageSize);
        $list = $queryObj->get($select);

        $status = 200;
      
        $data_result = array("list" => $list->toArray());
        $data_result['total_record'] = $total_record;
        $response = Utilities::buildSuccessResponse(10004, "Department List.", $data_result);
        return response()->json($response, $status);   
    }
    
    public function getAllMaster(Request $request)
    {
        $mdlList = new Department();
        $this->validate($request, $mdlList->rules($request, Constant::RequestType['GET_ALL']), $mdlList->messages($request, Constant::RequestType['GET_ALL']));
        
       
        $select = $this->select_columns;
        $mdlList->filterColumns($request);
        if($request->fields){
            $select = $request->fields;
        }
        
        $whereData = array();
        
        if ($request->organization_id) {
            if(strlen($request->organization_id) > 1){
               $organization_id_arr = explode(",", $request->organization_id);
            }else{
               $whereData[] = ['organization_id', $request->organization_id];
            }
        }
        
        if($request->dept_name) {   
            $whereData[] = ['dept_name', 'LIKE', "%{$request->dept_name}%"];
        }
        if($request->dept_desc) {   
            $whereData[] = ['dept_desc', 'LIKE', "%{$request->dept_desc}%"];
        }
        
         if($request->id) {   
            $whereData[] = ['id', $request->id];
        }
        
        if($request->activate != null) {
            $whereData[] = ['is_enable', $request->activate];
        }else{
            $whereData[] = ['is_enable', 1];
        }
        
        $queryObj = Department::where($whereData);
            
        
        if(!empty($organization_id_arr)){
            $queryObj->whereIn('organization_id', $organization_id_arr);
        }
        
        $queryObj->active();
        $list = $queryObj->get($select)->toArray();
        $status = 200;
        
        $dataResult = array("list" => $list);
        $response = Utilities::buildSuccessResponse(10004, "Section List.", $dataResult);
        return response()->json($response, $status);   
    }
}
