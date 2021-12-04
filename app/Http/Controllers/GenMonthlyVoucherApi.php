<?php

/**
 * Darulmadinah Api
Author: Muhammad Usama (it.dev7) 
 * 
 * This is a GenMonthlyVoucher API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\GenMonthlyVoucher;
use App\Models\StudentAdmission;
use App\Models\StdInterviewTest;
use App\Models\AssignFeeStructure;
use App\Models\FeeStructureDetail;
use App\Models\AssigndiscountPolicy;
use App\Models\GenMonthlyVoucherDetail;
use App\Models\FeeType;
use App\Models\Campus;
use App\Models\DiscountPolicy;
use App\Models\MonthlyFeeCollectionTemp;
use Illuminate\Http\Request;
use App\Utilities\Utilities;
use App\Utilities\SimpleXLSX;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

use Illuminate\Support\Facades\File;

class GenMonthlyVoucherAPi extends Controller
{

    /**
     * Constructor
     */
    private $mdlName;
             
    public function __construct()
    {
        $this->mdlName = new GenMonthlyVoucher();
    }
    
    
    private $select_column_dis = [
        'assign_discount_policy.organization_id', 
        'assign_discount_policy.country_id', 
        'assign_discount_policy.state_id', 
        'assign_discount_policy.region_id', 
        'assign_discount_policy.city_id', 
        'assign_discount_policy.campus_id', 
        'assign_discount_policy.class_id', 
        'assign_discount_policy.disc_code', 
        'discount_policy.no_of_month', 
        'discount_policy.discount_type', 
        'discount_policy.fees_type_id', 
        'discount_policy.disc_percentage', 
        'discount_policy.disc_from_date', 
        'discount_policy.disc_end_date', 
        'discount_policy.discount_type', 
        
        
    ];
    private $select_columns = [
           
    ];
        
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
     * Add GenMonthlyVoucher.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        $user_id         =   $request->data_user_id;
        $campus_id       =   $request->campus_id;
        $class_id        =   $request->class_id;
        $organization_id =   $request->organization_id;
        $admission_code  =   $request->admission_code;
        $month_close_date  =   $request->month_close_date;
        
        $check_date        = date("Y-m-d");
        
         
         if(strtotime($month_close_date) < strtotime($check_date)){
            $response = Utilities::buildBaseResponse(10003, "Challan Generation Failed Month Closed..!! Closed Date (".$month_close_date.")", 'Info');
            return response()->json($response, 200);
            exit;
        }
        
         
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        
       
        
        $this->mdlName->filterColumns($request);
        
         
        Utilities::defaultAddAttributes($request, $user_id);
        
//return response()->json($request->all(), 200);
//         exit;
//         
        
        //only campus wise hiearchy follow 
        $get_students_query = StudentAdmission::with('Campus');
        
        $get_students_query->where(['is_enable' => 1, 'student_status' => 1]);
        
        if(!empty($admission_code)){
           $get_students_query->where('admission_code', $admission_code);
        }
        
        if(!empty($campus_id)){
           $get_students_query->where('campus_id', $campus_id);
        }
        
        if(!empty($class_id)){
           $get_students_query->where('class_id', $class_id);
        }
        
        if(!empty($organization_id)){
           $get_students_query->where('organization_id', $organization_id);
        }
        
        if(!empty($admission_code)){
           $get_students_query->where('admission_code', $admission_code);
        }
        
        $get_students_list = $get_students_query->get()->toArray();
        
        if(empty($get_students_list)){
            $response = Utilities::buildBaseResponse(10003, "Student Not Found..!! ", 'Info');
            return response()->json($response, 200);
            exit;
        }
                  
        $fee_slip_master_data_arr     = [];
        $fee_structure_details_arr    = [];
        $fees_code_unique_arr         = [];
        $disc_code_unique_arr         = [];
        $disc_cod_arr                 = [];
        
        $warning = [];
        
        
        
        
        foreach ($get_students_list as $key => $value) {
                
            $organization_id    = $value['campus']['organization_id'];
            $country_id         = $value['campus']['countries_id'];
            $state_id           = $value['campus']['state_id'];
            $region_id          = $value['campus']['region_id'];
            $city_id            = $value['campus']['city_id'];
            $campus_id          = $value['campus_id'];
            $class_id           = $value['class_id'];
            $admission_code     = $value['admission_code'];
            $std_id             = $value['id'];
            
            $fee_code_admission_wise = $this->getAssignFeeCode(['organization_id' =>  $organization_id, 'country_id' => $country_id, 'admission_code' => $admission_code]);
            if(empty($fee_code_admission_wise)){
                
               $fee_code_class_wise = $this->getAssignFeeCode(['organization_id' =>  $organization_id, 'country_id' => $country_id, 'campus_id' => $campus_id, 'class_id' => $class_id], ['admission_code']);
               
               if(empty($fee_code_class_wise)){
                    $fee_code_campus_wise = $this->getAssignFeeCode(['organization_id' =>  $organization_id, 'country_id' => $country_id, 'campus_id' => $campus_id], ['admission_code', 'class_id']);
                    if(empty($fee_code_campus_wise)){
                        $fee_code_city_wise = $this->getAssignFeeCode(['organization_id' =>  $organization_id, 'country_id' => $country_id, 'city_id' => $city_id], ['admission_code', 'class_id', 'campus_id']);
                        if(empty($fee_code_city_wise)){
                            $fee_code_region_wise = $this->getAssignFeeCode(['organization_id' =>  $organization_id, 'country_id' => $country_id, 'region_id' => $region_id], ['admission_code', 'class_id', 'campus_id', 'city_id']);
                            if(empty($fee_code_region_wise)){
                                $fee_code_state_wise = $this->getAssignFeeCode(['organization_id' =>  $organization_id, 'country_id' => $country_id, 'state_id' => $state_id], ['admission_code', 'class_id', 'campus_id', 'city_id', 'region_id']);
                                if(empty($fee_code_state_wise)){
                                    $fee_code_country_wise = $this->getAssignFeeCode(['organization_id' =>  $organization_id, 'country_id' => $country_id], ['admission_code', 'class_id', 'campus_id', 'city_id', 'region_id', 'state_id']);
                                    if(empty($fee_code_country_wise)){
                                        $fee_code = null;
                                    }else{
                                        $fee_code =  $fee_code_country_wise;
                                    }
                                }else{
                                    $fee_code =  $fee_code_state_wise;
                                }
                            }else{
                               $fee_code =  $fee_code_region_wise;
                            }
                        }else{
                            $fee_code = $fee_code_city_wise;
                        }
                    }else{
                        $fee_code = $fee_code_campus_wise;
                    }
               }
               else{
                   $fee_code = $fee_code_class_wise;
               }
            }
            else{
                $fee_code = $fee_code_admission_wise;
            }
            
            if(empty($fee_code['id'])){
                
                $warning['fee_assign_errors'][] = $admission_code;
            }
            
            else{
                
                
                $disc_code_admission_wise = $this->getAssignDiscountCode(['organization_id' =>  $organization_id, 'country_id' => $country_id, 'admission_code' => $admission_code]);
                $disc_code_class_wise     = $this->getAssignDiscountCode(['organization_id' =>  $organization_id, 'country_id' => $country_id, 'campus_id' => $campus_id, 'class_id' => $class_id], ['admission_code']);
                $disc_code_campus_wise    = $this->getAssignDiscountCode(['organization_id' =>  $organization_id, 'country_id' => $country_id, 'campus_id' => $campus_id], ['admission_code', 'class_id']);
                $disc_code_city_wise      = $this->getAssignDiscountCode(['organization_id' =>  $organization_id, 'country_id' => $country_id, 'city_id'   => $city_id],   ['admission_code', 'class_id', 'campus_id']);
                $disc_code_region_wise    = $this->getAssignDiscountCode(['organization_id' =>  $organization_id, 'country_id' => $country_id, 'region_id' => $region_id], ['admission_code', 'class_id', 'campus_id', 'city_id']);
                $disc_code_state_wise     = $this->getAssignDiscountCode(['organization_id' =>  $organization_id, 'country_id' => $country_id, 'state_id' => $state_id], ['admission_code', 'class_id', 'campus_id', 'city_id', 'region_id']);
                $disc_code_country_wise   = $this->getAssignDiscountCode(['organization_id' =>  $organization_id, 'country_id' => $country_id], ['admission_code', 'class_id', 'campus_id', 'city_id', 'region_id', 'state_id']);
                
                     
                if(!empty($disc_code_admission_wise)){
                    $disc_cod_arr[$admission_code][]  = $disc_code_admission_wise;
                }
                if(!empty($disc_code_class_wise)){
                    $disc_cod_arr[$admission_code][]  = $disc_code_class_wise;
                }
                if(!empty($disc_code_campus_wise)){
                    $disc_cod_arr[$admission_code][]  = $disc_code_campus_wise;
                }
                if(!empty($disc_code_city_wise)){
                    $disc_cod_arr[$admission_code][]  = $disc_code_city_wise;
                }
                if(!empty($disc_code_region_wise)){
                    $disc_cod_arr[$admission_code][]  = $disc_code_region_wise;
                }
                if(!empty($disc_code_state_wise)){
                    $disc_cod_arr[$admission_code][]  = $disc_code_state_wise;
                }
                if(!empty($disc_code_country_wise)){
                    $disc_cod_arr[$admission_code][]  =  $disc_code_country_wise;
                }
                
                
                $fees_code_unique_arr[$fee_code['fees_code']]     = $fee_code['id'];
//                $disc_code_unique_arr[$fee_code['fees_code']]     = $fee_code['id'];
                
                
//                $post_arr[$admission_code]['country_id']          = $country_id;
//                $post_arr[$admission_code]['state_id']            = $state_id;
//                $post_arr[$admission_code]['region_id']           = $region_id;
//                $post_arr[$admission_code]['city_id']             = $city_id;
                
                $post_arr[$admission_code]['disc_cod_arr']        = $disc_cod_arr[$admission_code];
                $post_arr[$admission_code]['fees_code']           = $fee_code['fees_code'];
                
                $post_arr[$admission_code]['std_admission_id']    = $std_id;
                $post_arr[$admission_code]['fees_master_id']      = $fee_code['id'];
                $post_arr[$admission_code]['challan_no']          = substr($request->month, 2, 4) . $admission_code;
                $post_arr[$admission_code]['admission_code']      = $admission_code;
                $post_arr[$admission_code]['gr_no']               = $value['gr_no'];
                $post_arr[$admission_code]['session_id']          = $value['session_id'];
                $post_arr[$admission_code]['organization_id']     = $value['organization_id'];
                $post_arr[$admission_code]['campus_id']           = $campus_id;
                $post_arr[$admission_code]['class_id']            = $class_id;
                $post_arr[$admission_code]['section_id']          = $value['section_id'];
                
                $month = substr($request->month,4,2);
                $year  = substr($request->month,0,4);

                $post_arr[$admission_code]['fee_actual_date']     = date('Y-m-d', strtotime($year.'-'.$month.'-01'));
                $post_arr[$admission_code]['fee_month']           = $month;
                $post_arr[$admission_code]['fee_month_code']      = $request->month;
                $post_arr[$admission_code]['fee_date']            = $request->issue_date;
                
                $post_arr[$admission_code]['slip_issue_date']     = $request->issue_date;
                $post_arr[$admission_code]['slip_validity_date']  = $request->validity_date;
                $post_arr[$admission_code]['slip_due_date']       = $request->due_date;
                $post_arr[$admission_code]['slip_type_id']        = $request->slip_type;
                
                $post_arr[$admission_code]['kuickpay_id']         =  $this->getActiveBanks(['organization_id' =>  $organization_id, 'type' => 2])->ac_no . $admission_code;
                
                $post_arr[$admission_code]['bank_id']             =  $this->getActiveBanks(['organization_id' =>  $organization_id, 'type' => 1])->id;
                               
            }
       
        }
           
        $fs_detail_arr =  [];
        
      
        $where_fs_d    = array();
        
        $where_fs_d[] = ['is_enable',1];
        $where_fs_d[] = ['fees_is_new_addmission', 0];
        $where_fs_d[] = ['fees_from_date', '<=', $request->issue_date];
        $where_fs_d[] = ['fees_end_date',  '>=', $request->due_date];
        $fs_detail_query_obj = FeeStructureDetail::with('FeesType')->where($where_fs_d);
        $fs_detail_query_obj->whereIn('fees_master_id', $fees_code_unique_arr);
           
        
        $fs_detail_query_obj->get()->map(function($item) use(&$fs_detail_arr) {
            $fs_detail_arr[$item->fees_code][] = $item->toArray();
        });
        
        
//        return response()->json($fs_detail_arr, 200);
//    exit;
        
        $disc_code_unique_arr = array_unique(
                Arr::pluck(
                    Arr::flatten($disc_cod_arr),
                    'disc_code'
                ), 
                SORT_REGULAR
        );
        
          
        
        $fees_type_unique_arr = [];
        array_walk_recursive($fs_detail_arr, function ($value, $key) use (&$fees_type_unique_arr) {
            if ($key === 'fees_type_id') {
                $fees_type_unique_arr[$value] = $value;
            };
        });
    
        
        $disc_detail_arr =  [];
        $where_disc_dtls    = array();
        
        $where_disc_dtls[] = ['is_enable',   1];
        $where_disc_dtls[] = ['no_of_month', 1];
        
        $where_disc_dtls[] = ['disc_is_new_addmission', 0];
        $where_disc_dtls[] = ['disc_from_date', '<=', $request->issue_date];
        $where_disc_dtls[] = ['disc_end_date',  '>=', $request->due_date];
        
        $disc_detail_query_obj = DiscountPolicy::with('FeesType', 'DiscType')->where($where_disc_dtls);
        $disc_detail_query_obj->whereIn('disc_code', $disc_code_unique_arr);
        $disc_detail_query_obj->whereIn('fees_type_id', $fees_type_unique_arr);
           
        
        $disc_detail_query_obj->get()->map(function($item) use(&$disc_detail_arr) {
            $disc_detail_arr[$item->disc_code][] = $item->toArray();
        });
        
       
        if(!empty($post_arr)){
            try
            {
                DB::beginTransaction();

                foreach ($post_arr as $key_admission_code => $values) {

                    $voucher_master_data = $values;
                    unset($voucher_master_data['disc_cod_arr'], $voucher_master_data['fees_code']);

                    $voucher_master_data = Utilities::defaultAddAttributesArr($voucher_master_data, $request->data_user_id);


    // return response()->json($fs_detail_arr[$values['fees_code']], 200);
    //exit;
                        if( !empty($values['fees_code']) && !empty($fs_detail_arr[$values['fees_code']])){

                            $fee_slip_master_find = GenMonthlyVoucher::where( [
                                                                'organization_id'   => $voucher_master_data['organization_id'],
                                                                'fee_month'         => $voucher_master_data['fee_month'],
                                                                'fee_month_code'    => $voucher_master_data['fee_month_code'],
//                                                                'slip_type_id'      => $voucher_master_data['slip_type_id'],
                                                                'challan_no'        => $voucher_master_data['challan_no'],
                                                                'admission_code'    => $voucher_master_data['admission_code'],
                                                                'is_enable'         => 1,
                                                            ])->first();


                            if ($fee_slip_master_find !== null) {
                                $warning['challan_already_generated'][] = $key_admission_code;
                            }
                            else{

                                $fee_slip_master_obj =  GenMonthlyVoucher::Create($voucher_master_data);
                                $fee_slip_master_id  = $fee_slip_master_obj->id;

                                $fees_amount           = 0;
                                $total_discount_amount = 0;
                                $total_payable_amount  = 0;

                                foreach ($fs_detail_arr[$values['fees_code']] as $key_fd => $value_fd) {

                                    $fee_details_data['fee_slip_id']    = $fee_slip_master_id;
                                    $fee_details_data['fee_type_id']   = $value_fd['fees_type_id'];     
                                    $fee_details_data['fee_amount']     = $value_fd['fees_amount'];   

                                    $fees_amount += $value_fd['fees_amount']; 

                                    $fee_details_data = Utilities::defaultAddAttributesArr($fee_details_data, $request->data_user_id);

                                    GenMonthlyVoucherDetail::create($fee_details_data);
                                }

                                foreach ($values['disc_cod_arr'] as $key_discount_arr => $value_discount_arr) {

    //                                echo $value_discount_arr->disc_code.'oojh';
                                    if(!empty($disc_detail_arr[$value_discount_arr->disc_code])){

                                        foreach ($disc_detail_arr[$value_discount_arr->disc_code] as $key_discount_detailed_arr => $value_discount_detailed_arr) {

                                                if(!empty($value_discount_detailed_arr['disc_percentage'])){
                                                    $discount_amount = (($value_discount_detailed_arr['disc_percentage'] / 100 ) *  $fees_amount);  

                                                    $disc_details_data['fee_slip_id']             = $fee_slip_master_id;
                                                    $disc_details_data['disc_type_id']            = $value_discount_detailed_arr['discount_type'];     
                                                    $disc_details_data['fee_type_id']             = $value_discount_detailed_arr['fees_type_id'];     
                                                    $disc_details_data['discount_percentage']     = $value_discount_detailed_arr['disc_percentage'];     
                                                    $disc_details_data['fee_amount']              = 0;     
                                                    $disc_details_data['discount_amount']         = $discount_amount;     
                                                    $disc_details_data['is_discount_entry']       = 1;     

                                                    $total_discount_amount += $discount_amount;



                                                    $disc_details_data = Utilities::defaultAddAttributesArr($disc_details_data, $request->data_user_id);

                                                    GenMonthlyVoucherDetail::create($disc_details_data);


                                                }

                                        }
                                    }else{

                                        $warning['discount_detailed_error'][] = $key_admission_code;
                                    }
                                }


                                $total_payable_amount   = $fees_amount - $total_discount_amount;

                                $master_fee_update = [
                                    'total_fees'                 => $fees_amount,
                                    'total_discount'             => $total_discount_amount,
                                    'total_payable_amount'       => $total_payable_amount,
                                    'total_payable_amount_words' =>  Utilities::numberTowords($total_payable_amount)
                                ];

                                $fee_slip_master_obj->update($master_fee_update);

    //                            return response()->json($master_fee_update, 200);
    //                            exit;
                                $warning['challan_success_generated'][] = $key_admission_code;
                            } 


                        }else{
                            $warning['fee_detailed_error'][] = $key_admission_code;
                        }
                }


                DB::commit();
                $data  = [

                                'fs_detail_arr'        => $fs_detail_arr,
                                'disc_cod_arr'         => $disc_cod_arr, 
                                'disc_code_unique_arr' => $disc_code_unique_arr, 
                                'disc_detail_arr'      => $disc_detail_arr,
                                'fees_type_unique_arr' => $fees_type_unique_arr,
                                'warning'              => $warning,
                                ];

                $response = Utilities::buildSuccessResponse(10000, "Monthly Voucher Generated successfully created.", $data);
            }
            catch(\Exception $e)
            {
                DB::rollback();
                $response = Utilities::buildBaseResponse(10003, "Transaction Failed Monthly Voucher. ". $e, 'info');
            } 
        }else{
            
            $response = Utilities::buildBaseResponse(10003, "Transaction Failed Monthly Voucher. Fee Not Assigned", 'error');
        }        
        
        
