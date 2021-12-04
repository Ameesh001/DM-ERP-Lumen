<?php

namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\Classes;
use Illuminate\Http\Request;
use App\Utilities\Utilities;

class ClassesApi extends Controller
{

    /**
     * Constructor
     */
    public function __construct()
    {

    }

    private $select_columns = ['id','class_name', 'class_desc', 'is_enable as activate'];

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
       
        $mdlList = new Classes();
        $this->validate($request, $mdlList->rules($request), $mdlList->messages($request));
        $mdlList->filterColumns($request);
        Utilities::defaultAddAttributes($request, $request->data_user_id);
        $postArr = $request->all();
        $postArr['class_name'] = trim($postArr['class_name']);
        $postArr['class_desc'] = trim($postArr['class_desc']);
        $obj = Classes::create($postArr);
        $data = [ 'id' => $obj->id ];
        $response = Utilities::buildSuccessResponse(10000, "Classes successfully created.", $data);
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
        $mdlList = new Classes();
        $this->validate($request, $mdlList->rules($request), $mdlList->messages($request));
        $mdlList->filterColumns($request);
        Utilities::defaultUpdateAttributes($request, $request->data_user_id);
        $Classes = Classes::find($request->id);
        $postArr = $request->all();
        $postArr['class_name'] = trim($postArr['class_name']);
        $postArr['class_desc'] = trim($postArr['class_desc']);
        $status = 200;
        $response = [];
        if (! $Classes) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Classes not found.");
        } else {
            $obj = $Classes->update($postArr);
            if ($obj) {
                $data = [ 'id' => $Classes->id ];
                $response = Utilities::buildSuccessResponse(10001, "Classes successfully updated.", $data);
            }
        }
        return response()->json($response, $status);
    }

    /**
     * Fetch list by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll(Request $request)
    {
        $mdlList = new Classes();
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
        if($request->class_name) {   
            $whereData[] = ['class_name', 'LIKE', "%{$request->class_name}%"];
        }
        if($request->class_desc) {   
            $whereData[] = ['class_desc', 'LIKE', "%{$request->class_desc}%"];
        }

        if($request->organization_id) {   
            $whereData[] = ['organization_id', $request->organization_id];
        }
        
        if($request->activate != null) {
            $whereData[] = ['is_enable', $request->activate];
        }
        $total_record = Classes::where($whereData)->active()->count();
        $orderBy =  $mdlList->getOrderColumn($request->order_by);
        $orderType = $request->order_type ?? Constant::OrderType;
        
        $list = Classes::where($whereData)
            ->active()
            ->orderBy($orderBy, $orderType)
            ->offset($skip)
            ->limit($pageSize)
            ->get($select);
        $status = 200;
        
        $dataResult = array("list" => $list->toArray());
        $dataResult['total_record'] = $total_record;
        $response = Utilities::buildSuccessResponse(10004, "Class List.", $dataResult);
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
        $mdlList = new Classes();
        $request->request->add([ 'id' => $id ]);
        $this->validate($request, $mdlList->rules($request, Constant::RequestType['GET_ONE']), $mdlList->messages($request, Constant::RequestType['GET_ONE']));
        $select = $this->select_columns;
        $mdlList->filterColumns($request);
        if($request->fields){
            $select = $request->fields;
        }
        $list = Classes::where('id', $request->id)->first($select);
        $status = 200;
        $response = [];
        if (! $list) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Classes not found.");
        } else {
            $dataResult = array("list" => $list->toArray());
            $response = Utilities::buildSuccessResponse(10005, "Class Data.", $dataResult);
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
        $mdlList = new Classes();
        Utilities::removeAttributesExcept($request, ["id","activate"]);
        $this->validate($request, $mdlList->rules($request), $mdlList->messages($request));
        Utilities::defaultUpdateAttributes($request, $request->data_user_id);
        $activate = $request->activate == 1 ? Constant::RecordType['ENABLED'] : Constant::RecordType['DISABLED'];
        $request->request->add([ 'is_enable' => $activate ]);
        $Classes = Classes::find($request->id);
        $status = 200;
        $response = [];
        if (! $Classes) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Classes not found.");
        } else {
            $obj = $Classes->update($request->all());
            if ($obj) {
                $data = ['id' => $Classes->id ];
                $actMsg = $request->activate == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "Classes successfully $actMsg.", $data);
            }
        }
        return response()->json($response, $status);
    }

    /**
     * Delete.
     *
     * @param $id 'ID'  delete. (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id, Request $request)
    {
        $mdlList = new Classes();
        $request->request->add([ 'id' => $id ]);
        $this->validate($request, $mdlList->rules($request), $mdlList->messages($request));
        Utilities::defaultDeleteAttributes($request, 1);
        $request->request->add([
            'is_enable' => Constant::RecordType['DELETED']
        ]);
        $Classes = Classes::find($request->id);
        $status = 200;
        $response = [];
        if (! $Classes) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Classes not found.");
        } else {
            $obj = $Classes->update($request->all());
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "Classes successfully deleted.");
            }
        }
        return response()->json($response, $status);
    }


    public function getClass(Request $request, $id = null)
    {
        // $mdlList = new Classes();
        //  $this->validate($request, $this->mdlList->rules($request, Constant::RequestType['GET_ALL']), $this->mdlList->messages($request, Constant::RequestType['GET_ALL']));
        
       
        $select =  $this->select_columns;
        
        if($request->fields){
            $select = $request->fields;
        }
        $whereData = array();

        if($request->organization_id) {   
            $whereData[] = ['organization_id', $request->organization_id];
        }
                     
        if($request->is_enable != null) {
            $whereData[] = ['is_enable', $request->is_enable];
        }
        else{
            $whereData[] = ['is_enable', 1];
        }
        $data_set = Classes::where($whereData)
            ->active()
            ->get($select); 
        $data_result = [];
        $status = 200;
        $data_result['class'] = $data_set->toArray();
        
        $response = Utilities::buildSuccessResponse(10004, "Class List.", $data_result);

        return response()->json($response, $status); 
    }

}
