<?php

namespace App\Utilities;

use Illuminate\Http\Request;
use App\Config\Constant;
use App\Response\ValidationResponse;
use App\Response\ValidationReponseDetail;
use App\Config\CleanJsonSerializer;
use App\Response\SuccessResponse;
use App\Response\BaseResponse;
use Illuminate\Support\Facades\DB;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Models\UserDataPermission;
use App\Models\AuthUser;

class Utilities
{
    public static function getUserID_by_keycloak_id($keyCloak_id){

        // $users_id = DB::table('users')->where('keycloak_id', $keyCloak_id)->get('id');
        $users_id_q = AuthUser::where('keycloak_id', $keyCloak_id);
        
        if($users_id_q->count() >0){
            $users_id = $users_id_q->first('id');
            return $users_id->id;
        }
        return 0;
        // $response = new BaseResponse();
        // $response->setCode('10003');
        // $response->setMessage('User not found.');
        
        // $cleanJsonSerializer = new CleanJsonSerializer();
        // $response =  $cleanJsonSerializer->serialize($response);
        // return response()->json($response, 201);
        // exit;
    }

    public static function get_country_org_by_campus_id($campus_id){

        $pre_fix = DB::table('campus')
        ->join('countries', 'campus.countries_id', '=', 'countries.id')
        ->where('campus.id','=' , $campus_id)
        ->get(['countries.*', 'campus.*'])
        ->toArray();
        

        return $pre_fix;
    }

//    request method default values enhancements
    public static function defaultAddAttributes(Request $request, $created_by, $is_enabled = Constant::RecordType['ENABLED'], $created_dt = null)
    {
        $created_dt = ($created_dt == null) ? date('Y-m-d H:i:s') : $created_dt;
        
        $request->request->add([
            'is_enable' => $is_enabled,
            'created_by' => $created_by,
            'created_at' => $created_dt
        ]);
    
        
    }

    public static function defaultUpdateAttributes(Request $request, $updated_by, $updated_dt = null)
    {
        $updated_dt = ($updated_dt == null) ? date('Y-m-d H:i:s') : $updated_dt;
       
        
        $request->request->add([
            'updated_by' => $updated_by,
            'updated_at' => $updated_dt
        ]);
    }
    
    public static function defaultDeleteAttributes(Request $request, $updated_by, $deleted_dt = null)
    {
        $deleted_dt = ($deleted_dt == null) ? date('Y-m-d H:i:s') : $deleted_dt;
        
        $request->request->add([
            'deleted_at' => $deleted_dt
        ]);
    }

    
    
    
//    postarr defualt values addtion 
    public static function defaultAddAttributesArr($postArr, $created_by, $is_enabled = Constant::RecordType['ENABLED'], $created_dt = null)
    {
        $created_dt = ($created_dt == null) ? date('Y-m-d H:i:s') : $created_dt;
        
        return $postArr += [
            'is_enable' => $is_enabled,
            'created_by' => $created_by,
            'created_at' => $created_dt
        ];
        
        
        
    }

    public static function defaultUpdateAttributesArr($postArr, $updated_by, $updated_dt = null)
    {
        $updated_dt = ($updated_dt == null) ? date('Y-m-d H:i:s') : $updated_dt;
       
        return $postArr += [
            'updated_by' => $updated_by,
            'updated_at' => $updated_dt
        ];
    }
    
    public static function defaultDeleteAttributesArr($postArr, $updated_by, $deleted_dt = null)
    {
        $deleted_dt = ($deleted_dt == null) ? date('Y-m-d H:i:s') : $deleted_dt;
        
        return $postArr += [
            'deleted_at' => $deleted_dt
        ];
    }
    
    /**
     * Remove keys except provided, first convert values to keys b/c keys are in values and indexes in keys
     * 
     * @param Request $request
     * @param array $keys
     * 
     */
    public static function removeAttributesExcept(Request $request, array $keys)
    {
        //  Set indexes as values and keys at key
        $onlyKeys = array();
        foreach ($keys as $key => $value) {
            $onlyKeys[$value] = $key;
        }

        //  Remove keys except provided.
        $data = $request->all();
        foreach ($data as $key => $value) {
            
            if(!array_key_exists($key, $onlyKeys)) {
                $request->request->remove($key);
            }
        }
    }
    
