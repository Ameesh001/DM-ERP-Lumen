<?php

/**
 * Performance system API
 * This is a Designation API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\Designation;
use Illuminate\Http\Request;
use App\Utilities\Utilities;

class DesignationApi extends Controller
{

    /**
     * Constructor
     */
    public function __construct()
    {

    }

    private $select_columns = ['id', 'desig_name', 'desig_desc', 'department_id','organization_id', 'is_enable as activate'];

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
     * Add Designation.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        $mdlList = new Designation();
        $this->validate($request, $mdlList->rules($request), $mdlList->messages($request));
        $mdlList->filterColumns($request);

        Utilities::defaultAddAttributes($request, $request->data_user_id);
        $post_arr = $request->all();
        // $post_arr['department_id'] =  1;
        $obj = Designation::create($post_arr);
        $data = [ 'id' => $obj->id ];
        $response = Utilities::buildSuccessResponse(10000, "Designation successfully created.", $data);
        return response()->json($response, 201);
    }

    /**
     * Update Designation.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $mdlList = new Designation();
        $this->validate($request, $mdlList->rules($request), $mdlList->messages($request));
        $mdlList->filterColumns($request);
        Utilities::defaultUpdateAttributes($request, $request->data_user_id);
        $Designation = Designation::find($request->id);
        $status = 200;
        $response = [];
        if (! $Designation) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Designation not found.");
        } else {
            $obj = $Designation->update($request->all());
            if ($obj) {
                $data = [ 'id' => $Designation->id ];
                $response = Utilities::buildSuccessResponse(10001, "Designation successfully updated.", $data);
            }
        }
        return response()->json($response, $status);
    }
    
    /**
     * Activate/De-Activate Designation.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function patchDisableEnable(Request $request)
    {
        $mdlList = new Designation();
        Utilities::removeAttributesExcept($request, ["id","activate"]);
        $this->validate($request, $mdlList->rules($request), $mdlList->messages($request));
        Utilities::defaultUpdateAttributes($request, $request->data_user_id);
        $activate = $request->activate == 1 ? Constant::RecordType['ENABLED'] : Constant::RecordType['DISABLED'];
        $request->request->add([ 'is_enable' => $activate ]);
        $Designation = Designation::find($request->id);
        $status = 200;
        $response = [];
        if (! $Designation) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Designation not found.");
        } else {
            $obj = $Designation->update($request->all());
            if ($obj) {
                $data = ['id' => $Designation->id ];
                $actMsg = $request->activate == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "Designation successfully $actMsg.", $data);
            }
        }
        return response()->json($response, $status);
    }

    /**
     * Delete Designation.
     *
     * @param $id 'ID' of Designation to delete. (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id, Request $request)
    {
        $mdlList = new Designation();
        $request->request->add([ 'id' => $id ]);
        $this->validate($request, $mdlList->rules($request), $mdlList->messages($request));
        Utilities::defaultDeleteAttributes($request, 1);
        $request->request->add([
            'is_enable' => Constant::RecordType['DELETED']
        ]);
        $Designation = Designation::find($request->id);
        $status = 200;
        $response = [];
        if (! $Designation) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Designation not found.");
        } else {
            $obj = $Designation->update($request->all());
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "Designation successfully deleted.");
            }
        }
        return response()->json($response, $status);
    }

    /**
     * Get one Designation.
     *
     * @param $id 'ID' of Designation to return (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOne($id, Request $request)
    {
        $mdlList = new Designation();
        $request->request->add([ 'id' => $id ]);
        $this->validate($request, $mdlList->rules($request, Constant::RequestType['GET_ONE']), $mdlList->messages($request, Constant::RequestType['GET_ONE']));
        $select = $this->select_columns;
        $mdlList->filterColumns($request);
        if($request->fields){
            $select = $request->fields;
        }
        $list = Designation::where('id', $request->id)->first($select);
        $list->Organization;
        $status = 200;
        $response = [];
        if (! $list) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Designation not found.");
        } else {
            $dataResult = array("list" => $list->toArray());
            $response = Utilities::buildSuccessResponse(10005, "Designation Data.", $dataResult);
        }
        return response()->json($response, $status);
    }

    /**
     * Fetch list of Designation by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll(Request $request)
    {
        $mdlList = new Designation();
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
        if($request->department_id) {   
            $whereData[] = ['department_id', $request->department_id];
        }

        if($request->organization_id) {   
            $whereData[] = ['organization_id', $request->organization_id];
        }
        if($request->desig_name) {   
            $whereData[] = ['desig_name', 'LIKE', "%{$request->desig_name}%"];
        }
        if($request->desig_desc) {   
            $whereData[] = ['desig_desc', 'LIKE', "%{$request->desig_desc}%"];
        }
        
        if($request->activate != null) {
            $whereData[] = ['is_enable', $request->activate];
        }
        $total_record = Designation::where($whereData)->active()->count();
        $orderBy =  $mdlList->getOrderColumn($request->order_by);
        $orderType = $request->order_type ?? Constant::OrderType;
       
       

        $queryObj = Designation::with('Department');
        $queryObj->where($whereData);
       
        $queryObj->active();
        $queryObj->orderBy($orderBy, $orderType);
        $queryObj->offset($skip);
        $queryObj->limit($pageSize);
        $list = $queryObj->get($select);

        $status = 200;
      
        $data_result = array("list" => $list->toArray());
        $data_result['total_record'] = $total_record;
        $response = Utilities::buildSuccessResponse(10004, "Designation List.", $data_result);
        return response()->json($response, $status);   
    }
    public function getAllMaster(Request $request)
    {
        $mdlList = new Designation();
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
        if($request->desig_name) {   
            $whereData[] = ['desig_name', 'LIKE', "%{$request->desig_name}%"];
        }
        if($request->desig_desc) {   
            $whereData[] = ['desig_desc', 'LIKE', "%{$request->desig_desc}%"];
        }
        
         if($request->id) {   
            $whereData[] = ['id', $request->id];
        }
        
        if($request->activate != null) {
            $whereData[] = ['is_enable', $request->activate];
        }else{
            $whereData[] = ['is_enable', 1];
        }
        
        $queryObj = Designation::where($whereData)
            ->active();
        
        if(!empty($organization_id_arr)){
            $queryObj->whereIn('organization_id', $organization_id_arr);
        }
        
        $list = $queryObj->get($select);
        $status = 200;
        
        $dataResult = array("list" => $list->toArray());
        $response = Utilities::buildSuccessResponse(10004, "Designation List.", $dataResult);
        return response()->json($response, $status);   
    }
}
