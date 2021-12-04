<?php

/**
 * Darulmadinah Api
Author: Muhammad Usama (it.dev7) 
 * 
 * This is a CampusSubject API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\CampusSubject;
use Illuminate\Http\Request;
use App\Utilities\Utilities;

class CampusSubjectApi extends Controller
{

    /**
     * Constructor
     */
    private $mdlName;
             
    public function __construct()
    {
        $this->mdlName = new CampusSubject();
    }
    
    private $select_columns  = [
        'campus_subject.id', 
        //'campus.organization_id', 
       // 'campus.countries_id', 
        //'campus.state_id', 
        //'campus.region_id',
        //'campus.city_id', 
        'campus_subject.campus_id', 
        'campus_subject.class_id', 
        'campus_subject.subject_id', 
        'campus_subject.class_duration', 
        'campus_subject.online_class_duration', 
        'campus_subject.subject_marks', 
        'campus_subject.is_enable',
        'campus_subject.created_by',
        'campus_subject.created_at',
        'campus_subject.updated_by',
        'campus_subject.updated_at',
        'campus_subject.deleted_at'
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
     * Add CampusSubject.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
             
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        
        $this->mdlName->filterColumns($request);
        
         Utilities::defaultAddAttributes($request, $request->data_user_id);
            
        $post_arr = $request->all();
        //$post_arr['organization_id'] =  1;
        $obj = CampusSubject::create($post_arr);
        
        $data = [ 'id' => $obj->id ];
        
        $response = Utilities::buildSuccessResponse(10000, "CampusSubject successfully created.", $data);
        
        return response()->json($response, 201);
    }

    /**
     * Update CampusSubject.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        
        $this->mdlName->filterColumns($request);
        
        Utilities::defaultUpdateAttributes($request, $request->data_user_id);
//
        $result_set = CampusSubject::find($request->id);
        
        $status = 200;
        $response = [];
        
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "CampusSubject not found.");
        } else {
            
            $post_arr = $request->all();
           
            $obj = $result_set->update($post_arr);

            if ($obj) {
                $data = [ 'id' => $result_set->id ];
                $response = Utilities::buildSuccessResponse(10001, "CampusSubject successfully updated.", $data);
            } 
        }
        
        return response()->json($response, $status);
    }
    
    /**
     * Activate/De-Activate CampusSubject.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function patchDisableEnable(Request $request)
    {
 
        Utilities::removeAttributesExcept($request, ["id","is_enable"]);
        
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        
        Utilities::defaultUpdateAttributes($request, $request->data_user_id);
        
        $activate = $request->is_enable == 1 ? Constant::RecordType['ENABLED'] : Constant::RecordType['DISABLED'];
        
        $request->request->add([ 'is_enable' => $activate ]);
        
        $result_set = CampusSubject::find($request->id);
        $status = 200;
        $response = [];

        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "CampusSubject not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $data = ['id' => $result_set->id ];
                $actMsg = $request->is_enable == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "CampusSubject successfully $actMsg.", $data);
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Delete CampusSubject.
     *
     * @param $id 'ID' of CampusSubject to delete. (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id, Request $request)
    {
        $request->request->add([ 'id' => $id ]);
        
        $this->validate($request, $this->mdlName->rules($request), $this->mdlName->messages($request));
        
        Utilities::defaultDeleteAttributes($request, 1);
        
        $request->request->add([
            'is_enable' => Constant::RecordType['DELETED']
        ]);
        
        $result_set = CampusSubject::find($request->id);
        
        $status = 200;
        $response = [];
        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "CampusSubject not found.");
        } else {
            
            $obj = $result_set->update($request->all());
            
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "CampusSubject successfully deleted.");
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Get one CampusSubject.
     *
     * @param $id 'ID' of CampusSubject to return (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOne($id, Request $request)
    {
        $request->request->add([ 'id' => $id ]);
        
        $this->validate($request, $this->mdlName->rules($request, Constant::RequestType['GET_ONE']), $this->mdlName->messages($request, Constant::RequestType['GET_ONE']));
        
        $select = $this->select_columns;

        $this->mdlName->filterColumns($request);
        
        if($request->fields){
            $select = $request->fields;
        }
        
        $result_set = CampusSubject::with('Campus', 'Class', 'Subject')->where('campus_subject.id', $request->id)->join('campus', 'campus.id' , '=', 'campus_subject.campus_id')->first($select);
        
        $status = 200;
        $response = [];

        if (!$result_set) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "CampusSubject not found.");
        } else {
            
            $data_set = $result_set->toArray();
  
            $dataResult['data_set']             = $data_set;
  
            $response = Utilities::buildSuccessResponse(10005, "CampusSubject Data.", $dataResult);
        }
        
        return response()->json($response, $status);
    }

    /**
     * Fetch list of CampusSubject by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll(Request $request, $id = null)
    {
        
//        return response()->json(Utilities::set_data_permission_wheres($request), 200); 
        
        $this->validate($request, $this->mdlName->rules($request, Constant::RequestType['GET_ALL']), $this->mdlName->messages($request, Constant::RequestType['GET_ALL']));
        
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
            
//        
        if($request->campus_id) {   
            $whereData[] = ['campus_subject.campus_id', $request->campus_id];
        }
        
        if($request->class_id) {   
            $whereData[] = ['campus_subject.class_id', $request->class_id];
        }
        
        if($request->subject_id) {   
            $whereData[] = ['campus_subject.subject_id', $request->subject_id];
        }
        
        if($request->is_enable != null) {
            $whereData[] = ['campus_subject.is_enable', $request->is_enable];
        }
        
        
//        $whereData['dasd'] = 1;
        
        
        $total_query_obj = CampusSubject::
                
                 where($whereData)
                ->join('campus', 'campus.id' , '=', 'campus_subject.campus_id');
        
        $tableArr['parent'] = '';
        $tableArr['child'] = '';
        $total_query_obj = Utilities::set_all_data_permission_wheres($request, $total_query_obj, $tableArr);
        
        $total_record = $total_query_obj->count();
        
        $orderBy =  $this->mdlName->getOrderColumn($request->order_by);
        
        $orderType = $request->order_type ?? Constant::OrderType;
        
//        $whereData['dasd'] = 1;
        $data_query_obj = CampusSubject::with('Campus', 'Class', 'Subject')
             ->join('campus','campus.id' , '=', 'campus_subject.campus_id' )
            ->where($whereData)
            ->orderBy($orderBy, $orderType)
            ->offset($skip)
            ->limit($pageSize);
        
        $data_query_obj = Utilities::set_all_data_permission_wheres($request, $data_query_obj, $tableArr);
        
        $data_set = $data_query_obj->get($select);
        
        
        $data_result = [];
        $status = 200;
        $data_result['data_list'] = $data_set->toArray();
        $data_result['total_record'] = $total_record;
        
        $response = Utilities::buildSuccessResponse(10004, "CampusSubject List.", $data_result);

        return response()->json($response, $status); 
    }
    
    /**
     * Fetch list of CampusSubject for selectboc/dropdown by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCampusSubject(Request $request, $id = null)
    {
        $this->validate($request, $this->mdlName->rules($request, Constant::RequestType['GET_ALL']), $this->mdlName->messages($request, Constant::RequestType['GET_ALL']));
        
       
        $select =  $this->select_columns;
        
        if($request->fields){
            $select = $request->fields;
        }
        $whereData = array();       
        
            
        if($request->class_id) {   
            $whereData[] = ['class_id', $request->class_id];
        }
        
        if($request->campus_id) {   
            $whereData[] = ['campus_id', $request->campus_id];
        }
        
        if($request->subject_id) {   
            $whereData[] = ['subject_id', $request->subject_id];
        }         
 
        if($request->id) {   
            $whereData[] = ['id', $request->id];
        }
        if($request->is_enable != null) {
            $whereData[] = ['is_enable', $request->is_enable];
        }
        
        $data_set = CampusSubject::with('Campus','Class', 'Subject')
            ->where($whereData)
            ->active()
            ->get($select);
        
        
        $data_result = [];
        $status = 200;
        $data_result['campus'] = $data_set->toArray();
        
        $response = Utilities::buildSuccessResponse(10004, "CampusSubject List.", $data_result);

        return response()->json($response, $status); 
    }
}
