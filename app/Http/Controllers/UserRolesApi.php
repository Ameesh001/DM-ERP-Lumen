<?php

/**
 * Performance system API
 * This is a User Role API controller
 *
 */

namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\UserRoles;
use App\Response\UserRolesResponse;
use Illuminate\Http\Request;
use App\Utilities\Utilities;
use Illuminate\Support\Facades\DB;
class UserRolesApi extends Controller
{

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    private $select_columns = ['id', 'role_name as role', 'organization_id', 'is_enable as activate'];

    /**
     * This fucntion is called after validation fails in function $this->validate.
     * 
     * @param Request $request
     * @param array $errors
     * @return \Illuminate\Http\JsonResponse
     */

    protected function buildFailedValidationResponse(Request $request, array $errors){
        $response = Utilities::buildFailedValidationResponse(10000, "Unprocesssable Entity.", $errors);
        return response()->json($response, 400);
    }

    /**
     * Add Role.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addUserRoles(Request $request)
    {
      
       $mdlUserRoles = new UserRoles();

       $this->validate($request, $mdlUserRoles->rules($request), $mdlUserRoles->messages($request));

       $mdlUserRoles->filterColumns($request);

        Utilities::defaultAddAttributes($request, $request->data_user_id);

        $obj = UserRoles::create($request->all());

        $data = ['id' => $obj->id];

        $response = Utilities::buildSuccessResponse(10000, "Role successfully created.", $data);

        return response()->json($response, 201);
    }

    /**
     * Update Role.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateUserRoles(Request $request)
    {
        $mdlUserRoles = new UserRoles();

        $this->validate($request, $mdlUserRoles->rules($request), $mdlUserRoles->messages($request));

        $mdlUserRoles->filterColumns($request);

        Utilities::defaultAddAttributes($request, $request->data_user_id);

        $role = UserRoles::find($request->id);

        $status = 200;
        $response = [];

        if (!$role) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Role not found.");
        } else {

            $obj = $role->update($request->all());

            if ($obj) {
                $data = ['id' => $role->id];
                $response = Utilities::buildSuccessResponse(10001, "Role successfully updated.", $data);
            }
        }

        return response()->json($response, $status);
    }

    /**
     * Activate/De-Activate Role.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function patchDisableEnableUserRoles(Request $request)
    {
        $mdlUserRoles = new UserRoles();

        Utilities::removeAttributesExcept($request, ["id", "activate"]);

        $this->validate($request, $mdlUserRoles->rules($request), $mdlUserRoles->messages($request));

        Utilities::defaultAddAttributes($request, $request->data_user_id);

        $activate = $request->activate == 1 ? Constant::RecordType['ENABLED'] : Constant::RecordType['DISABLED'];

        $request->request->add(['is_enable' => $activate]);

        $role = UserRoles::find($request->id);

        $status = 200;
        $response = [];

        if (!$role) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Role not found.");
        } else {

            $obj = $role->update($request->all());

            if ($obj) {
                $data = ['id' => $role->id];
                $actMsg = $request->activate == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "Role successfully $actMsg.", $data);
            }
        }

        return response()->json($response, $status);
    }

    /**
     * Delete Role.
     *
     * @param $id 'ID' of Role to delete. (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteUserRoles($id, Request $request)
    {
        $mdlUserRoles = new UserRoles();

        $request->request->add(['id' => $id]);

        $this->validate($request, $mdlUserRoles->rules($request), $mdlUserRoles->messages($request));

       // $mdlUserRoles->defaultDeleteAttributes($request, 1);

        $request->request->add(['is_enable' => Constant::RecordType['DELETED']]);

        $role = UserRoles::find($request->id);

        $status = 200;
        $response = [];
        if (!$role) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Role not found.");
        } else {

            $obj = $role->update($request->all());

            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "Role successfully deleted.");
            }
        }

        return response()->json($response, $status);
    }

    /**
     * Get one Role.
     *
     * @param $id 'ID' of Role to return (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOneUserRoles($id, Request $request)
    {
        $mdlUserRoles = new UserRoles();

        $request->request->add(['id' => $id]);

        $this->validate($request, $mdlUserRoles->rules($request, Constant::RequestType['GET_ONE']), $mdlUserRoles->messages($request, Constant::RequestType['GET_ONE']));

        $select = $this->select_columns;

        $mdlUserRoles->filterColumns($request);

        if ($request->fields) {
            $select = $request->fields;
        }
       
        $role = UserRoles::where('id', $request->id)->first($select);
        $role->Organization ?? null;
        //$role->department ?? null;

        $status = 200;
        $response = [];

        if (!$role) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Role not found.");
        } else {
            $status = 200;
            $dataResult = array("role" => $role->toArray());
            $response = Utilities::buildSuccessResponse(10005, "Role Data.", $dataResult);
        }

        return response()->json($response, $status);
    }

    /**
     * Fetch list of Role by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllUserRoles(Request $request)
    {
        $mdlUserRoles = new UserRoles();

        $this->validate($request, $mdlUserRoles->rules($request, Constant::RequestType['GET_ALL']), $mdlUserRoles->messages($request, Constant::RequestType['GET_ALL']));

        $pageSize = $request->limit ?? Constant::PageSize;
        if ($pageSize > Constant::MaxPageSize) {
            $pageSize = Constant::PageSize;
        }
        $page = $request->page ?? Constant::Page;
        $skip = ($page - 1) * $pageSize;


        $select = $this->select_columns;

        $mdlUserRoles->filterColumns($request);

        if ($request->fields) {
            $select = $request->fields;
        }

        $whereData = array();
        // $whereClientData = array();
        // $whereDeptData = array();

        if ($request->organization_id) {
            $whereData[] = ['organization_id', $request->organization_id];
        }
        // if ($request->dept_id) {
        //     $whereData[] = ['dept_id', $request->dept_id];
        // }
        if ($request->role) {
            $whereData[] = ['role_name', 'LIKE', "%{$request->role}%"];
        }
        // if ($request->order) {
        //     $whereData[] = ['sorting', 'LIKE', "%{$request->order}%"];
        // }
        if ($request->activate != null) {
            $whereData[] = ['is_enable', $request->activate];
        }
        // if ($request->client) {
        //     if ($request->client['name']) {
        //         $whereClientData[] = ['client_name', 'LIKE', "%{$request->client['name']}%"];
        //     }
        // }
        // if ($request->department) {
        //     if ($request->department['dept_name']) {
        //         $whereDeptData[] = ['dept_name', 'LIKE', "%{$request->department['dept_name']}%"];
        //     }
        // }

        $orderBy =  $mdlUserRoles->getOrderColumn($request->order_by);
        $orderType = $request->order_type ?? Constant::OrderType;

       // $queryObj = UserRoles::all();
       $total_record = UserRoles::where($whereData)->active()->count();
        $queryObj = UserRoles::with('Organization');
        $queryObj->where($whereData);
        // $queryObj = UserRoles::where($whereData);
        $queryObj->active();
        $queryObj->orderBy($orderBy, $orderType);
        $queryObj->offset($skip);
        $queryObj->limit($pageSize);
        
        // if ($whereClientData) {
        //     $queryObj->whereHas('client', function ($query) use ($whereClientData) {
        //         $query->where($whereClientData);
        //     });
        // }
        // if ($whereDeptData) {
        //     $queryObj->whereHas('department', function ($query) use ($whereDeptData) {
        //         $query->where($whereDeptData);
        //     });
        // }
        // $queryObj->Organization;
        $roles = $queryObj->get($select);
       
        $status = 200;
        // $data_result = new UserRolesResponse();
        // $data_result->setRoles($roles->toArray());
        $data_result['roles'] = $roles->toArray();
        $data_result['total_record'] = $total_record;
        $response = Utilities::buildSuccessResponse(10004, "Role List.", $data_result);
        // print_r(DB::table($queryObj)->toSql());
        return response()->json($response, $status);
    }
    
    /**
     * Fetch list of Role for selectbox/dropdown by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllRoles(Request $request)
    {
        $mdlUserRoles = new UserRoles();

        $this->validate($request, $mdlUserRoles->rules($request, Constant::RequestType['GET_ALL']), $mdlUserRoles->messages($request, Constant::RequestType['GET_ALL']));

        $select = $this->select_columns;

        $mdlUserRoles->filterColumns($request);

        if ($request->fields) {
            $select = $request->fields;
        }

        $whereData = array();
        
       
//        return response()->json(var_dump($request->organization_id), 200);
        
        
        if ($request->organization_id) {

            if(strlen($request->organization_id) > 1){
               $organization_id_arr = explode(",", $request->organization_id);
            }else{
                $whereData[] = ['organization_id',  $request->organization_id];
            }            
        }

        if ($request->role) {
            $whereData[] = ['role_name', 'LIKE', "%{$request->role}%"];
        }
 
        if ($request->activate != null) {
            $whereData[] = ['is_enable', $request->activate];
        }else{
            $whereData[] = ['is_enable', 1];
        }
 
        if ($request->id != null) {
            $whereData[] = ['id', $request->id];
        } 
        
        // return response()->json($request->role_id, 200);
        // exit;

        if ($request->role_id) {

            if(strlen($request->role_id) > 1){
               $role_id_arr = explode(",", $request->role_id);
            }else{
                $whereData[] = ['id',  '!=', $request->role_id];
            }            
        }

        $queryObj = UserRoles::where($whereData);
        
        
        if(!empty($role_id_arr)){
            $queryObj->whereNotIn('id', $role_id_arr);
        }

        if(!empty($organization_id_arr)){
            $queryObj->whereIn('organization_id', $organization_id_arr);
        }
        
        $queryObj->active();
        $roles = $queryObj->get($select);

        $status = 200;
        
//        $data_result = new UserRolesResponse();
//        $data_result->setRoles($roles->toArray());
        $data_result['roles'] = $roles->toArray();
        
        $response = Utilities::buildSuccessResponse(10004, "Role List.", $data_result);
        return response()->json($response, $status);
    }



    /**
     * Fetch list of Role for selectbox/dropdown by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllRoles_org(Request $request)
    {
        $mdlUserRoles = new UserRoles();  
        $whereData = array(); 
        if ($request->data_organization_id) {
            $whereData[] = ['organization_id',  $request->data_organization_id];       
        }
        $whereData[] = ['is_enable', 1];
        $queryObj = UserRoles::where($whereData);
        
        $queryObj->active();
        $roles = $queryObj->get(['id', 'role_name']);
        $status = 200;
        $data_result['roles'] = $roles->toArray(); 
        $response = Utilities::buildSuccessResponse(10004, "Role List.", $data_result);
        return response()->json($response, $status);
    }

}
