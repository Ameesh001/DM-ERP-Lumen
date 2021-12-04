<?php

/**
 * Performance system API
 * This is a Basic Info Field Mapping API controller
 *
 */

namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\BasicInfoFieldMap;
use App\Response\BasicInfoFieldMapResponse;
use Illuminate\Http\Request;
use App\Utilities\Utilities;

class BasicInfoFieldMapApi extends Controller
{

    /**
     * Constructor
     */
    public function __construct()
    {

    }

    private $select_columns = ['id', 'client_id', 'dept_id', 'basic_info_field_id', 'org_hier_level_id as hl_id', 'sorting as order', 'org_hier_level_id'];
    /**
     * This fucntion is called after validation fails in function $this->validate.
     * 
     * @param Request $request
     * @param array $errors
     * @return \Illuminate\Http\JsonResponse
     */

    protected function buildFailedValidationResponse(Request $request, array $errors)
    {
        $response = Utilities::buildFailedValidationResponse(10000, "Unprocesssable Entity.", $errors);
        return response()->json($response, 400);
    }

    /**
     * Add Basic Info Field Map.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addBasicInfoMap(Request $request)
    {
        $mdlBasicInfoFldMap = new BasicInfoFieldMap();

        $this->validate($request, $mdlBasicInfoFldMap->rules($request), $mdlBasicInfoFldMap->messages($request));
        $mdlBasicInfoFldMap->filterColumns($request);
        
        $mappingData = [];
        
        $obj = BasicInfoFieldMap::create($request->all());
        $data = ['id' => $obj->id];

        $response = Utilities::buildSuccessResponse(10000, "Basic Info Field Mapping successfully created.", $data);

        return response()->json($response, 201);
    }

    /**
     * Update Basic Info Field Map.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateBasicInfoMap(Request $request)
    {
        $mdlBasicInfoFldMap = new BasicInfoFieldMap();

        $this->validate($request, $mdlBasicInfoFldMap->rules($request), $mdlBasicInfoFldMap->messages($request));

        $mdlBasicInfoFldMap->filterColumns($request);

        $basicInfoFieldMap = BasicInfoFieldMap::find($request->id);

        $status = 200;
        $response = [];

        if (!$basicInfoFieldMap) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Basic Info Field Mapping not found.");
        } else {

            $obj = $basicInfoFieldMap->update($request->all());

            if ($obj) {
                $data = ['id' => $basicInfoFieldMap->id];
                $response = Utilities::buildSuccessResponse(10001, "Basic Info Field Mapping successfully updated.", $data);
            }
        }

        return response()->json($response, $status);
    }

        /**
     * Delete Basic Info Field Mapping.
     *
     * @param $id 'ID' of Basic Info Field Mapping to delete. (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteBasicInfoMap($id, Request $request)
    {
        $mdlBasicInfoFldMap = new BasicInfoFieldMap();
        
        $request->request->add([ 'id' => $id ]);
        
        $this->validate($request, $mdlBasicInfoFldMap->rules($request), $mdlBasicInfoFldMap->messages($request));
        
        $basicInfoFieldMap = BasicInfoFieldMap::find($request->id);
        
        $status = 200;
        $response = [];

        if (! $basicInfoFieldMap) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Basic Info Field Mapping not found.");
        } else {
            
            $obj = BasicInfoFieldMap::where('id', $request->id)->delete();

            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "Basic Info Field Mapping successfully deleted.");
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
    public function getOneBasicInfoMap($id, Request $request)
    {
        $mdlBasicInfoFldMap = new BasicInfoFieldMap();

        $request->request->add(['id' => $id]);

        $this->validate($request, $mdlBasicInfoFldMap->rules($request, Constant::RequestType['GET_ONE']), $mdlBasicInfoFldMap->messages($request, Constant::RequestType['GET_ONE']));

        $select = $this->select_columns;

        $mdlBasicInfoFldMap->filterColumns($request);

        if ($request->fields) {
            $select = $request->fields;
        }

        $basicInfoFieldMap = BasicInfoFieldMap::where('id', $request->id)->first($select);
        $basicInfoFieldMap->client ?? null;
        $basicInfoFieldMap->department ?? null;
        $basicInfoFieldMap->org_hierarchy_level ?? null;
        $basicInfoFieldMap->basic_info_field ?? null;

        $status = 200;
        $response = [];

        if (!$basicInfoFieldMap) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Basic Info Field Mapping not found.");
        } else {
            $dataResult = array("basicInfoFieldMap" => $basicInfoFieldMap->toArray());
            $response = Utilities::buildSuccessResponse(10005, "Basic Info Field Mapping Data.", $dataResult);
        }

        return response()->json($response, $status);
    }

    /**
     * Fetch list of Basic Info Field by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllBasicInfoMap(Request $request)
    {
        $mdlBasicInfoFldMap = new BasicInfoFieldMap();

        $this->validate($request, $mdlBasicInfoFldMap->rules($request, Constant::RequestType['GET_ALL']), $mdlBasicInfoFldMap->messages($request, Constant::RequestType['GET_ALL']));

        $pageSize = $request->limit ?? Constant::PageSize;
        if ($pageSize > Constant::MaxPageSize) {
            $pageSize = Constant::PageSize;
        }
        $page = $request->page ?? Constant::Page;
        $skip = ($page - 1) * $pageSize;


        $select = $this->select_columns;

        $mdlBasicInfoFldMap->filterColumns($request);

        if ($request->fields) {
            $select = $request->fields;
        }
        $whereData = array();

        if ($request->client_id != null) {
            $whereData[] = ['client_id', $request->client_id];
        }
        
        if ($request->dept_id != null) {
            $whereData[] = ['dept_id', $request->dept_id];
        }
        
        if ($request->basic_info_field_id != null) {
            $whereData[] = ['basic_info_field_id', $request->basic_info_field_id];
        }
        
        if ($request->hl_id != null) {
            $whereData[] = ['org_hier_level_id', $request->hl_id];
        }
        
        if ($request->order != null) {
            $whereData[] = ['sorting', $request->order];
        }
        
        $orderBy =  $mdlBasicInfoFldMap->getOrderColumn($request->order_by);
        $orderType = $request->order_type ?? Constant::OrderType;

        $basicInfoFieldMap = BasicInfoFieldMap::with('client', 'department', 'basic_info_field', 'org_hierarchy_level')
            ->where($whereData)
            ->orderBy($orderBy, $orderType)
            ->offset($skip)
            ->limit($pageSize)
            ->get($select);

        $status = 200;
        $data_result = new BasicInfoFieldMapResponse();
        $data_result->setBasicInfoFieldMap($basicInfoFieldMap->toArray());
        $response = Utilities::buildSuccessResponse(10004, "Basic Info Field Mapping List.", $data_result);

        return response()->json($response, $status);
    }
}
