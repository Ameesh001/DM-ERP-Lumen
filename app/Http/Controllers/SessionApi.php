<?php

namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\Session;
use App\Models\SessionMonth;
use Illuminate\Http\Request;
use App\Utilities\Utilities;
use Carbon\Carbon;
use DateTime;
use DatePeriod;
use Illuminate\Support\Facades\DB;
use DateIntercal;
class SessionApi extends Controller
{

    /**
     * Constructor
     */
    public function __construct()
    {

    }

    private $select_columns = ['id','organization_id', 'session_name', 'start_date', 'end_date',  'start_month', 'end_month', 'start_year', 'end_year', 'is_enable as activate'];

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

        if($request->start_date != null && $request->end_date != null )
        {
            $ts1 = strtotime($request->start_date);
            $ts2 = strtotime($request->end_date);

            $year1 = date('Y', $ts1);
            $year2 = date('Y', $ts2);

            $month1 = date('m', $ts1);
            $month2 = date('m', $ts2);

            $diff = (($year2 - $year1) * 12) + ($month2 - $month1);
            
            if($diff != 11){ 

                $response = Utilities::buildBaseResponse(70001, "Please provide session of 12 month.");
                return response()->json($response, 200); 
                exit;
            }            
        }
       
        $mdlList = new Session();
        $this->validate($request, $mdlList->rules($request), $mdlList->messages($request));
        $mdlList->filterColumns($request);
        Utilities::defaultAddAttributes($request, $request->data_user_id);       

       
        $request->request->add([
            'start_month' => date('F', strtotime($request->start_date)),
            'end_month' => date('F', strtotime($request->end_date)),
            'start_year' => date('Y', strtotime($request->start_date)),
            'end_year' => date('Y', strtotime($request->end_date)),
            'session_name' =>  date('Y', strtotime($request->start_date)) . "-" . date('Y', strtotime($request->end_date))
        ]);

       
        try
        {
            DB::beginTransaction();

            $obj = Session::create($request->all());
            $data = [ 'id' => $obj->id ];

            //<Session Month Entry>
            if($obj->id != null){
            
            $Year = $request->start_year;

            for($i = 1;$i<13;$i++)
            {
                $dateObj   = DateTime::createFromFormat('!m', $month1);
                $monthName = $dateObj->format('F'); // March
                $shortMonth = substr($monthName, 0, 3); // Mar
                
                if(strlen($month1)<2){ 
                    $j = '0'.$month1;
                } else { 
                    $j = $month1;
                } 

                $request->request->add([                            
                    'session_id' => $obj->id,
                    'month_no' => $i,
                    'month_name' => $shortMonth,
                    'month_full_name' => $shortMonth.'-'.$Year,
                    'year_no' => $Year,
                    'month_index' => $Year.$j, 
                    'is_enable' => 0 
            ]);            
                SessionMonth::create($request->all());
                
                $j = null;
                $month1 ++;
                
                if($month1>12){ 
                    $month1=1; $Year++;
                }
            }        
        }
        // </Session Month Entry>        
        DB::commit();
    }
    catch(\Exception $e)
    {
        DB::rollback();
        $response = Utilities::buildBaseResponse(10003, $e."Transaction Failed Exam Subject. ");
    }


