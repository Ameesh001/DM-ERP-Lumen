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
use App\Models\StudentRegistration;
use App\Models\StdInterviewTest;
use App\Models\AssignFeeStructure;
use App\Models\FeeStructureDetail;
use App\Models\AssigndiscountPolicy;
use App\Models\GenAdmissionVoucherDetail;
use App\Models\FeeType;
use App\Models\Campus;
use App\Models\Bank;
use App\Models\DiscountPolicy;
use Illuminate\Http\Request;
use App\Utilities\Utilities;
use Illuminate\Support\Facades\DB;

class GenAdmissionVoucherAPi extends Controller
{

    /**
     * Constructor
     */
    private $mdlName;
             
    public function __construct()
    {
        $this->mdlName = new GenAdmissionVoucher();
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
     * Add GenAdmissionVoucher.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        $user_id =   Utilities::getUserID_by_keycloak_id($request->keycloak_id);
        
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        
        $this->mdlName->filterColumns($request);
        
        Utilities::defaultAddAttributes($request, $user_id);

        $std_reg = StudentRegistration::with('Organization','Country','State','Region' ,'City')->find($request->std_registration_id);
        if(empty($std_reg)){
            $response = Utilities::buildBaseResponse(10003, "Student Not Found..!! ", 'Info');
            return response()->json($response, 200);
            exit;
        }
        $std_hirarcy = Campus::find($std_reg->campus_id);            
       
        $number = str_replace(['+', '-'], '', filter_var($std_reg->registration_code, FILTER_SANITIZE_NUMBER_INT));
        $chalan_prefix;
        $post_arr = $request->all();
        $result = substr($post_arr['month'], 2, 4);
        $post_arr['challan_no'] = $result.$number;
        $post_arr['slip_month_code'] = $post_arr['month'];
        $post_arr['slip_month'] = substr($post_arr['month'],4,2);
        $post_arr['slip_date'] = date("Y-m-d");
        $post_arr['slip_fee_month'] = substr($post_arr['month'],4,2);
        $post_arr['slip_fine'] = 0;
        $post_arr['campus_id'] = $std_reg->campus_id;
        $post_arr['std_registration_id'] = $std_reg->id;
       
        $post_arr['slip_month_name'] = Utilities::get_month_name_by_yearmonth_index($post_arr['month']);

        
        $whereDup = array();
        $whereDup[] = ['std_registration_id',$std_reg->id];
        $duplicate=0;
        $duplicate = GenAdmissionVoucher::where($whereDup)->active()->count();

        
        if($duplicate>0){
            $response = Utilities::buildBaseResponse(10003, "Voucher Already Genderated...!! ", $duplicate);
            return response()->json($response, 200);
            exit;
        }
        
        // $check_date = substr($post_arr['month'], 0, 4) . '-' . substr($post_arr['month'], 4, 2) . '-01';
         $check_date = date("Y-m-d");
        
        //  $fc_code = '';
        //==============Fee Structure ===========================
                            $whereData = array();
                            
                            $whereData[] = ['is_enable',1];
                            $whereData[] = ['organization_id', $std_reg->organization_id];


                            $fc_code_q   = AssignFeeStructure::where($whereData)
                                                    ->where('campus_id',$std_reg->campus_id)           
                                                    ->where('class_id',$std_reg->class_id)
                                                    ->whereNull('admission_code');
                                   
                            if($fc_code_q->count() > 0)
                            {
                                $fc_code =  $fc_code_q->get();  
                               
                            }
                            else
                            {
                                $fc_code_2_q   = AssignFeeStructure::where($whereData)
                                                ->where('campus_id',$std_reg->campus_id)
                                                ->whereNull('class_id')
                                                ->whereNull('admission_code');
                                                
                                                
                                if($fc_code_2_q->count() > 0)
                                {
                                    $fc_code =  $fc_code_2_q->get();  
                                }

                            }

                            if(empty($fc_code['0']->fees_code))
                            {
                                $response = Utilities::buildBaseResponse(10003, "Fee Structure Not Defined..!! ");
                                return response()->json($response, 200);
                                exit;
                            }

                           
                            $fee_structure_master = DB::table('fee_structure_master')
                            ->where('fees_code',$fc_code['0']->fees_code)->first();
                           
                            $whereData_fc_d = array();
                            $whereData_fc_d[] = ['is_enable',1];
                            $whereData_fc_d[] = ['fees_code',$fc_code['0']->fees_code];
                            // $whereData_fc_d[] = ['fees_is_new_addmission',1];
                            $whereData_fc_d[] = ['fees_from_date','<=', $check_date];
                            $whereData_fc_d[] = ['fees_end_date','>=', $check_date];

                            $fc_detail = FeeStructureDetail::with('FeesType')->where($whereData_fc_d);
                            
                            
                            if($fc_detail->count()==0){
                                $response = Utilities::buildBaseResponse(10003, "Fee Structure Detail Not Found..!! ");
                                return response()->json($response, 200);
                                exit;
                            }

                            $fc_detail =  $fc_detail->get();

                           
        //====================END Fee Structure ==========================

        //==========================Discount Policy ====================
        
        $disc_where = array();
                            
        $disc_where[] = ['discount_policy.is_enable',1];
        $disc_where[] = ['assign_discount_policy.is_enable',1];
        $disc_where[] = ['assign_discount_policy.organization_id', $std_reg->organization_id];
        $disc_where[] = ['discount_policy.disc_from_date','<=', $check_date];
        $disc_where[] = ['discount_policy.disc_end_date','>=', $check_date];
        $disc_where[] = ['discount_policy.disc_is_new_addmission','=',1];

        // $disc_code   = AssigndiscountPolicy::where($disc_where)
        $is_discount_policy=0;
        $disc_code_q   = DB::table('assign_discount_policy')
                        ->Join('discount_policy', 'discount_policy.disc_code', '=', 'assign_discount_policy.disc_code')
                        ->where($disc_where);

        $is_discount_policy = $disc_code_q->count();                  
                        
        $disc_code = $disc_code_q->get($this->select_column_dis);

       
        
        // return response()->json($disc_code, 200);
        // exit;


       

        //=======================END Discount Policy ==========================
        $total_fess=0;
        $total_discount=0;
        $payable_amount=0;
        try
        {
            DB::beginTransaction();
                $post_arr['fees_master_id'] = $fee_structure_master->id;
                $post_arr['slip_payable_amount'] = 0;
                $post_arr['total_fees'] = 0;
                $post_arr['total_discount'] = 0;
                $post_arr['slip_issue_date'] = date("Y-m-d");
                $post_arr['slip_remarks'] = '';

                $whereBank[] = ['is_enable',1];
                $whereBank[] = ['type',1];
                $whereBank[] = ['organization_id', $std_reg->organization_id];
                $bank_id = Bank::where($whereBank)->first();
                $post_arr['bank_id'] = $bank_id->ac_no;


                $whereKp[] = ['is_enable',1];
                $whereKp[] = ['type',2];
                $whereKp[] = ['organization_id', $std_reg->organization_id];
                $kuickpay_id = Bank::where($whereKp)->first();
                
                $kp_no = (int) filter_var($std_reg->registration_code, FILTER_SANITIZE_NUMBER_INT);
                $post_arr['kuickpay_id'] = $kuickpay_id->ac_no . $kp_no;
               
                $obj = GenAdmissionVoucher::create($post_arr);
                $data = ['id'=>$obj->id];
                $fee_structure_fees_code;
                foreach($fc_detail as $fc_detail) 
                { 
                    $total_fess += $fc_detail->fees_amount;
                    $fee_array['reg_slip_master_id']= $obj->id;
                    $fee_array['slip_type_id']= 1;
                    $fee_array['fees_type_id']= $fc_detail->fees_type_id;     
                    $fee_array['disc_type_id']= null;     
                    $fee_array['month']= $post_arr['slip_fee_month'];  
                    $fee_array['fee_charges']= $fc_detail->fees_amount;     
                    $fee_array['discount_amount']= 0;     
                    $fee_array['created_by']= $user_id;     
                    $fee_structure_fees_code=$fc_detail->fees_code;
                    $charges_detail = GenAdmissionVoucherDetail::create($fee_array);
                }
                
                if($is_discount_policy>0)
                {
                    foreach($disc_code as $disc_details) 
                    { 
                        $is_discount=0;
                        if($disc_details->class_id == $std_reg->class_id && $disc_details->campus_id == $std_reg->campus_id)
                        {
                            $is_discount=1;
                        }
                        else if(empty($disc_details->class_id) && $disc_details->campus_id == $std_reg->campus_id)
                        {
                            $is_discount=1;
                        }
                        else if(empty( $disc_details->campus_id) &&  $disc_details->city_id == $std_hirarcy->city_id)
                        {
                        
                            $is_discount=1;
                        }
                        else if(empty($disc_details->city_id) && $disc_details->region_id == $std_hirarcy->region_id)
                        {
                            $is_discount=1;
                        }
                        else if(empty($disc_details->region_id) &&  $disc_details->state_id == $std_hirarcy->state_id)
                        {
                            $is_discount=1;
                        }
                        else if(empty($disc_details->state_id) &&  $disc_details->country_id == $std_hirarcy->country_id && empty($disc_details->state_id)  )
                        {
                            $is_discount=1;
                        }
                        
                        if($is_discount>0)
                        {
                            $disc_array['reg_slip_master_id']= $obj->id;
                            $disc_array['slip_type_id']= 1;
                            $disc_array['fees_type_id']= $disc_details->fees_type_id; 
                            $disc_array['month']= $post_arr['slip_fee_month']; 
                            $disc_array['fee_charges']=0; 
                            $disc_array['created_by']=$user_id; 
                            $disc_array['disc_type_id']=$disc_details->discount_type; 
                            $disc_array['discount_percentage']=$disc_details->disc_percentage; 
                           

                            $whereData_fee_st = array();
                            $whereData_fee_st[] = ['fees_from_date','<=', $check_date];
                            $whereData_fee_st[] = ['fees_end_date','>=', $check_date];
                            $whereData_fee_st[] = ['fees_type_id', $disc_details->fees_type_id];
                            $whereData_fee_st[] = ['fees_code', $fee_structure_fees_code];
                            $whereData_fee_st[] = ['fees_is_new_addmission',0];
                            
                            $fee_s   = FeeStructureDetail::where($whereData_fee_st)->get();
                           
                            $disc_array['discount_amount']= (( ($fee_s['0']->fees_amount * $disc_details->no_of_month) * $disc_details->disc_percentage)/100 );     
                            $total_discount += (( ($fee_s['0']->fees_amount * $disc_details->no_of_month) * $disc_details->disc_percentage)/100 );
                            $discount_detail = GenAdmissionVoucherDetail::create($disc_array);
                           
                        }
                    }
                  
                }
                // else
                // {   
                //     $response = Utilities::buildBaseResponse(10003, "Discount policy not found", 'info');
                //     return response()->json($response, 200);
                //     exit;
                // }
                
               
            $payable_amount = $total_fess - $total_discount;
            
            $data['total_fees']= $total_fess;
            $data['total_discount']= $total_discount;
            $data['payable_amount']= $payable_amount;
            
            if($payable_amount<0){
                DB::rollback();
                $response = Utilities::buildBaseResponse(10003, "Failed to Generate Voucher. Due to Payable Amount less than Zero.". $payable_amount, 'info');
                return response()->json($response, 200);
                exit;
            }
            $obj = $obj->update($data);

            DB::commit();
            $response = Utilities::buildSuccessResponse(10000, "Admission Voucher Generated successfully created.", $data);
        }
        catch(\Exception $e)
        {
            DB::rollback();
            $response = Utilities::buildBaseResponse(10003, $e." Transaction Failed Admission Voucher. ", 'info');
        }
       
        return response()->json($response, 200);

    }

