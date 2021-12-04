<?php

namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\Section;
use App\Models\CampusSection;
use Illuminate\Http\Request;
use App\Utilities\Utilities;

class SectionApi extends Controller
{

    /**
     * Constructor
     */
    public function __construct()
    {

    }

    private $select_columns = ['id','section_name', 'section_desc', 'is_enable as activate'];

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
       
        $mdlList = new Section();
        $this->validate($request, $mdlList->rules($request), $mdlList->messages($request));
        $mdlList->filterColumns($request);
        Utilities::defaultAddAttributes($request, $request->data_user_id);
        $obj = Section::create($request->all());
        $data = [ 'id' => $obj->id ];
        $response = Utilities::buildSuccessResponse(10000, "Section successfully created.", $data);
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
        $mdlList = new Section();
        $this->validate($request, $mdlList->rules($request), $mdlList->messages($request));
        $mdlList->filterColumns($request);
        Utilities::defaultUpdateAttributes($request, $request->data_user_id);
        $Section = Section::find($request->id);
        $status = 200;
        $response = [];
        if (! $Section) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Section not found.");
        } else {
            $obj = $Section->update($request->all());
            if ($obj) {
                $data = [ 'id' => $Section->id ];
                $response = Utilities::buildSuccessResponse(10001, "Section successfully updated.", $data);
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
        $mdlList = new Section();
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
        if($request->section_name) {   
            $whereData[] = ['section_name', 'LIKE', "%{$request->section_name}%"];
        }
        if($request->section_desc) {   
            $whereData[] = ['section_desc', 'LIKE', "%{$request->section_desc}%"];
        }
        
        if($request->activate != null) {
            $whereData[] = ['is_enable', $request->activate];
        }

        if($request->organization_id) {   
            $whereData[] = ['organization_id', $request->organization_id];
        }
        
        $total_record = Section::where($whereData)->active()->count();
        $orderBy =  $mdlList->getOrderColumn($request->order_by);
        $orderType = $request->order_type ?? Constant::OrderType;
       
        $list = Section::where($whereData)
            ->active()
            ->orderBy($orderBy, $orderType)
            ->offset($skip)
            ->limit($pageSize)
            ->get($select);
        $status = 200;
        
        $data_result = array("list" => $list->toArray());
        $data_result['total_record'] = $total_record;
        $response = Utilities::buildSuccessResponse(10004, "Section List.", $data_result);
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
        $mdlList = new Section();
        $request->request->add([ 'id' => $id ]);
        $this->validate($request, $mdlList->rules($request, Constant::RequestType['GET_ONE']), $mdlList->messages($request, Constant::RequestType['GET_ONE']));
        $select = $this->select_columns;
        $mdlList->filterColumns($request);
        if($request->fields){
            $select = $request->fields;
        }
        $list = Section::where('id', $request->id)->first($select);
        $status = 200;
        $response = [];
        if (! $list) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Section not found.");
        } else {
            $dataResult = array("list" => $list->toArray());
            $response = Utilities::buildSuccessResponse(10005, "Section Data.", $dataResult);
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
        $mdlList = new Section();
        Utilities::removeAttributesExcept($request, ["id","activate"]);
        $this->validate($request, $mdlList->rules($request), $mdlList->messages($request));
        Utilities::defaultUpdateAttributes($request, $request->data_user_id);
        $activate = $request->activate == 1 ? Constant::RecordType['ENABLED'] : Constant::RecordType['DISABLED'];
        $request->request->add([ 'is_enable' => $activate ]);
        $Section = Section::find($request->id);
        $status = 200;
        $response = [];
        if (! $Section) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Section not found.");
        } else {
            $obj = $Section->update($request->all());
            if ($obj) {
                $data = ['id' => $Section->id ];
                $actMsg = $request->activate == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "Section successfully $actMsg.", $data);
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
        $mdlList = new Section();
        $request->request->add([ 'id' => $id ]);
        $this->validate($request, $mdlList->rules($request), $mdlList->messages($request));
        Utilities::defaultDeleteAttributes($request, 1);
        $request->request->add([
            'is_enable' => Constant::RecordType['DELETED']
        ]);
        $Section = Section::find($request->id);
        $status = 200;
        $response = [];
        if (! $Section) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Section not found.");
        } else {
            $obj = $Section->update($request->all());
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "Section successfully deleted.");
            }
        }
        return response()->json($response, $status);
    }
    
    /**
     * Fetch list by searching with optional filters.. get all master section
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllMaster(Request $request)
    {
        $mdlList = new Section();
        $this->validate($request, $mdlList->rules($request, Constant::RequestType['GET_ALL']), $mdlList->messages($request, Constant::RequestType['GET_ALL']));
        
       
        $select = $this->select_columns;
        // $mdlList->filterColumns($request);
        if($request->fields){
            $select = $request->fields;
        }
        
        $whereData = array();
        $whereData[] = ['is_enable', 1];
        if($request->data_campus_id !== "null") {   
            $whereData[] = ['campus_id', $request->data_campus_id];
        }
        // if($request->section_name) {   
        //     $whereData[] = ['section_name', 'LIKE', "%{$request->section_name}%"];
        // }

        // if($request->organization_id) {   
        //     $whereData[] = ['organization_id', $request->organization_id];
        // }

        // if($request->section_desc) {   
        //     $whereData[] = ['section_desc', 'LIKE', "%{$request->section_desc}%"];
        // }
        
        //  if($request->id) {   
        //     $whereData[] = ['id', $request->id];
        // }
        
        // if($request->activate != null) {
        //     $whereData[] = ['is_enable', $request->activate];
        // }else{
        //     $whereData[] = ['is_enable', 1];
        // }
        $list = CampusSection::with('Section')->where($whereData)
        // $list = Section::where($whereData)
            ->active()
            ->get();
        $status = 200;
        
        $dataResult = array("list" => $list->toArray());
        $response = Utilities::buildSuccessResponse(10004, "Section List.", $dataResult);
        return response()->json($response, $status);   
    }


    /**
     * Fetch list by searching with All Active Section by Organization ID..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_active_section_by_org_id(Request $request)
    {  
        $whereData = array();
        $whereData[] = ['is_enable', 1];
        if($request->data_organization_id) {   
            $whereData[] = ['organization_id', $request->data_organization_id];
        }
        $list = Section::where($whereData)
            ->active()
            ->get();
        $status = 200;
        
        $data_result = array("list" => $list->toArray());
        $response = Utilities::buildSuccessResponse(10004, "Section List.", $data_result);
        return response()->json($response, $status);   
    }
    

}
