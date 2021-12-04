<?php

/**
 * Performance system API
 * This is a Country API controller
 *
 */

namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\RolePermission;
use App\Models\Organization;
use App\Models\AuthUser;
use App\Models\AuthModule;
use App\Response\RolePermissionResponse;
use Illuminate\Http\Request;
use App\Utilities\Utilities;
use Illuminate\Support\Facades\DB;

class RolePermissionApi extends Controller
{

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    private $select_column = ['role_id', 'module_id', 'route', 'action'];

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
     * Add Module Permission.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addRolePermission(Request $request)
    {
        $mdlRolePermission = new RolePermission();
        // $this->validate($request, $mdlRolePermission->rules($request), $mdlRolePermission->messages($request));
        // $mdlRolePermission->filterColumns($request);
        
        
        try{
           DB::beginTransaction();
       
            //RolePermission::where(['client_id' => $request->client_id, 'role_id' => $request->role_id])->delete();
            RolePermission::where(['role_id' => $request->role_id])->delete();

            $permissionData = [];
            foreach ($request->permModule as $m => $permModule) {
                $module_id = $permModule['module_id'];

                $module_permission = array_filter($permModule['permission']);
                
                if (count($module_permission)) {
                    
                    if(!empty($module_id)){
                        $parents = $this->getParents($module_id, []);
                        if ( !empty($parents) && count($parents)) {
                            foreach ($parents as $key => $value) {
                                if ($value['id'] != $module_id) {
                                    $permissionData[] = [
                                       // 'client_id' => $request->client_id,
                                        'role_id' => $request->role_id,
                                        'module_id' => $value['id'],
                                        'route' => NULL,
                                        'action' => NULL
                                    ];
                                }
                            }
                        }
                    }
                    
                    foreach ($module_permission as $action => $route) {
                        $permissionData[] = [
                           // 'client_id' => $request->client_id,
                            'role_id' => $request->role_id,
                            'module_id' => $module_id,
                            'route' => $route['detail'],
                            'action' => $route['detail']
                        ];
                    }
                }
            }

            $obj = RolePermission::insert($permissionData);
            
            $data = ['client_id' => $request->client_id, 'role_id' => $request->role_id];
            $data = ['role_id' => $request->role_id];

            $response = Utilities::buildSuccessResponse(10000, "Permission successfully created.", $data);
        
            DB::commit();
            
        }catch(\Exception $e){
            DB::rollback();
           $response = Utilities::buildBaseResponse(10003, "Transaction Failed Permission not Update. ");
        }
                    
        
        return response()->json($response, 201);
    }

