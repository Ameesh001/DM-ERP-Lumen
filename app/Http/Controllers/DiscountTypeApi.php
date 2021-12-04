<?php

/**
 * Darulmadinah Api

 * 
 * This is a Discount type  API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\DiscountType;
use Illuminate\Http\Request;
use App\Utilities\Utilities;

class DiscountTypeApi extends Controller
{

    /**
     * Constructor
     */
    private $mdlName;
             
    public function __construct()
    {
        $this->mdlName = new DiscountType();
    }

    private $select_columns = ['id', 'discount_type', 'discount_desc', 'is_enable', 'organization_id'];

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
        $postArr['discount_type'] = trim($postArr['discount_type']);
        $postArr['discount_desc'] = trim($postArr['discount_desc']);        
        $postArr['organization_id'] = trim($postArr['data_org_id']);
         
        // return response()->json($postArr, 200);
        // exit;
        
        Utilities::defaultAddAttributes($request, $request->data_user_id);
        $obj = DiscountType::create($postArr);
        $data = [ 'id' => $obj->id ];
        $response = Utilities::buildSuccessResponse(10000, "Discount Type successfully created.", $data);
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
        
        $postArr = $request->all();
        $postArr['discount_type'] = trim($postArr['discount_type']);
        $postArr['discount_desc'] = trim($postArr['discount_desc']);
        

        Utilities::defaultUpdateAttributes($request, $request->data_user_id);
        $result_set = DiscountType::find($request->id);
        $status = 200;
        $response = [];
        if (! $result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Discount Type not found.");
        } else {
            $obj = $result_set->update($postArr);
            if ($obj) {
                $data = [ 'id' => $result_set->id ];
                $response = Utilities::buildSuccessResponse(10001, "Discount Type successfully updated.", $data);
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
        $result_set = DiscountType::find($request->id);
        $status = 200;
        $response = [];
        if (! $result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Discount Type not found.");
        } else {
            $obj = $result_set->update($request->all());
            if ($obj) {
                $data = ['id' => $result_set->id ];
                $actMsg = $request->activate == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "Discount Type successfully $actMsg.", $data);
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
        $result_set = DiscountType::find($request->id);
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Discount type not found.");
        } else {
            $obj = $result_set->update($request->all());
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "Discount type successfully deleted.");
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
        $result_set = DiscountType::where('id', $request->id)->first($select);
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Discount type not found.");
        } else {
            $dataResult = array("data_set" => $result_set->toArray());
            $response = Utilities::buildSuccessResponse(10005, "Discount type Data.", $dataResult);
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
        
        if($request->discount_type) {   
            $whereData[] = ['discount_type', 'LIKE', "%{$request->discount_type}%"];
        }
        if($request->discount_desc) {   
            $whereData[] = ['discount_desc', 'LIKE', "%{$request->discount_desc}%"];
        }
        if($request->data_organization_id) {   
            $whereData[] = ['organization_id', 'LIKE', "%{$request->data_organization_id}%"];
        }
        if($request->activate != null) {
            $whereData[] = ['is_enable', $request->activate];
        }

     
        //  return response()->json($request->activate, 200);
       
        // exit;
        $total_record = DiscountType::where($whereData)->active()->count();
        $orderBy =  $request->order_by ?? Constant::OrderBy;
        $orderType = $request->order_type ?? Constant::OrderType;
        $data_set = DiscountType::where($whereData)
            ->active()
            ->orderBy($orderBy, $orderType)
            ->offset($skip)
            ->limit($pageSize)
            ->get($select);
        $data_result = [];
        $status = 200;
        $data_result['data_list'] = $data_set->toArray();
        $data_result['total_record'] = $total_record;
        $response = Utilities::buildSuccessResponse(10004, "Discount Type List.", $data_result);
        return response()->json($response, $status); 
    }
}