    /**
     * This static fucntion can be called to generate failed json response.
     *
     * @param number $code
     * @param string $message
     * @param array $errors
     * @return \Illuminate\Http\JsonResponse
     */
    public static function buildFailedValidationResponse($code, $message, array $errors)
    {
        $response = new ValidationResponse();
        $response->setCode($code);
        $response->setMessage($message);
        
        $vdList = array();
        
        foreach ($errors as $key => $values) {
            
            $vd = new ValidationReponseDetail();
            $vd->setType("validation_error");
            $vd->setField_name($key);
            $vd->setDetail($values);
            $vdList[] = $vd;
        }
        
        $response->setErrors($vdList);
        
        $cleanJsonSerializer = new CleanJsonSerializer();
        return $cleanJsonSerializer->serialize($response);
        
    }
    
    /**
     * This static fucntion can be called to generate success json response.
     *
     * @param number $code
     * @param string $message
     * @param array $data
     * @return array
     */
    public static function buildSuccessResponse($code, $message, $data)
    {
        $response = new SuccessResponse();
        $response->setCode($code);
        $response->setMessage($message);
        $response->setData($data); 
        $cleanJsonSerializer = new CleanJsonSerializer();

        return $cleanJsonSerializer->serialize($response);
    }
    
    /**
     * This static fucntion can be called to generate base json response.
     *
     * @param number $code
     * @param string $message
     * @return array
     */
    public static function buildBaseResponse($code, $message)
    {
        $response = new BaseResponse();
        $response->setCode($code);
        $response->setMessage($message);
        
        $cleanJsonSerializer = new CleanJsonSerializer();
        return $cleanJsonSerializer->serialize($response);
    }
    
   /**
     * Set column Name with Actual name.
     * 
     * @param string $request
     * @return array
     */
    public static function filterColumnsModel(Request $request, $columnList, $method)
    {
        if ($method == 'GET') {
            
            if($request['fields']){
                $data = explode(",", ($request['fields']));

                foreach ($data as $key => $value) {
                    if (!isset($columnList[$value])) {
                        unset($data[$key]);
                    } else {
                        $data[$key] = $columnList[$value].' as '. $value;
                    }
                }
                
                $request->merge( [ 'fields' => $data] );
            }
        }
         
        if ($method == 'POST' || $method == 'PUT') {
            $data = $request->all();

            foreach ($data as $key => $value) {
                if (array_key_exists($key, $columnList)) {
                    $request->merge([
                        $columnList[$key] => $value,
                    ]);
                } else {
                    $request->request->remove($key);
                }
            }
        }
    } 
    
    
    public static function set_all_data_permission_request_parameters(Request $request){
        $UserDataPermission = UserDataPermission::where(['user_id' => $request->data_user_id])->get()->toArray();
        
        $data_org_id        = [];
        $data_country_id    = [];
        $data_state_id      = [];
        $data_region_id     = [];
        $data_city_id       = [];
        $data_campus_id     = [];
        
        foreach ($UserDataPermission as $key => $data_perm){
            
            if($data_perm['hierarchy_level_id'] == 1){
                $data_org_id[] = $data_perm['data_permissions_id'];
            }

            if($data_perm['hierarchy_level_id'] == 2){
                $data_country_id[] = $data_perm['data_permissions_id'];
            }

            if($data_perm['hierarchy_level_id'] == 3){
                $data_state_id[] = $data_perm['data_permissions_id'];
            }

            if($data_perm['hierarchy_level_id'] == 4){
                $data_region_id[] = $data_perm['data_permissions_id'];
            }

            if($data_perm['hierarchy_level_id'] == 5){
                $data_city_id[] = $data_perm['data_permissions_id'];
            }

            if($data_perm['hierarchy_level_id'] == 6){
                $data_campus_id[] = $data_perm['data_permissions_id'];
            }
        }
        
        $new_request = [ 
            'data_p_org_id'     => $data_org_id, 
            'data_p_country_id' => $data_country_id, 
            'data_p_state_id'   => $data_state_id,
            'data_p_region_id'  => $data_region_id, 
            'data_p_city_id'    => $data_city_id, 
            'data_p_campus_id'  => $data_campus_id 
        ];
        
        $request->request->add($new_request);
    }
    
