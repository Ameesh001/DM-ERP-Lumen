<?php

namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\Subject;
use Illuminate\Http\Request;
use App\Utilities\Utilities;
use Illuminate\Support\Facades\DB;
class SubjectApi extends Controller
{

    /**
     * Constructor
     */
    public function __construct()
    {

    }

    private $select_columns = ['id','countries_id', 'state_id', 'subject_code', 'subject_name', 'subject_desc', 'is_enable as activate'];

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
     * Add .
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
       
        $mdlList = new Subject();
        $this->validate($request, $mdlList->rules($request), $mdlList->messages($request));
        $mdlList->filterColumns($request);
        Utilities::defaultAddAttributes($request, $request->data_user_id);
        $postArr = $request->all();
        $postArr['subject_code'] = trim($postArr['subject_code']);
        $postArr['subject_name'] = trim($postArr['subject_name']);
        $postArr['subject_desc'] = trim($postArr['subject_desc']);
        $obj = Subject::create($postArr);
        $data = [ 'id' => $obj->id ];
        $response = Utilities::buildSuccessResponse(10000, "Subject successfully created.", $data);
        return response()->json($response, 201);
    }

    /**
     * Update .
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $mdlList = new Subject();
        $this->validate($request, $mdlList->rules($request), $mdlList->messages($request));
        $mdlList->filterColumns($request);
        Utilities::defaultUpdateAttributes($request, $request->data_user_id);
        $Subject = Subject::find($request->id);
        $postArr = $request->all();
        $postArr['subject_code'] = trim($postArr['subject_code']);
        $postArr['subject_name'] = trim($postArr['subject_name']);
        $postArr['subject_desc'] = trim($postArr['subject_desc']);
        $status = 200;
        $response = [];
        if (! $Subject) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Subject not found.");
        } else {
            $obj = $Subject->update($postArr);
            if ($obj) {
                $data = [ 'id' => $Subject->id ];
                $response = Utilities::buildSuccessResponse(10001, "Subject successfully updated.", $data);
            }
        }
        return response()->json($response, $status);
    }

    /**
     * Fetch list searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll(Request $request)
    {
        $mdlList = new Subject();
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
        if($request->subject_code) {   
            $whereData[] = ['subject_code', 'LIKE', "%{$request->subject_code}%"];
        }
        if($request->subject_name) {   
            $whereData[] = ['subject_name', 'LIKE', "%{$request->subject_name}%"];
        }
        if($request->countries_id) {   
            $whereData[] = ['countries_id', $request->countries_id];
        }
        if($request->organization_id) {   
            $whereData[] = ['organization_id', $request->organization_id];
        }
        if($request->state_id) {   
            $whereData[] = ['state_id', $request->state_id];
        }
        if($request->activate != null) {
            $whereData[] = ['is_enable', $request->activate];
        }
        $total_record = Subject::where($whereData)->active()->count();
        $orderBy =  $mdlList->getOrderColumn($request->order_by);
        $orderType = $request->order_type ?? Constant::OrderType;
        $queryObj = Subject::with('Country' , 'State');
        // $queryObj = Subject::with('State');
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
    }

    /**
     * Get one List.
     *
     * @param $id 'ID' of List to return (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOne($id, Request $request)
    {
        $mdlList = new Subject();
        $request->request->add([ 'id' => $id ]);
        $this->validate($request, $mdlList->rules($request, Constant::RequestType['GET_ONE']), $mdlList->messages($request, Constant::RequestType['GET_ONE']));
        $select = $this->select_columns;
        $mdlList->filterColumns($request);
        if($request->fields){
            $select = $request->fields;
        }
        $list = Subject::where('id', $request->id)->first($select);
        $list->Country;
        $list->State;
        $status = 200;
        $response = [];
        if (! $list) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Subject not found.");
        } else {
            $dataResult = array("list" => $list->toArray());
            $response = Utilities::buildSuccessResponse(10005, "Subject Data.", $dataResult);
        }
        return response()->json($response, $status);
    }


    /**
     * Activate/De-Activate State.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function patchDisableEnable(Request $request)
    {
        $mdlList = new Subject();
        Utilities::removeAttributesExcept($request, ["id","activate"]);
        $this->validate($request, $mdlList->rules($request), $mdlList->messages($request));
        Utilities::defaultUpdateAttributes($request, $request->data_user_id);
        $activate = $request->activate == 1 ? Constant::RecordType['ENABLED'] : Constant::RecordType['DISABLED'];
        $request->request->add([ 'is_enable' => $activate ]);
        $Subject = Subject::find($request->id);
        $status = 200;
        $response = [];
        if (! $Subject) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Subject not found.");
        } else {
            $obj = $Subject->update($request->all());
            if ($obj) {
                $data = ['id' => $Subject->id ];
                $actMsg = $request->activate == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "Subject successfully $actMsg.", $data);
            }
        }
        return response()->json($response, $status);
    }

    /**
     * Delete.
     *
     * @param $id 'ID' of State to delete. (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id, Request $request)
    {
        $mdlList = new Subject();
        $request->request->add([ 'id' => $id ]);
        $this->validate($request, $mdlList->rules($request), $mdlList->messages($request));
        Utilities::defaultDeleteAttributes($request, 1);
        $request->request->add([
            'is_enable' => Constant::RecordType['DELETED']
        ]);
        $Subject = Subject::find($request->id);
        $status = 200;
        $response = [];
        if (! $Subject) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Subject not found.");
        } else {
            $obj = $Subject->update($request->all());
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "Subject successfully deleted.");
            }
        }
        return response()->json($response, $status);
    }

    /**
     * Fetch list searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllMaster(Request $request)
    {
        $mdlList = new Subject();
        
        $this->validate($request, $mdlList->rules($request, Constant::RequestType['GET_ALL']), $mdlList->messages($request, Constant::RequestType['GET_ALL']));
        
        $select = $this->select_columns;
        
        $mdlList->filterColumns($request);
        
        if($request->fields){
            $select = $request->fields;
        }
        
        $whereData = array();
        
        if($request->subject_code) {   
            $whereData[] = ['subject_code', 'LIKE', "%{$request->subject_code}%"];
        }
        if($request->subject_name) {   
            $whereData[] = ['subject_name', 'LIKE', "%{$request->subject_name}%"];
        }
        if($request->countries_id) {   
            $whereData[] = ['countries_id', $request->countries_id];
        }

        if($request->state_id) {   
            $whereData[] = ['state_id', $request->state_id];
        }

        if($request->data_state_id) {   
            $whereData[] = ['state_id', $request->data_state_id];
        }
        if($request->organization_id) {   
            $whereData[] = ['organization_id', $request->organization_id];
        }
        if($request->activate != null) {
            $whereData[] = ['is_enable', $request->activate];
        }else{
            $whereData[] = ['is_enable', 1];
        }
        
        if($request->id) {   
            $whereData[] = ['id', $request->id];
        }
       
 
        $queryObj = Subject::with('Country' , 'State');
        $queryObj->where($whereData);
        $queryObj->active();
        $list = $queryObj->get($select);
        $status = 200;

        $data_result = array("list" => $list->toArray());
     
        $response = Utilities::buildSuccessResponse(10004, "Subject List.", $data_result);
        return response()->json($response, $status);   
    }

}
