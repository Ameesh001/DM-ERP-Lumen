<?php

/**
 * Darulmadinah Api
Author: Muhammad Usama (it.dev7) 
 * 
 * This is a GenAdmissionVoucher API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\GenAdmissionVoucher;
use App\Models\AdmissionVoucherPosting;
use App\Models\StudentRegistration;
use App\Models\StdInterviewTest;
use App\Models\AssignFeeStructure;
use App\Models\FeeStructureDetail;
use App\Models\AssigndiscountPolicy;
use App\Models\GenAdmissionVoucherDetail;
use App\Models\FeeType;
use App\Models\Sliptype;
use App\Models\Campus;
use App\Models\Bank;
use App\Models\DiscountPolicy;
use App\Models\StudentAdmission;
use App\Models\GenMonthlyVoucher;
use App\Models\GenMonthlyVoucherDetail;
use App\Models\Session;
use App\Models\RegReptNote;
use Illuminate\Http\Request;
use App\Utilities\Utilities;
use Illuminate\Support\Facades\DB;

class AdmissionVoucherPostingAPi extends Controller
{

    /**
     * Constructor
     */
    private $mdlName;
             
    public function __construct()
    {
        $this->mdlName = new AdmissionVoucherPosting();
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

    
    public function searchAdmVoucher(Request $request)
    {
        $status=200;
        $where_reg_code = array();
                            
        $where_reg_code[] = ['is_enable',1];
        $where_reg_code[] = ['organization_id', $request->org_id];
        $where_reg_code[] = ['campus_id', $request->data_campus_id];

        if($request->registration_code){

            $where_reg_code[] = ['registration_code', $request->registration_code];
            $std_reg_q = StudentRegistration::with('Organization','Country','State','Region' ,'City', 'Class')
            ->where($where_reg_code);

            if($std_reg_q->count() == 0 )
            {
                $response = Utilities::buildBaseResponse(80001, "Student Not Found..!! ", 'Info');
                return response()->json($response, 200);
                exit;
            }
            else
            {
                $std_reg = $std_reg_q->first();
                $whereData[] = ['std_registration_id', $std_reg->id];
                $whereData[] = ['campus_id', $std_reg->campus_id];
                $whereData[] = ['organization_id', $std_reg->organization_id];
                $whereData[] = ['is_enable',1];
                
                $reg_master_list_q = GenAdmissionVoucher::where($whereData);

                if($reg_master_list_q->count() == 0)
                {
                    $response = Utilities::buildBaseResponse(80001, "Registration Slip Not Found....!! ", 'Info');
                    return response()->json($response, 200);
                    exit;

                }
                else
                {
                    $reg_master_list =  $reg_master_list_q->first();

                    $reg_master_detail = GenAdmissionVoucherDetail::with('FeesType', 'DiscountType')
                                                                ->where('reg_slip_master_id',$reg_master_list->id)->get();
                    $data_result['std_reg'] = $std_reg->toArray();
                    $data_result['reg_master_list'] = $reg_master_list->toArray();
                    $data_result['reg_master_detail'] = $reg_master_detail->toArray();
                }
            }    
        }
        
        $response = Utilities::buildSuccessResponse(10004, "Single Generate Admission  Voucher.", $data_result);
        return response()->json($response, $status); 
    }


    /**
     * Add AdmissionVoucher.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        // $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        
        // $this->mdlName->filterColumns($request);
        // return response()->json($request, 200);
        // exit;
        Utilities::defaultAddAttributes($request, $request->data_user_id);

        $post_arr = $request->all();

        $whereData[] = ['id', $post_arr['reg_master_id']];
        $whereData[] = ['organization_id', $post_arr['data_org_id']];
        $whereData[] = ['is_enable',1];
        $whereData[] = ['slip_status',1];
        
        $reg_master_list_q = GenAdmissionVoucher::where($whereData);

        if($reg_master_list_q->count() == 0){

            $response = Utilities::buildBaseResponse(80001, "Registration Slip Not Found....!! ", 'Info');
            return response()->json($response, 200);
            exit;
        }
        else
        {
            $reg_master_list =  $reg_master_list_q->first(); 
           
            $update_array = array();
            $update_array['slip_status']= 2;
            $update_array['rec_date']= $post_arr['pay_date'];

            $std_reg = StudentRegistration::with('Organization','Country','State','Region' ,'City', 'Class','reg_slip_master')
                                                ->where('id',$reg_master_list->std_registration_id)->first();

               
            $std_session_q = Session::where('id',$std_reg->session_id)->where('is_enable',1);
            if($std_session_q->count() == 0)
            {
                $response = Utilities::buildBaseResponse(80001, "Session Not Found....!! ", 'Info');
                return response()->json($response, 200);
                exit;
            }
            $std_session =  $std_session_q->first();

            $create_std_adm = array();
            $create_std_adm['std_registration_id'] = $std_reg->id;
            $create_std_adm['registration_code'] = $std_reg->registration_code;
            $create_std_adm['gr_no'] = 0;
            $create_std_adm['session_id'] = $std_reg->session_id;
            $create_std_adm['admission_date'] =  date("Y-m-d");
            $create_std_adm['admission_month'] =  $reg_master_list->slip_month_name;
            $create_std_adm['joinning_date'] = null;
            $create_std_adm['batch'] =  $std_session->start_year;
            $create_std_adm['organization_id'] = $std_reg->organization_id;
            $create_std_adm['campus_id'] = $std_reg->campus_id;
            $create_std_adm['class_id'] = $std_reg->class_id;
            $create_std_adm['section_id'] = null;
            $create_std_adm['student_name'] = $std_reg->full_name;
            $create_std_adm['father_name'] = $std_reg->father_name;
            $create_std_adm['gender'] = $std_reg->gender;
            $create_std_adm['dob'] = $std_reg->dob;
            $create_std_adm['father_nic'] = $std_reg->father_nic;
            $create_std_adm['mother_nic'] = $std_reg->mother_nic;
            $create_std_adm['home_cell_no'] = $std_reg->home_cell_no;
            $create_std_adm['father_cell_no'] = $std_reg->father_cell_no;
            $create_std_adm['mother_cell_no'] = $std_reg->mother_cell_no;
            $create_std_adm['home_address'] = $std_reg->address;
            $create_std_adm['place_of_birth'] = null;
            $create_std_adm['blood_group'] = null;
            $create_std_adm['religion'] = $std_reg->religion;
            $create_std_adm['nationality'] = $std_reg->country->country_name;
            $create_std_adm['caste'] = null;
            $create_std_adm['community'] = null;
            $create_std_adm['is_physically_fit'] = null;
            $create_std_adm['school_last_attended'] = null;
            $create_std_adm['grade'] = null;
            $create_std_adm['native_language'] = null;
            $create_std_adm['other_language'] = null;
            $create_std_adm['student_img'] = $std_reg->student_img;
            $create_std_adm['created_by'] = $request->data_user_id;

            
            $dup_entry_q = StudentAdmission::where('std_registration_id',$std_reg->id)
                                                ->where('registration_code',$std_reg->registration_code)
                                                ->where('is_enable',1);
            if($dup_entry_q->count() > 0)
            {
                $response = Utilities::buildBaseResponse(80001, "Student Already Posted...!! ", 'Info');
                return response()->json($response, 200);
                exit;
            }

            $fee_slip_master = array();
            $fee_slip_master_detail = array();

            $reg_m_l = GenAdmissionVoucher::where('id',$request->reg_master_id)->first()->toArray();
            $reg_m_l_d = GenAdmissionVoucherDetail::where('reg_slip_master_id', $request->reg_master_id)->get()->toArray();

            // return response()->json($reg_m_l, 200);
            // exit;

            $fee_slip_master = $reg_m_l;
            $fee_slip_master['fee_month_code']=$fee_slip_master['slip_month_code'];
            $fee_slip_master['fee_month']=$fee_slip_master['slip_month'];
            $fee_slip_master['fee_date']=$fee_slip_master['slip_issue_date'];
            $fee_slip_master['slip_validity_date']=$fee_slip_master['slip_valid_date'];
            $fee_slip_master['total_payable_amount']=$fee_slip_master['payable_amount'];
            $fee_slip_master['total_payable_amount_words']= Utilities::numberTowords($fee_slip_master['payable_amount']);
            $month = substr($fee_slip_master['slip_month_code'],4,2);
            $year  = substr($fee_slip_master['slip_month_code'],0,4);

            $fee_slip_master['fee_actual_date']  = date('Y-m-d', strtotime($year.'-'.$month.'-01'));

            // return response()->json($fee_slip_master['fee_actual_date'], 200);
            // exit;

            unset($fee_slip_master['created_at']);
            unset($fee_slip_master['created_by']);
            unset($fee_slip_master['deleted_at']);
            unset($fee_slip_master['updated_at']);
            unset($fee_slip_master['updated_by']);
            unset($fee_slip_master['slip_month']);
            unset($fee_slip_master['slip_month_code']);
            unset($fee_slip_master['slip_month_name']);
            unset($fee_slip_master['slip_payable_amount']);
            unset($fee_slip_master['slip_remarks']);
            unset($fee_slip_master['std_registration_id']);
            // unset($fee_slip_master['total_discount']);
            // unset($fee_slip_master['total_fees']);
            unset($fee_slip_master['payable_amount']);
            unset($fee_slip_master['slip_fine']);
            unset($fee_slip_master['slip_fee_month']);
            unset($fee_slip_master['slip_status']);
            unset($fee_slip_master['id']);
            unset($fee_slip_master['slip_valid_date']);
            unset($fee_slip_master['slip_date']);
            
            $fee_slip_master['payment_status']= 1;
            $fee_slip_master['rec_date']= $post_arr['pay_date'];
            $fee_slip_master['created_by'] = $request->data_user_id;
            $fee_slip_master['payment_by_channel'] = $post_arr['bank'];
            $fee_slip_master['transaction_no'] = $post_arr['transaction_no'];
           
            
            // $bank_list = Bank::find($request->bank);
           
            // if($bank_list->type == 1 )
            // {
            //     $fee_slip_master['bank_id'] = $bank_list->id;
            // }
            // else if($bank_list->type == 2 )
            // {
            //     $fee_slip_master['kuickpay_id'] = $bank_list->id;
            // }
               
            try
            {
                DB::beginTransaction();
                $reg_master_list->update($update_array);
                
                $std_ad = StudentAdmission::create($create_std_adm);
                $std_ad_find = StudentAdmission::find($std_ad->id);

               
                $fee_slip_master['std_admission_id']=$std_ad_find->id;
                $fee_slip_master['admission_code']=$std_ad_find->admission_code;
                $fee_slip_master['gr_no']=$std_ad_find->gr_no;
                $fee_slip_master['session_id']=$std_ad_find->session_id;
                $fee_slip_master['class_id']=$std_ad_find->class_id;
                $fee_slip_master['section_id']=$std_ad_find->section_id;
                
                $slip_type = Sliptype::where('organization_id',$fee_slip_master['organization_id'])
                ->where('prefix', 'mon')->first();  
                $fee_slip_master['slip_type_id']=$slip_type->id;
                
                // return response()->json($fee_slip_master, 200);
                // exit;
                $fee_slip_master_added = GenMonthlyVoucher::create($fee_slip_master);

                
                foreach($reg_m_l_d as $fs_detail) 
                { 
                    $fee_slip_master_detail = array();
                    $fee_slip_master_detail = $fs_detail;
                    $fee_slip_master_detail['created_by'] = $request->data_user_id;
                    $fee_slip_master_detail['fee_amount'] =$fee_slip_master_detail['fee_charges'];
                    $fee_slip_master_detail['fee_type_id'] =$fee_slip_master_detail['fees_type_id'];
                    
                    if($fee_slip_master_detail['discount_amount']>0)
                    {
                        $fee_slip_master_detail['is_discount_entry'] = 1;
                        $fee_slip_master_detail['discount_percentage'] = $fs_detail['discount_percentage'];
                    }

                    unset( $fee_slip_master_detail['created_at']);
                    unset( $fee_slip_master_detail['deleted_at']);
                    unset( $fee_slip_master_detail['id']);
                    unset( $fee_slip_master_detail['updated_at']);
                    unset( $fee_slip_master_detail['updated_by']);
                    unset( $fee_slip_master_detail['month']);
                    unset( $fee_slip_master_detail['reg_slip_master_id']);
                    unset( $fee_slip_master_detail['fee_charges']);
                    unset( $fee_slip_master_detail['fees_type_id']);
                    
                    $fee_slip_master_detail['fee_slip_id'] = $fee_slip_master_added->id;

                    $charges_detail = GenMonthlyVoucherDetail::create($fee_slip_master_detail);
                }

                // return response()->json($fee_slip_master, 200);
                // exit;

                //Find admission code here


                DB::commit();
                $response = Utilities::buildSuccessResponse(10000, "Admission Voucher Posted successfully created.", 'Info');
            }
            catch(\Exception $e)
            {
                DB::rollback();
                $response = Utilities::buildBaseResponse(10003, $e." Transaction Failed Admission Voucher. ", 'info');
            }  
        }
       
        $status=200;
        return response()->json($response, $status);
    }

    /**
     * Update AdmissionVoucher.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        
    }
    
    /**
     * Activate/De-Activate AdmissionVoucher.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function patchDisableEnable(Request $request)
    {
 
    }

    /**
     * Delete AdmissionVoucher.
     *
     * @param $id 'ID' of GenAdmissionVoucher to delete. (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id, Request $request)
    {
       
    }

    /**
     * Get one AdmissionVoucher.
     *
     * @param $id 'ID' of GenAdmissionVoucher to return (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOne($id, Request $request)
    {
        $request->request->add([ 'id' => $id ]);
        
        if($request->id) {   
            $whereData[] = ['std_registration_interview_test.id', $request->id];
        }

        $data_set = StdInterviewTest::with('Class')
        ->where($whereData)
        ->join('student_registration', 'student_registration.id' , '=', 'std_registration_interview_test.std_registration_id')
        // ->whereIn('std_registration_interview_test.final_result_id',['1','3'])
        ->get();
        $data_result = [];
        $status = 200;
        $data_result['data_list'] = $data_set->toArray();
        // $data_result['total_record'] = $total_record;
        // return response()->json($data_set, $status); 
        // exit;
        $response = Utilities::buildSuccessResponse(10004, "Single Generate Admission  Voucher List.", $data_result);
        
        return response()->json($response, $status); 
              

    }

    /**
     * Fetch list of GenAdmissionVoucher by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll(Request $request, $id = null)
    {
         $pageSize = $request->limit ?? Constant::PageSize;
        
        if($pageSize > Constant::MaxPageSize){
            $pageSize = Constant::MaxPageSize;
        }
        
        $page = $request->page ?? Constant::Page;
        
        $skip = ($page - 1) * $pageSize;
        
        $select =  $this->select_columns;
        
        if($request->fields){
            $select = $request->fields;
        }
        $whereData = array();
        $whereDataStd = array();
       
        if($request->data_campus_id) {   
            $whereData[] = ['campus_id', $request->data_campus_id];
        }
       
        if($request->challan_no) {   
            $whereData[] = ['challan_no', $request->challan_no];
        }
        if($request->registration_date) {   
            $whereDataStd[] = ['registration_date', $request->registration_date];
        }

        if($request->registration_code) {   
            $whereDataStd[] = ['registration_code', $request->registration_code];
        }
        
        if($request->full_name) {   
            $whereDataStd[] = ['student_registration.full_name', 'LIKE', "%{$request->full_name}%"];
        }

        if($request->father_name) {   
            $whereDataStd[] = ['student_registration.father_name', 'LIKE', "%{$request->father_name}%"];
        }

        if($request->dob) {   
            $whereDataStd[] = ['student_registration.dob', $request->dob];
        }

        if($request->father_cell_no) {   
            $whereDataStd[] = ['student_registration.father_cell_no', $request->father_cell_no];
        }
        
        
        // if($request->class_id) {   
        //     $whereData[] = ['student_registration.class_id', $request->class_id];
        // }

       
        
        $total_record_obj_q = GenAdmissionVoucher::with('Student')->where($whereData);

        $total_record_obj_q->whereHas('Student', function($q) use ($whereDataStd){
            $q->where($whereDataStd);
           
        });


        $total_record =  $total_record_obj_q->count();
        
        $orderBy =  $this->mdlName->getOrderColumn($request->order_by);
        
        $orderType = $request->order_type ?? Constant::OrderType;
        
       
        $data_set_q = GenAdmissionVoucher::with('Student')
                        ->where($whereData)
                        ->orderBy('id', 'desc')
                        ->offset($skip)
                        ->limit($pageSize);

        $data_set_q->whereHas('Student', function($q) use ($whereDataStd){
                    $q->where($whereDataStd);
        });    
                                        
        $data_set = $data_set_q->get();
        $data_result = [];
        $status = 200;
        $data_result['data_list'] = $data_set->toArray();
        $data_result['total_record'] = $total_record;
        
        $response = Utilities::buildSuccessResponse(10004, "Generate Admission  Voucher List.", $data_result);

        return response()->json($response, $status); 
    }
    /**
     * Fetch list of AdmissionVoucher Card View by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cardView(Request $request, $id = null)
    {
        $whereData = array();
        $whereDataStd = array();
       
        if($request->student_id) {   
            $whereData[] = ['std_registration_id', $request->student_id];
        }
       
        
        $student_adm_q = StudentAdmission::with('Class', 'Organization', 'Campus')->where($whereData);

        // $student_adm_q->whereHas('Student', function($q) use ($whereDataStd){
        //     $q->where($whereDataStd);
           
        // });

        if($student_adm_q->count() == 0){
            $response = Utilities::buildBaseResponse(80001, "Student Admission Not Found...!! ", 'Info');
            return response()->json($response, 200);
            exit;
        }
        $data_set = $student_adm_q->first();
               
        $whereData_note = array();
        $whereData_note[] = ['is_enable', 1];
        $whereData_note[] = ['note_type', 3];
        $whereData_note[] = ['type', 2];
       
        if($request->organization_id) {   
            $whereData_note[] = ['organization_id', $request->organization_id];
        }  
        
        $data_set_note = RegReptNote::where($whereData_note)->orderBy('sort_no', 'asc')->first();
        $data_result = [];
        $status = 200;
        $data_result['data_list'] = $data_set->toArray();
        $data_result['reg_note'] = $data_set_note->toArray();
        $response = Utilities::buildSuccessResponse(10004, "Student Card View List.", $data_result);
        return response()->json($response, $status); 
    }
    

    



    
    
   
    
    
   
}
