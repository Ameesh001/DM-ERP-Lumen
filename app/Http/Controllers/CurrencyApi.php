<?php

namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\Currency;
use Illuminate\Http\Request;
use App\Utilities\Utilities;

class CurrencyApi extends Controller
{

    /**
     * Constructor
     */
    public function __construct()
    {

    }

    private $select_columns = ['id','currency_name', 'currency_symbol', 'is_enable as activate'];

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
       
        $mdlList = new Currency();
        $this->validate($request, $mdlList->rules($request), $mdlList->messages($request));
        $mdlList->filterColumns($request);
        Utilities::defaultAddAttributes($request, $request->data_user_id);
        $postArr = $request->all();
        $postArr['currency_name'] = trim($postArr['currency_name']);
        $postArr['currency_symbol'] = trim($postArr['currency_symbol']);
        $obj = Currency::create($postArr);
        $data = [ 'id' => $obj->id ];
        $response = Utilities::buildSuccessResponse(10000, "Currency successfully created.", $data);
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
        $mdlList = new Currency();
        $this->validate($request, $mdlList->rules($request), $mdlList->messages($request));
        $mdlList->filterColumns($request);
        Utilities::defaultUpdateAttributes($request, $request->data_user_id);
        $Currency = Currency::find($request->id);
        $postArr = $request->all();
        $postArr['currency_name'] = trim($postArr['currency_name']);
        $postArr['currency_symbol'] = trim($postArr['currency_symbol']);
        $status = 200;
        $response = [];
        if (! $Currency) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Currency not found.");
        } else {
            $obj = $Currency->update($postArr);
            if ($obj) {
                $data = [ 'id' => $Currency->id ];
                $response = Utilities::buildSuccessResponse(10001, "Currency successfully updated.", $data);
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
        $mdlList = new Currency();
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
        if($request->currency_name) {   
            $whereData[] = ['currency_name', 'LIKE', "%{$request->currency_name}%"];
        }
        if($request->currency_symbol) {   
            $whereData[] = ['currency_symbol', 'LIKE', "%{$request->currency_symbol}%"];
        }
        if($request->activate != null) {
            $whereData[] = ['is_enable', $request->activate];
        }
        $orderBy =  $mdlList->getOrderColumn($request->order_by);
        $orderType = $request->order_type ?? Constant::OrderType;
        $total_record = Currency::where($whereData)->active()->count();
        $list = Currency::where($whereData)
            ->active()
            ->orderBy($orderBy, $orderType)
            ->offset($skip)
            ->limit($pageSize)
            ->get($select);
        $status = 200;
        $data_result = [];
        $data_result['list'] = $list->toArray();
        $data_result['total_record'] = $total_record;
        $response = Utilities::buildSuccessResponse(10004, "Currency List.", $data_result);
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
        $mdlList = new Currency();
        $request->request->add([ 'id' => $id ]);
        $this->validate($request, $mdlList->rules($request, Constant::RequestType['GET_ONE']), $mdlList->messages($request, Constant::RequestType['GET_ONE']));
        $select = $this->select_columns;
        $mdlList->filterColumns($request);
        if($request->fields){
            $select = $request->fields;
        }
        $list = Currency::where('id', $request->id)->first($select);
        $status = 200;
        $response = [];
        if (! $list) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Currency not found.");
        } else {
            $dataResult = array("list" => $list->toArray());
            $response = Utilities::buildSuccessResponse(10005, "Currency Data.", $dataResult);
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
        $mdlList = new Currency();
        Utilities::removeAttributesExcept($request, ["id","activate"]);
        $this->validate($request, $mdlList->rules($request), $mdlList->messages($request));
        Utilities::defaultUpdateAttributes($request, $request->data_user_id);
        $activate = $request->activate == 1 ? Constant::RecordType['ENABLED'] : Constant::RecordType['DISABLED'];
        $request->request->add([ 'is_enable' => $activate ]);
        $listFind = Currency::find($request->id);
        $status = 200;
        $response = [];
        if (! $listFind) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Currency not found.");
        } else {
            $obj = $listFind->update($request->all());
            if ($obj) {
                $data = ['id' => $listFind->id ];
                $actMsg = $request->activate == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "Currency successfully $actMsg.", $data);
            }
        }
        return response()->json($response, $status);
    }

    /**
     * Delete.
     *
     * @param $id 'ID' delete. (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id, Request $request)
    {
        $mdlList = new Currency();
        $request->request->add([ 'id' => $id ]);
        $this->validate($request, $mdlList->rules($request), $mdlList->messages($request));
        Utilities::defaultDeleteAttributes($request, 1);
        $request->request->add([
            'is_enable' => Constant::RecordType['DELETED']
        ]);
        $listFound = Currency::find($request->id);
        $status = 200;
        $response = [];
        if (! $listFound) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Currency not found.");
        } else {
            $obj = $listFound->update($request->all());
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "Currency successfully deleted.");
            }
        }
        return response()->json($response, $status);
    }

}
