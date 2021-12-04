<?php

/**
 * Performance system API
 * This is a Country API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\AuthUserPerms;
use App\Models\UserDataPermission;
use App\Models\AuthUser;
use App\Response\AuthUserPermsResponse;
use Illuminate\Http\Request;
use App\Utilities\Utilities;
use Illuminate\Support\Facades\DB;

class AuthUserPermsApi extends Controller
{

    /**
     * Constructor
     */
    public function __construct()
    {

    }

    private $select_columns = ['id', 'user_id', 'role_id'];

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
     * Add User Perms.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addAuthUserPerms(Request $request)
    {
        $mdlUserPerms = new AuthUserPerms();

        $this->validate($request, $mdlUserPerms->rules($request), $mdlUserPerms->messages($request));
        
        $user = AuthUser::find($request->user_id);
        
        if (! $user) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "User not found.");
        } else {
            
            $userArr['designation_id'] =  $request->designation_id;
            $userArr['department_id']  =  $request->department_id;
            $userArr['is_data_perm']   =  1;
            $userObj = $user->update($userArr);
            
            
            if($userObj){
                
                try{
                    DB::beginTransaction();
                        /*
                         * Your DB code
                         * */
                        AuthUserPerms::where(['user_id' => $request->user_id])->delete();
                        
                        $userRoleArr['role_ids_obj'] = json_encode($request->role_id, true);
                        foreach ($request->role_id as $key => $value) {
//                            $userRoleArr = ['user_id' => $request->user_id, 'role_id' => $value['id'] ];
                            $userRoleArr['user_id'] = $request->user_id;
                            $userRoleArr['role_id'] = $value['id'];
                            
                            AuthUserPerms::insert($userRoleArr);
                        }

                        UserDataPermission::where(['user_id' => $request->user_id])->delete();


                        $userOrgDataPermsArr = [];
                        if(!empty($request->organization_id)){
                            $userOrgDataPermsArr['data_permissions_obj'] = json_encode($request->organization_id, true);
                            foreach ($request->organization_id as $key => $value) {
                                $userOrgDataPermsArr['user_id'] = $request->user_id;
                                $userOrgDataPermsArr['hierarchy_level_id'] = 1;
                                $userOrgDataPermsArr['data_permissions_id'] = $value['id'];

                                UserDataPermission::insert($userOrgDataPermsArr);
                            }
                        }

                        $userCountryDataPermsArr = [];
                        if(!empty($request->countries_id)){
                            $userCountryDataPermsArr['data_permissions_obj'] = json_encode($request->countries_id, true);
                            foreach ($request->countries_id as $key => $value) {
                                $userCountryDataPermsArr['user_id'] = $request->user_id;
                                $userCountryDataPermsArr['hierarchy_level_id'] = 2;
                                $userCountryDataPermsArr['data_permissions_id'] = $value['id'];

                                UserDataPermission::insert($userCountryDataPermsArr);
                            }
                        }

                        $userStateDataPermsArr = [];
                        if(!empty($request->state_id)){
                            $userStateDataPermsArr['data_permissions_obj'] = json_encode($request->state_id, true);
                            foreach ($request->state_id as $key => $value) {
                                $userStateDataPermsArr['user_id'] = $request->user_id;
                                $userStateDataPermsArr['hierarchy_level_id'] = 3;
                                $userStateDataPermsArr['data_permissions_id'] = $value['id'];

                                UserDataPermission::insert($userStateDataPermsArr);
                            }
                        }

                        if(!empty($request->region_id)){
                            $userRegionDataPermsArr['data_permissions_obj'] = json_encode($request->region_id, true);
                            foreach ($request->region_id as $key => $value) {
                                $userRegionDataPermsArr['user_id'] = $request->user_id;
                                $userRegionDataPermsArr['hierarchy_level_id'] = 4;
                                $userRegionDataPermsArr['data_permissions_id'] = $value['id'];

                                UserDataPermission::insert($userRegionDataPermsArr);
                            }
                        }

                        if(!empty($request->city_id)){
                            $userCityDataPermsArr['data_permissions_obj'] = json_encode($request->city_id, true);
                            foreach ($request->city_id as $key => $value) {
                                $userCityDataPermsArr['user_id'] = $request->user_id;
                                $userCityDataPermsArr['hierarchy_level_id'] = 5;
                                $userCityDataPermsArr['data_permissions_id'] = $value['id'];

                                UserDataPermission::insert($userCityDataPermsArr);
                            }
                        }

                        if(!empty($request->campus_id)){
                            $userCampusDataPermsArr['data_permissions_obj'] = json_encode($request->campus_id, true);
                            foreach ($request->campus_id as $key => $value) {
                                $userCampusDataPermsArr['user_id'] = $request->user_id;
                                $userCampusDataPermsArr['hierarchy_level_id'] = 6;
                                $userCampusDataPermsArr['data_permissions_id'] = $value['id'];

                                UserDataPermission::insert($userCampusDataPermsArr);
                            }
                        }

                        DB::commit();
                        $data = [ 'id' => $user->id ];
                
                        $status = 200;
                        $response = Utilities::buildSuccessResponse(10000, "User permission successfully created.", $data);
                        
                    }catch(\Exception $e){
                        DB::rollback();
                       $response = Utilities::buildBaseResponse(10003, "Transaction Failed User not Update. ");
                    }
            }
            else{
                $status = 404;
                $response = Utilities::buildBaseResponse(10003, "User not Update.");
            }
        }
        
        return response()->json($response, 201);
        
    }


    /**
     * Get one User permission.
     *
     * @param $user_id 'ID' of User permission to return (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOneAuthUserPerms($id, Request $request)
    {
        // $mdlUserPermsPerms = new AuthUserPerms();
        
        // $request->request->add(['id' => $id ]);
    
        // $this->validate($request, $mdlUserPermsPerms->rules($request, Constant::RequestType['GET_ONE']), $mdlUserPermsPerms->messages($request, Constant::RequestType['GET_ONE']));
        
        // $select = $this->select_columns;

        // $mdlUserPermsPerms->filterColumns($request);
        
        // if($request->fields){ 
        //     $select = $request->fields;
        // }
        
       
        //  $userPermission = AuthUserPerms::where('id', $request->id)->first();
        
        // $status = 200;
        // $response = [];
        
        // if (! $userPermission) {
        //     $status = 404;
        //     $response = Utilities::buildBaseResponse(10003, "User Permission not found.");
        // } else {
        //     $userPermission->user;
        //     $userPermission->role;
        //     $userPermission->level;

        //     $dataResult = array("userPerms" => $userPermission->toArray());
        //     $response = Utilities::buildSuccessResponse(10005, "User Permission Data.", $dataResult);
        // }
        
        // return response()->json($response, $status);
    }

    /**
     * Fetch list of User permission by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllAuthUserPerms(Request $request)
    {
        $mdlUserPermsPerms = new AuthUserPerms();
        
        $this->validate($request, $mdlUserPermsPerms->rules($request, Constant::RequestType['GET_ALL']), $mdlUserPermsPerms->messages($request, Constant::RequestType['GET_ALL']));
        
        $pageSize = $request->limit ?? Constant::PageSize;
        if($pageSize > Constant::MaxPageSize){
            $pageSize = Constant::PageSize;
        }
        $page = $request->page ?? Constant::Page;
        $skip = ($page - 1) * $pageSize;

        
        $select = $this->select_columns;

        $mdlUserPermsPerms->filterColumns($request);
        
        if($request->fields){
            $select = $request->fields;
        }
        
        $whereData = array();
        
        if($request->user_id) {   
            $whereData[] = ['user_id', $request->user_id];
        }
        if($request->role_id) {   
            $whereData[] = ['role_id', $request->role_id];
        }

        $orderBy =  $mdlUserPermsPerms->getOrderColumn($request->order_by);
        $orderType = $request->order_type ?? Constant::OrderType;
        $userPermission = AuthUserPerms::with('user', 'role')
            ->where($whereData)
            ->orderBy($orderBy, $orderType)
            ->offset($skip)
            ->limit($pageSize)
            ->get($select);
        
            foreach ($userPermission as $value) {
                $value->user_role_level_detail;
            }

        $status = 200;
        $data_result = new AuthUserPermsResponse();
        $data_result->setUserPerms($userPermission->toArray());
        $response = Utilities::buildSuccessResponse(10004, "User Perms List.", $data_result);
        
        return response()->json($response, $status);   
    }
    
    /**
     * Fetch list of User permission by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllUserPermsMaster(Request $request)
    {
        $mdlUserDataPermission = new UserDataPermission;
        
        $mdlUserPermsPerms = new AuthUserPerms();
        
//        $this->validate($request, $mdlUserPermsPerms->rules($request, Constant::RequestType['GET_ALL']), $mdlUserPermsPerms->messages($request, Constant::RequestType['GET_ALL']));
        
        
        $select = ['id', 'user_id', 'hierarchy_level_id', 'data_permissions_id', 'data_permissions_obj'];
     
        $whereData = array();
        
        if($request->user_id) {   
            $whereData[] = ['user_id', $request->user_id];
        }

        $userDataPermQuery = UserDataPermission::where($whereData)
                                                ->get($select);
        $userDataPermission = $userDataPermQuery->toArray();
        
        $userOrg_ids     = [];
        $userOrgObj     = [];

        $userCountry_ids = [];
        $userCountryObj = [];

        $userState_ids   = [];
        $userStateObj   = [];

        $userRegion_ids  = [];
        $userRegionObj  = [];

        $userCity_ids    = [];
        $userCityObj    = [];

        $userCampus_ids  = [];
        $userCampusObj  = [];
            
        if(!empty($userDataPermission) && count($userDataPermission) > 0){
            
            foreach ($userDataPermission as $key => $value){
                if($value['hierarchy_level_id'] == 1){
                   $userOrg_ids[]       = $value['data_permissions_id']; 
                   $userOrgObj[]        = json_decode( $value['data_permissions_obj'], true); 
                }
                if($value['hierarchy_level_id'] == 2){
                   $userCountry_ids[]     = $value['data_permissions_id']; 
                   $userCountryObj[]      = json_decode( $value['data_permissions_obj'], true);
                }
                if($value['hierarchy_level_id'] == 3){
                   $userState_ids[]       = $value['data_permissions_id']; 
                   $userStateObj[]        = json_decode( $value['data_permissions_obj'], true);
                }
                if($value['hierarchy_level_id'] == 4){
                   $userRegion_ids[]      = $value['data_permissions_id']; 
                   $userRegionObj[]       = json_decode( $value['data_permissions_obj'], true);
                }
                if($value['hierarchy_level_id'] == 5){
                   $userCity_ids[]        = $value['data_permissions_id']; 
                   $userCityObj[]         = json_decode( $value['data_permissions_obj'], true);
                }
                if($value['hierarchy_level_id'] == 6){
                   $userCampus_ids[]      = $value['data_permissions_id']; 
                   $userCampusObj[]       = json_decode( $value['data_permissions_obj'], true);
                }
                
            }
        }
        
//        return response()->json($userOrgObj, 200);   
//             exit;
        
        if($request->role_id) {   
            $whereData[] = ['role_id', $request->role_id];
        }
        
        $userRolePermQuery  = AuthUserPerms::with('role')->where($whereData)
                                                ->get();
        $userRolePermission = $userRolePermQuery->toArray();
        
        
        $status = 200;
        $data_result = [];
        
        $data_result['userOrg_ids']     = $userOrg_ids;
        $data_result['userOrgObj']      =  $userOrgObj ? $userOrgObj[0] : null;
        
        $data_result['userCountry_ids'] = $userCountry_ids;
        $data_result['userCountryObj']  = $userCountryObj ? $userCountryObj[0] : null;
        
        $data_result['userState_ids']   = $userState_ids;
        $data_result['userStateObj']    = $userStateObj ? $userStateObj[0] : null;
        
        $data_result['userRegion_ids']  = $userRegion_ids;
        $data_result['userRegionObj']   = $userRegionObj ? $userRegionObj[0] : null;
        
        $data_result['userCity_ids']    = $userCity_ids;
        $data_result['userCityObj']     = $userCityObj ? $userCityObj[0] : null;
        
        $data_result['userCampus_ids']  = $userCampus_ids;
        $data_result['userCampusObj']   = $userCampusObj ? $userCampusObj[0] : null;
        
        $data_result['userRoles_ids']   = $userRolePermission;
        $data_result['userRolesObj']    = $userRolePermission ? json_decode($userRolePermission[0]['role_ids_obj'], true) : null;
        
//        $data_result->setUserPerms($userPermission->toArray());
        
        $response = Utilities::buildSuccessResponse(10004, "User Perms List.", $data_result);
        
        return response()->json($response, $status);   
    }
}