    public static function set_all_data_permission_wheres(Request $request, $query_obj, $tableArr, $hierarchy_type = 0, $is_hierarchy_parent = 0)
    {
        
        Utilities::set_all_data_permission_request_parameters($request);
        
        $data_org_id        = $request->data_p_org_id;
        $data_country_id    = $request->data_p_country_id;
        $data_state_id      = $request->data_p_state_id;
        $data_region_id     = $request->data_p_region_id;
        $data_city_id       = $request->data_p_city_id;
        $data_campus_id     = $request->data_p_campus_id;
        
        $whereData = [];
        if (!empty($data_org_id) && ( $hierarchy_type == 1 || $hierarchy_type == 0 ) ) {
            
            $organization_id = ( $is_hierarchy_parent == 1 ) ? $tableArr['parent'].'id' : $tableArr['child'].'organization_id';
            
            $query_obj->where(function ($query) use ($data_org_id, $organization_id){
                foreach ($data_org_id as $key => $value) {
                  $query->orWhere($organization_id, '=', $value);
                }
            });
        }
        
        if ( !empty($data_country_id) && ($hierarchy_type == 2 || $hierarchy_type == 0 )) {
            
            $countries_id = ( $is_hierarchy_parent == 1 ) ? $tableArr['parent'].'id' : $tableArr['child'].'countries_id';
             
            $query_obj->where(function ($query) use ($data_country_id, $countries_id){
                foreach ($data_country_id as $key => $value) {
                  $query->orWhere($countries_id, '=', $value);
                }
            });
        }
        
        if (!empty($data_state_id) && ($hierarchy_type == 3 || $hierarchy_type == 0 )) {
            
            $state_id = ( $is_hierarchy_parent == 1 ) ? $tableArr['parent'].'id' : $tableArr['child'].'state_id';
            
            $query_obj->where(function ($query) use ($data_state_id, $state_id){
                foreach ($data_state_id as $key => $value) {
                  $query->orWhere($state_id, '=', $value);
                }
            });
        }
        
        if (!empty($data_region_id) && ($hierarchy_type == 4 || $hierarchy_type == 0 )) {
            
            $region_id = ( $is_hierarchy_parent == 1 ) ? $tableArr['parent'].'id' : $tableArr['child'].'region_id';
            
            $query_obj->where(function ($query) use ($data_region_id, $region_id){
                foreach ($data_region_id as $key => $value) {
                  $query->orWhere($region_id, '=', $value);
                }
            });
        }
        
        if ( !empty($data_city_id)  && ($hierarchy_type == 5 || $hierarchy_type == 0 )) {
            
            $city_id = ( $is_hierarchy_parent == 1 ) ? $tableArr['parent'].'id' : $tableArr['child'].'city_id';
            
            $query_obj->where(function ($query) use ($data_city_id, $city_id){
                foreach ($data_city_id as $key => $value) {
                  $query->orWhere($city_id, '=', $value);
                }
            });
        }
        
        if (!empty($data_campus_id) && ($hierarchy_type == 6 || $hierarchy_type == 0 )) {
            
           $campus_id =  $is_hierarchy_parent == 1  ? $tableArr['parent'].'id' :  'campus_id';
           
            $query_obj->where(function ($query) use ($data_campus_id, $campus_id){
                foreach ($data_campus_id as $key => $value) {
                  $query->orWhere($campus_id, '=', $value);
                }
            });
        }
        
        return $query_obj;
    }


