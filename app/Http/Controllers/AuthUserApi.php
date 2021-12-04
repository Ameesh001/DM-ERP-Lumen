<?php

/**
 * Performance system API
 * This is a Country API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\AuthUser;
use App\Models\Client;
use App\Models\Language;
use App\Response\AuthUserResponse;
use Illuminate\Http\Request;
use App\Utilities\Utilities;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\DB;

class AuthUserApi extends Controller
{

    /**
     * Constructor
     */
    public function __construct()
    {

    }

    private $select_columns = [
        'users.id', 
        'users.keycloak_id', 
        'users.username', 
        'users.firstName', 
        'users.lastName', 
        'users.full_name', 
        'users.phone', 
        'users.email', 
        'users.address', 
        'users.user_type', 
        'users.is_enable as activate',  
        'users.department_id' , 
        'users.designation_id', 
        'users.is_teacher', 
        'users.whatsapp_num', 
        'users.gender', 
        'users.education', 
        'users.reporting_manager', 
        'users.is_manager'
    ];

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
     * Add User.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addAuthUser(Request $request)
    {
        $mdlUser = new AuthUser();

        $this->validate($request, $mdlUser->rules($request), $mdlUser->messages($request));
        
        $mdlUser->filterColumns($request);
        
        Utilities::defaultAddAttributes($request, $request->data_user_id);

        $postArr = $request->all();
        
//        $postArr = array_map('trim', $postArr);
        
        $postArr['full_name'] = $postArr['firstName'] . ' ' . $postArr['lastName'];
        
//        return response()->json($postArr, 201);
        $password = $postArr['password'];
        $postArr['username']    = trim($postArr['username']);
        $postArr['firstName']   = trim($postArr['firstName']);
        $postArr['lastName']    = trim($postArr['lastName']);
        $postArr['full_name']   = trim($postArr['full_name']);
        $postArr['phone']       = trim($postArr['phone']);
        $postArr['address']     = trim($postArr['address']);
        $postArr['email']       = trim($postArr['email']);
        
//        unset($postArr['password']);
        $obj = AuthUser::create($postArr);
        
        if($obj){
            $html  = '';
            $html .= '<div class="container">';
            $html .= '<h2>Congrats</h2>';
            $html .= '<p>Darulmadinah Schooling System</p><hr>';
            $html .= '<span>Name: '. $postArr['username'].'</span><br>
                        <span>Email: '.$postArr['email'].'</span><br>
                        <span>Password: '.$password.'</span><br>
                        <span>Link: <a href="https://stage-dmsms-client.dibaadm.com/" target="_blank">https://stage-dmsms-client.dibaadm.com/</a> </span><br><hr>
                        <span>Note: Kindly no reply here.</span>
                     </div>';

            $param['html']     = $html;
            $param['to']       = $postArr['email'];
            $param['to_name']  = $postArr['username'];
            $sendMail = $this->sendMail($param);  
            
            if($sendMail === true){
                $data = [ 'id' => $obj->id ];
                $response = Utilities::buildSuccessResponse(10000, "User successfully created.", $data);
            }else{
//                $response = Utilities::buildSuccessResponse(10003, "Email Not Send Some error", $sendMail);
            }
           
        }
        return response()->json($response, 201);
       
    }

    /**
     * Update User.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAuthUser(Request $request)
    {
        $mdlUser = new AuthUser();

        $this->validate($request, $mdlUser->rules($request), $mdlUser->messages($request));
        
        $mdlUser->filterColumns($request);
        
        Utilities::defaultUpdateAttributes($request, $request->data_user_id);

        $user = AuthUser::find($request->id);

        $status = 200;
        $response = [];
        
        if (! $user) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "User not found.");
        } else {
            $postArr = $request->all();
            
            $postArr = array_map('trim', $postArr);
            
            // return response()->json($postArr, $status);
            $postArr['full_name'] = $postArr['firstName'] . ' ' . $postArr['lastName'];
            
//            $postArr['username'] = trim($postArr['username']);
//            $postArr['firstName'] = trim($postArr['firstName']);
//            $postArr['lastName'] = trim($postArr['lastName']);
//            $postArr['full_name'] = trim($postArr['full_name']);
//            $postArr['phone'] = trim($postArr['phone']);
//            $postArr['address'] = trim($postArr['address']);
//            $postArr['email'] = trim($postArr['email']);
            $obj = $user->update($postArr);
            
            if ($obj) {
                $data = [ 'id' => $user->id ];
                $data = [ 'keycloak_id' => $user->keycloak_id ];
                $response = Utilities::buildSuccessResponse(10001, "User successfully updated.", $data);
            }
        }
        
        return response()->json($response, $status);
    }
    
    /**
     * Activate/De-Activate User.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function patchDisableEnableAuthUser(Request $request)
    {
        $mdlUser = new AuthUser();

        Utilities::removeAttributesExcept($request, ["id","activate"]);
        
        $this->validate($request, $mdlUser->rules($request), $mdlUser->messages($request));
        
        Utilities::defaultAddAttributes($request, $request->data_user_id);
        
        $activate = $request->activate == 1 ? Constant::RecordType['ENABLED'] : Constant::RecordType['DISABLED'];
        
        $request->request->add([ 'is_enable' => $activate ]);
        
        $user = AuthUser::find($request->id);
        $status = 200;
        $response = [];

        if (! $user) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "User not found.");
        } else {
            
            $obj = $user->update($request->all());
            
            if ($obj) {
                $data = ['id' => $user->id ];
                $actMsg = $request->activate == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "User successfully $actMsg.", $data);
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Delete User.
     *
     * @param $id 'ID' of User to delete. (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAuthUser($id, Request $request)
    {
        $mdlUser = new AuthUser();
        
        $request->request->add([ 'id' => $id ]);
        
        $this->validate($request, $mdlUser->rules($request), $mdlUser->messages($request));
        
        Utilities::defaultDeleteAttributes($request, 1);
        
        $request->request->add([ 'is_enable' => Constant::RecordType['DELETED'] ]);
        
        $user = AuthUser::find($request->id);
        
        $status = 200;
        $response = [];
        if (! $user) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "User not found.");
        } else {
            
            $obj = $user->update($request->all());
            
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "User successfully deleted.");
            }
        }
        
        return response()->json($response, $status);
    }
    
    /**
     * Delete User.
     *
     * @param $id 'ID' of User to delete. (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
//    public function hardDeleteAuthUser($id, Request $request)
//    {
////        $mdlUser = new AuthUser();
//        
////        $request->request->add([ 'id' => $id ]);
//        
////        $this->validate($request, $mdlUser->rules($request), $mdlUser->messages($request));
//        
////        Utilities::defaultDeleteAttributes($request, 1);
//        
////        $request->request->add([ 'is_enable' => Constant::RecordType['DELETED'] ]);
//        
//        $user = AuthUser::where('keycloak_id', '=', $id);
//        
//        $status = 200;
//        $response = [];
//        if (! $user) {
//            $status = 404;
//            $response = Utilities::buildBaseResponse(10003, "User not found.");
//        } else {
//            
//            $obj = $user->delete();
//            
//            if ($obj) {
//                $response = Utilities::buildBaseResponse(10006, "User successfully deleted.");
//            }
//        }
//        
//        return response()->json($response, $status);
//    }

    /**
     * Get one User.
     *
     * @param $id 'ID' of User to return (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOneAuthUser($id, Request $request)
    {
        $mdlUser = new AuthUser();
        
        $request->request->add([ 'id' => $id ]);
        
        $this->validate($request, $mdlUser->rules($request, Constant::RequestType['GET_ONE']), $mdlUser->messages($request, Constant::RequestType['GET_ONE']));
        
        $select = $this->select_columns;

        $mdlUser->filterColumns($request);
        
        if($request->fields){
            $select = $request->fields;
        }
        
        $user = AuthUser::where('id', $request->id)->first($select);
//        $user->client;
        $user->user_roles_permission;
        $user->user_data_permission;

        $status = 200;
        $response = [];

        if (! $user) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "User not found.");
        } else {
            $dataResult = array("user" => $user->toArray());
            $response = Utilities::buildSuccessResponse(10005, "User Data.", $dataResult);
        }
        
        return response()->json($response, $status);
    }
    
    /**
     * Get one User roles and levels by keycloak id.
     *
     * @param $id 'ID' of User to return (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserRolesLevels($kc_id, Request $request)
    {
        $mdlUser = new AuthUser();
        
        // $request->request->add([ 'keycloak_id' => $kc_id ]);
        // $this->validate($request, $mdlUser->rules($request, Constant::RequestType['GET_ONE']), $mdlUser->messages($request, Constant::RequestType['GET_ONE']));
        
        $select = ['id', 'keycloak_id', 'user_type'];

        $mdlUser->filterColumns($request);
        
        if($request->fields){
            $select = $request->fields;
        }
        
        $user = AuthUser::where('keycloak_id', $request->kc_id)->first($select);
        $user->user_role_level_permission;
        $user->user_data_permission;

        $status = 200;
        $response = [];

        if (! $user) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "User not found.");
        } else {
            $dataResult = array("user" => $user->toArray());
            $response = Utilities::buildSuccessResponse(10005, "User role level.", $dataResult);
        }
        
        return response()->json($response, $status);
    }

    public function getUserLang($kc_id){

        $user = AuthUser::where('keycloak_id', $kc_id)->first(['user_type']);
        $status = 200;

        if($user->user_type == 1){
            $dataResult = [
                'client_lang' => 'eng',
                'lang_dir' => 'ltr',
                'lang_name' => 'English'
            ];

            // $dataResult = [
            //     'client_lang' => 'urd',
            //     'lang_dir' => 'rtl',
            //     'lang_name' => 'Urdu'
            // ];
            
            $response = Utilities::buildSuccessResponse(10005, "Default Language.", $dataResult);

        }else{
            $user->client_lang;
    
            $response = [];
    
            if (! $user) {
                $status = 404;
                $response = Utilities::buildBaseResponse(10003, "User language not found.");
            } else {
                // $client_lang = $user->client_lang;
                // $lang_dir = 'rtl';
                
                // if($client_lang->lang_dir == 2){
                //     $lang_dir = 'ltr';
                // }
                
                // $dataResult = [
                //     'client_lang' => $client_lang->lang_code,
                //     'lang_dir' => $lang_dir,
                //     'lang_name' => $client_lang->lang_name
                // ];
                $dataResult = [
                    'client_lang' => 'eng',
                    'lang_dir' => 'ltr',
                    'lang_name' => 'English'
                ];
                
                $response = Utilities::buildSuccessResponse(10005, "User Language.", $dataResult);
            }
        }
        
        return response()->json($response, $status);
    }


    /**
     * Fetch list of User by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllAuthUser(Request $request)
    {
        $mdlUser = new AuthUser();
        
        $this->validate($request, $mdlUser->rules($request, Constant::RequestType['GET_ALL']), $mdlUser->messages($request, Constant::RequestType['GET_ALL']));
        
        $pageSize = $request->limit ?? Constant::PageSize;
        if($pageSize > Constant::MaxPageSize){
            $pageSize = Constant::PageSize;
        }
        $page = $request->page ?? Constant::Page;
        $skip = ($page - 1) * $pageSize;

        
        $select = $this->select_columns;

        $mdlUser->filterColumns($request);
        
        if($request->fields){
            $select = $request->fields;
        }
        
        $whereData = array();
 
        
        if($request->user_list == 1) {
            
            if($request->data_user_id != null) {
                $whereData[] = ['users.id', '!=' , $request->data_user_id];
            }
        }
        
        if($request->keycloak_id != null) {
            $whereData[] = ['users.keycloak_id', $request->keycloak_id];
        }
        
        if($request->username) {   
            $whereData[] = ['users.username', 'LIKE', "%{$request->username}%"];
        }
        if($request->full_name) {   
            $whereData[] = ['users.full_name', 'LIKE', "%{$request->full_name}%"];
        }
        if($request->phone) {
            $whereData[] = ['users.phone', 'LIKE', "%{$request->phone}%"];
        }
        if($request->email) {
            $whereData[] = ['users.email', 'LIKE', "%{$request->email}%"];
        }
        if($request->address) {   
            $whereData[] = ['users.address', 'LIKE', "%{$request->address}%"];
        }
        if($request->user_type != null) {
            $whereData[] = ['users.user_type', $request->user_type];
        }
        if($request->activate != null) {
            $whereData[] = ['users.is_enable', $request->activate];
        }

       
       
        
        $orderBy =  $mdlUser->getOrderColumn($request->order_by);
        $orderType = $request->order_type ?? Constant::OrderType;
        
        $data_user_id = $request->data_user_id;
                
        Utilities::set_all_data_permission_request_parameters($request);
        
        $data_org_id       = $request->data_p_org_id;
        $data_country_id   = $request->data_p_country_id;
        $data_state_id     = $request->data_p_state_id;
        $data_region_id    = $request->data_p_region_id;
        $data_city_id      = $request->data_p_city_id;
        $data_campus_id    = $request->data_p_campus_id;
                
       
        $total_record_q    = AuthUser::with('user_data_permission')->where($whereData);
        
        if($request->user_list == 1){
            
            $total_record_q = $this->user_data_or_where($total_record_q, $data_org_id,      1, $data_user_id);
            $total_record_q = $this->or_has_user_data_or_where($total_record_q, $data_country_id,  2, $data_user_id);
            $total_record_q = $this->or_has_user_data_or_where($total_record_q, $data_state_id,    3, $data_user_id);
            $total_record_q = $this->or_has_user_data_or_where($total_record_q, $data_region_id,   4, $data_user_id);
            $total_record_q = $this->or_has_user_data_or_where($total_record_q, $data_city_id,     5, $data_user_id);
            $total_record_q = $this->or_has_user_data_or_where($total_record_q, $data_campus_id,   6, $data_user_id);
            
            
//            if(!empty($data_org_id)){
//                $total_record_q->whereHas('user_data_permission', function ($q) use ($data_org_id, $data_country_id, $data_state_id, $data_region_id, $data_city_id, $data_campus_id) {
//
//
//                    $q = $this->user_data_or_where_($q, $data_org_id,      1);
//                    $q = $this->user_data_or_where_($q, $data_country_id,  2);
//                    $q = $this->user_data_or_where_($q, $data_state_id,    3);
//                    $q = $this->user_data_or_where_($q, $data_region_id,   4);
//                    $q = $this->user_data_or_where_($q, $data_city_id,     5);
//                    $q = $this->user_data_or_where_($q, $data_campus_id,   6);
//
//                });
//            }
            
            $total_record_q->orWhere(function($query) use ($data_user_id) {
                $query->where('created_by', $data_user_id);
                $query->where('is_data_perm', 0);
            });
            
        }
        
        $total_record = $total_record_q->active()->count();
        
//         $whereData[] = ['users.is_enable1', 1];
        $queryObj = AuthUser::select($select)->with('user_data_permission')->where($whereData);
        
        if($request->user_list == 1){
            
            if(!empty($data_org_id)){
            
//                $queryObj->whereHas('user_data_permission', function ($q) use ($data_org_id, $data_country_id, $data_state_id, $data_region_id, $data_city_id, $data_campus_id) {
//
//
//                    $q = $this->user_data_or_where_($q, $data_org_id,      1);
//                    $q = $this->user_data_or_where_($q, $data_country_id,  2);
//                    $q = $this->user_data_or_where_($q, $data_state_id,    3);
//                    $q = $this->user_data_or_where_($q, $data_region_id,   4);
//                    $q = $this->user_data_or_where_($q, $data_city_id,     5);
//                    $q = $this->user_data_or_where_($q, $data_campus_id,   6);
//
//                });
            }
        
            $queryObj = $this->user_data_or_where($queryObj, $data_org_id,      1, $data_user_id);
            $queryObj = $this->or_has_user_data_or_where($queryObj, $data_country_id,  2, $data_user_id);
            $queryObj = $this->or_has_user_data_or_where($queryObj, $data_state_id,    3, $data_user_id);
            $queryObj = $this->or_has_user_data_or_where($queryObj, $data_region_id,   4, $data_user_id);
            $queryObj = $this->or_has_user_data_or_where($queryObj, $data_city_id,     5, $data_user_id);
            $queryObj = $this->or_has_user_data_or_where($queryObj, $data_campus_id,   6, $data_user_id);
            
        }
        
        $queryObj->orWhere(function($query) use ($data_user_id) {
            $query->where('created_by', $data_user_id);
            $query->where('is_data_perm', 0);
        });
        
        $queryObj->active();
        $queryObj->orderBy($orderBy, $orderType);
        $queryObj->offset($skip);
        $queryObj->limit($pageSize);
        
        $users = $queryObj->get();

     
        foreach ($users as $key => $user) {
            $user->user_roles_permission;

            $user->user_data_permission;
        }

        $status = 200;
       
        $data_result['users'] = $users->toArray();
        $data_result['total_record'] = $total_record;
        $response = Utilities::buildSuccessResponse(10004, 'List ', $data_result);
        
        return response()->json($response, $status);   
    }
    
    public function user_data_or_where($queryObj, $ids, $level_id = 1, $user_id = 1){
        
        if (!empty($ids)) {
            $queryObj->whereHas('user_data_permission', function ($q) use ($ids, $level_id, $user_id) {
                $q->where(function($query) use ($ids, $level_id, $user_id) {
                    $query->where('hierarchy_level_id', $level_id);
                    $query->where('user_id', '!=', $user_id);
                    foreach ($ids as $key => $value) {
                        $query->orWhere('user_data_permission.data_permissions_id', '=', $value);
                    }
                });
            });
        }
        
        return $queryObj;
    }
    
    public function or_has_user_data_or_where($queryObj, $ids, $level_id = 1, $user_id  = 1){
        
        if (!empty($ids)) {
            $queryObj->orwhereHas('user_data_permission', function ($q) use ($ids, $level_id, $user_id) {
                $q->where(function($query) use ($ids, $level_id, $user_id) {
                    $query->where('hierarchy_level_id', $level_id);
                    $query->where('user_id', '!=', $user_id);
                    foreach ($ids as $key => $value) {
                        $query->orWhere('user_data_permission.data_permissions_id', '=', $value);
                    }
                });
            });
        }
        
        return $queryObj;
    }
    
    public function user_data_or_where_($q, $ids, $level_id = 1){
        
        if (!empty($ids)) {
            
            $q->where(function($query) use ($ids, $level_id) {
                $query->where('hierarchy_level_id', $level_id);
                foreach ($ids as $key => $value) {
                    $query->orWhere('user_data_permission.data_permissions_id', '=', $value);
                }
            });
            
        }
        
        return $q;
    }
    
    /**
     * Fetch list of User by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserMaster(Request $request)
    {   
        $mdlUser = new AuthUser();
        
        $this->validate($request, $mdlUser->rules($request, Constant::RequestType['GET_ALL']), $mdlUser->messages($request, Constant::RequestType['GET_ALL']));
        
        
        $select = $this->select_columns;

        $mdlUser->filterColumns($request);
        
        if($request->fields){
            $select = $request->fields;
        }
        
        $whereData = array();
        
      
        if($request->id != null) {
            $whereData[] = ['id', $request->id];
        }
        
        if(!$request->ignore_keycloack){
            if($request->keycloak_id != null) {
                $whereData[] = ['keycloak_id', $request->keycloak_id];
            }
        }
        
        
        if($request->is_manager != null) {
            $whereData[] = ['is_manager', $request->is_manager];
        }
        
        if($request->is_teacher != null) {
            $whereData[] = ['is_teacher', $request->is_teacher];
        }
        
        if($request->username) {   
            $whereData[] = ['username', 'LIKE', "%{$request->username}%"];
        }
        if($request->full_name) {   
            $whereData[] = ['full_name', 'LIKE', "%{$request->full_name}%"];
        }
        if($request->phone) {
            $whereData[] = ['phone', 'LIKE', "%{$request->phone}%"];
        }
        if($request->email) {
            $whereData[] = ['email', 'LIKE', "%{$request->email}%"];
        }
        if($request->address) {   
            $whereData[] = ['address', 'LIKE', "%{$request->address}%"];
        }
        if($request->user_type != null) {
            $whereData[] = ['user_type', $request->user_type];
        }
        if($request->activate != null) {
            $whereData[] = ['is_enable', $request->activate];
        }else{
            $whereData[] = ['is_enable', 1];
        }

       
        $queryObj = AuthUser::where($whereData);
        $queryObj->active();

        $users = $queryObj->get($select);

        foreach ($users as $key => $user) {
            $user->user_roles_permission;
        }

        $status = 200;
        $data_result['users'] = $users->toArray();
        $response = Utilities::buildSuccessResponse(10004, "User List.", $data_result);
        
        return response()->json($response, $status);   
    }
    
    public static function sendMail($param)
    {

        require(base_path('/PHPMailer-master/src/PHPMailer.php'));
        require(base_path('/PHPMailer-master/src/SMTP.php'));
        require(base_path('/PHPMailer-master/src/Exception.php'));

        $mail = new PHPMailer(true); // notice the \  you have to use root namespace here
        try {
            $mail->isSMTP(); // tell to use smtp
            $mail->CharSet = "utf-8"; // set charset to utf8
            $mail->SMTPAuth = true;  // use smpt auth
            $mail->SMTPSecure = "tls"; // or ssl
            $mail->Host = "smtp.gmail.com";
            $mail->Port = 587; // most likely something different for you. This is the mailtrap.io port i use for testing. 
            //
            //
//            $mail->Username = "stage email";
//            $mail->Password = "stage pasword";
            
            $mail->Username = "it.dev7@dawateislami.net";
            $mail->Password = "11a1996aptechgdn";
            
            // for sending from localhost email 
            $mail->smtpConnect(
                array(
                    "ssl" => array(
                        "verify_peer" => false,
                        "verify_peer_name" => false,
                        "allow_self_signed" => true
                    )
                )
            );

//            $mail->setFrom("stage email", "Dawateislami Schooling System");
            $mail->setFrom("it.dev7@dawateislami.net", "Dawateislami Schooling System");
            
            $mail->Subject = !empty($param['subject']) ? $param['subject'] : 'Dawateislami';
            $mail->MsgHTML($param['html']);
            $mail->addAddress($param['to'], $param['to_name']);
            $mail->send();
        } catch (phpmailerException $e) {
            return $e;
//            dd($e);
        } catch (Exception $e) {
            return $e;
//            dd($e);
        }
        return true;
    }
    
}
