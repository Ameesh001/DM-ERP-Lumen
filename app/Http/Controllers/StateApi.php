<?php

namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\State;
use App\Response\StateResponse;
use Illuminate\Http\Request;
use App\Utilities\Utilities;

class StateApi extends Controller
{

    /**
     * Constructor
     */
    public function __construct()
    {

    }

    private $select_columns = ['id','countries_id', 'state_name', 'is_enable as activate'];

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
       
        $mdlList = new State();
        $this->validate($request, $mdlList->rules($request), $mdlList->messages($request));
        $mdlList->filterColumns($request);
        Utilities::defaultAddAttributes($request, $request->data_user_id);
        $obj = State::create($request->all());
        $data = [ 'id' => $obj->id ];
        $response = Utilities::buildSuccessResponse(10000, "State successfully created.", $data);
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
        $mdlList = new State();
        $this->validate($request, $mdlList->rules($request), $mdlList->messages($request));
        $mdlList->filterColumns($request);
        Utilities::defaultUpdateAttributes($request, $request->data_user_id);
        $State = State::find($request->id);
        $status = 200;
        $response = [];
        if (! $State) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "State not found.");
        } else {
            $obj = $State->update($request->all());
            if ($obj) {
                $data = [ 'id' => $State->id ];
                $response = Utilities::buildSuccessResponse(10001, "State successfully updated.", $data);
            }
        }
        return response()->json($response, $status);
    }

    /**
     * Fetch list of State by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll(Request $request)
    {   
        $mdlList = new State();
        $this->validate($request, $mdlList->rules($request, Constant::RequestType['GET_ALL']), $mdlList->messages($request, Constant::RequestType['GET_ALL']));
        $pageSize = $request->limit ?? Constant::PageSize;
        if($pageSize > Constant::MaxPageSize){
            $pageSize = Constant::PageSize;
        }
        $page = $request->page ?? Constant::Page;
        $skip = ($page - 1) * $pageSize;
        $select = $this->select_columns;
        $mdlList->filterColumns($request);

        Utilities::set_all_data_permission_request_parameters($request);
        $data_country_id     = $request->data_p_country_id;
        $data_state_id       = $request->data_p_state_id;

        if($request->fields){
            $select = $request->fields;
        }
        $whereData = array();
        if($request->state_name) {   
            $whereData[] = ['state_name', 'LIKE', "%{$request->state_name}%"];
        }
        if($request->countries_id) {   
            $whereData[] = ['countries_id', $request->countries_id];
        }
        if($request->activate != null) {
            $whereData[] = ['is_enable', $request->activate];
        }
        $orderBy =  $mdlList->getOrderColumn($request->order_by);
        $orderType = $request->order_type ?? Constant::OrderType;

       


        $total_record_q = State::where($whereData);

        if(!empty($data_country_id)){
            $total_record_q->where(function($query) use ($data_country_id) {
                foreach ($data_country_id as $key => $value) {
                    $query->orWhere('countries_id', '=', $value);
                }
            });
        }

        $total_record = $total_record_q->active()->count();

        $queryObj = State::with('Country');
        $queryObj->where($whereData);
        $queryObj->active();
        $queryObj->orderBy($orderBy, $orderType);
        $queryObj->offset($skip);
        $queryObj->limit($pageSize);

        // if(!empty($data_state_id)){
        //     $queryObj->where(function($query) use ($data_state_id) {
        //         foreach ($data_state_id as $key => $value) {
        //             $query->orWhere('id', '=', $value);
        //         }
        //     });
        // }
        
         if(!empty($data_country_id)){
            $queryObj->where(function($query) use ($data_country_id) {
                foreach ($data_country_id as $key => $value) {
                    $query->orWhere('countries_id', '=', $value);
                }
            });
        }
        


        $list = $queryObj->active()->get($select);


        $status = 200;
        $data_result = array("list" => $list->toArray());
        $data_result['total_record'] = $total_record;
        $response = Utilities::buildSuccessResponse(10004, "State List.", $data_result);
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
        $mdlList = new State();
        $request->request->add([ 'id' => $id ]);
        $this->validate($request, $mdlList->rules($request, Constant::RequestType['GET_ONE']), $mdlList->messages($request, Constant::RequestType['GET_ONE']));
        $select = $this->select_columns;
        $mdlList->filterColumns($request);
        if($request->fields){
            $select = $request->fields;
        }
        $list = State::where('id', $request->id)->first($select);
        $list->Country;
        $status = 200;
        $response = [];
        if (! $list) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "State not found.");
        } else {
            $dataResult = array("state" => $list->toArray());
            $response = Utilities::buildSuccessResponse(10005, "State Data.", $dataResult);
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
        $mdlList = new State();
        Utilities::removeAttributesExcept($request, ["id","activate"]);
        $this->validate($request, $mdlList->rules($request), $mdlList->messages($request));
        Utilities::defaultUpdateAttributes($request, $request->data_user_id);
        $activate = $request->activate == 1 ? Constant::RecordType['ENABLED'] : Constant::RecordType['DISABLED'];
        $request->request->add([ 'is_enable' => $activate ]);
        $State = State::find($request->id);
        $status = 200;
        $response = [];
        if (! $State) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "State not found.");
        } else {
            $obj = $State->update($request->all());
            if ($obj) {
                $data = ['id' => $State->id ];
                $actMsg = $request->activate == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "State successfully $actMsg.", $data);
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
        $mdlList = new State();
        $request->request->add([ 'id' => $id ]);
        $this->validate($request, $mdlList->rules($request), $mdlList->messages($request));
        Utilities::defaultDeleteAttributes($request, 1);
        $request->request->add([
            'is_enable' => Constant::RecordType['DELETED']
        ]);
        $State = State::find($request->id);
        $status = 200;
        $response = [];
        if (! $State) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "State not found.");
        } else {
            $obj = $State->update($request->all());
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "State successfully deleted.");
            }
        }
        return response()->json($response, $status);
    }
    
    public function getState(Request $request)
    {
        $mdlList = new State();
       
        $select = $this->select_columns;
        
        if($request->fields){
            $select = $request->fields;
        }
        $whereData = array();
        
        if($request->state_name) {   
            $whereData[] = ['state_name', 'LIKE', "%{$request->state_name}%"];
        }
           
        if ($request->countries_id) {
            if(strlen($request->countries_id) > 1){
               $countries_id_arr = explode(",", $request->countries_id);
            }else{
               $whereData[] = ['countries_id', $request->countries_id];
            }
        }
        
        if($request->id) {   
            $whereData[] = ['id', $request->id];
        }
        if($request->is_enable != null) {
            $whereData[] = ['is_enable', $request->is_enable];
        }
        else{
            $whereData[] = ['is_enable', 1];
        }
        
        Utilities::set_all_data_permission_request_parameters($request);
        $data_country_id     = $request->data_p_country_id;
        $data_state_id       = $request->data_p_state_id;
        
        
        $queryObj = State::with('Country')
                    ->where($whereData)
                    ->active();
        
        if(!empty($data_state_id)){
            $queryObj->where(function($query) use ($data_state_id) {
                foreach ($data_state_id as $key => $value) {
                    $query->orWhere('id', '=', $value);
                }
            });
        }
        
         if(!empty($data_country_id)){
            $queryObj->where(function($query) use ($data_country_id) {
                foreach ($data_country_id as $key => $value) {
                    $query->orWhere('countries_id', '=', $value);
                }
            });
        }
        
        
        if(!empty($countries_id_arr)){
           $queryObj->whereIn('countries_id', $countries_id_arr);
        }
        
        $result_set = $queryObj->get($select);
        
        $data_result['state'] = $result_set->toArray();
        
        $status = 200;
        $response = Utilities::buildSuccessResponse(10004, "State List.", $data_result);
        return response()->json($response, $status);   
    }
    

}