    public static function get_month_name_by_yearmonth_index($ym){

        $month_no = substr($ym,4,2);
        $month_name='';

      
        switch($month_no)
        {
            case "01":
                $month_name = 'Jan';
                break;
            case "02":
                $month_name = 'Feb';
                break;
            case "03":
                $month_name = 'Mar';
                break;
            case "04":
                $month_name = 'Apr';
                break;
            case "05":
                $month_name = 'May';
                break;
            case "06":
                $month_name = 'Jun';
                break;
            case "07":
                $month_name = 'Jul';
                break;
            case "08":
                $month_name = 'Aug';
                break;
            case "09":
                $month_name = 'Sep';
                break;
            case "10":
                $month_name = 'Oct';
                break;
            case "11":
                $month_name = 'Nov';
                break;
            case "12":
                $month_name = 'Dec';
                break;

                default:
                $month_name = '';
        }

       
        return $month_name .= '-'. substr($ym,0,4);
    }
    
    
    //number convert to words 
    //prm 2500 return two thousand five hundered 
    public static  function numberTowords($num) {

        $ones = array(
            0 => "ZERO",
            1 => "ONE",
            2 => "TWO",
            3 => "THREE",
            4 => "FOUR",
            5 => "FIVE",
            6 => "SIX",
            7 => "SEVEN",
            8 => "EIGHT",
            9 => "NINE",
            10 => "TEN",
            11 => "ELEVEN",
            12 => "TWELVE",
            13 => "THIRTEEN",
            14 => "FOURTEEN",
            15 => "FIFTEEN",
            16 => "SIXTEEN",
            17 => "SEVENTEEN",
            18 => "EIGHTEEN",
            19 => "NINETEEN",
            "014" => "FOURTEEN"
        );
        $tens = array(
            0 => "ZERO",
            1 => "TEN",
            2 => "TWENTY",
            3 => "THIRTY",
            4 => "FORTY",
            5 => "FIFTY",
            6 => "SIXTY",
            7 => "SEVENTY",
            8 => "EIGHTY",
            9 => "NINETY"
        );
        $hundreds = array(
            "HUNDRED",
            "THOUSAND",
            "MILLION",
            "BILLION",
            "TRILLION",
            "QUARDRILLION"
        ); /* limit t quadrillion */
        $num = number_format($num, 2, ".", ",");
        $num_arr = explode(".", $num);
        $wholenum = $num_arr[0];
        $decnum = $num_arr[1];
        $whole_arr = array_reverse(explode(",", $wholenum));
        krsort($whole_arr, 1);
        $rettxt = "";
        foreach ($whole_arr as $key => $i) {

            while (substr($i, 0, 1) == "0")
                $i = substr($i, 1, 5);
            if ($i < 20) {
                /* echo "getting:".$i; */
                $rettxt .= $ones[$i] ?? null;
            } elseif ($i < 100) {
                if (substr($i, 0, 1) != "0")
                    $rettxt .= $tens[substr($i, 0, 1)];
                if (substr($i, 1, 1) != "0")
                    $rettxt .= " " . $ones[substr($i, 1, 1)];
            } else {
                if (substr($i, 0, 1) != "0")
                    $rettxt .= $ones[substr($i, 0, 1)] . " " . $hundreds[0];
                if (substr($i, 1, 1) != "0")
                    $rettxt .= " " . $tens[substr($i, 1, 1)];
                if (substr($i, 2, 1) != "0")
                    $rettxt .= " " . $ones[substr($i, 2, 1)];
            }
            if ($key > 0) {
                $rettxt .= " " . $hundreds[$key] . " ";
            }
        }
        if ($decnum > 0) {
            $rettxt .= " and ";
            if ($decnum < 20) {
                $rettxt .= $ones[$decnum];
            } elseif ($decnum < 100) {
                $rettxt .= $tens[substr($decnum, 0, 1)];
                $rettxt .= " " . $ones[substr($decnum, 1, 1)];
            }
        }
        return $rettxt;
    }
    
    
    //string to number array without expolde key
    //input 123 return [1,2,3];
    
    public static function string_to_arr($theString){
        $j = mb_strlen($theString);
        for ($k = 0; $k < $j; $k++) 
        {
            $char = mb_substr($theString, $k, 1);
            $var_arr[$k] =  $char;
        }
        return $var_arr;
    }
    
    
    //multidimensional array has key 
    public static function findKey($array, $keySearch)
    {
        foreach ($array as $key => $item) {
            if ($key == $keySearch) {
//                echo 'yes, it exists';
                return true;
            } elseif (is_array($item) && Utilities::findKey($item, $keySearch)) {
                return true;
            }
        }
        return false;
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

            $mail->setFrom("it.dev7@dawateislami.net", "Dawateislami Schooling System");
            
            $mail->Subject = !empty($param['subject']) ? $param['subject'] : 'Dawateislami';
            $mail->MsgHTML($param['html']);
            $mail->addAddress($param['to'], $param['to_name']);
            $mail->send();
        } catch (phpmailerException $e) {
            return $e;

        } catch (Exception $e) {
            return $e;
        }
        return true;
    }
}