        $response = Utilities::buildSuccessResponse(10000, "Session successfully created.", $data);
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
        $mdlList = new Session();
        $this->validate($request, $mdlList->rules($request), $mdlList->messages($request));
        $mdlList->filterColumns($request);
        Utilities::defaultUpdateAttributes($request, $request->data_user_id);
        $Session = Session::find($request->id);
        $status = 200;
        $response = [];
        if (! $Session) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Session not found.");
        } else {
            $request->request->add([
                'start_month' => date('F', strtotime($request->start_date)),
                'end_month' => date('F', strtotime($request->end_date)),
                'start_year' => date('Y', strtotime($request->start_date)),
                'end_year' => date('Y', strtotime($request->end_date)),
                'session_name' =>  date('Y', strtotime($request->start_date)) . "-" . date('Y', strtotime($request->end_date))
            ]);
            $obj = $Session->update($request->all());
            if ($obj) {
                $data = [ 'id' => $Session->id ];
                $response = Utilities::buildSuccessResponse(10001, "Session successfully updated.", $data);
            }
        }
        return response()->json($response, $status);
    }

    /**
     * Fetch list of Session by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll(Request $request)
    {
        $mdlList = new Session();
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
        if($request->start_date) {   
            $whereData[] = ['start_date', $request->start_date];
        }
        if($request->end_date) {   
            $whereData[] = ['end_date', $request->end_date];
        }

        if($request->organization_id) {   
            $whereData[] = ['organization_id', $request->organization_id];
        }
        
        if($request->activate != null) {
            $whereData[] = ['is_enable', $request->activate];
        }
       
        $orderBy =  $mdlList->getOrderColumn($request->order_by);
        $orderType = $request->order_type ?? Constant::OrderType;
        $total_record = Session::where($whereData)->active()->count();
        $list = Session::where($whereData)
            ->active()
            ->orderBy($orderBy, $orderType)
            ->offset($skip)
            ->limit($pageSize)
            ->get($select);
        $status = 200;
        $data_result = [];
        $data_result['list'] = $list->toArray();
        $data_result['total_record'] = $total_record;
        $response = Utilities::buildSuccessResponse(10004, "Session List.", $data_result);
        return response()->json($response, $status);   
    }
    
    /**
     * Fetch list of Session by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllMaster(Request $request)
    {
        $mdlList = new Session();
        $this->validate($request, $mdlList->rules($request, Constant::RequestType['GET_ALL']), $mdlList->messages($request, Constant::RequestType['GET_ALL']));
        
        $select = $this->select_columns;
        $mdlList->filterColumns($request);
        
        if($request->fields){
            $select = $request->fields;
        }
        $whereData = array();
        
        if($request->start_date) {   
            $whereData[] = ['start_date', $request->start_date];
        }

        if($request->organization_id) {   
            $whereData[] = ['organization_id', $request->organization_id];
        }
        
        
        if($request->end_date) {   
            $whereData[] = ['end_date', $request->end_date];
        }
        
        if($request->id) {   
            $whereData[] = ['id', $request->id];
        }
        
        if($request->activate != null) {
            $whereData[] = ['is_enable', $request->activate];
        }else{
             $whereData[] = ['is_enable', 1];
        }
       
       
        $list = Session::where($whereData)
            ->active()
            ->get($select);
        $status = 200;
        $data_result = [];
        $data_result['list'] = $list->toArray();
        $response = Utilities::buildSuccessResponse(10004, "Session List.", $data_result);
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
        $mdlList = new Session();
        $request->request->add([ 'id' => $id ]);
        $this->validate($request, $mdlList->rules($request, Constant::RequestType['GET_ONE']), $mdlList->messages($request, Constant::RequestType['GET_ONE']));
        $select = $this->select_columns;
        $mdlList->filterColumns($request);
        if($request->fields){
            $select = $request->fields;
        }
        $list = Session::where('id', $request->id)->first($select);
        $status = 200;
        $response = [];
        if (! $list) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Session not found.");
        } else {
            $dataResult = array("list" => $list->toArray());
            $response = Utilities::buildSuccessResponse(10005, "Session Data.", $dataResult);
        }
        return response()->json($response, $status);
    }


    /**
     * Activate/De-Activate Session.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function patchDisableEnable(Request $request)
    {
        $mdlList = new Session();
        Utilities::removeAttributesExcept($request, ["id","activate"]);
        $this->validate($request, $mdlList->rules($request), $mdlList->messages($request));
        Utilities::defaultUpdateAttributes($request, $request->data_user_id);
        $activate = $request->activate == 1 ? Constant::RecordType['ENABLED'] : Constant::RecordType['DISABLED'];
        $request->request->add([ 'is_enable' => $activate ]);
        $Session = Session::find($request->id);
        $status = 200;
        $response = [];
        if (! $Session) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Session not found.");
        } else {
            $obj = $Session->update($request->all());
            if ($obj) {
                $data = ['id' => $Session->id ];
                $actMsg = $request->activate == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "Session successfully $actMsg.", $data);
            }
        }
        return response()->json($response, $status);
    }

    /**
     * Delete.
     *
     * @param $id 'ID' of List to delete. (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id, Request $request)
    {
        $mdlList = new Session();
        $request->request->add([ 'id' => $id ]);
        $this->validate($request, $mdlList->rules($request), $mdlList->messages($request));
        Utilities::defaultDeleteAttributes($request, 1);
        $request->request->add([
            'is_enable' => Constant::RecordType['DELETED']
        ]);
        $Session = Session::find($request->id);
        $status = 200;
        $response = [];
        if (! $Session) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Session not found.");
        } else {
            $obj = $Session->update($request->all());
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "Session successfully deleted.");
            }
        }
        return response()->json($response, $status);
    }


    public function getSession(Request $request){
        $mdlList = new Session();
       
        $select = $this->select_columns;
        
        if($request->fields){
            $select = $request->fields;
        }
        $whereData = array();
       
        if($request->countries_id) {   
            $whereData[] = ['campus_id', $request->campus_id];
        }
        if($request->id) {   
            $whereData[] = ['id', $request->id];
        }
        
        if($request->session_id) {   
            $whereData[] = ['id', $request->session_id];
        }

        if($request->organization_id) {   
            $whereData[] = ['organization_id', $request->organization_id];
        }
        
        if($request->is_enable != null) {
            $whereData[] = ['is_enable', $request->is_enable];
        }
        else{
            $whereData[] = ['is_enable', 1];
        }
       
        $result = Session::where($whereData)
                    ->active()
                    ->get($select);
        
        $data_result['session'] = $result->toArray();
        $status = 200;
        $response = Utilities::buildSuccessResponse(10004, "Session List.", $data_result);
        return response()->json($response, $status);  
    }


    // public function add2(Request $request)
    // {
    //      $request->request->add([
    //         'session_id' => 1,
    //         'month_no' => 3,
    //         'month_name' => 'Mar',
    //         'month_full_name' => 'Mar-2021',
    //         'year_no' => 2025,
    //         'month_index' => 11
    //    ]);
        
    //     $obj = SessionMonth::create($request->all());
    //     $data = [ 'id' => $obj->id ];        


    //     $response = Utilities::buildSuccessResponse(10000, "Session successfully created.", $data);
    //     return response()->json($response, 201);
    // }
}
