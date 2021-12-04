<?php

/**
 * Darulmadinah Api

 * 
 * This is a Fee type  API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\Sliptype;
use Illuminate\Http\Request;
use App\Utilities\Utilities;
use Illuminate\Support\Facades\DB;
use App\Response\SuccessResponse;
use App\Config\CleanJsonSerializer;

class SliptypeApi extends Controller
{

    /**
     * Constructor
     */
    private $mdlName;
             
    public function __construct()
    {
        $this->mdlName = new Sliptype();
    }

    private $select_columns = ['id', 'slip_type', 'is_enable'];

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
     * Add.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        $this->mdlName->filterColumns($request);
        $postArr = $request->all();
        $postArr['slip_type'] = trim($postArr['slip_type']);
        //return response()->json($postArr, 200);
        Utilities::defaultAddAttributes($request, $request->data_user_id);
        $obj = Sliptype::create($postArr);
        $data = [ 'id' => $obj->id ];
        $response = Utilities::buildSuccessResponse(10000, "Fee Type successfully created.", $data);
        return response()->json($response, 201);
    }

    /**
     * Update.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        $this->mdlName->filterColumns($request);
        Utilities::defaultUpdateAttributes($request, $request->data_user_id);
        $result_set = Sliptype::find($request->id);
        $postArr = $request->all();
        $postArr['slip_type'] = trim($postArr['slip_type']);
        $status = 200;
        $response = [];
        if (! $result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Fee Type not found.");
        } else {
             
            $obj = $result_set->update($postArr);
            if ($obj) {
                $data = [ 'id' => $result_set->id ];
                $response = Utilities::buildSuccessResponse(10001, "Fee Type successfully updated.", $data);
            }
        }
        return response()->json($response, $status);
    }
    
    /**
     * Activate/De-Activate.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function patchDisableEnable(Request $request)
    {
        Utilities::removeAttributesExcept($request, ["id","activate"]);
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        Utilities::defaultUpdateAttributes($request, $request->data_user_id);
        $activate = $request->activate == 1 ? Constant::RecordType['ENABLED'] : Constant::RecordType['DISABLED'];
        $request->request->add([ 'is_enable' => $activate ]);
        $result_set = Sliptype::find($request->id);
        $status = 200;
        $response = [];
        if (! $result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Fee Type not found.");
        } else {
            $obj = $result_set->update($request->all());
            if ($obj) {
                $data = ['id' => $result_set->id ];
                $actMsg = $request->activate == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "Fee Type successfully $actMsg.", $data);
            }
        }
        return response()->json($response, $status);
    }

    /**
     * Delete.
     *
     * @param $id 'ID'  to delete. (required)
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
        $result_set = Sliptype::find($request->id);
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Sliptype not found.");
        } else {
            $obj = $result_set->update($request->all());
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "Sliptype successfully deleted.");
            }
        }
        return response()->json($response, $status);
    }

    /**
     * Get one.
     *
     * @param $id 'ID' to return (required)
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
        $result_set = Sliptype::where('id', $request->id)->first($select);
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Sliptype not found.");
        } else {
            $dataResult = array("data_set" => $result_set->toArray());
            $response = Utilities::buildSuccessResponse(10005, "Sliptype Data.", $dataResult);
        }
        return response()->json($response, $status);
    }

    /**
     * Fetch list by searching with optional filters..
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
        $select = '*';
        if($request->fields){
            $select = $request->fields;
        }
        $whereData = array();
        
        if($request->slip_type) {   
            $whereData[] = ['slip_type', 'LIKE', "%{$request->slip_type}%"];
        }
        
        if($request->activate != null) {
            $whereData[] = ['is_enable', $request->activate];
        }
        if($request->organization_id) {   
            $whereData[] = ['organization_id', $request->organization_id];
        }
        //  return response()->json($request->activate, 200);
       
        // exit;
        $total_record = Sliptype::where($whereData)->active()->count();
        $orderBy =  $request->order_by ?? Constant::OrderBy;
        $orderType = $request->order_type ?? Constant::OrderType;
        $data_set = Sliptype::where($whereData)
            ->active()
            ->orderBy($orderBy, $orderType)
            ->offset($skip)
            ->limit($pageSize)
            ->get($select);
        $data_result = [];
        $status = 200;
        $data_result['data_list'] = $data_set->toArray();
        $data_result['total_record'] = $total_record;
        $response = Utilities::buildSuccessResponse(10004, "Sliptype List.", $data_result);
        return response()->json($response, $status); 
    }
    
    /**
     * Fetch list by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllMaster(Request $request, $id = null)
    {
        $this->validate($request, $this->mdlName->rules($request, Constant::RequestType['GET_ALL']), $this->mdlName->messages($request, Constant::RequestType['GET_ALL']));
        
        $select = '*';
        
        if($request->fields){
            $select = $request->fields;
        }
        $whereData = array();
        
        if($request->slip_type) {   
            $whereData[] = ['slip_type', 'LIKE', "%{$request->slip_type}%"];
        }
        
        if($request->id) {   
            $whereData[] = ['id', $request->id];
        }
        
        if($request->prefix) {   
            $whereData[] = ['prefix', $request->prefix];
        }
        
        if($request->organization_id) {   
            $whereData[] = ['organization_id', $request->organization_id];
        }
        
        if($request->data_organization_id) {   
            $whereData[] = ['organization_id', $request->data_organization_id];
        }
        
        if($request->is_enable != null) {
            $whereData[] = ['is_enable', $request->is_enable];
        }else{
            $whereData[] = ['is_enable', 1];
        }
        
        $data_set = Sliptype::where($whereData)
            ->active()
            ->get($select);
        
        $data_result = [];
        $status = 200;
        $data_result['list'] = $data_set->toArray();
        
        $response = Utilities::buildSuccessResponse(10004, "Sliptype List.", $data_result);
        return response()->json($response, $status); 
    }
    
    public function getSlipSetup(Request $request, $id = null)
    {
        $data_result = [];
        
        $slip_setup_q = DB::table(Constant::Tables['slip_setup'])->where('is_enable','=', 1);
        
        if(!empty($request->slip_type_id)){
            $slip_setup_q->where('slip_type_id', $request->slip_type_id); 
        }
        
        if(!empty($request->month_index)){
            $slip_setup_q->where('month_index', $request->month_index); 
        }
        
        $slip_setup_q->get('*');
        if($slip_setup_q->count() > 0){
           $slip_setup = $slip_setup_q->first();
           $data_result['list'] = $slip_setup;
        }
        $status = 200;
        return response()->json($data_result, $status); 
    }
}
