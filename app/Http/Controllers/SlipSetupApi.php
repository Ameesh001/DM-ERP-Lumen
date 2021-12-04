<?php

/**
 * Darulmadinah Api
 * 
 * This is a SlipSetup API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\SlipSetup;
use App\Models\SessionMonth;
use Illuminate\Http\Request;
use App\Utilities\Utilities;
use Illuminate\Support\Facades\DB;

class SlipSetupApi extends Controller
{

    /**
     * Constructor
     */
    private $mdlName;
             
    public function __construct()
    {
        $this->mdlName = new SlipSetup();
    }
    
    private $select_columns  = [
        'id',
        'slip_type_id',
        'session_month_id',
        'month_index',
        'month_close_date',
        'issue_date',
        'due_date',
        'validity_date',
        'is_enable',
        'created_by',
        'created_at' ,
        'updated_by' ,
        'updated_at',
        'deleted_at' 
    ];

    private $select_columns2  = [
        'id',
        'session_id',
        'month_no',
        'month_name',
        'month_full_name',
        'year_no',
        'month_index',
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
     * Add SlipSetup.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        if($request->issue_date > $request->due_date){

            $response = Utilities::buildBaseResponse(70001, "Issue Date should be less than Due Date.");
            return response()->json($response, 200); 
            exit;
        }

        if($request->due_date > $request->month_close_date){

            $response = Utilities::buildBaseResponse(70001, "Due Date should be less than Month Close Date.");
            return response()->json($response, 200); 
            exit;
        }

        if($request->validity_date > $request->month_close_date){

            $response = Utilities::buildBaseResponse(70001, "Validity Date should be less than Month Close Date.");
            return response()->json($response, 200); 
            exit;
        }
        
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        
        $this->mdlName->filterColumns($request);
        
        Utilities::defaultAddAttributes($request, 1);         
            
        $post_arr = $request->all();
        
        $obj = SlipSetup::create($post_arr);
        
        $data = [ 'id' => $obj->id ];
        
        $response = Utilities::buildSuccessResponse(10000, "Voucher Generated Successfully.", $data);
        
        return response()->json($response, 201);
    }

    /**
     * Update SlipSetup.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {

        if($request->issue_date > $request->due_date){

            $response = Utilities::buildBaseResponse(70001, "Issue Date should be less than Due Date.");
            return response()->json($response, 200); 
            exit;
        }

        if($request->due_date > $request->month_close_date){

            $response = Utilities::buildBaseResponse(70001, "Due Date should be less than Month Close Date.");
            return response()->json($response, 200); 
            exit;
        }

        if($request->validity_date > $request->month_close_date){

            $response = Utilities::buildBaseResponse(70001, "Validity Date should be less than Month Close Date.");
            return response()->json($response, 200); 
            exit;
        }

        $user_id =   Utilities::getUserID_by_keycloak_id($request->keycloak_id);
        $mdlName = new SlipSetup();
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        
        $this->mdlName->filterColumns($request);
        
        Utilities::defaultUpdateAttributes($request, $user_id);
//
        $result_set = SlipSetup::find($request->id);
        
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
     * Activate/De-Activate SlipSetup.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function patchDisableEnable(Request $request)
    {

        if($request->is_enable==1){ 
            
            $allEnableRec = SessionMonth::where('is_enable', 1)
                            ->where('session_id', $request->session_id)->first('is_enable');            

            if(!empty($allEnableRec)){

              $response = Utilities::buildBaseResponse(70001, "Please De-activate other Month! ");
              return response()->json($response, 200); 
              exit;

            }
        }


 
        Utilities::removeAttributesExcept($request, ["id","is_enable"]);
        
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        
        Utilities::defaultUpdateAttributes($request, 1);
        
        $activate = $request->is_enable == 1 ? Constant::RecordType['ENABLED'] : Constant::RecordType['DISABLED'];
        
        $request->request->add([ 'is_enable' => $activate ]);
        
        $result_set = SessionMonth::find($request->id);

        $status = 200;
        $response = [];

        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Session Month not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $data = ['id' => $result_set->id ];
                $actMsg = $request->is_enable == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "Succeed $actMsg.", $data);
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Delete SlipSetup.
     *
     * @param $id 'ID' of SlipSetup to delete. (required)
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
        
        $result_set = SlipSetup::find($request->id);
        
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Voucher not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "Voucher successfully deleted.");
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Get one SlipSetup.
     *
     * @param $id 'ID' of SlipSetup to return (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOne($id, Request $request)
    {

        $this->mdlName = new SessionMonth();
        $request->request->add([ 'id' => $id ]);
        $this->validate($request, $this->mdlName->rules($request, Constant::RequestType['GET_ONE']), $this->mdlName->messages($request, Constant::RequestType['GET_ONE']));
        $select = $this->select_columns;
        $this->mdlName->filterColumns($request);
        if($request->fields){
            $select = $request->fields;
        }
        //$result_set = SlipSetup::where('id', $request->id)->first($select);
        $result_set = SessionMonth::with('Session','SlipSetup')->where('id', $request->id)->first();
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Voucher not found.");
        } else {
            $dataResult = array("data_set" => $result_set->toArray());
            $response = Utilities::buildSuccessResponse(10005, "Voucher Data.", $dataResult);
        }
        return response()->json($response, $status);
    }

    /**
     * Fetch list of SlipSetup by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function getAll(Request $request, $id = null)
    {
         $mdlName2;

        $this->mdlName2 = new SessionMonth();
       
        $this->validate($request, $this->mdlName2->rules($request, Constant::RequestType['GET_ALL']),
         $this->mdlName2->messages($request, Constant::RequestType['GET_ALL']));
        
        $pageSize = $request->limit ?? Constant::PageSize;
        
        if($pageSize > Constant::MaxPageSize){
            $pageSize = Constant::MaxPageSize;
        }
        
        $page = $request->page ?? Constant::Page;
        
        $skip = ($page - 1) * $pageSize;
        
        $select =  $this->select_columns2;
        
        if($request->fields){
            $select = $request->fields;
        }
        $whereData = array();
        
        
        if(isset($request->data_is_enable)) {   
            $whereData[] = ['is_enable', $request->data_is_enable];
        }

        if(isset($request->is_enable)) {   
            $whereData[] = ['is_enable', $request->is_enable];
        }

        if(isset($request->session_id)) {   
            $whereData[] = ['session_id', $request->session_id];
        }
        
        
        
        $mdlList2 = new SessionMonth();

        $total_record = SessionMonth::where($whereData)->active()->count();
        $orderBy =  $mdlList2->getOrderColumn($request->order_by);
        $orderType = $request->order_type ?? Constant::OrderType;
        
        //$queryObj = SessionMonth::with('Campus','TeacherName','Class','Subject');
        $queryObj = SessionMonth::with('Session','SlipSetup');
        $queryObj->where($whereData);
        $queryObj->active();
        $queryObj->orderBy($orderBy, $orderType);
        //$queryObj->offset($skip);
       // $queryObj->limit($pageSize);
        $list = $queryObj->get($select);
        $status = 200;
        // $data_result = new StateResponse();
        // $data_result->setState($list->toArray());

        $data_result = array("list" => $list->toArray());
        $data_result['total_record'] = $total_record;
        $response = Utilities::buildSuccessResponse(10004, "Voucher List.", $data_result);
        return response()->json($response, $status);  

        //return response()->json(SessionMonth::all());
    }
    
    /**
     * Fetch list of SessionMonth for selectboc/dropdown by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSlipSetup(Request $request, $id = null)
    {
        $this->validate($request, $this->mdlName->rules($request, Constant::RequestType['GET_ALL']), $this->mdlName->messages($request, Constant::RequestType['GET_ALL']));
        
       
        $select =  $this->select_columns;
        
        if($request->fields){
            $select = $request->fields;
        }
        $whereData = array();
        
        
        if($request->slip_type_id) {   
            $whereData[] = ['slip_type_id', $request->slip_type_id];
        }
        
        if($request->session_month_id) {   
            $whereData[] = ['session_month_id', $request->session_month_id];
        }
        
        if($request->month_index) {   
            $whereData[] = ['month_index', $request->month_index];
       
        }if($request->month_close_date) {   
            $whereData[] = ['month_close_date', $request->month_close_date];
        }

        if(isset($request->issue_date)) {   
            $whereData[] = ['issue_date', $request->issue_date];
        }

        if(isset($request->due_date)) {   
            $whereData[] = ['due_date', $request->due_date];
        }

        if(isset($request->validity_date)) {   
            $whereData[] = ['validity_date', $request->validity_date];
        }
        
        if(isset($request->is_enable)) {   
            $whereData[] = ['is_enable', $request->is_enable];
        }
        if(isset($request->data_is_enable)) {   
            $whereData[] = ['is_enable', $request->data_is_enable];
        }
        
        $data_set = SlipSetup::where($whereData)
           ->where($whereData)
            ->active()
            ->get($select);
        
        
        $data_result = [];
        $status = 200;
        $data_result['campus'] = $data_set->toArray();
        
        $response = Utilities::buildSuccessResponse(10004, "SlipSetup List.", $data_result);

        return response()->json($response, $status); 
    }
}
