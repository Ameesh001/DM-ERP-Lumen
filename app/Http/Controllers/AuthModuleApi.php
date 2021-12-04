<?php

/**
 * Performance system API
 * This is a Module API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\AuthModule;
use App\Models\RolePermission;
use App\Response\AuthModuleResponse;
use Illuminate\Http\Request;
use App\Utilities\Utilities;
use Illuminate\Support\Facades\DB;

class AuthModuleApi extends Controller
{

    /**
     * Constructor
     */
    public function __construct()
    {

    }

    private $select_columns = ['id', 'name as ms_name', 'default_url as route', 'icon_class', 'parent_id as parent_module_id', 'allowed_permissions as permissions', 'sorting', 'is_enable as activate'];

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
     * Add Module.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addAuthModule(Request $request)
    {
        $mdlAuthModule = new AuthModule();
        
        $this->validate($request, $mdlAuthModule->rules($request), $mdlAuthModule->messages($request));
        
        $mdlAuthModule->filterColumns($request);
        
        Utilities::defaultAddAttributes($request, $request->data_user_id);
        
        $obj = AuthModule::create($request->all());
        
        $data = [ 'id' => $obj->id ];
        
        $response = Utilities::buildSuccessResponse(10000, "Module successfully created.", $data);
        
        return response()->json($response, 201);
    }
    
    /**
     * Update Module.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAuthModule(Request $request)
    {
        $mdlAuthModule = new AuthModule();
        
        $this->validate($request, $mdlAuthModule->rules($request), $mdlAuthModule->messages($request));
        
        $mdlAuthModule->filterColumns($request);
        
        Utilities::defaultUpdateAttributes($request, $request->data_user_id);

        $authModule = AuthModule::find($request->id);
        $status = 200;
        $response = [];

        if (! $authModule) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Module not found.");
        } else {
            $obj = $authModule->update($request->all());
            if ($obj) {
                $data = [ 'id' => $authModule->id ];
                $response = Utilities::buildSuccessResponse(10001, "Module successfully updated.", $data);
            }
        }
        
        return response()->json($response, $status);
    }
    
    /**
     * Activate/De-Activate Module.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function patchDisableEnableAuthModule(Request $request)
    {
        $mdlAuthModule = new AuthModule();

        $mdlAuthModule->removeAttributesExcept($request, ["id","activate"]);
        
        $this->validate($request, $mdlAuthModule->rules($request), $mdlAuthModule->messages($request));
        
        Utilities::defaultAddAttributes($request, $request->data_user_id);
        
        $activate = $request->activate == 1 ? Constant::RecordType['ENABLED'] : Constant::RecordType['DISABLED'];
        
        $request->request->add([ 'is_enable' => $activate ]);
        
        $language = AuthModule::find($request->id);

        $status = 200;
        $response = [];

        if (! $language) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Module not found.");
        } else {
            
            $obj = $language->update($request->all());
            
            if ($obj) {
                $data = [ 'id' => $language->id ];
                $actMsg = $request->activate == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "Module successfully $actMsg.", $data);
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Delete Module.
     *
     * @param $id 'ID' of Module to delete. (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAuthModule($id, Request $request)
    {
        $mdlAuthModule = new AuthModule();
        
        $request->request->add([ 'id' => $id ]);
        
        $this->validate($request, $mdlAuthModule->rules($request), $mdlAuthModule->messages($request));
        
        Utilities::defaultDeleteAttributes($request, 1);

        $request->request->add([ 'is_enable' => Constant::RecordType['DELETED'] ]);
        
        $authModule = AuthModule::find($request->id);

        $status = 200;
        $response = [];

        if (! $authModule) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Module not found.");
        } else {
            
            try{
               DB::beginTransaction();
           
                //Delete child record
                RolePermission::where('module_id', $request->id)->delete();
                $obj = $authModule->update($request->all());
                
                if ($obj) {
                    $response = Utilities::buildBaseResponse(10006, "Module successfully deleted.");
                }
            
                DB::commit();

            }catch(\Exception $e){
                DB::rollback();
               $response = Utilities::buildBaseResponse(10003, "Transaction Failed Module not Update. ");
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Get one Module.
     *
     * @param $id 'ID' of Module to return (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOneAuthModule($id, Request $request)
    {
        $mdlAuthModule = new AuthModule();
        
        $request->request->add([ 'id' => $id ]);
        
        $this->validate($request, $mdlAuthModule->rules($request, Constant::RequestType['GET_ONE']), $mdlAuthModule->messages($request,  Constant::RequestType['GET_ONE']));
        
        $select = $this->select_columns;

        $mdlAuthModule->filterColumns($request);
        
        if($request->fields){
            $select = $request->fields;
        }

        $authModule = AuthModule::where('id', $request->id)->first($select);

        $status = 200;
        $response = [];

        if (! $authModule) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Module not found.");
        } else {
            $dataResult = array("module" => $authModule->toArray());
            $response = Utilities::buildSuccessResponse(10005, "Module Data.", $dataResult);
        }
        
        return response()->json($response, $status);
    }

    /**
     * Fetch list of Module by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllAuthModule(Request $request)
    {
        $mdlAuthModule = new AuthModule();
        
        $this->validate($request, $mdlAuthModule->rules($request, Constant::RequestType['GET_ALL']), $mdlAuthModule->messages($request, Constant::RequestType['GET_ALL']));

        $select = $this->select_columns;
        
        $mdlAuthModule->filterColumns($request);

        if($request->fields){
            $select = $request->fields;
        }

        $pageSize = $request->limit ?? Constant::PageSize;
        if($pageSize > Constant::MaxPageSize){
            $pageSize = Constant::PageSize;
        }
        $page = $request->page ?? Constant::Page;
        $skip = ($page - 1) * $pageSize;

        $whereData = array('is_visible' => 1);
        
        if($request->ms_name) {   
            $whereData[] = ['name', 'LIKE', "%{$request->ms_name}%"];
        }
        if($request->route) {   
            $whereData[] = ['default_url', 'LIKE', "%{$request->route}%"];
        }
        if($request->icon_class) {   
            $whereData[] = ['icon_class', 'LIKE', "%{$request->icon_class}%"];
        }
        if($request->parent_module_id != null) {   
            $whereData[] = ['parent_id', $request->parent_module_id];
        }
        if($request->detail) {
            $whereData[] = ['detail', $request->detail];
        }
        if($request->activate != null) {
            $whereData[] = ['is_enable', $request->activate];
        }
        $total_record = AuthModule::where($whereData)->active()->count();
        $orderBy =  $request->order_by ?? Constant::OrderBy;
        $orderType = $request->order_type ?? Constant::OrderType;
        $data_set = AuthModule::where($whereData)
            ->active()
            ->orderBy($orderBy, $orderType)
            ->offset($skip)
            ->limit($pageSize)
            ->get($select);

        // $orderBy =  $mdlAuthModule->getOrderColumn($request->order_by);
        // $orderType = $request->order_type ?? Constant::OrderType;
        
        // $languages = AuthModule::where($whereData)
        //     ->active()
        //     ->orderBy($orderBy, $orderType)
        //     ->offset($skip)
        //     ->limit($pageSize)
        //     ->get($select);

        $status = 200;
        $data_result['modules'] = $data_set->toArray();
        $data_result['total_record'] = $total_record;
        $response = Utilities::buildSuccessResponse(10004, "Modules List.", $data_result);
        return response()->json($response, $status); 

        // $data_result = new AuthModuleResponse();
        // $data_result->setModules($languages->toArray());
        // $response = Utilities::buildSuccessResponse(10004, "Modules List.", $data_result);

        // return response()->json($response, $status);   
    }

    public function getAllModulesParents(Request $request)
    {
        $orderBy = $request->order_by ?? 'order';
        $orderType = $request->order_type ?? 'asc';

        $modulePermission = AuthModule::active()->where(['detail' => 1, 'is_visible' => 1])->orderBy($orderBy, $orderType)->get(['id', 'name as title', 'default_url as path','sorting as order' , 'icon_class as icon', 'parent_id', 'have_child', 'allowed_permissions as permissions']);

        if (!count($modulePermission)) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Modules not found.");
        } else {

            foreach ($modulePermission as $key => &$value) {
                $value['h_heading'] = $value['title']; 

                if(!empty($value->parent_id)){
                    $parentData = $this->getModuleParents($value->parent_id);

                    if($parentData){
                        foreach ($parentData as $prn => $parent) {
                            $value['h_heading'] = $parent['title'].' / '.$value['h_heading'];
                        }
                    }
                }                
                
                if($request->role_id){
                    $allowedPerms = RolePermission::where( [ 'role_id' => $request->role_id, 'module_id' => $value['id'] ] )->get(['route', 'action']);
                    if($allowedPerms){
                        $value['allowed_permissions'] = json_encode( array_column($allowedPerms->toArray(), 'route') );
                    }
                }

               
            }

            $status = 200;
            $response = [];

            $dataResult = array("roleModulePermission" => $modulePermission->toArray());
            $response = Utilities::buildSuccessResponse(10004, "Modules List.", $dataResult);
        }

        return response()->json($response, $status);
    }

    public function getModuleParents($id)
    {
        $column = ['parent_id', 'id', 'name as title'];
        $kpisTree = AuthModuleApi::buildHTree($id, [], $column);
        return $kpisTree;
    }
    
    public static function buildHTree($id, $data, $select) {
        if(!empty($id)){
            $child_q = AuthModule::where('id', $id)->first($select);
        
            if($child_q){
                $child = $child_q->toArray();
                
                $data[] = $child;
                if($child['parent_id'] > 0){
                    $data = self::buildHTree($child['parent_id'], $data, $select);
                }

                return $data;  
            }
        }
    }


    public function getAllModulesParents_workflow(Request $request)
    {
        $orderBy = $request->order_by ?? 'name';
        $orderType = $request->order_type ?? 'asc';

        $modulePermission = AuthModule::active()->where(['detail' => 0, 'is_visible' => 1, 'is_workflow' =>1])
        ->orderBy($orderBy, $orderType)
        ->get(['id', 'name']);

        if (!count($modulePermission)) 
        {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Modules not found.");
        } 
        else 
        {
            $status = 200;
            $response = [];
            $dataResult = array("parents_module_wf" => $modulePermission->toArray());
            $response = Utilities::buildSuccessResponse(10004, "Modules List.", $dataResult);
        }

        return response()->json($response, $status);
    }

    public function getAllModulesChild_workflow(Request $request)
    {
        $orderBy = $request->order_by ?? 'name';
        $orderType = $request->order_type ?? 'asc';
        $module_id = $request->module_id ?? 0;
        $whereData = array('is_visible' => 1);
        $whereData[]= $whereData[] = ['detail', 1];
        $whereData[]= $whereData[] = ['is_workflow', 1];
        $whereData[]= $whereData[] = ['parent_id', $module_id];
        $modulePermission = AuthModule::active()->where($whereData)
        ->orderBy($orderBy, $orderType)
        ->get(['id', 'name']);

        if (!count($modulePermission)) 
        {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Child Modules not found.");
        } 
        else 
        {
            $status = 200;
            $response = [];
            $dataResult = array("child_module_wf" => $modulePermission->toArray());
            $response = Utilities::buildSuccessResponse(10004, "Child Modules List.", $dataResult);
        }

        return response()->json($response, $status);
    }
}
