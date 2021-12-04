<?php

/**
 * Darulmadinah Api

 * 
 * This is a Fee type  API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\FeeStructureDetail;
use Illuminate\Http\Request;
use App\Utilities\Utilities;
use App\Models\FeeStructureMaster;

class FeeStructureDetailApi extends Controller
{

    /**
     * Constructor
     */
    private $mdlName;
             
    public function __construct()
    {
        $this->mdlName = new FeeStructureDetail();
    }

    private $select_columns = ['id', 'fees_code', 'fees_master_id','fees_type_id','fees_amount','fees_from_date','fees_end_date', 'fees_is_new_addmission', 'is_enable'];

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

        if($request->fees_master_id=="NewCode") {
            
            $postArr2 = $request->all();
            $post_arr2['organization_id'] = $request->organization_id;            
            $master_obj = FeeStructureMaster::create($postArr2);  

            $master_data = FeeStructureMaster::find($master_obj->id); 
             

            $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
            $this->mdlName->filterColumns($request);
            $postArr = $request->all();
        
            $postArr['fees_master_id'] = $master_data->id;
            $postArr['fees_code'] = $master_data->fees_code;            
          

        }
        else{

            $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
            $this->mdlName->filterColumns($request);
            $postArr = $request->all();

        }   


        
        
        Utilities::defaultAddAttributes($request, $request->data_user_id);
        $obj = FeeStructureDetail::create($postArr);
        $data = [ 'id' => $obj->id ];

        $msg=  "Fee Type successfully created with code: ";
        $msg .=  $obj->fees_code;

        $response = Utilities::buildSuccessResponse(10000,$msg, $data);
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
        $result_set = FeeStructureDetail::find($request->id);
        $postArr = $request->all();
        // $postArr['fee_type'] = trim($postArr['fee_type']);
        // $postArr['fee_desc'] = trim($postArr['fee_desc']);
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
     * is_enable/De-is_enable.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function patchDisableEnable(Request $request)
    {
        Utilities::removeAttributesExcept($request, ["id","is_enable"]);
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        Utilities::defaultUpdateAttributes($request, $request->data_user_id);
        $is_enable = $request->is_enable == 1 ? Constant::RecordType['ENABLED'] : Constant::RecordType['DISABLED'];
        $request->request->add([ 'is_enable' => $is_enable ]);
        $result_set = FeeStructureDetail::find($request->id);
        $status = 200;
        $response = [];
        if (! $result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Fee Type not found.");
        } else {
            $obj = $result_set->update($request->all());
            if ($obj) {
                $data = ['id' => $result_set->id ];
                $actMsg = $request->is_enable == 1 ? "activated" : "de-activated";
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
        $result_set = FeeStructureDetail::find($request->id);
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "FeeStructureDetail not found.");
        } else {
            $obj = $result_set->update($request->all());
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "FeeStructureDetail successfully deleted.");
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
        $result_set = FeeStructureDetail::where('id', $request->id)->first($select);
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "FeeStructureDetail not found.");
        } else {
            $dataResult = array("data_set" => $result_set->toArray());
            $response = Utilities::buildSuccessResponse(10005, "FeeStructureDetail Data.", $dataResult);
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
      
        if($request->is_enable != null) {
            $whereData[] = ['is_enable', $request->is_enable];
        }

        if($request->fees_amount) {   
            $whereData[] = ['fees_amount', $request->fees_amount];
        }

        if($request->fees_code) {   
            $whereData[] = ['fees_code', $request->fees_code];
        }

        if($request->fees_type_id) {   
            $whereData[] = ['fees_type_id', $request->fees_type_id];
        }

        if($request->fees_from_date) {   
            $whereData[] = ['fees_from_date', $request->fees_from_date];
        }
        if($request->fees_end_date) {   
            $whereData[] = ['fees_end_date', $request->fees_end_date];
        }
        if($request->fees_is_new_addmission) {   
            $whereData[] = ['fees_is_new_addmission', $request->fees_is_new_addmission];
        }

        if($request->data_organization_id) {   
            $whereDataStd[] = ['organization_id', $request->data_organization_id];
        }    

        $orderBy =  $request->order_by ?? Constant::OrderBy;
        $orderType = $request->order_type ?? Constant::OrderType;

        $data_set_q = FeeStructureDetail::with('FeesMaster','FeesType')->where($whereData)
            ->active()
            ->orderBy($orderBy, $orderType)
            ->offset($skip)
            ->limit($pageSize);
            
        $data_set_q->whereHas('FeesMaster', function($q) use ($whereDataStd){
            $q->where($whereDataStd);    
        });

        $data_set =  $data_set_q->get($select);
                
        $data_result = [];
        $status = 200;
        $data_result['data_list'] = $data_set->toArray();
        $data_result['total_record'] = $data_set_q->count();
        $response = Utilities::buildSuccessResponse(10004, "FeeStructureDetail List.", $data_result);
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
        
       
        if($request->id) {   
            $whereData[] = ['id', $request->id];
        }
        
        if($request->organization_id) {   
            $whereData[] = ['organization_id', $request->organization_id];
        }

        if($request->data_organization_id) {   
            $whereData[] = ['organization_id', $request->data_organization_id];
        }

        if($request->fees_amount) {   
            $whereData[] = ['fees_amount', $request->fees_amount];
        }

        if($request->fees_code) {   
            $whereData[] = ['fees_code', $request->fees_code];
        }

        if($request->fees_amount) {   
            $whereData[] = ['fees_amount', $request->fees_amount];
        }
        
        if($request->is_enable != null) {
            $whereData[] = ['is_enable', $request->is_enable];
        }
        
        $data_set = FeeStructureDetail::where($whereData)
            ->active()
            ->get($select);
        
        $data_result = [];
        $status = 200;
        $data_result['list'] = $data_set->toArray();
        
        $response = Utilities::buildSuccessResponse(10004, "FeeStructureDetail List.", $data_result);
        return response()->json($response, $status); 
    }
}
