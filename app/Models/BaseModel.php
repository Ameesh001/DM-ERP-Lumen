<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Config\Constant;
use App\Response\ValidationResponse;
use App\Response\ValidationReponseDetail;
use App\Config\CleanJsonSerializer;
use App\Response\SuccessResponse;
use App\Response\BaseResponse;

class BaseModel extends Model
{
    public function defaultAddAttributes(Request $request, $created_by, $is_enabled = Constant::RecordType['ENABLED'], $created_dt = null)
    {
        $created_dt = ($created_dt == null) ? date('Y-m-d H:i:s') : $created_dt;
        
        $request->request->add([
            'is_enable' => $is_enabled,
            'created_by' => $created_by,
            'created_at' => $created_dt
        ]);
    }

    public function defaultUpdateAttributes(Request $request, $updated_by, $updated_dt = null)
    {
        $updated_dt = ($updated_dt == null) ? date('Y-m-d H:i:s') : $updated_dt;
        
        $request->request->add([
            'updated_by' => $updated_by,
            'updated_at' => $updated_dt
        ]);
    }
    
    public function defaultDeleteAttributes(Request $request, $updated_by, $deleted_dt = null)
    {
        $deleted_dt = ($deleted_dt == null) ? date('Y-m-d H:i:s') : $deleted_dt;
        
        $request->request->add([
            'deleted_at' => $deleted_dt
        ]);
    }

    /**
     * Remove keys except provided, first convert values to keys b/c keys are in values and indexes in keys
     * 
     * @param Request $request
     * @param array $keys
     * 
     */
    public function removeAttributesExcept(Request $request, array $keys)
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
    public function buildFailedValidationResponse($code, $message, array $errors)
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
    public function buildSuccessResponse($code, $message, $data)
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
    public function buildBaseResponse($code, $message)
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
    protected function filterColumnsModel(Request $request, $columnList, $method)
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
   
}
