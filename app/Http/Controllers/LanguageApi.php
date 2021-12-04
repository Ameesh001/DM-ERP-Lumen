<?php

/**
 * Performance system API
 * This is a Language API controller
 *
 */
namespace App\Http\Controllers;

use App\Config\Constant;
use App\Models\Language;
use App\Response\LanguageResponse;
use Illuminate\Http\Request;
use App\Utilities\Utilities;

class LanguageApi extends Controller
{

    /**
     * Constructor
     */
    public function __construct()
    {

    }

    private $select_columns = ['id', 'lang_code as lang', 'lang_name as name', 'lang_dir as dir', 'is_enable as activate'];
    private $select_list = ['id', 'lang_name as name' , 'lang_dir as dir', 'is_enable as activate'];

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
     * Add Language.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addLanguage(Request $request)
    {
        $mdlLanguage = new Language();
        
        $this->validate($request, $mdlLanguage->rules($request), $mdlLanguage->messages($request));
        
        $mdlLanguage->filterColumns($request);

        Utilities::defaultAddAttributes($request, $request->data_user_id);
        $postArr = $request->all();
        $postArr['lang_name'] = trim($postArr['lang_name']);
        $postArr['lang_code'] = trim($postArr['lang_code']);
        $obj = Language::create($postArr);
        
        $data = [ 'lang' => $obj->lang_code ];
        
        $response = Utilities::buildSuccessResponse(10000, "Language successfully created.", $data);
        
        return response()->json($response, 201);
    }