    /**
     * Update GenAdmissionVoucher.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        
    }
    
    /**
     * Activate/De-Activate GenAdmissionVoucher.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function patchDisableEnable(Request $request)
    {
 
    }

    /**
     * Delete GenAdmissionVoucher.
     *
     * @param $id 'ID' of GenAdmissionVoucher to delete. (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id, Request $request)
    {
       
    }

    /**
     * Get one GenAdmissionVoucher.
     *
     * @param $id 'ID' of GenAdmissionVoucher to return (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOne($id, Request $request)
    {
        $request->request->add([ 'id' => $id ]);
        
        if($request->id) {   
            $whereData[] = ['std_registration_interview_test.std_registration_id', $request->id];
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
        // $this->validate($request, $this->mdlName->rules($request, Constant::RequestType['GET_ALL']), $this->mdlName->messages($request, Constant::RequestType['GET_ALL']));
       
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
       
        if($request->data_campus_id) {   
            $whereData[] = ['campus_id', $request->data_campus_id];
        }
       
        if($request->registration_date) {   
            $whereData[] = ['student_registration.registration_date', $request->registration_date];
        }

        if($request->registration_code) {   
            $whereData[] = ['student_registration.registration_code', $request->registration_code];
        }
        
        if($request->full_name) {   
            $whereData[] = ['student_registration.full_name', 'LIKE', "%{$request->full_name}%"];
        }

        if($request->father_name) {   
            $whereData[] = ['student_registration.father_name', 'LIKE', "%{$request->father_name}%"];
        }

        if($request->dob) {   
            $whereData[] = ['student_registration.dob', $request->dob];
        }

        if($request->father_cell_no) {   
            $whereData[] = ['student_registration.father_cell_no', $request->father_cell_no];
        }
        
        
        if($request->class_id) {   
            $whereData[] = ['student_registration.class_id', $request->class_id];
        }

        $whereData[] = ['std_registration_interview_test.is_seat_alloted',0];
        if($request->data_campus_id) {   
            $whereData[] = ['student_registration.campus_id', $request->data_campus_id];
        }
       
        // $total_record_obj = StdInterviewTest::where($whereData)
        //                             ->join('student_registration', 'student_registration.id' , '=', 'std_registration_interview_test.std_registration_id')
        //                             ->whereIn('std_registration_interview_test.final_result_id',['1','3'])
        //                             ->offset($skip)
        //                             ->limit($pageSize);
        $total_record_obj = StdInterviewTest::with('Class')
                                ->where($whereData)
                                ->join('student_registration', 'student_registration.id' , '=', 'std_registration_interview_test.std_registration_id')
                                ->whereIn('std_registration_interview_test.final_result_id',['1','3'])
                                ->orderBy('student_registration.id', 'desc');

        // $total_record_obj = GenAdmissionVoucher::where($whereData)->active();
        
        $total_record =  $total_record_obj->count();
       
        $orderBy =  $this->mdlName->getOrderColumn($request->order_by);
        
        $orderType = $request->order_type ?? Constant::OrderType;
        
        // $data_set_obj = DB::table('std_registration_interview_test')
        //     ->where($whereData)
           
        //     // ->orderBy($orderBy, $orderType)
        //     // ->offset($skip)
        //     // ->limit($pageSize)
        //     ->get()->toArray();
      
        // $data_set =  $data_set_obj;
        $data_set = StdInterviewTest::with('Class')
                                ->where($whereData)
                                ->join('student_registration', 'student_registration.id' , '=', 'std_registration_interview_test.std_registration_id')
                                ->whereIn('std_registration_interview_test.final_result_id',['1','3'])
                                ->orderBy('student_registration.id', 'desc')
                                ->offset($skip)
                                ->limit($pageSize)
                                ->get();
        $data_result = [];
        $status = 200;
        $data_result['data_list'] = $data_set->toArray();
        $data_result['total_record'] = $total_record;
       
        $response = Utilities::buildSuccessResponse(10004, "Generate Admission  Voucher List.", $data_result);
        return response()->json($response, $status); 
        exit;
        // return response()->json($response, $status); 
        // exit;
    }
    /**
     * Fetch list of Regisration Recetip Note by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    // public function regNote(Request $request, $id = null)
    // {   
    //     $whereData = array();
    //     $whereData[] = ['is_enable', 1];
    //     $whereData[] = ['note_type', 1];
    //     if($request->type)
    //     {
    //         $whereData[] = ['type', 2];
    //     }else{
    //         $whereData[] = ['type', 1];
    //     }   
    //     if($request->organization_id) {   
    //         $whereData[] = ['organization_id', $request->organization_id];
    //     }  
    //     $data_set = DB::table('reg_rept_note')->where($whereData)->orderBy('sort_no', 'asc')->get('*');
    //     $status=200;
    //     return response()->json($data_set, $status); 
    // }

    /**
     * Fetch list of Month List by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMonth(Request $request, $id = null)
    {
        $whereData = array();
        
        if($request->data_session_id){
            $whereData[] = ['session_id', $request->data_session_id];
        }
        
        $whereData[] = ['session_month.is_enable', 1];
        
        $data_set = DB::table('session_month')
                    ->where($whereData)
                    ->join('session', 'session.id' , '=', 'session_month.session_id')
                    ->get();
        
        
        if( !empty($request->type) && $request->type == 'advance'){
            
            //only for advance
            if($request->data_session_id){
                $whereData2[] = ['session_id', $request->data_session_id];
            }
            
            $whereData2[] = ['session_month.month_no', '>=',  $data_set[0]->month_no];
            
            $data_set = DB::table('session_month')
                        ->where($whereData2)
                        ->join('session', 'session.id' , '=', 'session_month.session_id')
                        ->get();
        }
        
        if($data_set)
        {
            return response()->json($data_set, 200);
        }
        
        
    }


    public function getBankByOrgID(Request $request, $id = null)
    {
        $whereData = array();
        
        $whereData[] = ['is_enable', 1];
        
        if($request->data_organization_id){
            $whereData[] = ['organization_id', $request->data_organization_id];
        }
        
        if($request->org_id){
            $whereData[] = ['organization_id', $request->org_id];
        }
        
        $data_set = Bank::where($whereData)->get();
        
        if($data_set)
        {
            return response()->json($data_set, 200);
        }
    }


    public function reg_slip_master_one(Request $request, $id = null)
    {
       
        
        if($request->std_registration_id) {   
            $whereData[] = ['std_registration_id', $request->std_registration_id];
            $whereData[] = ['is_enable',1];
        }

        $data_set = GenAdmissionVoucher::where($whereData)->get();
        $is_data = GenAdmissionVoucher::where($whereData)->count();
        $data_result = [];
        
        // $status = 200;
        // return response()->json($data_set['0']->id, $status); 
        // exit;
        

        $data_result['data_list'] = $data_set->toArray();
        if($is_data>0){
            $data_set_detail = GenAdmissionVoucherDetail::with('FeesType', 'DiscountType')->where('reg_slip_master_id',$data_set['0']->id)->get();
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

    
   
    
    
   
}