    public static function getParents($id, $data)
    {
        $select = ['id', 'parent_id'];
        
        if($id){
            $child_q = AuthModule::where('id', $id)->first($select);

            if($child_q){
                $child = $child_q->toArray();

                $data[] = $child;

                if ($child['parent_id'] > 0) {
                    $data = self::getParents($child['parent_id'], $data, $select);
                }

                return $data;
            }
        }
        
        return null;
    }
    /**
     * Get one Module Permission.
     *
     * @param $id 'ID' of Module Permission to return (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOneRolePermission($client_id, $role_id, Request $request)
    {
        $mdlRolePermission = new RolePermission();

        $request->request->add(['client_id' => $client_id, 'role_id' => $role_id]);

        $this->validate($request, $mdlRolePermission->rules($request, Constant::RequestType['GET_ONE']), $mdlRolePermission->messages($request, Constant::RequestType['GET_ONE']));

        $select = $this->select_column;

        $mdlRolePermission->filterColumns($request);

        if ($request->fields) {
            $select = $request->fields;
        }

        $modulePermission = RolePermission::with('role', 'client')->where(['client_id' => $request->client_id, 'role_id' => $request->role_id])->get($select);

        foreach ($modulePermission as $key => $value) {
            $value->auth_module;
        }

        $status = 200;
        $response = [];

        if (!$modulePermission) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Permission not found.");
        } else {
            $dataResult = array("rolePermission" => $modulePermission->toArray());
            $response = Utilities::buildSuccessResponse(10004, "Role Permissions.", $dataResult);
        }

        return response()->json($response, $status);
    }

    /**
     * Fetch list of Module Permission by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllRolePermission(Request $request)
    {
        $mdlRolePermission = new RolePermission();

        $this->validate($request, $mdlRolePermission->rules($request, Constant::RequestType['GET_ALL']), $mdlRolePermission->messages($request, Constant::RequestType['GET_ALL']));

        $pageSize = $request->limit ?? Constant::PageSize;
        if ($pageSize > Constant::MaxPageSize) {
            $pageSize = Constant::PageSize;
        }
        $page = $request->page ?? Constant::Page;
        $skip = ($page - 1) * $pageSize;


        $select = $this->select_column;

        $mdlRolePermission->filterColumns($request);

        if ($request->fields) {
            $select = $request->fields;
        }

        $whereData = array();

        if ($request->client_id) {
            $whereData[] = ['client_id', $request->client_id];
        }
        if ($request->role_id) {
            $whereData[] = ['role_id', $request->role_id];
        }
        if ($request->module_id) {
            $whereData[] = ['module_id', $request->module_id];
        }
        if ($request->route) {
            $whereData[] = ['route', 'LIKE', "%{$request->route}%"];
        }
        if ($request->action) {
            $whereData[] = ['action', 'LIKE', "%{$request->action}%"];
        }

        $orderBy =  $mdlRolePermission->getOrderColumn($request->order_by);
        $orderType = $request->order_type ?? Constant::OrderType;

        $modulePermission = RolePermission::where($whereData)
            // ->active()
            ->orderBy($orderBy, $orderType)
            ->offset($skip)
            ->limit($pageSize)
            ->get($select);

        $status = 200;
        $data_result = new RolePermissionResponse();
        $data_result->setRolePermission($modulePermission->toArray());
        $response = Utilities::buildSuccessResponse(10004, "Role Permissions.", $data_result);

        return response()->json($response, $status);
    }

    public function getRoleModulePermission($user_id, Request $request)
    {
        $mdlRolePermission = new RolePermission();

        //$request->request->add(['role_id' => $role_id]);

        // $this->validate($request, $mdlRolePermission->rules($request, Constant::RequestType['GET_ONE']), $mdlRolePermission->messages($request, Constant::RequestType['GET_ONE']));

        $users  = AuthUser::where('id', $user_id)->first();
        if($users->is_superadmin == 0){
                $whereData = [];
        
        
                // if ($request->role_id) {
                //     $whereData['role_id'] = $request->role_id;
                // }
                $role_array = DB::table('user_role_levels')->where('user_id', $user_id)->get('role_id')->toArray();
                // $modulePermission = DB::table('user_role_levels')
                //     ->rightJoin('auth_role_module_perms', 'user_role_levels.role_id', '=', 'auth_role_module_perms.role_id')
                //      ->Join('auth_modules', 'auth_modules.id', '=', 'auth_role_module_perms.module_id')
                //     ->where('user_role_levels.user_id','=' , $role_id)
                //     ->get();
                    // ->toArray();
                $user_role_ids = array();
                $user_role_ids_arr = [];
                   foreach($role_array as $rl){
                    array_push($user_role_ids,$rl->role_id);
                   }


                // $modulePermission = RolePermission::with('role', 'client')->orderBy('module_id', 'asc')->where($whereData)->get($this->select_column);
                // DB::enableQueryLog();
                //  $modulePermission = DB::table('auth_role_module_perms')->whereIn('role_id', ['17','16'])->get('role_id')->toArray();

                 $modulePermission = RolePermission::whereIn('role_id', $user_role_ids)->get($this->select_column);
                // return response()->json(DB::getQueryLog(),200);
                // dd(DB::getQueryLog());

                if (!count($modulePermission)) {
                    $status = 404;
                    $response = Utilities::buildBaseResponse(10003, "Permission not found.");
                } else {
                    $modulePermissionData = [];
                    // $modulePermissionData['client'] = $modulePermission[0]->client->toArray();
                    // $modulePermissionData['role'] = $modulePermission[0]->role->toArray();
                    $moduleData = [];

                    foreach ($modulePermission as $key => $value) {
                        $module = $value->auth_module;
                        if ($module) {
                            $module = $module->toArray();
                            $moduleData[$module['id']] = $module;
                            foreach ($modulePermission as $key1 => $value1) {
                                if ($value1->module_id == $module['id']) {
                                    $moduleData[$module['id']]['permission'][] = [$value1->route => $value1->action];
                                }
                            }
                        }
                    }

                    $status = 200;
                    $response = [];
                    $modulePermissionData['module'] = RolePermissionApi::buildTree($moduleData);

                    $dataResult = array("roleModulePermission" => $modulePermissionData);
                    $response = Utilities::buildSuccessResponse(10004, "Role Permissions.", $dataResult);
                }

                return response()->json($response, $status);
        
        }else{
            $moduleData = AuthModule::where('is_enable', '=', 1)->orderBy('order', 'asc')->select('id', 'name as title', 'default_url as path', 'icon_class as icon', 'parent_id', 'have_child', 'sorting as order')->get()->toArray();
            $status = 200;
            $response = [];
            $modulePermissionData['module'] = RolePermissionApi::buildTree($moduleData);

            $dataResult = array("roleModulePermission" => $modulePermissionData);
            $response = Utilities::buildSuccessResponse(10004, "Role Permissions.", $dataResult);
            
            return response()->json($response, $status);
            
            
        }
            
            
        
    }

    public function getAllowedUrl(Request $request){
        $user_id = $request->user_id;
        $url = $request->url;
        
        $users  = AuthUser::where('id', $user_id)->first();
        if($users->is_superadmin == 0){
            
            $list = DB::table('user_role_levels')
                ->rightJoin('auth_role_module_perms', 'user_role_levels.role_id', '=', 'auth_role_module_perms.role_id')
                 ->Join('auth_modules', 'auth_modules.id', '=', 'auth_role_module_perms.module_id')
                ->where('user_role_levels.user_id','=' , $user_id)
                ->where('auth_modules.default_url','=' , $url)
                ->where('auth_role_module_perms.route','=' , 'GET')
                ->get();
            // return response()->json(DB::getQueryLog(),200);
            //  return response()->json($list,200);
            $list = $list->toArray();
            
            
        
        }else{
            $list = ['is_superadmin' => 1 ];
        }
        // return response()->json($url,200);
//         DB::enableQueryLog();
        
        $status = 200;
        $data_result = array("list" => $list);  
        $response = Utilities::buildSuccessResponse(10004, "Allowed URL List.", $data_result);
        return response()->json($data_result, $status);   
        // return response()->json($request,200);
    }
    public function getAllowedData(Request $request){
        $user_id = $request->user_id;
        $url = $request->url;
        $route = $request->route;
        
        $status = 200;
        
        $users  = AuthUser::where('id', $user_id)->first();
        if($users->is_superadmin == 0){
          // return response()->json($route,200);
          //         DB::enableQueryLog();
            $list = DB::table('user_role_levels')
                ->rightJoin('auth_role_module_perms', 'user_role_levels.role_id', '=', 'auth_role_module_perms.role_id')
                 ->Join('auth_modules', 'auth_modules.id', '=', 'auth_role_module_perms.module_id')
                ->where('user_role_levels.user_id','=' , $user_id)
                ->where('auth_modules.default_url','=' , $url)
                // ->where('auth_role_module_perms.route','=' , $route)
                ->get('action');
            // return response()->json(DB::getQueryLog(),200);
            //  return response()->json($list,200);
           

            $url_action = [];
            foreach($list->toArray() as $key => $value){
                $url_action[$value->action] = $value->action;
            }  
        }else{
            $url_action['POST']   = 'POST';
            $url_action['PUT']    = 'PUT';
            $url_action['DELETE'] = 'DELETE';
            $url_action['PATCH']  = 'PATCH';
        }

        

        $data_result = array('url_action' => $url_action);  
        $response = Utilities::buildSuccessResponse(10004, "Allowed URL List.", $data_result);
        return response()->json($data_result, $status);   
        // return response()->json($request,200);
    }

    public function getAdminModulePermission(Request $request)
    {
        $mdlRolePermission = new RolePermission();

        // echo "<pre>";
        // print_r( $request);
        // exit;
        // $this->validate($request, $mdlRolePermission->rules($request, Constant::RequestType['GET_ONE']), $mdlRolePermission->messages($request, Constant::RequestType['GET_ONE']));

        // $modulePermission = AuthModule::active()->orderBy('order', 'asc')->get(['id', 'name as title', 'default_url as path','sorting as order' , 'icon_class as icon', 'parent_id', 'have_child']);

        // if (!count($modulePermission)) {
        //     $status = 404;
        //     $response = Utilities::buildBaseResponse(10003, "Permission not found.");
        // } else {

        //     $status = 200;
        //     $response = [];
        //     $modulePermissionData['module'] = RolePermissionApi::buildTree($modulePermission->toArray());

        //     $dataResult = array("roleModulePermission" => $modulePermissionData);
        //     $response = Utilities::buildSuccessResponse(10004, "Role Permissions.", $dataResult);
        // }

        // return response()->json($response, $status);
    }

    public static function buildTree(array $modules, $parentId = 0)
    {
        $treeData = array();

        foreach ($modules as &$menu) {
            if ($menu['parent_id'] == $parentId) {
                $menu['class'] = ($menu['have_child'] == 1 ? 'has-arrow' : '');
                $menu['submenu'] = [];
                $menu['extralink'] = false;
                $menu['labelClass'] = '';
                $submenu = self::buildTree($modules, $menu['id']);
                if (count($submenu) > 0) {
                    $menu['submenu'] = $submenu;
                }
                $treeData[] = $menu;
            }
        }

        return $treeData;
    }


    public function getCountryHirarcy(Request $request){
        $user_id = $request->data_user_id;
        if($user_id > 0)
        {
//            DB::enableQueryLog();
            $list = DB::table('user_data_permission')
                    ->select(['countries.id', 'countries.country_name as name'])
            ->leftJoin('countries', 'countries.id', '=', 'user_data_permission.data_permissions_id')
            ->where('user_data_permission.user_id','=' , $user_id)
            ->where('countries.is_enable', 1)
            ->where('user_data_permission.hierarchy_level_id','=' , 2)
            ->groupBy(['countries.id', 'countries.country_name'])
            ->get();
            //  ->get(['hierarchy_level_id', 'user_id', 'data_permissions_id']);

            $status = 200;

            $data_result = array("list" => $list->toArray(), 'countries' => $list->toArray());  
            $response = Utilities::buildSuccessResponse(10004, "Country List.", $data_result);
            return response()->json($data_result, $status);
        }else
        {
            $data_result = array("list" => []);  
            $response = Utilities::buildSuccessResponse(10004, "Country List.", $data_result);
            return response()->json($data_result, 200);
        }   
        
    }

    public function getStateHirarcy(Request $request){
        $user_id = $request->data_user_id;
        $countries_id = $request->countries_id;
        if($user_id > 0  && $countries_id > 0)
        {
//            DB::enableQueryLog();
            $list = DB::table('user_data_permission')
            ->leftJoin('state', 'state.id', '=', 'user_data_permission.data_permissions_id')
            ->where('user_data_permission.user_id','=' , $user_id)
            ->where('state.countries_id','=' , $countries_id)
            ->where('state.is_enable', 1)
            ->where('user_data_permission.hierarchy_level_id','=' , 3)
            ->groupBy(['state.id', 'state.state_name'])
            ->get(['state.id', 'state.state_name']);
            
            $status = 200;

            $data_result = array("list" => $list->toArray(), 'state' => $list->toArray());  
            $response = Utilities::buildSuccessResponse(10004, "State List.", $data_result);
            return response()->json($data_result, $status);
        }
        else
        {
            $data_result = array("list" => []);  
            $response = Utilities::buildSuccessResponse(10004, "State List.", $data_result);
            return response()->json($data_result, 200);
        }         
        
    }


    

    
    public function getRegionHirarcy(Request $request){
        $user_id = $request->data_user_id;
        $state_id = $request->state_id;
        if($user_id>0  && $state_id>0)
        {
//            DB::enableQueryLog();
            $list = DB::table('user_data_permission')
            ->leftJoin('region', 'region.id', '=', 'user_data_permission.data_permissions_id')
            ->where('user_data_permission.user_id','=' , $user_id)
            ->where('region.state_id','=' , $state_id)
            ->where('region.is_enable', 1)
            ->where('user_data_permission.hierarchy_level_id','=' , 4)
            ->groupBy(['region.id', 'region.region_name'])
            ->get(['region.id', 'region.region_name']);
            
            $status = 200;

            $data_result = array("list" => $list->toArray(), 'region' => $list->toArray());  
            $response = Utilities::buildSuccessResponse(10004, "Region List.", $data_result);
            return response()->json($data_result, $status);
        }
        else
        {
            $data_result = array("list" => []);  
            $response = Utilities::buildSuccessResponse(10004, "Region List.", $data_result);
            return response()->json($data_result, 200);
        }         
        
    }

    public function getCityHirarcy(Request $request){
        $user_id = $request->data_user_id;
        $region_id = $request->region_id;
        if($user_id>0  && $region_id>0)
        {
//            DB::enableQueryLog();
            $list = DB::table('user_data_permission')
            ->leftJoin('city', 'city.id', '=', 'user_data_permission.data_permissions_id')
            ->where('user_data_permission.user_id','=' , $user_id)
            ->where('city.region_id','=' , $region_id)
            ->where('city.is_enable', 1)
            ->where('user_data_permission.hierarchy_level_id','=' , 5)
            ->groupBy(['city.id', 'city.city_name'])
            ->get(['city.id', 'city.city_name']);
            
            $status = 200;

            $data_result = array("list" => $list->toArray(), 'city' => $list->toArray());  
            $response = Utilities::buildSuccessResponse(10004, "City List.", $data_result);
            return response()->json($data_result, $status);
        }
        else
        {
            $data_result = array("list" => []);  
            $response = Utilities::buildSuccessResponse(10004, "City List.", $data_result);
            return response()->json($data_result, 200);
        }         
    }


    public function getCampusHirarcy(Request $request){
        $user_id = $request->data_user_id;
        $city = $request->city_id;
        if($user_id>0  && $city>0)
        {
//            DB::enableQueryLog();
            $list = DB::table('user_data_permission')
            ->leftJoin('campus', 'campus.id', '=', 'user_data_permission.data_permissions_id')
            ->where('user_data_permission.user_id','=' , $user_id)
            ->where('campus.city_id','=' , $city)
            ->where('campus.is_enable', 1)
            ->where('user_data_permission.hierarchy_level_id','=' , 6)
            ->groupBy(['campus.id', 'campus.campus_name'])
            ->get(['campus.id', 'campus.campus_name']);
            
            $status = 200;

            $data_result = array("list" => $list->toArray(), 'campus' => $list->toArray());  
            $response = Utilities::buildSuccessResponse(10004, "Campus List.", $data_result);
            return response()->json($data_result, $status);
        }
        else
        {
            $data_result = array("list" => []);  
            $response = Utilities::buildSuccessResponse(10004, "Campus List.", $data_result);
            return response()->json($data_result, 200);
        }         
    }
    public function getCampusSession(Request $request){
        $campus_id = $request->campus_id;
        
        if($campus_id>0)
        {
//            DB::enableQueryLog();
            $list = DB::table('campus_session')
            ->leftJoin('session', 'session.id', '=', 'campus_session.session_id')
            ->where('campus_session.campus_id','=' , $campus_id)
            ->where('session.is_enable', 1)
            ->groupBy(['session.id', 'session.session_name'])
            ->get(['session.id', 'session.session_name']);
            
            $status = 200;

            $data_result = array("list" => $list->toArray() );  
            $response = Utilities::buildSuccessResponse(10004, "Session List.", $data_result);
            return response()->json($data_result, $status);
        }
        else
        {
            $data_result = array("list" => []);  
            $response = Utilities::buildSuccessResponse(10004, "Session List.", $data_result);
            return response()->json($data_result, 200);
        }         
    }

    public function getCampusSection(Request $request){
        $class_id = $request->class_id;
        $campus_id = $request->campus_id;
        
        if($class_id > 0 && $campus_id > 0)
        {
//            DB::enableQueryLog();
            $list = DB::table('campus_section')
            ->leftJoin('section', 'section.id', '=', 'campus_section.section_id')
            ->where('campus_section.class_id','=' , $class_id)            
            ->where('campus_section.campus_id','=' , $campus_id)
            ->where('campus_section.campus_id','=' , $campus_id)
            ->where('section.is_enable', 1)
            ->where('campus_section.is_enable', 1)
            ->groupBy(['section.id', 'section.section_name'])
            ->get(['section.id', 'section.section_name']);
            
            $status = 200;

            $data_result = array("list" => $list->toArray());  
            $response = Utilities::buildSuccessResponse(10004, "Section List.", $data_result);
            return response()->json($data_result, $status);
        }
        else
        {
            $data_result = array("list" => []);  
            $response = Utilities::buildSuccessResponse(10004, "Section List.", $data_result);
            return response()->json($data_result, 200);
        }         
    }



    public function getUserCampu___s(Request $request){
        $user_id = $request->user_id;
        $campus_id = $request->campus_id;
        $session_id = $request->session_id;

        if($user_id>0 && $campus_id>0 && $session_id>0)
        {
            DB::enableQueryLog();
            $list = DB::table('user_data_permission')
            ->leftJoin('campus', 'campus.id', '=', 'user_data_permission.data_permissions_id')
            ->where('user_data_permission.user_id','=' , $user_id)
            ->where('campus.is_enable', 1)
            ->where('campus.id', $campus_id)
            ->where('user_data_permission.hierarchy_level_id','=' , 6)
            ->orderBy('id', 'desc')
            ->limit(1)
            // ->latest()
            ->get(['campus.id', 'campus.campus_name'])->toArray();;
            
            $session_list = DB::table('session')->where('id', $session_id)->orderBy('id', 'desc')
            ->limit(1)->get()->toArray();

            $status = 200;

            // $data_result = array("list" => $list->toArray());  
            $data_result['list'] = $list;
            $data_result['session_list']= $session_list;

            $response = Utilities::buildSuccessResponse(10004, "Campus Name.", $data_result);
            return response()->json($data_result, $status);
        }
        elseif($user_id>0){
            DB::enableQueryLog();
            $list = DB::table('user_data_permission')
            ->leftJoin('campus', 'campus.id', '=', 'user_data_permission.data_permissions_id')
            ->where('user_data_permission.user_id','=' , $user_id)
            ->where('campus.is_enable', 1)
            ->where('user_data_permission.hierarchy_level_id','=' , 6)
            ->orderBy('id', 'desc')
            ->limit(1)
            // ->latest()
            ->get(['campus.id', 'campus.campus_name']);
            
            foreach($list as $campus_lst){
                $session_list = DB::table('session')->where('id', $session_id)->orderBy('id', 'desc')
                ->limit(1)->get()->toArray();
            }
            $status = 200;

            $data_result = array("list" => $list->toArray());  
            $response = Utilities::buildSuccessResponse(10004, "Campus Name.", $data_result);
            return response()->json($data_result, $status);

        }
        else
        {
            $data_result = array("list" => []);  
            $response = Utilities::buildSuccessResponse(10004, "Campus Name.", $data_result);
            return response()->json($data_result, 200);
        }         
    }


    public function getUserCampus(Request $request){
        
        $user_id   = $request->user_id;
        $campus_id = $request->campus_id;
        $session_id = $request->session_id;
        $org_list=null;
        if(!empty($user_id) && $user_id > 0)
        {
            $permQuery = DB::table('user_data_permission')
            ->leftJoin('campus', 'campus.id', '=', 'user_data_permission.data_permissions_id')
            ->where('user_data_permission.user_id','=' , $user_id)
            ->where('campus.is_enable', 1);
            
            if(!empty($campus_id) && $campus_id > 0){
                $permQuery->where('campus.id', $campus_id);
            }   
            
            // $permQuery->where('user_data_permission.hierarchy_level_id','=' , 6)
            $permQuery->orderBy('id', 'desc')
            ->limit(1);
            
            $list = $permQuery->get(['campus.id', 'campus.campus_name', 'campus.organization_id']);
            $result_set = $list->toArray();
             
             $sessionQueryObj =  DB::table('campus_session')
             ->join('session', 'session.id', '=', 'campus_session.session_id');

             if(!empty($result_set[0]->id)){
                $sessionQueryObj->where('campus_session.campus_id', $result_set[0]->id);
                $org_list = Organization::where('id', $result_set[0]->organization_id)->get();

                    $getImg       = url('app/organization_file/');
                    $getImgPublic = '';
                    if (strpos($getImg, 'public') !== false) {
                        $getImgPublic = str_replace('public', 'storage', $getImg);
                    }else{
                        $getImgPublic = url('storage/app/organization_file/');
                    }
                    $org_list['logo_path']    = $getImgPublic . '/'. $org_list[0]->org_logo.'?'.rand();
             }
             if(!empty($session_id)){
                $sessionQueryObj->where('campus_session.session_id', $session_id);
             }

            



            $session_list = $sessionQueryObj->orderBy('campus_session.id', 'desc')
              ->limit(1)->get(['campus_session.session_id', 'session.session_name'])->toArray();

            $status = 200;

            $data_result = array(
                                    "list"          => $result_set, 
                                    'session_list'  => $session_list,
                                    'org_list'      => $org_list
                                );  
            $response = Utilities::buildSuccessResponse(10004, "Campus Name.", $data_result);
            return response()->json($data_result, $status);
        }
        else
        {
            $data_result = array("list" => []);  
            $response = Utilities::buildSuccessResponse(10004, "Campus Name.", $data_result);
            return response()->json($data_result, 200);
        }         
    }


    public function get_occupation_list(Request $request){
            $is_enable = $request->is_enable;

            $list = DB::table('occupation')
            
            ->where('is_enable',1)
            
            ->get(['id', 'occupation_name']);
            
            $status = 200;

            $data_result = array("list" => $list->toArray());  
            $response = Utilities::buildSuccessResponse(10004, "Occupation List.", $data_result);
            return response()->json($data_result, $status);
            
    }

}