    /**
     * Update Language.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateLanguage(Request $request)
    {
        $mdlLanguage = new Language();
        
        $this->validate($request, $mdlLanguage->rules($request), $mdlLanguage->messages($request));
        
        $mdlLanguage->filterColumns($request);
        
        Utilities::defaultAddAttributes($request, $request->data_user_id);
        
        $language = Language::find($request->lang_code);
        $postArr = $request->all();
        $postArr['lang_name'] = trim($postArr['lang_name']);
        $postArr['lang_code'] = trim($postArr['lang_code']);

        $status = 200;
        $response = [];
        
        if (! $language) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Language not found.");
        } else {
            $obj = $language->update($postArr);
            if ($obj) {
                $data = [ 'lang' => $language->lang_code ];
                $response = Utilities::buildSuccessResponse(10001, "Language successfully updated.", $data);
            }
        }
        
        return response()->json($response, $status);
    }
    
    /**
     * Activate/De-Activate Language.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function patchDisableEnableLanguage(Request $request)
    {
        $mdlLanguage = new Language();

        Utilities::removeAttributesExcept($request, ["lang","activate"]);
        
        $this->validate($request, $mdlLanguage->rules($request), $mdlLanguage->messages($request));
        
        Utilities::defaultAddAttributes($request, $request->data_user_id);
        
        $activate = $request->activate == 1 ? Constant::RecordType['ENABLED'] : Constant::RecordType['DISABLED'];
        
        $request->request->add([ 'is_enable' => $activate ]);
        
        $language = Language::find($request->lang);
        $status = 200;
        $response = [];

        if (! $language) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Language not found.");
        } else {
            
            $obj = $language->update($request->all());
            
            if ($obj) {
                $data = [ 'lang' => $language->lang_code ];
                $actMsg = $request->activate == 1 ? "activated" : "de-activated";
                $response = Utilities::buildSuccessResponse(10001, "Language successfully $actMsg.", $data);
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Delete Language.
     *
     * @param $id 'ID' of Language to delete. (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteLanguage($lang_code, Request $request)
    {
        $mdlLanguage = new Language();
        
        $request->request->add([ 'lang' => $lang_code ]);
        
        $this->validate($request, $mdlLanguage->rules($request), $mdlLanguage->messages($request));
        
        Utilities::defaultDeleteAttributes($request, 1);
        
        $request->request->add([ 'is_enable' => Constant::RecordType['DELETED'] ]);
        
        $language = Language::find($request->lang_code);
        
        $status = 200;
        $response = [];

        if (! $language) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Language not found.");
        } else {    
            $obj = $language->update($request->all());
            
            if ($obj) {
                $response = Utilities::buildBaseResponse(10006, "Language successfully deleted.");
            }
        }
        
        return response()->json($response, $status);
    }

    /**
     * Get one Language.
     *
     * @param $id 'ID' of Language to return (required)
     *            
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOneLanguage($lang_code, Request $request)
    {
        $mdlLanguage = new Language();
        
        $request->request->add([ 'lang' => $lang_code ]);
        
        $this->validate($request, $mdlLanguage->rules($request, Constant::RequestType['GET_ONE']), $mdlLanguage->messages($request,  Constant::RequestType['GET_ONE']));
        
        $select = $this->select_columns;

        $mdlLanguage->filterColumns($request);

        if($request->fields){
            $select = $request->fields;
        }

        $language = Language::where('lang_code', $request->lang)->first($select);

        $status = 200;
        $response = [];
        
        if (! $language) {
            $status = 404;
            $response = Utilities::buildBaseResponse(10003, "Language not found.");
        } else {
            $dataResult = array("language" => $language->toArray());
            $response = Utilities::buildSuccessResponse(10005, "Language Data.", $dataResult);
        }
        
        return response()->json($response, $status);
    }

    /**
     * Fetch list of Language by searching with optional filters..
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllLanguage(Request $request)
    {
        $mdlLanguage = new Language();
        
        $this->validate($request, $mdlLanguage->rules($request, Constant::RequestType['GET_ALL']), $mdlLanguage->messages($request, Constant::RequestType['GET_ALL']));
        
        $pageSize = $request->limit ?? Constant::PageSize;
        
        if($pageSize > Constant::MaxPageSize){
            $pageSize = Constant::PageSize;
        }
        $page = $request->page ?? Constant::Page;
        $skip = ($page - 1) * $pageSize;
        
        $select = $this->select_columns;
        
        $mdlLanguage->filterColumns($request);
        
        if($request->fields){
            $select = $request->fields;
        }

        $whereData = array();
        
        if($request->lang) {   
            $whereData[] = ['lang', $request->name];
        }
        if($request->name) {   
            $whereData[] = ['lang_name', 'LIKE', "%{$request->name}%"];
        }
        if($request->dir != null) {   
            $whereData[] = ['lang_dir', 'LIKE', "%{$request->dir}%"];
        }
        if($request->activate != null) {
            $whereData[] = ['is_enable', $request->activate];
        }
        
        $orderBy =  $mdlLanguage->getOrderColumn($request->order_by);
        $orderType = $request->order_type ?? Constant::OrderType;
        $total_record = Language::where($whereData)->active()->count();
        $languages = Language::where($whereData)
            ->active()
            ->orderBy($orderBy, $orderType)
            ->offset($skip)
            ->limit($pageSize)
            ->get($select);

        $status = 200;
        // $data_result = new LanguageResponse();
        // $data_result->setLanguages($languages->toArray());
        $data_result['languages'] = $languages->toArray();
        $data_result['total_record'] = $total_record;
        $response = Utilities::buildSuccessResponse(10004, "Language List.", $data_result);

        return response()->json($response, $status);   
    }


    /**
     * Fetch list of Language by searching with optional filters for dropdown/selectbox list
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllData(Request $request)
    {
       
        $whereData = array();
        
        if($request->lang) {   
            $whereData[] = ['lang', $request->name];
        }
        if($request->name) {   
            $whereData[] = ['lang_name', 'LIKE', "%{$request->name}%"];
        }
        if($request->dir != null) {   
            $whereData[] = ['lang_dir', 'LIKE', "%{$request->dir}%"];
        }
        if($request->activate != null) {
            $whereData[] = ['is_enable', $request->activate];
        }
        
        if($request->is_enable != null) {
            $whereData[] = ['is_enable', $request->is_enable];
        }else{
            $whereData[] = ['is_enable', 1];
        }
        
        if($request->id != null) {
            $whereData[] = ['id', $request->id];
        }
        
        $select = $this->select_list;
        
        $language = Language::where($whereData)
            ->active()
            ->get($select);
        
        $status = 200;

        $data_result['language'] = $language->toArray();
        
        $response = Utilities::buildSuccessResponse(10004, "Language List", $data_result);
        
        return response()->json($response, $status);   
    }
}