//        return response()->json($post_arr, 200);
//exit;
//        $data  = [
//                'postArr' => $post_arr,
//                'fs_detail_arr'        => $fs_detail_arr,
//                'disc_cod_arr'         => $disc_cod_arr, 
//                'disc_code_unique_arr' => $disc_code_unique_arr, 
//                'disc_detail_arr'      => $disc_detail_arr,
//                'fees_type_unique_arr' => $fees_type_unique_arr,
//                'warning'   => $warning,
//                ];
//        
//        $response = array_merge($response, $data);
        
        return response()->json($response, 200);
        exit;
       
    }

    
    
    /**
     * Fetch list of All Monthly Voucher by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll(Request $request, $id = null)
    {
       
        $whereData = array();
        

        if(!empty($request->organization_id)) {   
            $whereData[] = ['organization_id', $request->organization_id];
        }
        
        if(!empty($request->data_org_id)) {   
            $whereData[] = ['organization_id', $request->data_org_id];
        }
        
        if(!empty($request->data_organization_id)) {   
            $whereData[] = ['organization_id', $request->data_organization_id];
        }
        
        if(!empty($request->campus_id)) {   
            $whereData[] = ['campus_id', $request->campus_id];
        }
        
        if(!empty($request->data_campus_id)) {   
            $whereData[] = ['campus_id', $request->data_campus_id];
        }
        
        if(!empty($request->class_id)) {   
            $whereData[] = ['class_id', $request->class_id];
        }
        
        if(!empty($request->admission_code)) {   
            $whereData[] = ['admission_code', $request->admission_code];
        }
        
        
        if(!empty($request->challan_no)) {   
            $where_admission_code = substr($request->challan_no,4,6);
            
            if(!empty($where_admission_code)){
                $whereData[] = ['admission_code', $where_admission_code];
            }
        }
        
        
        
        
        if(!empty($request->session_id)) {   
            $whereData[] = ['session_id', $request->session_id];
        }
        
        if(!empty($request->data_session_id)) {   
            $whereData[] = ['session_id', $request->data_session_id];
        }
                
        if(!empty($request->slip_type)) {   
            $whereData[] = ['slip_type_id', $request->slip_type];
        }
        
//        if($request->month) {   
//            $whereData[] = ['month', $request->month];
//        }
                
        
        if($request->is_enable != null) {
            $whereData[] = ['is_enable', $request->is_enable];
        }else{
            $whereData[] = ['is_enable', 1];
        }
        
        $whereData[] = ['payment_status', 0];
        
        if(!empty($request->month)) { 
            $month = substr($request->month,4,2);
            $year  = substr($request->month,0,4);
            
            $fee_actual_date  = date('Y-m-d', strtotime($year.'-'.$month.'-01'));
        }
        
        
                
        $queryObj = GenMonthlyVoucher::with('FeeSlipDetails','stdAdmission', 'Organization','Campus', 'Class', 'Bank')
                    ->where($whereData);
        
        if(!empty($fee_actual_date)) {   
           $queryObj->whereDate('fee_actual_date', '<=', $fee_actual_date);
        }
        
        $data_set = $queryObj->get();
            
        $getImg       = url('app/organization_file/');
        $getImgPublic = '';
        if (strpos($getImg, 'public') !== false) {
            $getImgPublic = str_replace('public', 'storage', $getImg);
        }else{
            $getImgPublic = url('storage/app/organization_file/');
        }
            
        $fees_voucher_array = [];
        $total_payable_amount_array = [];
        $total_arrears_amount_arr = [];
        $fees_arrears       = [];
        
        $keyCount = 0;
        $current_total_payable_amount = 0;
        $fee_arrears_count = 0;
        $total_arrears_amount = 0;
        $payable_key = 0;
        
        
        if(!empty($data_set->toArray())){
            
            foreach ( $data_set->toArray() as $key => $value) {

               if(substr($request->month,4,2) == $value['fee_month']){

                    $fees_voucher_array[$keyCount]['id']             = $value['id'];
                    $fees_voucher_array[$keyCount]['id_']            = $value['id'];
                    $fees_voucher_array[$keyCount]['is_challan_customize']            = $value['is_challan_customize'];
                    $fees_voucher_array[$keyCount]['admission_code'] = $value['admission_code'];
                    $fees_voucher_array[$keyCount]['bank_id']        = $value['bank_id'];
                    $fees_voucher_array[$keyCount]['bank_name']      = $value['bank']['name'];
                    $fees_voucher_array[$keyCount]['bank_ac_no']     = $value['bank']['ac_no'];
                    $fees_voucher_array[$keyCount]['bank_ac_no_arr'] = Utilities::string_to_arr($value['bank']['ac_no']);
                    $fees_voucher_array[$keyCount]['campus_name']    = $value['campus']['campus_name'];
                    $fees_voucher_array[$keyCount]['class_name']     = $value['class']['class_name'];
                    $fees_voucher_array[$keyCount]['campus_id']      = $value['campus_id'];
                    $fees_voucher_array[$keyCount]['class_id']       = $value['class_id'];
                    $fees_voucher_array[$keyCount]['challan_no']     = $value['challan_no'];
                    $fees_voucher_array[$keyCount]['fee_date']       = $value['slip_issue_date'];
                    $fees_voucher_array[$keyCount]['fee_month']      = $value['fee_month'];
                    $fees_voucher_array[$keyCount]['fee_month_code'] = $value['fee_month_code'];
                    $fees_voucher_array[$keyCount]['fee_month_name'] = Utilities::get_month_name_by_yearmonth_index($value['fee_month_code']);
                    $fees_voucher_array[$keyCount]['fees_master_id'] = $value['fees_master_id'];
                    $fees_voucher_array[$keyCount]['gr_no']            = $value['gr_no'];
                    $fees_voucher_array[$keyCount]['kuickpay_id']      = $value['kuickpay_id'];
                    $fees_voucher_array[$keyCount]['organization_id']  = $value['organization_id'];
                    $fees_voucher_array[$keyCount]['organization_img'] = $getImgPublic . '/'. $value['organization']['org_logo'].'?'.rand();
                    $fees_voucher_array[$keyCount]['payment_status']   = $value['payment_status'];
                    $fees_voucher_array[$keyCount]['section_id']       = $value['section_id'];
                    $fees_voucher_array[$keyCount]['session_id']       = $value['session_id'];
                    $fees_voucher_array[$keyCount]['slip_due_date']    = $value['slip_due_date'];
                    $fees_voucher_array[$keyCount]['slip_issue_date']  = $value['slip_issue_date'];
                    $fees_voucher_array[$keyCount]['slip_type_id']        = $value['slip_type_id'];
                    $fees_voucher_array[$keyCount]['slip_validity_date']  = $value['slip_validity_date'];
                    $fees_voucher_array[$keyCount]['student_name']        = $value['std_admission']['student_name'];
                    $fees_voucher_array[$keyCount]['father_name']         = $value['std_admission']['father_name'];
                    $fees_voucher_array[$keyCount]['student_full_name']   = $value['std_admission']['student_name'] .' '.$value['std_admission']['father_name'];
                    $fees_voucher_array[$keyCount]['amount']   = $value['total_payable_amount'];


                    $fees_voucher_array[$keyCount]['fee_slip_details'] = $value['fee_slip_details'];

                    $current_total_payable_amount = $value['total_payable_amount'];

                    $current_total_payable_amount_arr[$value['admission_code']] = $current_total_payable_amount;

                    $keyCount++;

                }
                else{
                    $fees_arrears[$fee_arrears_count]['id']             = $value['id'];
                    $fees_arrears[$fee_arrears_count]['id_']            = $value['id'];
                    $fees_arrears[$fee_arrears_count]['is_challan_customize']            = $value['is_challan_customize'];
                    $fees_arrears[$fee_arrears_count]['admission_code'] = $value['admission_code'];
                    $fees_arrears[$fee_arrears_count]['month']          = $value['fee_month'];
                    $fees_arrears[$fee_arrears_count]['arrears_amount'] = $value['total_payable_amount'];
                    $fees_arrears[$fee_arrears_count]['fee_month_code'] = $value['fee_month_code'];
                    $fees_arrears[$fee_arrears_count]['fee_month_name'] = Utilities::get_month_name_by_yearmonth_index($value['fee_month_code']);
                    $fee_arrears_count++;
                }

            }

            foreach ($fees_arrears as $key => $value) {
                if(isset($total_arrears_amount_arr[$value['admission_code']])) {
                    $total_arrears_amount_arr[$value['admission_code']] += $value['arrears_amount'];
                } else {
                    $total_arrears_amount_arr[$value['admission_code']] = $value['arrears_amount'];
                }
            }

            $total_payable_amount_key = 0;
            if(!empty($current_total_payable_amount_arr)){
                foreach ($current_total_payable_amount_arr as $key => $value) {

                    if(!empty($total_arrears_amount_arr[$key])){
                        $total_payable_amount_array[$total_payable_amount_key]['is_arrears'] = 1;
                        $total_payable_amount_array[$total_payable_amount_key]['admission_code'] = $key;
                        $total_payable_amount_array[$total_payable_amount_key]['grand_total_payable_amount'] = $value + $total_arrears_amount_arr[$key];
                        $total_payable_amount_array[$total_payable_amount_key]['grand_total_payable_amount_words'] = Utilities::numberTowords($value + $total_arrears_amount_arr[$key]);
                    }else{
                        $total_payable_amount_array[$total_payable_amount_key]['is_arrears'] = 0;
                        $total_payable_amount_array[$total_payable_amount_key]['admission_code'] = $key;
                        $total_payable_amount_array[$total_payable_amount_key]['grand_total_payable_amount'] = $value;
                        $total_payable_amount_array[$total_payable_amount_key]['grand_total_payable_amount_words'] = Utilities::numberTowords($value);
                    }
                    $total_payable_amount_key++;    

                }

                $data_result = [];
                $status = 200;
                $data_result['current_total_payable_amount_arr'] = $current_total_payable_amount_arr;
                $data_result['total_payable_amount_array']      =  $total_payable_amount_array;
        //        $data_result['data_list'] = $data_set->toArray();
        //        $data_result['post'] = $request->all();
        //        $data_result['mnth'] = substr($request->month,4,2);
                $data_result['fees_voucher_array'] = $fees_voucher_array;
                
                usort($fees_arrears, function ($item1, $item2) {
                    return $item1['fee_month_code'] <=> $item2['fee_month_code'];
                });


                $data_result['fees_arrears'] = $fees_arrears;
                $data_result['total_arrears_amount_arr'] = $total_arrears_amount_arr;

                $response = Utilities::buildSuccessResponse(10004, "Get All Monthly Vouchers.", $data_result);

            }else{

                $status = 200;
                $response = Utilities::buildBaseResponse(10003, "Challan Not Exits!", 'error');
            }
        
        }
        else{
            $status = 200;
            $response = Utilities::buildBaseResponse(10003, "Challan Not Exits!!", 'error');
        }
        
        
        

        return response()->json($response, $status); 
    }
    
    /**
     * Fetch list of All Monthly Voucher by searching with optional filters..
     * Only for customize challan
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChallanCustomize(Request $request, $id = null)
    {   
        
        $formDataArr = json_decode($request->formDataArr, true);
        
        if(empty($formDataArr)){
            $status = 200;
            $response = Utilities::buildBaseResponse(10003, "Please Select Atleast one month fee", 'error');
            return response()->json($response, $status); 
            exit;
        }
        
           
        usort($formDataArr, function ($item1, $item2) {
            return $item1['fee_month_code'] <=> $item2['fee_month_code'];
        });
                
        $whereData = array();
        
        if($request->organization_id) {   
            $whereData[] = ['organization_id', $request->organization_id];
        }
        
        if($request->data_org_id) {   
            $whereData[] = ['organization_id', $request->data_org_id];
        }
        
        if($request->data_organization_id) {   
            $whereData[] = ['organization_id', $request->data_organization_id];
        }
        
        if($request->campus_id) {   
            $whereData[] = ['campus_id', $request->campus_id];
        }
        
        if($request->data_campus_id) {   
            $whereData[] = ['campus_id', $request->data_campus_id];
        }
        
        if($request->class_id) {   
            $whereData[] = ['class_id', $request->class_id];
        }
        
        if($request->admission_code) {   
            $whereData[] = ['admission_code', $request->admission_code];
        }
        
        if($request->session_id) {   
            $whereData[] = ['session_id', $request->session_id];
        }
        
        if($request->data_session_id) {   
            $whereData[] = ['session_id', $request->data_session_id];
        }
                
        if($request->slip_type) {   
            $whereData[] = ['slip_type_id', $request->slip_type];
        }
        
//        if($request->month) {   
//            $whereData[] = ['month', $request->month];
//        }
                
        
        if($request->is_enable != null) {
            $whereData[] = ['is_enable', $request->is_enable];
        }else{
            $whereData[] = ['is_enable', 1];
        }
        
        $whereData[] = ['payment_status', 0];
        
        if($request->month) { 
            $month = substr($request->month,4,2);
            $year  = substr($request->month,0,4);
            
            $fee_actual_date  = date('Y-m-d', strtotime($year.'-'.$month.'-01'));
        }
        
        
                
        $queryObj = GenMonthlyVoucher::with('FeeSlipDetails','stdAdmission', 'Organization','Campus', 'Class', 'Bank')
                    ->where($whereData);
        
        if($fee_actual_date) {   
           $queryObj->whereDate('fee_actual_date', '<=', $fee_actual_date);
        }
        
//        if(!empty($formDataArr)){
//            $queryObj->where(function($query) use ($formDataArr) {
//                foreach ($formDataArr as $key => $value) {
//                    $query->orWhere('fee_month_code', '=', $value['fee_month_code']);
//                }
//            });
//        }
        
        
        $data_set = $queryObj->get();
            
        $data_set_arr = $data_set->toArray();
        
        usort($data_set_arr, function ($item1, $item2) {
            return $item1['fee_month_code'] <=> $item2['fee_month_code'];
        });
        
//        echo '<pre>';
        $data_set_final_arr = [];
        $sequence_arr       = [];
        
        
        foreach ($data_set_arr as $key => $value) {
           
           if(!empty($formDataArr[$key]) && $formDataArr[$key]['fee_month_code'] ==  $value['fee_month_code']  ){
//               echo ' yes'.$value['fee_month_code'].'   ';
               $data_set_final_arr[] = $value;
//               echo '';
           }
           
           elseif(!empty($formDataArr[$key]) && $formDataArr[$key]['fee_month_code'] !=  $value['fee_month_code']  ){
//               echo ' no'.$value['fee_month_code'].'  ';
               $sequence_arr[$value['fee_month_code']] = Utilities::get_month_name_by_yearmonth_index($value['fee_month_code']);
//               $sequence_arr[$value['fee_month_name']] = $value['fee_month_name'];
           }
//            
        }
        
        if(!empty($sequence_arr)){
            $status = 200;
            $response = Utilities::buildBaseResponse(10003, "Please Select Month Seqence wise (FIFO Base)", 'error');
            return response()->json($response, $status); 
            exit;
        }
        else{
        
//        print_r(json_encode($data_set_final_arr));
//        echo 'error ';
//        print_r($sequence_arr);
//        print_r($formDataArr);
//        print_r($data_set_final_arr);
//        dd($data_set->toArray());
//        return response()->json(1, 200);
//        exit;
        
        $getImg       = url('app/organization_file/');
        $getImgPublic = '';
        if (strpos($getImg, 'public') !== false) {
            $getImgPublic = str_replace('public', 'storage', $getImg);
        }else{
            $getImgPublic = url('storage/app/organization_file/');
        }
            
        $fees_voucher_array = [];
        $total_payable_amount_array = [];
        $total_arrears_amount_arr = [];
        $fees_arrears       = [];
       
        $fees_arrears_plus_current_amount       = [];
       
        
        
        $keyCount = 0;
        $current_total_payable_amount = 0;
        $fee_arrears_count = 0;
        $total_arrears_amount = 0;
        $payable_key = 0;
        
        
        
        
        if(!empty($data_set_final_arr)){
           
            $fee_id_unique_arr = [];
            
            foreach ( $data_set_final_arr as $key => $value) {
                
               $fee_id_unique_arr[$value['id']] = $value['id'];
               
               if( $key  == 0){

                    $fees_voucher_array[$keyCount]['id']             = $value['id'];
                    $fees_voucher_array[$keyCount]['admission_code'] = $value['admission_code'];
                    $fees_voucher_array[$keyCount]['bank_id']        = $value['bank_id'];
                    $fees_voucher_array[$keyCount]['bank_name']      = $value['bank']['name'];
                    $fees_voucher_array[$keyCount]['bank_ac_no']     = $value['bank']['ac_no'];
                    $fees_voucher_array[$keyCount]['bank_ac_no_arr'] = Utilities::string_to_arr($value['bank']['ac_no']);
                    $fees_voucher_array[$keyCount]['campus_name']    = $value['campus']['campus_name'];
                    $fees_voucher_array[$keyCount]['class_name']     = $value['class']['class_name'];
                    $fees_voucher_array[$keyCount]['campus_id']      = $value['campus_id'];
                    $fees_voucher_array[$keyCount]['class_id']       = $value['class_id'];
                    $fees_voucher_array[$keyCount]['challan_no']     = substr($request->month, 2, 4) . $value['admission_code'];
                    $fees_voucher_array[$keyCount]['fee_date']       = $value['slip_issue_date'];
                    $fees_voucher_array[$keyCount]['fee_month']      = $value['fee_month'];
                    $fees_voucher_array[$keyCount]['fee_month_code'] = $value['fee_month_code'];
                    $fees_voucher_array[$keyCount]['fee_month_name'] = Utilities::get_month_name_by_yearmonth_index($value['fee_month_code']);
                    $fees_voucher_array[$keyCount]['fees_master_id'] = $value['fees_master_id'];
                    $fees_voucher_array[$keyCount]['gr_no']            = $value['gr_no'];
                    $fees_voucher_array[$keyCount]['kuickpay_id']      = $value['kuickpay_id'];
                    $fees_voucher_array[$keyCount]['organization_id']  = $value['organization_id'];
                    $fees_voucher_array[$keyCount]['organization_img'] = $getImgPublic . '/'. $value['organization']['org_logo'].'?'.rand();
                    $fees_voucher_array[$keyCount]['payment_status']   = $value['payment_status'];
                    $fees_voucher_array[$keyCount]['section_id']       = $value['section_id'];
                    $fees_voucher_array[$keyCount]['session_id']       = $value['session_id'];
                    $fees_voucher_array[$keyCount]['slip_due_date']    = $request->issue_date;
                    $fees_voucher_array[$keyCount]['slip_issue_date']  = $request->due_date;
                    $fees_voucher_array[$keyCount]['slip_type_id']        = $value['slip_type_id'];
                    $fees_voucher_array[$keyCount]['slip_validity_date']  = $request->validity_date;
                    $fees_voucher_array[$keyCount]['student_name']        = $value['std_admission']['student_name'];
                    $fees_voucher_array[$keyCount]['father_name']         = $value['std_admission']['father_name'];
                    $fees_voucher_array[$keyCount]['student_full_name']   = $value['std_admission']['student_name'] .' '.$value['std_admission']['father_name'];
                    $fees_voucher_array[$keyCount]['amount']              = $value['total_payable_amount'];


                    $fees_arrears_plus_current_amount[$value['fee_month_code']]['amount'] = $value['total_payable_amount'];
                    $fees_arrears_plus_current_amount[$value['fee_month_code']]['fee_month_name'] = Utilities::get_month_name_by_yearmonth_index($value['fee_month_code']);
                    $fees_arrears_plus_current_amount[$value['fee_month_code']]['fee_month_code'] = $value['fee_month_code'];
                    $fees_arrears_plus_current_amount[$value['fee_month_code']]['fee_type'] = 'Tuition Fee';
                            
                    $fees_voucher_array[$keyCount]['fee_slip_details'] = $value['fee_slip_details'];

                    $current_total_payable_amount = $value['total_payable_amount'];

                    $current_total_payable_amount_arr[$value['admission_code']] = $current_total_payable_amount;

                    $keyCount++;

                }
               else{
                    
                    $fees_arrears_plus_current_amount[$value['fee_month_code']]['amount'] = $value['total_payable_amount'];
                    $fees_arrears_plus_current_amount[$value['fee_month_code']]['fee_month_name'] = Utilities::get_month_name_by_yearmonth_index($value['fee_month_code']);
                    $fees_arrears_plus_current_amount[$value['fee_month_code']]['fee_month_code'] = $value['fee_month_code'];
                    $fees_arrears_plus_current_amount[$value['fee_month_code']]['fee_type'] = 'Tuition Fee';
                    
                    
                    $fees_arrears[$fee_arrears_count]['admission_code'] = $value['admission_code'];
                    $fees_arrears[$fee_arrears_count]['month']          = $value['fee_month'];
                    $fees_arrears[$fee_arrears_count]['arrears_amount'] = $value['total_payable_amount'];
                    $fees_arrears[$fee_arrears_count]['fee_month_code'] = $value['fee_month_code'];
                    $fees_arrears[$fee_arrears_count]['fee_month_name'] = Utilities::get_month_name_by_yearmonth_index($value['fee_month_code']);
                    $fee_arrears_count++;
                }
            }

//            return response()->json($fee_id_unique_arr, 200);
//             exit;
             
            GenMonthlyVoucher::
                    where( 
                            [
                                'admission_code'          => $request->admission_code, 
                                'customize_by_challan_no' => substr($request->month, 2, 4) . $request->admission_code,
                                'is_challan_customize'    => 1,
                            ]
                        )
                  ->update(
                            [ 
                             'is_challan_customize'    => 0, 
                             'customize_by_challan_no' => ''
                            ]
                        );
            
            
            $master_fee_update = [
                'is_challan_customize'       => 1,
                'customize_by_challan_no'    => substr($request->month, 2, 4) . $request->admission_code,
            ];
            
            GenMonthlyVoucher::whereIn('id', $fee_id_unique_arr)->where(['admission_code' => $request->admission_code])->update($master_fee_update);
            
            
            foreach ($fees_arrears as $key => $value) {
                if(isset($total_arrears_amount_arr[$value['admission_code']])) {
                    $total_arrears_amount_arr[$value['admission_code']] += $value['arrears_amount'];
                } else {
                    $total_arrears_amount_arr[$value['admission_code']] = $value['arrears_amount'];
                }
            }

            $total_payable_amount_key = 0;
            if(!empty($current_total_payable_amount_arr)){
                foreach ($current_total_payable_amount_arr as $key => $value) {

                    if(!empty($total_arrears_amount_arr[$key])){
                        $total_payable_amount_array[$total_payable_amount_key]['is_arrears'] = 1;
                        $total_payable_amount_array[$total_payable_amount_key]['admission_code'] = $key;
                        $total_payable_amount_array[$total_payable_amount_key]['grand_total_payable_amount'] = $value + $total_arrears_amount_arr[$key];
                        $total_payable_amount_array[$total_payable_amount_key]['grand_total_payable_amount_words'] = Utilities::numberTowords($value + $total_arrears_amount_arr[$key]);
                    }else{
                        $total_payable_amount_array[$total_payable_amount_key]['is_arrears'] = 0;
                        $total_payable_amount_array[$total_payable_amount_key]['admission_code'] = $key;
                        $total_payable_amount_array[$total_payable_amount_key]['grand_total_payable_amount'] = $value;
                        $total_payable_amount_array[$total_payable_amount_key]['grand_total_payable_amount_words'] = Utilities::numberTowords($value);
                    }
                    $total_payable_amount_key++;    

                }

                $data_result = [];
                $status = 200;
                $data_result['current_total_payable_amount_arr'] = $current_total_payable_amount_arr;
                $data_result['total_payable_amount_array']      =  $total_payable_amount_array;
        //        $data_result['data_list'] = $data_set->toArray();
        //        $data_result['post'] = $request->all();
        //        $data_result['mnth'] = substr($request->month,4,2);
                $data_result['sequence_arr'] = $sequence_arr;
               
                $data_result['fees_voucher_array'] = $fees_voucher_array;
                
                usort($fees_arrears, function ($item1, $item2) {
                    return $item1['fee_month_code'] <=> $item2['fee_month_code'];
                });
                
                $data_result['fees_arrears'] = $fees_arrears;
                $data_result['total_arrears_amount_arr'] = $total_arrears_amount_arr;
                
                
                $data_result['fees_arrears_plus_current_amount'] = $fees_arrears_plus_current_amount;

                $response = Utilities::buildSuccessResponse(10004, "Get All Monthly Vouchers.", $data_result);

            }
            else{
                $status = 200;
                $response = Utilities::buildBaseResponse(10003, "Challan Not Exits!", 'error');
            }
        
        }
        else{
            $status = 200;
            $response = Utilities::buildBaseResponse(10003, "Challan Not Exits!!", 'error');
        }
        
        return response()->json($response, $status); 
        }
    }
    
    /**
     * Fetch list of All Monthly Voucher customize or all  by searching with optional filters..
     * Only for challan posting
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChallanForPosting(Request $request, $id = null)
    {   
              
        $whereData = array();
        
        if(!empty($request->organization_id)) {   
            $whereData[] = ['organization_id', $request->organization_id];
        }
        
        if(!empty($request->data_org_id)) {   
            $whereData[] = ['organization_id', $request->data_org_id];
        }
        
        if(!empty($request->data_organization_id)) {   
            $whereData[] = ['organization_id', $request->data_organization_id];
        }
        
        if(!empty($request->campus_id)) {   
            $whereData[] = ['campus_id', $request->campus_id];
        }
        
        if(!empty($request->data_campus_id)) {   
            $whereData[] = ['campus_id', $request->data_campus_id];
        }
        
        if(!empty($request->class_id)) {   
            $whereData[] = ['class_id', $request->class_id];
        }
        
        if(!empty($request->admission_code)) {   
            $whereData[] = ['admission_code', $request->admission_code];
        }
        
        
        if(!empty($request->challan_no)) {   
            $where_admission_code = substr($request->challan_no,4,6);
            
            if(!empty($where_admission_code)){
                $whereData[] = ['admission_code', $where_admission_code];
            }
        }
        
        
        
        
        if(!empty($request->session_id)) {   
            $whereData[] = ['session_id', $request->session_id];
        }
        
        if(!empty($request->data_session_id)) {   
            $whereData[] = ['session_id', $request->data_session_id];
        }
                
//        if(!empty($request->slip_type)) {   
//            $whereData[] = ['slip_type_id', $request->slip_type];
//        }
        
//        if($request->month) {   
//            $whereData[] = ['month', $request->month];
//        }
                
        
        if($request->is_enable != null) {
            $whereData[] = ['is_enable', $request->is_enable];
        }else{
            $whereData[] = ['is_enable', 1];
        }
        
//        $whereData[] = ['payment_status', 0];
        
        if(!empty($request->month)) { 
            $month = substr($request->month,4,2);
            $year  = substr($request->month,0,4);
            
            $fee_actual_date  = date('Y-m-d', strtotime($year.'-'.$month.'-01'));
        }
        
        $queryObj = GenMonthlyVoucher::with('FeeSlipDetails','stdAdmission', 'Organization','Campus', 'Class', 'Bank')
                    ->where($whereData);
        
        if(!empty($fee_actual_date)) {   
           $queryObj->whereDate('fee_actual_date', '<=', $fee_actual_date);
        }
        
        
        $data_set = $queryObj->get();
            
        $data_set_arr = $data_set->toArray();
        
        usort($data_set_arr, function ($item1, $item2) {
            return $item1['fee_month_code'] <=> $item2['fee_month_code'];
        });
        
        
//        print_r(json_encode($data_set_final_arr));
//        echo 'error ';
//        print_r($sequence_arr);
//        print_r($formDataArr);
//        print_r($data_set_final_arr);
//        dd($data_set->toArray());
//        return response()->json(1, 200);
//        exit;
        
        $getImg       = url('app/organization_file/');
        $getImgPublic = '';
        if (strpos($getImg, 'public') !== false) {
            $getImgPublic = str_replace('public', 'storage', $getImg);
        }else{
            $getImgPublic = url('storage/app/organization_file/');
        }
        
        
        //cc varibale preifx for customize challan.
        //other for else all arrears
        
        $fees_voucher_array_cc  = [];
        $fees_voucher_array     = [];
        
        
        $total_payable_amount_array_cc = [];
        $total_payable_amount_array = [];
        
        
        $total_arrears_amount_arr = [];
        
        $fees_arrears_cc    = [];
        $fees_arrears       = [];
       
        $fees_arrears_plus_current_amount_cc    = [];
        $fees_arrears_plus_current_amount       = [];
       
        
        
        $keyCount_cc = 0;
        $keyCount    = 0;
        
        $current_total_payable_amount_arr_cc = [];
        $current_total_payable_amount_arr    = [];
                
        $current_total_payable_amount_cc = 0;
        $current_total_payable_amount = 0;
        
        $fee_arrears_count_cc = 0;
        $fee_arrears_count    = 0;
        
        $total_arrears_amount_cc = 0;
        $total_arrears_amount = 0;
        
        $payable_key = 0;
        
        
        
        
        if(!empty($data_set_arr)){
           
            $fee_id_unique_arr = [];
            
            $max_key = count($data_set_arr) >= 1 ? count($data_set_arr) - 1 : 0;
            
//            echo '<br>';
//            echo $min = min($data_set_arr);
            
//            return response()->json($data_set_arr, 200);
//            exit;

            
            //if customize challan row exits then only accept customize challan
            //if customize challan not exits then show all arears 
            
            foreach ( $data_set_arr as $key => $value) {
                
               $fee_id_unique_arr[$value['id']] = $value['id'];
               
                if( $key  == $max_key){
                    if($value['is_challan_customize'] == 1 && $value['customize_by_challan_no'] == $data_set_arr[$max_key]['challan_no']){
               
                        $fees_voucher_array_cc[$keyCount_cc]['id']             = $value['id'];
                        $fees_voucher_array_cc[$keyCount_cc]['admission_code'] = $value['admission_code'];
                        $fees_voucher_array_cc[$keyCount_cc]['bank_id']        = $value['bank_id'];
                        $fees_voucher_array_cc[$keyCount_cc]['bank_name']      = $value['bank']['name'];
                        $fees_voucher_array_cc[$keyCount_cc]['bank_ac_no']     = $value['bank']['ac_no'];
                        $fees_voucher_array_cc[$keyCount_cc]['bank_ac_no_arr'] = Utilities::string_to_arr($value['bank']['ac_no']);
                        $fees_voucher_array_cc[$keyCount_cc]['campus_name']    = $value['campus']['campus_name'];
                        $fees_voucher_array_cc[$keyCount_cc]['class_name']     = $value['class']['class_name'];
                        $fees_voucher_array_cc[$keyCount_cc]['campus_id']      = $value['campus_id'];
                        $fees_voucher_array_cc[$keyCount_cc]['class_id']       = $value['class_id'];
                        $fees_voucher_array_cc[$keyCount_cc]['challan_no']     = $value['challan_no'];
                        $fees_voucher_array_cc[$keyCount_cc]['fee_date']       = $value['slip_issue_date'];
                        $fees_voucher_array_cc[$keyCount_cc]['fee_month']      = $value['fee_month'];
                        $fees_voucher_array_cc[$keyCount_cc]['fee_month_code'] = $value['fee_month_code'];
                        $fees_voucher_array_cc[$keyCount_cc]['fee_month_name'] = Utilities::get_month_name_by_yearmonth_index($value['fee_month_code']);
                        $fees_voucher_array_cc[$keyCount_cc]['fees_master_id'] = $value['fees_master_id'];
                        $fees_voucher_array_cc[$keyCount_cc]['gr_no']            = $value['gr_no'];
                        $fees_voucher_array_cc[$keyCount_cc]['kuickpay_id']      = $value['kuickpay_id'];
                        $fees_voucher_array_cc[$keyCount_cc]['organization_id']  = $value['organization_id'];
                        $fees_voucher_array_cc[$keyCount_cc]['organization_img'] = $getImgPublic . '/'. $value['organization']['org_logo'].'?'.rand();
                        $fees_voucher_array_cc[$keyCount_cc]['payment_status']   = $value['payment_status'];
                        $fees_voucher_array_cc[$keyCount_cc]['section_id']       = $value['section_id'];
                        $fees_voucher_array_cc[$keyCount_cc]['session_id']       = $value['session_id'];
                        $fees_voucher_array_cc[$keyCount_cc]['slip_due_date']    = $value['slip_due_date'];
                        $fees_voucher_array_cc[$keyCount_cc]['slip_issue_date']  = $value['slip_issue_date'];
                        $fees_voucher_array_cc[$keyCount_cc]['slip_type_id']        = $value['slip_type_id'];
                        $fees_voucher_array_cc[$keyCount_cc]['slip_validity_date']  = $value['slip_validity_date'];
                        $fees_voucher_array_cc[$keyCount_cc]['student_name']        = $value['std_admission']['student_name'];
                        $fees_voucher_array_cc[$keyCount_cc]['father_name']         = $value['std_admission']['father_name'];
                        $fees_voucher_array_cc[$keyCount_cc]['gender']              = $value['std_admission']['gender'];
                        $fees_voucher_array_cc[$keyCount_cc]['student_full_name']   = $value['std_admission']['student_name'] .' '.$value['std_admission']['father_name'];
                        $fees_voucher_array_cc[$keyCount_cc]['amount']              = $value['total_payable_amount'];
                        $fees_voucher_array_cc[$keyCount_cc]['fee_slip_details']    = $value['fee_slip_details'];


                        $fees_arrears_plus_current_amount_cc[$value['fee_month_code']]['admission_code'] = $value['admission_code'];
                        $fees_arrears_plus_current_amount_cc[$value['fee_month_code']]['amount'] = $value['total_payable_amount'];
                        $fees_arrears_plus_current_amount_cc[$value['fee_month_code']]['fee_month_name'] = Utilities::get_month_name_by_yearmonth_index($value['fee_month_code']);
                        $fees_arrears_plus_current_amount_cc[$value['fee_month_code']]['fee_month_code'] = $value['fee_month_code'];
                        $fees_arrears_plus_current_amount_cc[$value['fee_month_code']]['fee_type'] = 'Tuition Fee';



                        $current_total_payable_amount_cc                               = $value['total_payable_amount'];
                        $current_total_payable_amount_arr_cc[$value['admission_code']] = $current_total_payable_amount_cc;

                        $keyCount_cc++;
                        
                        }
                    elseif(empty($value['is_challan_customize'])){
                        
                        $fees_voucher_array[$keyCount]['id']             = $value['id'];
                        $fees_voucher_array[$keyCount]['admission_code'] = $value['admission_code'];
                        $fees_voucher_array[$keyCount]['bank_id']        = $value['bank_id'];
                        $fees_voucher_array[$keyCount]['bank_name']      = $value['bank']['name'];
                        $fees_voucher_array[$keyCount]['bank_ac_no']     = $value['bank']['ac_no'];
                        $fees_voucher_array[$keyCount]['bank_ac_no_arr'] = Utilities::string_to_arr($value['bank']['ac_no']);
                        $fees_voucher_array[$keyCount]['campus_name']    = $value['campus']['campus_name'];
                        $fees_voucher_array[$keyCount]['class_name']     = $value['class']['class_name'];
                        $fees_voucher_array[$keyCount]['campus_id']      = $value['campus_id'];
                        $fees_voucher_array[$keyCount]['class_id']       = $value['class_id'];
                        $fees_voucher_array[$keyCount]['challan_no']     = $value['challan_no'];
                        $fees_voucher_array[$keyCount]['fee_date']       = $value['slip_issue_date'];
                        $fees_voucher_array[$keyCount]['fee_month']      = $value['fee_month'];
                        $fees_voucher_array[$keyCount]['fee_month_code'] = $value['fee_month_code'];
                        $fees_voucher_array[$keyCount]['fee_month_name'] = Utilities::get_month_name_by_yearmonth_index($value['fee_month_code']);
                        $fees_voucher_array[$keyCount]['fees_master_id'] = $value['fees_master_id'];
                        $fees_voucher_array[$keyCount]['gr_no']            = $value['gr_no'];
                        $fees_voucher_array[$keyCount]['kuickpay_id']      = $value['kuickpay_id'];
                        $fees_voucher_array[$keyCount]['organization_id']  = $value['organization_id'];
                        $fees_voucher_array[$keyCount]['organization_img'] = $getImgPublic . '/'. $value['organization']['org_logo'].'?'.rand();
                        $fees_voucher_array[$keyCount]['payment_status']   = $value['payment_status'];
                        $fees_voucher_array[$keyCount]['section_id']       = $value['section_id'];
                        $fees_voucher_array[$keyCount]['session_id']       = $value['session_id'];
                        $fees_voucher_array[$keyCount]['slip_due_date']    = $value['slip_due_date'];
                        $fees_voucher_array[$keyCount]['slip_issue_date']  = $value['slip_issue_date'];
                        $fees_voucher_array[$keyCount]['slip_type_id']        = $value['slip_type_id'];
                        $fees_voucher_array[$keyCount]['slip_validity_date']  = $value['slip_validity_date'];
                        $fees_voucher_array[$keyCount]['student_name']        = $value['std_admission']['student_name'];
                        $fees_voucher_array[$keyCount]['father_name']         = $value['std_admission']['father_name'];
                        $fees_voucher_array[$keyCount]['gender']         = $value['std_admission']['gender'];
                        $fees_voucher_array[$keyCount]['student_full_name']   = $value['std_admission']['student_name'] .' '.$value['std_admission']['father_name'];
                        $fees_voucher_array[$keyCount]['amount']              = $value['total_payable_amount'];
                        $fees_voucher_array[$keyCount]['fee_slip_details']    = $value['fee_slip_details'];


                        $fees_arrears_plus_current_amount[$value['fee_month_code']]['admission_code'] = $value['admission_code'];
                        $fees_arrears_plus_current_amount[$value['fee_month_code']]['payment_status'] = $value['payment_status'];
                        $fees_arrears_plus_current_amount[$value['fee_month_code']]['amount'] = $value['total_payable_amount'];
                        $fees_arrears_plus_current_amount[$value['fee_month_code']]['fee_month_name'] = Utilities::get_month_name_by_yearmonth_index($value['fee_month_code']);
                        $fees_arrears_plus_current_amount[$value['fee_month_code']]['fee_month_code'] = $value['fee_month_code'];
                        $fees_arrears_plus_current_amount[$value['fee_month_code']]['fee_type'] = 'Tuition Fee';



                        $current_total_payable_amount                               = $value['total_payable_amount'];
                        $current_total_payable_amount_arr[$value['admission_code']] = $current_total_payable_amount;

                        $keyCount++;     
                    }
                }
                else
                {
                    if($value['is_challan_customize'] == 1 && $value['customize_by_challan_no'] == $data_set_arr[$max_key]['challan_no'])
                    {
                        $fees_arrears_plus_current_amount_cc[$value['fee_month_code']]['admission_code'] = $value['admission_code'];
                        $fees_arrears_plus_current_amount_cc[$value['fee_month_code']]['payment_status'] = $value['payment_status'];
                        $fees_arrears_plus_current_amount_cc[$value['fee_month_code']]['amount'] = $value['total_payable_amount'];
                        $fees_arrears_plus_current_amount_cc[$value['fee_month_code']]['fee_month_name'] = Utilities::get_month_name_by_yearmonth_index($value['fee_month_code']);
                        $fees_arrears_plus_current_amount_cc[$value['fee_month_code']]['fee_month_code'] = $value['fee_month_code'];
                        $fees_arrears_plus_current_amount_cc[$value['fee_month_code']]['fee_type'] = 'Tuition Fee';


                        $fees_arrears_cc[$fee_arrears_count_cc]['admission_code'] = $value['admission_code'];
                        $fees_arrears_cc[$fee_arrears_count_cc]['month']          = $value['fee_month'];
                        $fees_arrears_cc[$fee_arrears_count_cc]['arrears_amount'] = $value['total_payable_amount'];
                        $fees_arrears_cc[$fee_arrears_count_cc]['fee_month_code'] = $value['fee_month_code'];
                        $fees_arrears_cc[$fee_arrears_count_cc]['fee_month_name'] = Utilities::get_month_name_by_yearmonth_index($value['fee_month_code']);
                        $fee_arrears_count_cc++;
                    }
                    elseif(empty($value['is_challan_customize'])){
                            
                            $fees_arrears_plus_current_amount[$value['fee_month_code']]['admission_code'] = $value['admission_code'];
                            $fees_arrears_plus_current_amount[$value['fee_month_code']]['payment_status'] = $value['payment_status'];
                            $fees_arrears_plus_current_amount[$value['fee_month_code']]['amount'] = $value['total_payable_amount'];
                            $fees_arrears_plus_current_amount[$value['fee_month_code']]['fee_month_name'] = Utilities::get_month_name_by_yearmonth_index($value['fee_month_code']);
                            $fees_arrears_plus_current_amount[$value['fee_month_code']]['fee_month_code'] = $value['fee_month_code'];
                            $fees_arrears_plus_current_amount[$value['fee_month_code']]['fee_type'] = 'Tuition Fee';


                            $fees_arrears[$fee_arrears_count]['admission_code'] = $value['admission_code'];
                            $fees_arrears[$fee_arrears_count]['month']          = $value['fee_month'];
                            $fees_arrears[$fee_arrears_count]['arrears_amount'] = $value['total_payable_amount'];
                            $fees_arrears[$fee_arrears_count]['fee_month_code'] = $value['fee_month_code'];
                            $fees_arrears[$fee_arrears_count]['fee_month_name'] = Utilities::get_month_name_by_yearmonth_index($value['fee_month_code']);
                            $fee_arrears_count++; 
                            
                        }
                } 
   
            }

            
            $overall_payment_status_cc = 0;
            $overall_payment_status    = 0;
            
            if(!empty($fees_arrears_plus_current_amount_cc)){
                
                foreach ($fees_arrears_plus_current_amount_cc as $key => $value) {
                    if(isset($total_payable_amount_array_cc[$value['admission_code']])) {
                        if( $value['payment_status'] == 1){
                            $overall_payment_status_cc = 1;
                        }
                        
                        $total_payable_amount_array_cc[$value['admission_code']] += $value['amount'];
                    } else {
                        if( $value['payment_status'] == 1){
                            $overall_payment_status_cc = 1;
                        }
                        
                        $total_payable_amount_array_cc[$value['admission_code']] = $value['amount'];
                    }
                }
            }
            
            if(!empty($fees_arrears_plus_current_amount)){
                foreach ($fees_arrears_plus_current_amount as $key => $value) {
                    if(isset($total_payable_amount_array[$value['admission_code']])) {
                        
                        if( $value['payment_status'] == 1){
                            $overall_payment_status = 1;
                        }
                        
                        $total_payable_amount_array[$value['admission_code']] += $value['amount'];
                    } else {
                        if( $value['payment_status'] == 1){
                            $overall_payment_status = 1;
                        }
                        $total_payable_amount_array[$value['admission_code']] = $value['amount'];
                    }
                }
            }
            
            if(empty($current_total_payable_amount_arr_cc) && empty($current_total_payable_amount_arr) ){
                $status = 200;
                $response = Utilities::buildBaseResponse(10003, "Challan Not Exits!", 'error');
                return response()->json($response, $status); 
                exit;
            }
            
            
            

            $data_result = [];
            $status = 200;
            $data_result['current_total_payable_amount_arr'] = !empty($current_total_payable_amount_arr_cc) ? $current_total_payable_amount_arr_cc : $current_total_payable_amount_arr;
         
            $data_result['fees_voucher_array'] =   !empty($fees_voucher_array_cc)  ? $fees_voucher_array_cc : $fees_voucher_array;

            $fees_arrears = !empty($fees_arrears_cc) ? $fees_arrears_cc : $fees_arrears;

            usort($fees_arrears, function ($item1, $item2) {
                return $item1['fee_month_code'] <=> $item2['fee_month_code'];
            });

            $data_result['fees_arrears'] = $fees_arrears;
            $data_result['total_payable_amount_array'] =  !empty($total_payable_amount_array_cc) ? $total_payable_amount_array_cc : $total_payable_amount_array;
            $data_result['overall_payment_status']     =  !empty($overall_payment_status_cc)     ? $overall_payment_status_cc     : $overall_payment_status;

            
//            
//             echo $overall_payment_status_cc.'sd';
//             echo $overall_payment_status.'df';
             
            $data_result['fees_arrears_plus_current_amount'] =  !empty($fees_arrears_plus_current_amount_cc) ? $fees_arrears_plus_current_amount_cc : $fees_arrears_plus_current_amount;
//return response()->json($data_result, 200);
//             exit;
            $response = Utilities::buildSuccessResponse(10004, "Get All Monthly Vouchers.", $data_result);

            
        
        }
        else{
            $status = 200;
            $response = Utilities::buildBaseResponse(10003, "Challan Not Exits!!", 'error');
        }
        
        return response()->json($response, $status); 
    
    }
    
   
    
//    monthlyChallanPosting for all month challan posting status update if challan customize then only customize entry update
    public function monthlyChallanPosting(Request $request, $id = null)
    {   
        
        $this->validate($request, $this->mdlName->postingRules($request), $this->mdlName->postingMessages($request));
        
        $this->mdlName->filterColumns($request);
         
        Utilities::defaultUpdateAttributes($request, $request->data_user_id);
        
        $whereData = array();
        
        if(!empty($request->organization_id)) {   
            $whereData[] = ['organization_id', $request->organization_id];
        }
        
        if(!empty($request->data_org_id)) {   
            $whereData[] = ['organization_id', $request->data_org_id];
        }
        
        if(!empty($request->data_organization_id)) {   
            $whereData[] = ['organization_id', $request->data_organization_id];
        }
        
        if(!empty($request->campus_id)) {   
            $whereData[] = ['campus_id', $request->campus_id];
        }
        
        if(!empty($request->data_campus_id)) {   
            $whereData[] = ['campus_id', $request->data_campus_id];
        }
        
        if(!empty($request->class_id)) {   
            $whereData[] = ['class_id', $request->class_id];
        }
        
        if(!empty($request->admission_code)) {   
            $whereData[] = ['admission_code', $request->admission_code];
        }
        
        
        if(!empty($request->challan_no)) {   
            $where_admission_code = substr($request->challan_no,4,6);
            
            if(!empty($where_admission_code)){
                $whereData[] = ['admission_code', $where_admission_code];
            }
        }
        
        
 
        if(!empty($request->session_id)) {   
            $whereData[] = ['session_id', $request->session_id];
        }
        
        if(!empty($request->data_session_id)) {   
            $whereData[] = ['session_id', $request->data_session_id];
        }
                
//        if(!empty($request->slip_type)) {   
//            $whereData[] = ['slip_type_id', $request->slip_type];
//        }
        
//        if($request->month) {   
//            $whereData[] = ['month', $request->month];
//        }
                
        
        if($request->is_enable != null) {
            $whereData[] = ['is_enable', $request->is_enable];
        }else{
            $whereData[] = ['is_enable', 1];
        }
        
        $whereData[] = ['payment_status', 0];
        
        if(!empty($request->month)) { 
            $month = substr($request->month,4,2);
            $year  = substr($request->month,0,4);
            
            $fee_actual_date  = date('Y-m-d', strtotime($year.'-'.$month.'-01'));
        }
        
        $queryObj = GenMonthlyVoucher::with('FeeSlipDetails','stdAdmission', 'Organization','Campus', 'Class', 'Bank')
                    ->where($whereData);
        
        if(!empty($fee_actual_date)) {   
           $queryObj->whereDate('fee_actual_date', '<=', $fee_actual_date);
        }
        
        
        $data_set = $queryObj->get();
            
        $data_set_arr = $data_set->toArray();
        
        usort($data_set_arr, function ($item1, $item2) {
            return $item1['fee_month_code'] <=> $item2['fee_month_code'];
        });
        
        
        //cc varibale preifx for customize challan.
        //other for else all arrears
        

        $fees_arrears_plus_current_amount_cc    = [];
        $fees_arrears_plus_current_amount       = [];
       
        
        
        if(!empty($data_set_arr)){
           
            
            $max_key = count($data_set_arr) >= 1 ? count($data_set_arr) - 1 : 0;
            
//            echo '<br>';
//            echo $min = min($data_set_arr);
            
//            return response()->json($data_set_arr, 200);
//            exit;

            
            //if customize challan row exits then only accept customize challan
            //if customize challan not exits then show all arears 
            
            foreach ( $data_set_arr as $key => $value) {
               
                if( $key  == $max_key){
                    if($value['is_challan_customize'] == 1 && $value['customize_by_challan_no'] == $data_set_arr[$max_key]['challan_no']){
                        
                        $fees_arrears_plus_current_amount_cc[$value['fee_month_code']]['id'] = $value['id'];
                        $fees_arrears_plus_current_amount_cc[$value['fee_month_code']]['admission_code'] = $value['admission_code'];
                        $fees_arrears_plus_current_amount_cc[$value['fee_month_code']]['amount'] = $value['total_payable_amount'];
                        $fees_arrears_plus_current_amount_cc[$value['fee_month_code']]['fee_month_name'] = Utilities::get_month_name_by_yearmonth_index($value['fee_month_code']);
                        $fees_arrears_plus_current_amount_cc[$value['fee_month_code']]['fee_month_code'] = $value['fee_month_code'];
                        $fees_arrears_plus_current_amount_cc[$value['fee_month_code']]['fee_type'] = 'Tuition Fee';
                    }
                    elseif(empty($value['is_challan_customize'])){
                        
                        $fees_arrears_plus_current_amount[$value['fee_month_code']]['id'] = $value['id'];
                        $fees_arrears_plus_current_amount[$value['fee_month_code']]['admission_code'] = $value['admission_code'];
                        $fees_arrears_plus_current_amount[$value['fee_month_code']]['payment_status'] = $value['payment_status'];
                        $fees_arrears_plus_current_amount[$value['fee_month_code']]['amount'] = $value['total_payable_amount'];
                        $fees_arrears_plus_current_amount[$value['fee_month_code']]['fee_month_name'] = Utilities::get_month_name_by_yearmonth_index($value['fee_month_code']);
                        $fees_arrears_plus_current_amount[$value['fee_month_code']]['fee_month_code'] = $value['fee_month_code'];
                        $fees_arrears_plus_current_amount[$value['fee_month_code']]['fee_type'] = 'Tuition Fee';
                        
                    }
                }
                else
                {
                    if($value['is_challan_customize'] == 1 && $value['customize_by_challan_no'] == $data_set_arr[$max_key]['challan_no'])
                    {
                        $fees_arrears_plus_current_amount_cc[$value['fee_month_code']]['id'] = $value['id'];
                        $fees_arrears_plus_current_amount_cc[$value['fee_month_code']]['admission_code'] = $value['admission_code'];
                        $fees_arrears_plus_current_amount_cc[$value['fee_month_code']]['payment_status'] = $value['payment_status'];
                        $fees_arrears_plus_current_amount_cc[$value['fee_month_code']]['amount'] = $value['total_payable_amount'];
                        $fees_arrears_plus_current_amount_cc[$value['fee_month_code']]['fee_month_name'] = Utilities::get_month_name_by_yearmonth_index($value['fee_month_code']);
                        $fees_arrears_plus_current_amount_cc[$value['fee_month_code']]['fee_month_code'] = $value['fee_month_code'];
                        $fees_arrears_plus_current_amount_cc[$value['fee_month_code']]['fee_type'] = 'Tuition Fee';

                    }
                    elseif(empty($value['is_challan_customize'])){
                        
                        $fees_arrears_plus_current_amount[$value['fee_month_code']]['id'] = $value['id'];
                        $fees_arrears_plus_current_amount[$value['fee_month_code']]['admission_code'] = $value['admission_code'];
                        $fees_arrears_plus_current_amount[$value['fee_month_code']]['payment_status'] = $value['payment_status'];
                        $fees_arrears_plus_current_amount[$value['fee_month_code']]['amount'] = $value['total_payable_amount'];
                        $fees_arrears_plus_current_amount[$value['fee_month_code']]['fee_month_name'] = Utilities::get_month_name_by_yearmonth_index($value['fee_month_code']);
                        $fees_arrears_plus_current_amount[$value['fee_month_code']]['fee_month_code'] = $value['fee_month_code'];
                        $fees_arrears_plus_current_amount[$value['fee_month_code']]['fee_type'] = 'Tuition Fee';
                    }
                } 
   
            }
            
            
            $status = 200;
            if(empty($fees_arrears_plus_current_amount_cc) && empty($fees_arrears_plus_current_amount) ){
                $status = 200;
                $response = Utilities::buildBaseResponse(10003, "Challan Already Paid OR Challan Not Exits!!", 'error');
                return response()->json($response, $status); 
                exit;
            }
            
            
            
            $fees_arrears_plus_current_array = !empty($fees_arrears_plus_current_amount_cc) ? $fees_arrears_plus_current_amount_cc : $fees_arrears_plus_current_amount;
            
            
            
            $fee_id_unique_arr = [];
            
            $total_payable_amount = 0;
            foreach ( $fees_arrears_plus_current_array as $key => $value) {
               $fee_id_unique_arr[$value['id']] = $value['id'];
               $total_payable_amount += $value['amount'];
            }
		
           
             
             
            if(!empty($request->transaction_amount) && $total_payable_amount != $request->transaction_amount){
       
                $status = 200;
                $response = Utilities::buildBaseResponse(10003, "Voucher Posting Failed Amount Not Correct Total Payable amount is ".$total_payable_amount."!!", 'error');
                return response()->json($response, $status); 
                exit;
            }   
            
            $master_fee_update = [
                'payment_status'      => 1,
                'payment_by_channel'  => $request->bank_id,
                'bank_id'             => $request->bank_id,
                'transaction_no'      => $request->transaction_no,
                'transaction_amount'  => $request->transaction_amount,
                'pay_date'            => $request->pay_date
            ];
            
            $updatedobj = GenMonthlyVoucher::whereIn('id', $fee_id_unique_arr)->where(['admission_code' => $request->admission_code])->update($master_fee_update);
            
            if ($updatedobj) {
                $status = 200;
                $response = Utilities::buildSuccessResponse(10001, "Voucher Posting successfully updated.", $updatedobj);
                return response()->json($response, $status); 
                exit;
            }
            
        
        }
        else{
            $status = 200;
            $response = Utilities::buildBaseResponse(10003, "Challan Already Paid OR Challan Not Exits!", 'error');
            return response()->json($response, $status); 
            exit;
        }
       
    
    }

    
//    monthlyBulkChallanPosting for all month challan posting status update if challan customize then only customize entry update
    public function monthlyBulkChallanPosting(Request $request, $id = null)
    {   

        $fileName = time().'.'.$request->file('formFile')->extension();  
        $folder = storage_path('/app/monthly_fee_collection/');
        
        if($request->file('formFile')->move($folder, $fileName)){
                
            if ( $xlsx = SimpleXLSX::parse($folder.$fileName)) {
                // Produce array keys from the array values of 1st array element
                $header_values = $rows = [];
                foreach ( $xlsx->rows() as $k => $r ) {
                    if ( $k === 0 ) {
//                            print_r($r);
                        $header_values = $r;
                        continue;
                    }

                    $rows[] = array_combine( $header_values, $r );
                }

//                 return response()->json($rows, 200); 
//                exit;
                
                if(!empty($rows) && Utilities::findKey($rows, 'Admission_Number') && Utilities::findKey($rows, 'Amount') ){
                    
                    //old temp data reset
                    $updatedobj = MonthlyFeeCollectionTemp::where(['is_enable' => 1])->update(['is_enable' => 0, 'updated_by' => $request->data_user_id,  'updated_at' => date('Y-m-d H:i:s')]);
                    $createobj  = MonthlyFeeCollectionTemp::Create([ 'file_name' => $fileName, 'file_upload_by' => $request->data_user_id, 'created_by' => $request->data_user_id, 'created_at' => date('Y-m-d H:i:s')]);

            
            
                    $admisison_code_unique_arr = Arr::pluck( $rows, 'Admission_Number');

                    $whereData[] = ['is_enable', 1];

                    $queryObj = GenMonthlyVoucher::with('FeeSlipDetails', 'stdAdmission', 'Organization','Campus', 'Class', 'Bank')
                                ->where($whereData);

                    $queryObj->where(function($query) use ($rows) {
                        foreach ($rows as $key => $value) {
                            $query->orWhere('admission_code', '=', $value['Admission_Number']);
                        }
                    });

                    $data_set = $queryObj->get();
                    $data_set_arr = $data_set->toArray();


                    $data_set->map(function($item) use(&$data_set_payment_status) {
                        if($item->payment_status == 0){
                            $data_set_payment_status[$item->admission_code][] = $item->toArray();
                        }
                    });


                    $exitsCount    = 0;
                    $notExitsCount = 0;
                    $error_log     = [];
                    foreach ($admisison_code_unique_arr as $key => $value) {

                        if(isset($data_set_payment_status[$value])){

                        }else{
                            $notExitsCount += 1;
                            $error_log[] = 'Challan Not Extis For this Admission Code: '.$value. "\n";
                        }
                    }



                    //if customize challan row exits then only accept customize challan
                    //if customize challan not exits then show all arears 

                    $fees_arrears_plus_current_amount_cc    = [];
                    $fees_arrears_plus_current_amount       = [];


                    $challan_posting_arr = [];
                    foreach ( $data_set_payment_status as $key => $array_value) {

                        foreach ($array_value as $ckey => $value) {
                            if($value['is_challan_customize'] == 1 ){
        //                        echo 'customize';

                                $fees_arrears_plus_current_amount_cc[$key][$value['fee_month_code']]['id'] = $value['id'];
                                $fees_arrears_plus_current_amount_cc[$key][$value['fee_month_code']]['admission_code'] = $value['admission_code'];
                                $fees_arrears_plus_current_amount_cc[$key][$value['fee_month_code']]['amount'] = $value['total_payable_amount'];
                                $fees_arrears_plus_current_amount_cc[$key][$value['fee_month_code']]['fee_month_name'] = Utilities::get_month_name_by_yearmonth_index($value['fee_month_code']);
                                $fees_arrears_plus_current_amount_cc[$key][$value['fee_month_code']]['fee_month_code'] = $value['fee_month_code'];
                                $fees_arrears_plus_current_amount_cc[$key][$value['fee_month_code']]['fee_type'] = 'Tuition Fee';
                            }

                            elseif(empty($value['is_challan_customize'])){
        //                        echo 'not customize';
                                $fees_arrears_plus_current_amount[$key][$value['fee_month_code']]['id'] = $value['id'];
                                $fees_arrears_plus_current_amount[$key][$value['fee_month_code']]['admission_code'] = $value['admission_code'];
                                $fees_arrears_plus_current_amount[$key][$value['fee_month_code']]['payment_status'] = $value['payment_status'];
                                $fees_arrears_plus_current_amount[$key][$value['fee_month_code']]['amount'] = $value['total_payable_amount'];
                                $fees_arrears_plus_current_amount[$key][$value['fee_month_code']]['fee_month_name'] = Utilities::get_month_name_by_yearmonth_index($value['fee_month_code']);
                                $fees_arrears_plus_current_amount[$key][$value['fee_month_code']]['fee_month_code'] = $value['fee_month_code'];
                                $fees_arrears_plus_current_amount[$key][$value['fee_month_code']]['fee_type'] = 'Tuition Fee';
                            }
                        }

                    }

                    foreach ($fees_arrears_plus_current_amount as $key => $value) {

                        if(!empty($fees_arrears_plus_current_amount_cc[$key])){
                            $challan_posting_arr[$key] = $fees_arrears_plus_current_amount_cc[$key];
                        }else{
                            $challan_posting_arr[$key] = $value;
                        }

                    }

                    $get_all_sheet_data  = [];
                    foreach ($rows as $key => $value) {
                        $get_all_sheet_data[$value['Admission_Number']]  = $value;
                    }


                    $total_amount_arr = [];


                    foreach ($challan_posting_arr as $p_key => $p_value) {
                        $total_amount = 0;
                        foreach($p_value as $ch_key => $child_values){
                           $total_amount += $child_values['amount'];
                           $total_amount_arr[$p_key] = $total_amount;
                        }
                    }


//                         print_r($total_amount_arr); 

                    foreach ($get_all_sheet_data as $key => $value) {
                        if(!empty($total_amount_arr[$key]) && $total_amount_arr[$key] != $value['Amount']){
                            $error_log[] = 'Amount Not Correct for this Student Admission Code: '.$key. "\n";
                        }elseif(!empty($total_amount_arr[$key]) && $total_amount_arr[$key] == $value['Amount']){

                             $exitsCount += 1;
//                                $challan_posting_arr[$key] loop update status paid;

                        }
                    }



                    $status = 200;
                    if(empty($challan_posting_arr) ){
                        $status = 200;
                        $response = Utilities::buildBaseResponse(10003, "Challan Not Exits!!", 'error');
                        return response()->json($response, $status); 
                        exit;
                    }



                    $jsongFile = '';
                    if($notExitsCount > 0){
                        $jsongFile = $fileName.'_log_file.txt';
                        File::put(storage_path('/app/monthly_fee_collection/logs/'.$jsongFile), $error_log);


                        $getLog       = url('app/monthly_fee_collection/logs/');
                        $getLogPublic = '';
                        if (strpos($getLog, 'public') !== false) {
                            $getLogPublic = str_replace('public', 'storage', $getLog);
                        }else{
                            $getLogPublic = url('storage/app/monthly_fee_collection/logs/');
                        }


                        $return_data['log_file'] = $getLogPublic.'/'.$jsongFile;
//                            $return_data['log_file'] = storage_path('/app/monthly_fee_collection/logs/'.$jsongFile);
                    }

                    $updatedobj = MonthlyFeeCollectionTemp::where(['is_enable' => 1])->update(['error_log_file' => $jsongFile, 'std_verified_count' => $exitsCount, 'updated_by' => $request->data_user_id,  'updated_at' => date('Y-m-d H:i:s')]);

                    if($exitsCount > 0){
                        $return_data['std_exits'] =  $exitsCount;
                    }else{
                        $return_data['std_exits'] =  0;
                    }

                }
                else{
                    $return_data['data_error'] =  'File Format Not Correct';
                }

            }
            else{
                $return_data['data_error'] =  'File Data not Getting';
            }
                
           
            
        }else{
            $return_data['upload_error'] =  'file Not Upload..';
        }
        

        $status = 200;
        $response = Utilities::buildSuccessResponse(10001, "Sheet Uploading successfully Check Logs.", $return_data);
        return response()->json($response, $status); 
        exit;
    
    }
    

//    monthlyBulkChallanPosting for all month challan posting status update if challan customize then only customize entry update
    public function monthlyCollectionFileUploaded(Request $request)
    {   
        
       $file_exits = MonthlyFeeCollectionTemp::where(['is_enable' => 1])->get()->toArray();
       
       $return_data = [];
       
       if(!empty($file_exits)){
           
            $file_exits =  $file_exits[0];
          
            $fileName       = $file_exits['file_name'];  
            $error_log_file = $file_exits['error_log_file'];  
            $std_verified_count = $file_exits['std_verified_count'];  
            $folder = storage_path('/app/monthly_fee_collection/');

            if(!empty($file_exits['file_name'])){


                if(!empty($error_log_file)){
                    
                    
                    $getLog       = url('app/monthly_fee_collection/logs/');
                    $getLogPublic = '';
                    if (strpos($getLog, 'public') !== false) {
                        $getLogPublic = str_replace('public', 'storage', $getLog);
                    }else{
                        $getLogPublic = url('storage/app/monthly_fee_collection/logs/');
                    }
           
                    
//                    $return_data['log_file'] = storage_path('/app/monthly_fee_collection/logs/'.$error_log_file);
                    $return_data['log_file'] = $getLogPublic.'/'.$error_log_file;
                    
                }

                if($std_verified_count > 0){
                    $return_data['std_exits'] =  $std_verified_count ;
                }else{
                    $return_data['std_exits'] =  0;
                }
                    
            }
            else{
                $return_data['upload_error'] =  'file Not Upload..';
            }
        
       }
       
        $status = 200;
        $response = Utilities::buildSuccessResponse(10001, "Sheet Uploading successfully Check Logs.", $return_data);
        return response()->json($response, $status); 
        exit;
        
    }

//    monthlyBulkChallanPosting for all month challan posting status update if challan customize then only customize entry update to paid
    public function setMonthlyBulkChallanPosting(Request $request, $id = null)
    {   

        $file_exits = MonthlyFeeCollectionTemp::where(['is_enable' => 1])->get()->toArray();
            
        
        if(!empty($file_exits)){
           
            $file_exits =  $file_exits[0];

            $fileName       = $file_exits['file_name'];  
            $error_log_file = $file_exits['error_log_file'];  
            $std_verified_count = $file_exits['std_verified_count'];  
            $folder = storage_path('/app/monthly_fee_collection/');

            
        }
           
        
        if($fileName){
                
            if ( $xlsx = SimpleXLSX::parse($folder.$fileName)) {
                // Produce array keys from the array values of 1st array element
                $header_values = $rows = [];
                foreach ( $xlsx->rows() as $k => $r ) {
                    if ( $k === 0 ) {
//                            print_r($r);
                        $header_values = $r;
                        continue;
                    }

                    $rows[] = array_combine( $header_values, $r );
                }


                if($rows){

                    $admisison_code_unique_arr = Arr::pluck( $rows, 'Admission_Number');

                    $whereData[] = ['is_enable', 1];

                    $queryObj = GenMonthlyVoucher::with('FeeSlipDetails', 'stdAdmission', 'Organization','Campus', 'Class', 'Bank')
                                ->where($whereData);

                    $queryObj->where(function($query) use ($rows) {
                        foreach ($rows as $key => $value) {
                            $query->orWhere('admission_code', '=', $value['Admission_Number']);
                        }
                    });

                    $data_set = $queryObj->get();
                    $data_set_arr = $data_set->toArray();


                    $data_set->map(function($item) use(&$data_set_payment_status) {
                        if($item->payment_status == 0){
                            $data_set_payment_status[$item->admission_code][] = $item->toArray();
                        }
                    });


                    $exitsCount    = 0;
                    $notExitsCount = 0;
                    $error_log     = [];
                    foreach ($admisison_code_unique_arr as $key => $value) {

                        if(isset($data_set_payment_status[$value])){

                        }else{
                            $notExitsCount += 1;
                            $error_log[] = 'Challan Not Extis For this Admission Code: '.$value. "\n";
                        }
                    }



                    //if customize challan row exits then only accept customize challan
                    //if customize challan not exits then show all arears 

                    $fees_arrears_plus_current_amount_cc    = [];
                    $fees_arrears_plus_current_amount       = [];


                    $challan_posting_arr = [];
                    foreach ( $data_set_payment_status as $key => $array_value) {

                        foreach ($array_value as $ckey => $value) {
                            if($value['is_challan_customize'] == 1 ){
        //                        echo 'customize';

                                $fees_arrears_plus_current_amount_cc[$key][$value['fee_month_code']]['id'] = $value['id'];
                                $fees_arrears_plus_current_amount_cc[$key][$value['fee_month_code']]['admission_code'] = $value['admission_code'];
                                $fees_arrears_plus_current_amount_cc[$key][$value['fee_month_code']]['amount'] = $value['total_payable_amount'];
                                $fees_arrears_plus_current_amount_cc[$key][$value['fee_month_code']]['fee_month_name'] = Utilities::get_month_name_by_yearmonth_index($value['fee_month_code']);
                                $fees_arrears_plus_current_amount_cc[$key][$value['fee_month_code']]['fee_month_code'] = $value['fee_month_code'];
                                $fees_arrears_plus_current_amount_cc[$key][$value['fee_month_code']]['fee_type'] = 'Tuition Fee';
                            }

                            elseif(empty($value['is_challan_customize'])){
        //                        echo 'not customize';
                                $fees_arrears_plus_current_amount[$key][$value['fee_month_code']]['id'] = $value['id'];
                                $fees_arrears_plus_current_amount[$key][$value['fee_month_code']]['admission_code'] = $value['admission_code'];
                                $fees_arrears_plus_current_amount[$key][$value['fee_month_code']]['payment_status'] = $value['payment_status'];
                                $fees_arrears_plus_current_amount[$key][$value['fee_month_code']]['amount'] = $value['total_payable_amount'];
                                $fees_arrears_plus_current_amount[$key][$value['fee_month_code']]['fee_month_name'] = Utilities::get_month_name_by_yearmonth_index($value['fee_month_code']);
                                $fees_arrears_plus_current_amount[$key][$value['fee_month_code']]['fee_month_code'] = $value['fee_month_code'];
                                $fees_arrears_plus_current_amount[$key][$value['fee_month_code']]['fee_type'] = 'Tuition Fee';
                            }
                        }

                    }

                    foreach ($fees_arrears_plus_current_amount as $key => $value) {

                        if(!empty($fees_arrears_plus_current_amount_cc[$key])){
                            $challan_posting_arr[$key] = $fees_arrears_plus_current_amount_cc[$key];
                        }else{
                            $challan_posting_arr[$key] = $value;
                        }

                    }

                    $get_all_sheet_data  = [];
                    foreach ($rows as $key => $value) {
                        $get_all_sheet_data[$value['Admission_Number']]  = $value;
                    }


                    $total_amount_arr = [];


                    foreach ($challan_posting_arr as $p_key => $p_value) {
                        $total_amount = 0;
                        foreach($p_value as $ch_key => $child_values){
                           $total_amount += $child_values['amount'];
                           $total_amount_arr[$p_key] = $total_amount;
                        }
                    }

                    if(empty($challan_posting_arr) ){
                        $status = 200;
                        $response = Utilities::buildBaseResponse(10003, "Challan Not Exits!!", 'error');
                        return response()->json($response, $status); 
                        exit;
                    }


//                         print_r($total_amount_arr); 

                    $fee_id_unique_arr = [];
                    foreach ($get_all_sheet_data as $key => $value) {
                        if(!empty($total_amount_arr[$key]) && $total_amount_arr[$key] != $value['Amount']){
                            $error_log[] = 'Amount Not Correct for this Student Admission Code: '.$key. "\n";
                        }elseif(!empty($total_amount_arr[$key]) && $total_amount_arr[$key] == $value['Amount']){

                             $exitsCount += 1;
//                                $challan_posting_arr[$key] loop update status paid;

                             foreach ($challan_posting_arr[$key] as $ch_key => $ch_value) {
                                 $fee_id_unique_arr[$ch_value['id']] = $ch_value['id'];
                            }
                        }
                    }

                    $master_fee_update = [
                        'payment_status'      => 1,
                        'payment_by_channel'  => $request->bank,
                        'bank_id'             => $request->bank,
                        'pay_date'            => $request->pay_date
                    ];

                    $updatedobj = GenMonthlyVoucher::whereIn('id', $fee_id_unique_arr)->update($master_fee_update);


                    $updatedobj = MonthlyFeeCollectionTemp::where(['is_enable' => 1])->update(['is_enable' => 0, 'file_submited_by' => $request->data_user_id, 'updated_by' => $request->data_user_id,  'updated_at' => date('Y-m-d H:i:s')]);


                    if($exitsCount > 0){
                        $return_data['std_exits'] =  $exitsCount;
                    }else{
                        $return_data['std_exits'] =  0;
                    }

                }

            }
            else{
                $return_data['data_error'] =  'File Data not Getting';
            }
                          
        }
        else{
            $return_data['upload_error'] =  'file Not Upload..';
        }
        

        $status = 200;
        $response = Utilities::buildSuccessResponse(10001, "Sheet successfully Posted.", $return_data);
        return response()->json($response, $status); 
        exit;
    
    }
  
    
    
    
    
    public function reg_slip_master_one(Request $request, $id = null)
    {
       
        
        if($request->std_registration_id) {   
            $whereData[] = ['std_registration_id', $request->std_registration_id];
            $whereData[] = ['is_enable',1];
        }

        $data_set = GenMonthlyVoucher::where($whereData)->get();
        $is_data = GenMonthlyVoucher::where($whereData)->count();
        $data_result = [];
        
        // $status = 200;
        // return response()->json($data_set['0']->id, $status); 
        // exit;
        

        $data_result['data_list'] = $data_set->toArray();
        if($is_data>0){
            $data_set_detail = GenMonthlyVoucherDetail::with('FeesType', 'DiscountType')->where('reg_slip_master_id',$data_set['0']->id)->get();
            $data_result['data_list_detail'] = $data_set_detail->toArray();
        }else{
            $response = Utilities::buildBaseResponse(80001, "Voucher Not Found..!! ", 'Info');
            return response()->json($response, 200);
            exit;
        }
        
        $response = Utilities::buildSuccessResponse(10004, "Single Generate Admission  Voucher.", $data_result);
        
        $status = 200;
        return response()->json($response, $status); 
    }

    
   
    public function getAssignFeeCode($param, $paramISNull = null) {
        
        $fc_code = null;
        
        
        $whereData[] = ['assign_fee_structure.is_enable', 1];
        
        $fc_code_q   = AssignFeeStructure::select('fee_structure_master.id', 'assign_fee_structure.fees_code')->join('fee_structure_master', 'fee_structure_master.fees_code', '=', 'assign_fee_structure.fees_code');
                
        if(!empty($param['organization_id'])){
            $whereData[] = ['assign_fee_structure.organization_id', $param['organization_id']];
        }
        
        if(!empty($param['country_id'])){
            $whereData[] = ['assign_fee_structure.country_id', $param['country_id']];
        }
        
        if(!empty($paramISNull) && in_array('country_id', $paramISNull)){
           $fc_code_q->whereNull('assign_fee_structure.country_id');
        }
        
        if(!empty($param['state_id'])){
            $whereData[] = ['assign_fee_structure.state_id', $param['state_id']];
        }
        
        if(!empty($paramISNull) && in_array('state_id', $paramISNull)){
            $fc_code_q->whereNull('assign_fee_structure.state_id');
        }
        
        if(!empty($param['region_id'])){
            $whereData[] = ['assign_fee_structure.region_id', $param['region_id']];
        }
        
        if(!empty($paramISNull) && in_array('region_id', $paramISNull)){
            $fc_code_q->whereNull('assign_fee_structure.region_id');
        }
        
        if(!empty($param['city_id'])){
            $whereData[] = ['assign_fee_structure.city_id', $param['city_id']];
        }
        
        if(!empty($paramISNull) && in_array('city_id', $paramISNull)){
            $fc_code_q->whereNull('assign_fee_structure.city_id');
        }
        
        if(!empty($param['campus_id'])){
            $whereData[] = ['assign_fee_structure.campus_id', $param['campus_id']];
        }
        
        if(!empty($paramISNull) && in_array('campus_id', $paramISNull)){
            $fc_code_q->whereNull('assign_fee_structure.campus_id');
        }
        
        
        if(!empty($param['class_id'])){
            $whereData[] = ['assign_fee_structure.class_id', $param['class_id']];
        }
        
        if(!empty($paramISNull) && in_array('class_id', $paramISNull)){
            $fc_code_q->whereNull('assign_fee_structure.class_id');
        }
        
        if(!empty($param['admission_code'])){
            $whereData[] = ['assign_fee_structure.admission_code', $param['admission_code']];
        }
        
        if(!empty($paramISNull) && in_array('admission_code', $paramISNull)){
            $fc_code_q->whereNull('assign_fee_structure.admission_code');
        }
        
        $fc_code_q->where($whereData)->orderBy('assign_fee_structure.id','asc')->limit(1);
        
        if($fc_code_q->count() > 0)
        {
            $fc_code =  $fc_code_q->get();
            
        }
        
        return $fc_code[0] ?? null;
        
    }
    
    public function getAssignDiscountCode($param, $paramISNull = null) {
        
        $disc_code = null;
//        
        $whereData[] = ['is_enable', 1];
        
        $disc_code_q   = DB::table('assign_discount_policy');
                
        if(!empty($param['organization_id'])){
            $whereData[] = ['organization_id', $param['organization_id']];
        }
        
        if(!empty($param['country_id'])){
            $whereData[] = ['country_id', $param['country_id']];
        }
        
        if(!empty($paramISNull) && in_array('country_id', $paramISNull)){
           $disc_code_q->whereNull('country_id');
        }
        
        if(!empty($param['state_id'])){
            $whereData[] = ['state_id', $param['state_id']];
        }
        
        if(!empty($paramISNull) && in_array('state_id', $paramISNull)){
            $disc_code_q->whereNull('state_id');
        }
        
        if(!empty($param['region_id'])){
            $whereData[] = ['region_id', $param['region_id']];
        }
        
        if(!empty($paramISNull) && in_array('region_id', $paramISNull)){
            $disc_code_q->whereNull('region_id');
        }
        
        if(!empty($param['city_id'])){
            $whereData[] = ['city_id', $param['city_id']];
        }
        
        if(!empty($paramISNull) && in_array('city_id', $paramISNull)){
            $disc_code_q->whereNull('city_id');
        }
        
        if(!empty($param['campus_id'])){
            $whereData[] = ['campus_id', $param['campus_id']];
        }
        
        if(!empty($paramISNull) && in_array('campus_id', $paramISNull)){
            $disc_code_q->whereNull('campus_id');
        }
        
        
        if(!empty($param['class_id'])){
            $whereData[] = ['class_id', $param['class_id']];
        }
        
        if(!empty($paramISNull) && in_array('class_id', $paramISNull)){
            $disc_code_q->whereNull('class_id');
        }
        
        if(!empty($param['admission_code'])){
            $whereData[] = ['admission_code', $param['admission_code']];
        }
//        
        if(!empty($paramISNull) && in_array('admission_code', $paramISNull)){
            $disc_code_q->whereNull('admission_code');
        }
        
        $disc_code_q->where($whereData);
        
//        if($disc_code_q->get())
//        {
            $disc_code =  $disc_code_q->get('disc_code');
//        }
        
        return $disc_code[0] ?? null;
    }
    
    public function getActiveBanks($param) {
        
        $disc_code = null;
//        
        $whereData[] = ['is_enable', 1];
        
        $bank_q   = DB::table('bank');
                
        if(!empty($param['organization_id'])){
            $whereData[] = ['organization_id', $param['organization_id']];
        }
        
        if(!empty($param['type'])){
            $whereData[] = ['type', $param['type']];
        }
        

        $bank_q->where($whereData);
    
        $bank_result =  $bank_q->get()->toArray();
        
        return $bank_result[0] ?? null;
    }
    
    
    //advance challan generate process
        
    
     public function getMonth($session_id, $type = null)
    {
        $whereData = array();
        
        if($session_id){
            $whereData[] = ['session_id', $session_id];
        }
        
        $whereData[] = ['session_month.is_enable', 1];
        
        $data_set = DB::table('session_month')
                    ->where($whereData)
                    ->join('session', 'session.id' , '=', 'session_month.session_id')
                    ->get();
        
        
        if( !empty($type) && $type == 'advance'){
            
            //only for advance
            if($session_id){
                $whereData2[] = ['session_id', $session_id];
            }
            
            $whereData2[] = ['session_month.month_no', '>=',  $data_set[0]->month_no];
            
            $data_set = DB::table('session_month')
                        ->where($whereData2)
                        ->join('session', 'session.id' , '=', 'session_month.session_id')
                        ->get();
        }
        
        if($data_set)
        {
            $data_set_arr = [];
            $data_set->map(function($item) use(&$data_set_arr) {
               $data_set_arr[$item->month_index] = $item->month_index;
            });
                    
            return $data_set_arr;
        }
        
        
    }
    
    
    public function getSlipSetup($slip_type_id, $month_index)
    {
        $data_result = [];
        $slip_setup  = [];
        $slip_setup_q = DB::table(Constant::Tables['slip_setup'])->where('is_enable','=', 1);
        
        if(!empty($slip_type_id)){
            $slip_setup_q->where('slip_type_id', $slip_type_id); 
        }
        
        if(!empty($month_index)){
            $slip_setup_q->where('month_index', $month_index); 
        }
        
        $slip_setup_q->get('*');
        
        if($slip_setup_q->count() > 0){
           $slip_setup = $slip_setup_q->first();
           $data_result['list'] = $slip_setup;
        }
        return $slip_setup; 
    }
    
    /**
     * advanceFeeAdd GenMonthlyVoucher.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function advanceFeeAdd(Request $request)
    {
        $user_id         =   $request->data_user_id;
        $campus_id       =   $request->campus_id;
        $class_id        =   $request->class_id;
        $organization_id =   $request->organization_id;
        $session_id      =   $request->session_id;
        $admission_code  =   $request->admission_code;
        $slip_type       =   $request->slip_type;

        
        
        $check_date      = date("Y-m-d");
        
        $requested_fee_months      = $request->month;
        
        $advance_fee_month_list = $this->getMonth($session_id, 'advance');

            
//        foreach ($advance_fee_month_list as $key => $value) {
//            
//            if($requested_fee_months[$key] $value){
//                
//            }
//            $sequence_arr
//                    
//        }
        
//         if(!empty($sequence_arr)){
//            $status = 200;
//            $response = Utilities::buildBaseResponse(10003, "Please Select Month Seqence wise (FIFO Base)", 'error');
//            return response()->json($response, $status); 
//            exit;
//        }
        
         
        $this->validate($request, $this->mdlName->advanceRules($request), $this->mdlName->advanceMessages($request));
        
       
        
        $this->mdlName->filterColumns($request);
        
         
        Utilities::defaultAddAttributes($request, $user_id);
        
        
        //only campus wise hiearchy follow 
        $get_students_query = StudentAdmission::with('Campus');
        
        $get_students_query->where(['is_enable' => 1, 'student_status' => 1]);
        
        if(!empty($admission_code)){
           $get_students_query->where('admission_code', $admission_code);
        }
        
        if(!empty($campus_id)){
           $get_students_query->where('campus_id', $campus_id);
        }

        if(!empty($organization_id)){
           $get_students_query->where('organization_id', $organization_id);
        }

        $get_students_list = $get_students_query->get()->toArray();
        
        if(empty($get_students_list)){
            $response = Utilities::buildBaseResponse(10003, "Student Not Found..!! ", 'Info');
            return response()->json($response, 200);
            exit;
        }
        
        
        $get_students_unpaid_challan_extis = GenMonthlyVoucher::where( [
                                                            'organization_id'   => $organization_id,
                                                            'payment_status'    => 0,
                                                            'admission_code'    => $admission_code,
                                                            'is_enable'         => 1,
                                                        ])->first();
        
//        if ($get_students_unpaid_challan_extis !== null) {
//            $response = Utilities::buildBaseResponse(10003, "Student Prevoius Challan Not Paid..!! ", 'Error');
//            return response()->json($response, 200);
//            exit;
//        }
                        
                  
        $fee_slip_master_data_arr     = [];
        $fee_structure_details_arr    = [];
        $fees_code_unique_arr         = [];
        $disc_code_unique_arr         = [];
        $disc_cod_arr                 = [];
        
        $warning = [];
        
        foreach ($get_students_list as $key => $value) {
                
            $organization_id    = $value['campus']['organization_id'];
            $country_id         = $value['campus']['countries_id'];
            $state_id           = $value['campus']['state_id'];
            $region_id          = $value['campus']['region_id'];
            $city_id            = $value['campus']['city_id'];
            $campus_id          = $value['campus_id'];
            $class_id           = $value['class_id'];
            $admission_code     = $value['admission_code'];
            $std_id             = $value['id'];
            
            $fee_code_admission_wise = $this->getAssignFeeCode(['organization_id' =>  $organization_id, 'country_id' => $country_id, 'admission_code' => $admission_code]);
            if(empty($fee_code_admission_wise)){
                
               $fee_code_class_wise = $this->getAssignFeeCode(['organization_id' =>  $organization_id, 'country_id' => $country_id, 'campus_id' => $campus_id, 'class_id' => $class_id], ['admission_code']);
               
               if(empty($fee_code_class_wise)){
                    $fee_code_campus_wise = $this->getAssignFeeCode(['organization_id' =>  $organization_id, 'country_id' => $country_id, 'campus_id' => $campus_id], ['admission_code', 'class_id']);
                    if(empty($fee_code_campus_wise)){
                        $fee_code_city_wise = $this->getAssignFeeCode(['organization_id' =>  $organization_id, 'country_id' => $country_id, 'city_id' => $city_id], ['admission_code', 'class_id', 'campus_id']);
                        if(empty($fee_code_city_wise)){
                            $fee_code_region_wise = $this->getAssignFeeCode(['organization_id' =>  $organization_id, 'country_id' => $country_id, 'region_id' => $region_id], ['admission_code', 'class_id', 'campus_id', 'city_id']);
                            if(empty($fee_code_region_wise)){
                                $fee_code_state_wise = $this->getAssignFeeCode(['organization_id' =>  $organization_id, 'country_id' => $country_id, 'state_id' => $state_id], ['admission_code', 'class_id', 'campus_id', 'city_id', 'region_id']);
                                if(empty($fee_code_state_wise)){
                                    $fee_code_country_wise = $this->getAssignFeeCode(['organization_id' =>  $organization_id, 'country_id' => $country_id], ['admission_code', 'class_id', 'campus_id', 'city_id', 'region_id', 'state_id']);
                                    if(empty($fee_code_country_wise)){
                                        $fee_code = null;
                                    }else{
                                        $fee_code =  $fee_code_country_wise;
                                    }
                                }else{
                                    $fee_code =  $fee_code_state_wise;
                                }
                            }else{
                               $fee_code =  $fee_code_region_wise;
                            }
                        }else{
                            $fee_code = $fee_code_city_wise;
                        }
                    }else{
                        $fee_code = $fee_code_campus_wise;
                    }
               }
               else{
                   $fee_code = $fee_code_class_wise;
               }
            }
            else{
                $fee_code = $fee_code_admission_wise;
            }
            
            if(empty($fee_code['id'])){
                
                $warning['fee_assign_errors'][] = $admission_code;
            }
            
            else{
                
                
                $disc_code_admission_wise = $this->getAssignDiscountCode(['organization_id' =>  $organization_id, 'country_id' => $country_id, 'admission_code' => $admission_code]);
                $disc_code_class_wise     = $this->getAssignDiscountCode(['organization_id' =>  $organization_id, 'country_id' => $country_id, 'campus_id' => $campus_id, 'class_id' => $class_id], ['admission_code']);
                $disc_code_campus_wise    = $this->getAssignDiscountCode(['organization_id' =>  $organization_id, 'country_id' => $country_id, 'campus_id' => $campus_id], ['admission_code', 'class_id']);
                $disc_code_city_wise      = $this->getAssignDiscountCode(['organization_id' =>  $organization_id, 'country_id' => $country_id, 'city_id'   => $city_id],   ['admission_code', 'class_id', 'campus_id']);
                $disc_code_region_wise    = $this->getAssignDiscountCode(['organization_id' =>  $organization_id, 'country_id' => $country_id, 'region_id' => $region_id], ['admission_code', 'class_id', 'campus_id', 'city_id']);
                $disc_code_state_wise     = $this->getAssignDiscountCode(['organization_id' =>  $organization_id, 'country_id' => $country_id, 'state_id' => $state_id], ['admission_code', 'class_id', 'campus_id', 'city_id', 'region_id']);
                $disc_code_country_wise   = $this->getAssignDiscountCode(['organization_id' =>  $organization_id, 'country_id' => $country_id], ['admission_code', 'class_id', 'campus_id', 'city_id', 'region_id', 'state_id']);
                
                     
                if(!empty($disc_code_admission_wise)){
                    $disc_cod_arr[$admission_code][]  = $disc_code_admission_wise;
                }
                if(!empty($disc_code_class_wise)){
                    $disc_cod_arr[$admission_code][]  = $disc_code_class_wise;
                }
                if(!empty($disc_code_campus_wise)){
                    $disc_cod_arr[$admission_code][]  = $disc_code_campus_wise;
                }
                if(!empty($disc_code_city_wise)){
                    $disc_cod_arr[$admission_code][]  = $disc_code_city_wise;
                }
                if(!empty($disc_code_region_wise)){
                    $disc_cod_arr[$admission_code][]  = $disc_code_region_wise;
                }
                if(!empty($disc_code_state_wise)){
                    $disc_cod_arr[$admission_code][]  = $disc_code_state_wise;
                }
                if(!empty($disc_code_country_wise)){
                    $disc_cod_arr[$admission_code][]  =  $disc_code_country_wise;
                }
                
                $fees_code_unique_arr[$fee_code['fees_code']]     = $fee_code['id'];
                
                $post_arr[$admission_code]['disc_cod_arr']        = $disc_cod_arr[$admission_code];
                $post_arr[$admission_code]['fees_code']           = $fee_code['fees_code'];
                
                $post_arr[$admission_code]['std_admission_id']    = $std_id;
                $post_arr[$admission_code]['fees_master_id']      = $fee_code['id'];
                
 
                $post_arr[$admission_code]['admission_code']      = $admission_code;
                $post_arr[$admission_code]['advance_refrence_by_challan_no']    = substr(min($requested_fee_months), 2, 4) . $admission_code;
                $post_arr[$admission_code]['gr_no']               = $value['gr_no'];
                $post_arr[$admission_code]['session_id']          = $value['session_id'];
                $post_arr[$admission_code]['organization_id']     = $value['organization_id'];
                $post_arr[$admission_code]['campus_id']           = $campus_id;
                $post_arr[$admission_code]['class_id']            = $class_id;
                $post_arr[$admission_code]['section_id']          = $value['section_id'];
                $post_arr[$admission_code]['kuickpay_id']         =  $this->getActiveBanks(['organization_id' =>  $organization_id, 'type' => 2])->ac_no . $admission_code;
                $post_arr[$admission_code]['bank_id']             =  $this->getActiveBanks(['organization_id' =>  $organization_id, 'type' => 1])->id; 
                
                
            }
       
        }
           
        
        try
        {
        DB::beginTransaction();
                    
                    
            $fs_detail_arr =  [];
            foreach ($requested_fee_months as $fee_month_key => $fee_month_value) {

                $getSlipSetup = $this->getSlipSetup($slip_type, $fee_month_value);

                if(empty($getSlipSetup)){
                    $response = Utilities::buildBaseResponse(10003, "Slip setup Not Found For Month..!! ".$fee_month_value, 'Info');
                    return response()->json($response, 200);
                    exit;
                }

                $where_fs_d    = array();
                $where_fs_d[] = ['is_enable',1];
                $where_fs_d[] = ['fees_is_new_addmission', 0];
                $where_fs_d[] = ['fees_from_date', '<=', $getSlipSetup->issue_date];
                $where_fs_d[] = ['fees_end_date',  '>=', $getSlipSetup->due_date];
                $fs_detail_query_obj = FeeStructureDetail::with('FeesType')->where($where_fs_d);
                $fs_detail_query_obj->whereIn('fees_master_id', $fees_code_unique_arr);


                $fs_detail_query_obj->get()->map(function($item) use(&$fs_detail_arr, $fee_month_key) {
                    $fs_detail_arr[$item->fees_code.'_mk_'.$fee_month_key][] = $item->toArray();
                });

                $disc_code_unique_arr = array_unique(
                        Arr::pluck(
                            Arr::flatten($disc_cod_arr),
                            'disc_code'
                        ), 
                        SORT_REGULAR
                );

                $fees_type_unique_arr = [];
                array_walk_recursive($fs_detail_arr, function ($value, $key) use (&$fees_type_unique_arr) {
                    if ($key === 'fees_type_id') {
                        $fees_type_unique_arr[$value] = $value;
                    };
                });

                $disc_detail_arr =  [];
                $where_disc_dtls    = array();

                $where_disc_dtls[] = ['is_enable',   1];
                $where_disc_dtls[] = ['no_of_month', 1];

                $where_disc_dtls[] = ['disc_is_new_addmission', 0];
                $where_disc_dtls[] = ['disc_from_date', '<=', $getSlipSetup->issue_date];
                $where_disc_dtls[] = ['disc_end_date',  '>=', $getSlipSetup->due_date];

                $disc_detail_query_obj = DiscountPolicy::with('FeesType', 'DiscType')->where($where_disc_dtls);
                $disc_detail_query_obj->whereIn('disc_code', $disc_code_unique_arr);
                $disc_detail_query_obj->whereIn('fees_type_id', $fees_type_unique_arr);


                $disc_detail_query_obj->get()->map(function($item) use(&$disc_detail_arr, $fee_month_key) {
                    $disc_detail_arr[$item->disc_code.'_mk_'.$fee_month_key][] = $item->toArray();
                });


                if(!empty($post_arr)){

                    $key_admission_code = $admission_code;
                    $voucher_master_data = $post_arr[$admission_code];
                    unset($voucher_master_data['disc_cod_arr'], $voucher_master_data['fees_code']);
                    $voucher_master_data = Utilities::defaultAddAttributesArr($voucher_master_data, $request->data_user_id);
                    $values              = $post_arr[$admission_code];

                    $month = substr($fee_month_value,4,2);
                    $year  = substr($fee_month_value,0,4);

                    $voucher_master_data['fee_actual_date']     = date('Y-m-d', strtotime($year.'-'.$month.'-01'));
                    $voucher_master_data['fee_month']           = $month;
                    $voucher_master_data['fee_month_code']      = $fee_month_value;
                    $voucher_master_data['fee_date']            = $getSlipSetup->issue_date;
                    $voucher_master_data['slip_issue_date']     = $getSlipSetup->issue_date;
                    $voucher_master_data['slip_validity_date']  = $getSlipSetup->validity_date;
                    $voucher_master_data['slip_due_date']       = $getSlipSetup->due_date;
                    $voucher_master_data['slip_type_id']        = $slip_type;
                    $voucher_master_data['challan_no']          = substr($fee_month_value, 2, 4) . $admission_code;


                    if( !empty($values['fees_code']) && !empty($fs_detail_arr[$values['fees_code'].'_mk_'.$fee_month_key])){

                        $fee_slip_master_find = GenMonthlyVoucher::where( [
                                                            'organization_id'   => $voucher_master_data['organization_id'],
                                                            'fee_month'         => $voucher_master_data['fee_month'],
                                                            'fee_month_code'    => $voucher_master_data['fee_month_code'],
    //                                                                    'slip_type_id'      => $voucher_master_data['slip_type_id'],
                                                            'challan_no'        => $voucher_master_data['challan_no'],
                                                            'admission_code'    => $voucher_master_data['admission_code'],
                                                            'is_enable'         => 1,
                                                        ])->first();


                        if ($fee_slip_master_find !== null) {
                            $warning['challan_already_generated'][] = $fee_month_value;
                        }
                        else{

                            $fee_slip_master_obj =  GenMonthlyVoucher::Create($voucher_master_data);
                            $fee_slip_master_id  = $fee_slip_master_obj->id;

                            $fees_amount           = 0;
                            $total_discount_amount = 0;
                            $total_payable_amount  = 0;

                            foreach ($fs_detail_arr[$values['fees_code'].'_mk_'.$fee_month_key] as $key_fd => $value_fd) {

                                $fee_details_data['fee_slip_id']    = $fee_slip_master_id;
                                $fee_details_data['fee_type_id']   = $value_fd['fees_type_id'];     
                                $fee_details_data['fee_amount']     = $value_fd['fees_amount'];   

                                $fees_amount += $value_fd['fees_amount']; 

                                $fee_details_data = Utilities::defaultAddAttributesArr($fee_details_data, $request->data_user_id);

                                GenMonthlyVoucherDetail::create($fee_details_data);
                            }

                            foreach ($values['disc_cod_arr'] as $key_discount_arr => $value_discount_arr) {

                                if(!empty($disc_detail_arr[$value_discount_arr->disc_code.'_mk_'.$fee_month_key])){

                                    foreach ($disc_detail_arr[$value_discount_arr->disc_code.'_mk_'.$fee_month_key] as $key_discount_detailed_arr => $value_discount_detailed_arr) {
                                            if(!empty($value_discount_detailed_arr['disc_percentage'])){
                                                $discount_amount = (($value_discount_detailed_arr['disc_percentage'] / 100 ) *  $fees_amount);  

                                                $disc_details_data['fee_slip_id']             = $fee_slip_master_id;
                                                $disc_details_data['disc_type_id']            = $value_discount_detailed_arr['discount_type'];     
                                                $disc_details_data['fee_type_id']             = $value_discount_detailed_arr['fees_type_id'];     
                                                $disc_details_data['discount_percentage']     = $value_discount_detailed_arr['disc_percentage'];     
                                                $disc_details_data['fee_amount']              = 0;     
                                                $disc_details_data['discount_amount']         = $discount_amount;     
                                                $disc_details_data['is_discount_entry']       = 1;     

                                                $total_discount_amount += $discount_amount;

                                                $disc_details_data = Utilities::defaultAddAttributesArr($disc_details_data, $request->data_user_id);
                                                GenMonthlyVoucherDetail::create($disc_details_data);
                                            }
                                    }
                                }else{

                                    $warning['discount_detailed_error'][] = $fee_month_value;
                                }
                            }


                            $total_payable_amount   = $fees_amount - $total_discount_amount;

                            $master_fee_update = [
                                'total_fees'                 => $fees_amount,
                                'total_discount'             => $total_discount_amount,
                                'total_payable_amount'       => $total_payable_amount,
                                'total_payable_amount_words' =>  Utilities::numberTowords($total_payable_amount)
                            ];

                            $fee_slip_master_obj->update($master_fee_update);

                            $warning['challan_success_generated'][] = $fee_month_value;
                        } 


                    }
                    else{
                        $warning['fee_detailed_error'][] = $fee_month_value;
                    }
                }
                else{
                    $response = Utilities::buildBaseResponse(10003, "Transaction Failed Monthly Voucher. Fee Not Assigned", 'error');
                } 

            }

        DB::commit();
        
            $data  = [
                'fs_detail_arr'        => $fs_detail_arr,
                'disc_cod_arr'         => $disc_cod_arr, 
                'disc_code_unique_arr' => $disc_code_unique_arr, 
                'disc_detail_arr'      => $disc_detail_arr,
                'fees_type_unique_arr' => $fees_type_unique_arr,
                'warning'              => $warning,
            ];

            $response = Utilities::buildSuccessResponse(10000, "Monthly Voucher Generated successfully created.", $data);
        }
        catch(\Exception $e)
        {
            DB::rollback();
            $response = Utilities::buildBaseResponse(10003, "Transaction Failed Monthly Voucher. ". $e, 'info');
        } 
       
        
        return response()->json($response, 200);
        exit;
       
    }
    
    /**
     * Fetch list of All Monthly Voucher by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllAdvanceVoucher(Request $request, $id = null)
    {
       
        $whereData = array();
        

        if(!empty($request->organization_id)) {   
            $whereData[] = ['organization_id', $request->organization_id];
        }
        
        if(!empty($request->data_org_id)) {   
            $whereData[] = ['organization_id', $request->data_org_id];
        }
        
        if(!empty($request->data_organization_id)) {   
            $whereData[] = ['organization_id', $request->data_organization_id];
        }
        
        if(!empty($request->campus_id)) {   
            $whereData[] = ['campus_id', $request->campus_id];
        }
        
        if(!empty($request->data_campus_id)) {   
            $whereData[] = ['campus_id', $request->data_campus_id];
        }
        
       
        
        if(!empty($request->admission_code)) {   
            $whereData[] = ['admission_code', $request->admission_code];
        }
        
        
        if(!empty($request->challan_no)) {   
            $where_admission_code = substr($request->challan_no,4,6);
            
            if(!empty($where_admission_code)){
                $whereData[] = ['admission_code', $where_admission_code];
            }
        }
        
        
        
        
        if(!empty($request->session_id)) {   
            $whereData[] = ['session_id', $request->session_id];
        }
        
        if(!empty($request->data_session_id)) {   
            $whereData[] = ['session_id', $request->data_session_id];
        }
                
//        if(!empty($request->slip_type)) {   
//            $whereData[] = ['slip_type_id', $request->slip_type];
//        }
        
        
        if ($request->month) {
            if(strlen($request->month) > 1){
               $month_arr = explode(",", $request->month);
            }else{
               $whereData[] = ['fee_month', substr($request->month,4,2)];
            }
        }
        
                
        
        if($request->is_enable != null) {
            $whereData[] = ['is_enable', $request->is_enable];
        }else{
            $whereData[] = ['is_enable', 1];
        }
        
        $whereData[] = ['payment_status', 0];
        
//        if(!empty($request->month)) { 
//            $month = substr($request->month,4,2);
//            $year  = substr($request->month,0,4);
//            
//            $fee_actual_date  = date('Y-m-d', strtotime($year.'-'.$month.'-01'));
//        }
        
        
                
        $queryObj = GenMonthlyVoucher::with('FeeSlipDetails','stdAdmission', 'Organization','Campus', 'Class', 'Bank')
                    ->where($whereData);
        
        if(!empty($month_arr)){
            
            $queryObj->where(function($query) use ($month_arr) {
                foreach ($month_arr as $key => $value) {
                    $query->orWhere('fee_month', '=', substr($value,4,2));
                }
            });
           
        }
       
        
//        if(!empty($fee_actual_date)) {   
//           $queryObj->whereDate('fee_actual_date', '<=', $fee_actual_date);
//        }
        
        $data_set = $queryObj->get();
        
        
        
            
        $getImg       = url('app/organization_file/');
        $getImgPublic = '';
        if (strpos($getImg, 'public') !== false) {
            $getImgPublic = str_replace('public', 'storage', $getImg);
        }else{
            $getImgPublic = url('storage/app/organization_file/');
        }
            
        $fees_voucher_array = [];
        $total_payable_amount_array = [];
        $total_arrears_amount_arr = [];
        $fees_arrears       = [];
        
        $keyCount = 0;
        $current_total_payable_amount = 0;
        $fee_arrears_count = 0;
        $total_arrears_amount = 0;
        $payable_key = 0;
        
        $data_set_arr = $data_set->toArray();
         
        usort($data_set_arr, function ($item1, $item2) {
            return $item1['fee_month_code'] <=> $item2['fee_month_code'];
        });
        
        if(!empty($data_set_arr)){
            
            foreach ( $data_set_arr as $key => $value) {

               if($key == 0){

                    $fees_voucher_array[$keyCount]['id']             = $value['id'];
                    $fees_voucher_array[$keyCount]['id_']            = $value['id'];
                    $fees_voucher_array[$keyCount]['is_challan_customize']            = $value['is_challan_customize'];
                    $fees_voucher_array[$keyCount]['admission_code'] = $value['admission_code'];
                    $fees_voucher_array[$keyCount]['bank_id']        = $value['bank_id'];
                    $fees_voucher_array[$keyCount]['bank_name']      = $value['bank']['name'];
                    $fees_voucher_array[$keyCount]['bank_ac_no']     = $value['bank']['ac_no'];
                    $fees_voucher_array[$keyCount]['bank_ac_no_arr'] = Utilities::string_to_arr($value['bank']['ac_no']);
                    $fees_voucher_array[$keyCount]['campus_name']    = $value['campus']['campus_name'];
                    $fees_voucher_array[$keyCount]['class_name']     = $value['class']['class_name'];
                    $fees_voucher_array[$keyCount]['campus_id']      = $value['campus_id'];
                    $fees_voucher_array[$keyCount]['class_id']       = $value['class_id'];
                    $fees_voucher_array[$keyCount]['challan_no']     = $value['challan_no'];
                    $fees_voucher_array[$keyCount]['fee_date']       = $value['slip_issue_date'];
                    $fees_voucher_array[$keyCount]['fee_month']      = $value['fee_month'];
                    $fees_voucher_array[$keyCount]['fee_month_code'] = $value['fee_month_code'];
                    $fees_voucher_array[$keyCount]['fee_month_name'] = Utilities::get_month_name_by_yearmonth_index($value['fee_month_code']);
                    $fees_voucher_array[$keyCount]['fees_master_id'] = $value['fees_master_id'];
                    $fees_voucher_array[$keyCount]['gr_no']            = $value['gr_no'];
                    $fees_voucher_array[$keyCount]['kuickpay_id']      = $value['kuickpay_id'];
                    $fees_voucher_array[$keyCount]['organization_id']  = $value['organization_id'];
                    $fees_voucher_array[$keyCount]['organization_img'] = $getImgPublic . '/'. $value['organization']['org_logo'].'?'.rand();
                    $fees_voucher_array[$keyCount]['payment_status']   = $value['payment_status'];
                    $fees_voucher_array[$keyCount]['section_id']       = $value['section_id'];
                    $fees_voucher_array[$keyCount]['session_id']       = $value['session_id'];
                    $fees_voucher_array[$keyCount]['slip_due_date']    = $value['slip_due_date'];
                    $fees_voucher_array[$keyCount]['slip_issue_date']  = $value['slip_issue_date'];
                    $fees_voucher_array[$keyCount]['slip_type_id']        = $value['slip_type_id'];
                    $fees_voucher_array[$keyCount]['slip_validity_date']  = $value['slip_validity_date'];
                    $fees_voucher_array[$keyCount]['student_name']        = $value['std_admission']['student_name'];
                    $fees_voucher_array[$keyCount]['father_name']         = $value['std_admission']['father_name'];
                    $fees_voucher_array[$keyCount]['student_full_name']   = $value['std_admission']['student_name'] .' '.$value['std_admission']['father_name'];
                    $fees_voucher_array[$keyCount]['amount']   = $value['total_payable_amount'];


                    $fees_voucher_array[$keyCount]['fee_slip_details'] = $value['fee_slip_details'];

                    $current_total_payable_amount = $value['total_payable_amount'];

                    $current_total_payable_amount_arr[$value['admission_code']] = $current_total_payable_amount;

                    $keyCount++;

                }
                else{
                    $fees_arrears[$fee_arrears_count]['id']             = $value['id'];
                    $fees_arrears[$fee_arrears_count]['id_']            = $value['id'];
                    $fees_arrears[$fee_arrears_count]['is_challan_customize']            = $value['is_challan_customize'];
                    $fees_arrears[$fee_arrears_count]['admission_code'] = $value['admission_code'];
                    $fees_arrears[$fee_arrears_count]['month']          = $value['fee_month'];
                    $fees_arrears[$fee_arrears_count]['arrears_amount'] = $value['total_payable_amount'];
                    $fees_arrears[$fee_arrears_count]['fee_month_code'] = $value['fee_month_code'];
                    $fees_arrears[$fee_arrears_count]['fee_month_name'] = Utilities::get_month_name_by_yearmonth_index($value['fee_month_code']);
                    $fee_arrears_count++;
                }

            }

            foreach ($fees_arrears as $key => $value) {
                if(isset($total_arrears_amount_arr[$value['admission_code']])) {
                    $total_arrears_amount_arr[$value['admission_code']] += $value['arrears_amount'];
                } else {
                    $total_arrears_amount_arr[$value['admission_code']] = $value['arrears_amount'];
                }
            }

            $total_payable_amount_key = 0;
            if(!empty($current_total_payable_amount_arr)){
                foreach ($current_total_payable_amount_arr as $key => $value) {

                    if(!empty($total_arrears_amount_arr[$key])){
                        $total_payable_amount_array[$total_payable_amount_key]['is_arrears'] = 1;
                        $total_payable_amount_array[$total_payable_amount_key]['admission_code'] = $key;
                        $total_payable_amount_array[$total_payable_amount_key]['grand_total_payable_amount'] = $value + $total_arrears_amount_arr[$key];
                        $total_payable_amount_array[$total_payable_amount_key]['grand_total_payable_amount_words'] = Utilities::numberTowords($value + $total_arrears_amount_arr[$key]);
                    }else{
                        $total_payable_amount_array[$total_payable_amount_key]['is_arrears'] = 0;
                        $total_payable_amount_array[$total_payable_amount_key]['admission_code'] = $key;
                        $total_payable_amount_array[$total_payable_amount_key]['grand_total_payable_amount'] = $value;
                        $total_payable_amount_array[$total_payable_amount_key]['grand_total_payable_amount_words'] = Utilities::numberTowords($value);
                    }
                    $total_payable_amount_key++;    

                }

                $data_result = [];
                $status = 200;
                $data_result['current_total_payable_amount_arr'] = $current_total_payable_amount_arr;
                $data_result['total_payable_amount_array']      =  $total_payable_amount_array;
                $data_result['data_list'] = $data_set_arr;
        //        $data_result['post'] = $request->all();
        //        $data_result['mnth'] = substr($request->month,4,2);
                $data_result['fees_voucher_array'] = $fees_voucher_array;
                
                usort($fees_arrears, function ($item1, $item2) {
                    return $item1['fee_month_code'] <=> $item2['fee_month_code'];
                });


                $data_result['fees_arrears'] = $fees_arrears;
                $data_result['total_arrears_amount_arr'] = $total_arrears_amount_arr;

                $response = Utilities::buildSuccessResponse(10004, "Get All Monthly Vouchers.", $data_result);

            }
            else{

                $status = 200;
                $response = Utilities::buildBaseResponse(10003, "Challan Not Exits!", 'error');
            }
        
        }
        else{
            $status = 200;
            $response = Utilities::buildBaseResponse(10003, "Challan Not Exits!!", 'error');
        }
        
        
        

        return response()->json($response, $status); 
    }
    
     
}
