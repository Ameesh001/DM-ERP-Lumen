<?php

/**
 * Performance system API
 * This is a Kpi API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\BasicInfoField;
use App\Response\BasicInfoFieldResponse;
use Illuminate\Http\Request;
use App\Utilities\Utilities;

class BasicInfoFieldApi extends Controller
{

    /**
     * Constructor
     */
    public function __construct()
    {

    }
    private $select_columns = ['id', 'label', 'fieldname', 'input_type', 'is_enable as activate'];
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
     * Add Basic Info.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addBasicInfo(Request $request)
    {
        $mdlKpi = new BasicInfoField();
        
        $this->validate($request, $mdlKpi->rules($request), $mdlKpi->messages($request));
        
        $mdlKpi->filterColumns($request);
        
        Utilities::defaultAddAttributes($request, $request->data_user_id);
        
        $obj = BasicInfoField::create($request->all());
        
        $data = [ 'id' => $obj->id ];
        
        $response =Utilities::buildSuccessResponse(10000, "Basic Info Field successfully created.", $data);
        
        return response()->json($response, 201);
    }

    /**
     * Update Basic Info Field.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateBasicInfo(Request $request)
    {
        $mdlKpi = new BasicInfoField();
        
        $this->validate($request, $mdlKpi->rules($request), $mdlKpi->messages($request));
        
        $mdlKpi->filterColumns($request);
        
        Utilities::defaultAddAttributes($request, $request->data_user_id);

        $basicInfoField = BasicInfoField::find($request->id);
        
        $status = 200;
        $response = [];
        
        if (! $basicInfoField) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Basic Info Field not found.");
        } else {
            
            $obj = $basicInfoField->update($request->all());
            
            if ($obj) {
                $data = [ 'id' => $basicInfoField->id ];
                $response = Utilities::buildSuccessResponse(10001, "Basic Info Field successfully updated.", $data);
            }
        }
        
        return response()->json($response, $status);
    }
    
    /**
     * Activate/De-Activate Basic Info Field.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function patchDisableEnableBasicInfo(Request $request)
    {
        $mdlKpi = new BasicInfoField();

        Utilities::removeAttributesExcept($request, ["id","activate"]);
        
        $this->validate($request, $mdlKpi->rules($request), $mdlKpi->messages($request));
        
        Utilities::defaultAddAttributes($request, $request->data_user_id);
        
        $activate = $request->activate == 1 ? Constant::RecordType['ENABLED'] : Constant::RecordType['DISABLED'];
        
        $request->request->add([ 'is_enable' => $activate ]);
        
        $basicInfoField = BasicInfoField::find($request->id);

        $status = 200;
        $response = [];

        if (! $basicInfoField) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Basic Info Field not found.");
        } else {
            
            $obj = $basicInfoField->update($request->all());
            
            if ($obj) {
                $data = ['id' => $basicInfoField->id ];
                $actMsg = $request->activate == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "Basic Info Field successfully $actMsg.", $data);
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Delete Basic Info Field.
     *
     * @param $id 'ID' of Basic Info Field to delete. (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteBasicInfo($id, Request $request)
    {
        $mdlKpi = new BasicInfoField();
        
        $request->request->add([ 'id' => $id ]);
        
        $this->validate($request, $mdlKpi->rules($request), $mdlKpi->messages($request));
        
        Utilities::defaultDeleteAttributes($request, 1);
        
        $request->request->add([ 'is_enable' => Constant::RecordType['DELETED'] ]);
        
        $basicInfoField = BasicInfoField::find($request->id);
        
        $status = 200;
        $response = [];
        if (! $basicInfoField) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Basic Info Field not found.");
        } else {
            $obj = $basicInfoField->update($request->all());
            
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "Basic Info Field successfully deleted.");
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Get one Basic Info Field.
     *
     * @param $id 'ID' of Kpi to return (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOneBasicInfo($id, Request $request)
    {
        $mdlKpi = new BasicInfoField();
        
        $request->request->add([ 'id' => $id ]);
        
        $this->validate($request, $mdlKpi->rules($request, Constant::RequestType['GET_ONE']), $mdlKpi->messages($request, Constant::RequestType['GET_ONE']));
        
        $select = $this->select_columns;

        $mdlKpi->filterColumns($request);
        
        if($request->fields){
            $select = $request->fields;
        }
        
        $basicInfoField = BasicInfoField::where('id', $request->id)->first($select);
        
        $status = 200;
        $response = [];

        if (! $basicInfoField) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Basic Info Field not found.");
        } else {
            $dataResult = array("basicInfoField" => $basicInfoField->toArray());
            $response = Utilities::buildSuccessResponse(10005, "Basic Info Field Data.", $dataResult);
        }
        
        return response()->json($response, $status);
    }

    /**
     * Fetch list of Basic Info Field by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllBasicInfo(Request $request)
    {
        $mdlKpi = new BasicInfoField();
        
        $this->validate($request, $mdlKpi->rules($request, Constant::RequestType['GET_ALL']), $mdlKpi->messages($request, Constant::RequestType['GET_ALL']));
        
        $pageSize = $request->limit ?? Constant::PageSize;
        if($pageSize > Constant::MaxPageSize){
            $pageSize = Constant::PageSize;
        }
        $page = $request->page ?? Constant::Page;
        $skip = ($page - 1) * $pageSize;

        
        $select = $this->select_columns;

        $mdlKpi->filterColumns($request);
        
        if($request->fields){
            $select = $request->fields;
        }
        $whereData = array();
        
        if($request->label) {   
            $whereData[] = ['label', 'LIKE', "%{$request->label}%"];
        }
        if($request->fieldname) {   
            $whereData[] = ['fieldname', 'LIKE', "%{$request->fieldname}%"];
        }
        if($request->input_type != null) {   
            $whereData[] = ['input_type', $request->input_type];
        }
        if($request->activate != null) {   
            $whereData[] = ['is_enable', $request->activate];
        }
        
        $orderBy =  $mdlKpi->getOrderColumn($request->order_by);
        $orderType = $request->order_type ?? Constant::OrderType;
        
        $basicInfoField = BasicInfoField::where($whereData)
            ->active()
            ->orderBy($orderBy, $orderType)
            ->offset($skip)
            ->limit($pageSize)
            ->get($select);
        $status = 200;
        $data_result = new BasicInfoFieldResponse();
        $data_result->setBasicInfoField($basicInfoField->toArray());
        $response = Utilities::buildSuccessResponse(10004, "Basic Info Field List.", $data_result);
        
        return response()->json($response, $status);   
    }
}
