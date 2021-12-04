<?php

namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Utilities\Utilities;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TeacherTimeTable extends Model
{
    // use HasFactory;

    protected $table = 'teacher_time_table';

    public $timestamps  = false;

    
    public function Campus()
    {
        return $this->belongsTo(Campus::class, 'campus_id')->select('id','campus_name');
    }
    public function TeacherName()
    {
        return $this->belongsTo(User::class, 'teacher_id')->select('id','username');
    }
    public function Class()
    {
        return $this->belongsTo(Classes::class, 'class_id')->select('id','class_name');
    }
    public function Subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id')->select('id','subject_name');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'campus_id',
        'class_id',
        'subject_id',
        'teacher_id',
        'day',
        'class_start_time',
        'class_end_time',
        'break_start_time',
        'break_end_time',
        'is_enable',
        'created_by',
        'created_at' ,
        'updated_by' ,
        'updated_at',
        'deleted_at' 
    ];

    protected $tableColumnList = [

        'id' => 'id',
        'campus_id' => 'campus_id',
        'class_id'=>'class_id',
        'subject_id'=>'subject_id',
        'teacher_id' => 'teacher_id',
        'day'=>'day',
        'class_start_time'=>'class_start_time',
        'class_end_time'=>'class_end_time',
        'break_start_time'=>'break_start_time',
        'break_end_time'=>'break_end_time',
        'is_enable'=>'is_enable',
        'created_by' => 'created_by',
        'created_at' => 'created_at',
        'updated_by' => 'updated_by',
        'updated_at' => 'updated_at',
        'deleted_at' => 'deleted_at'
       
    ];

    protected $otherColumnList = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Scope a query to only include active records.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_enable', '!=', Constant::RecordType['DELETED']);
    }

    /**
     * Set column Name with Actual name.
     * 
     * @param string $request
     * @return array
     */
    public function filterColumns(Request $request, $method = null)
    {
        if ($method == null) {
            $method = $request->method();
        }

        $columnList = $this->tableColumnList + $this->otherColumnList;

        Utilities::filterColumnsModel($request, $columnList, $method);
    }

    /**
     * Get column for ordering after varification.
     * 
     * @param string $field
     * @return string[]|array|string
     */
    public function getOrderColumn($field)
    {
        $columnList = $this->tableColumnList + $this->otherColumnList;

        foreach ($columnList as $key => $value) {
            if ($key === $field)
                return $key;
        }

        return "id";
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules($request, $method = null)
    {
        if ($method == null) {
            $method = $request->method();
        }

        $rules = [];

        $rules = match ($method) {


            'POST' =>  [
                'campus_id' => 'required',
                'teacher_id' => 'required',
                'class_id' => 'required',                
                'class_start_time' => 'required',                
                'class_end_time' => 'required',

                // Start Time Validate
                'class_id' =>[
                    'required', Rule::unique('teacher_time_table', 'class_id')->where(function ($query) use ($request) {
                      
                        // Start Time Validate
                        $query->where( DB::raw('TIME(class_start_time)'), '<=', date('Y-m-d H:i:s', strtotime($request->class_start_time)));
                        $query->where( DB::raw('TIME(class_end_time)'), '>', date('Y-m-d H:i:s', strtotime($request->class_start_time)));
                        
                         $query->where('class_id',   '=', $request->class_id);
                         $query->where('teacher_id', '=', $request->teacher_id);
                         $query->where('campus_id',  '=', $request->campus_id);
                         $query->where('day',        '=', $request->day);
                         $query->where('subject_id', '=', $request->subject_id);
                         $query->where('is_enable', '<>', '2');
                         $query->where('id', '<>', $request->id);

                    }),
                ],
               
               
                // End Time Validate
                'day'  =>[
                    'required', Rule::unique('teacher_time_table', 'day')->where(function ($query) use ($request) {
                      
                        // End Time Validate
                         $query->where( DB::raw('TIME(class_start_time)'), '<=', date('Y-m-d H:i:s', strtotime($request->class_end_time)));
                         $query->where( DB::raw('TIME(class_end_time)'), '>=', date('Y-m-d H:i:s', strtotime($request->class_end_time)));
                        
                         $query->where('class_id',   '=', $request->class_id);
                         $query->where('teacher_id', '=', $request->teacher_id);
                         $query->where('campus_id',  '=', $request->campus_id);
                         $query->where('day',        '=', $request->day);
                         $query->where('subject_id', '=', $request->subject_id);
                         $query->where('is_enable', '<>', '2');
                         $query->where('id', '<>', $request->id);

                    }),
                ],
                'subject_id' => [
                    'required', Rule::unique('teacher_time_table', 'subject_id')->where(function ($query) use ($request) {
                        
                         $query->where('class_id',   '=', $request->class_id);
                         $query->where('teacher_id', '=', $request->teacher_id);
                         $query->where('campus_id',  '=', $request->campus_id);
                         $query->where('day',        '=', $request->day);
                         $query->where('subject_id', '=', $request->subject_id);
                         $query->where('class_start_time', '=', $request->class_start_time);                         
                         $query->where('class_end_time', '=', $request->class_end_time);
                         $query->where('is_enable', '<>', '2');
                         $query->where('id', '<>', $request->id);
                    }),
                ],
                
            ],





            'PUT' =>  [
                'campus_id' => 'required',
                'teacher_id' => 'required',
                'class_id' => 'required',                
                'class_start_time' => 'required',                
                'class_end_time' => 'required',

                // Start Time Validate
                'class_id' =>[
                    'required', Rule::unique('teacher_time_table', 'class_id')->where(function ($query) use ($request) {
                      
                        // Start Time Validate
                        $query->where( DB::raw('TIME(class_start_time)'), '<=', date('Y-m-d H:i:s', strtotime($request->class_start_time)));
                        $query->where( DB::raw('TIME(class_end_time)'), '>', date('Y-m-d H:i:s', strtotime($request->class_start_time)));
                        
                         $query->where('class_id',   '=', $request->class_id);
                         $query->where('teacher_id', '=', $request->teacher_id);
                         $query->where('campus_id',  '=', $request->campus_id);
                         $query->where('day',        '=', $request->day);
                         $query->where('subject_id', '=', $request->subject_id);
                         $query->where('is_enable', '<>', '2');
                         $query->where('id', '<>', $request->id);

                    }),
                ],
               
               
                // End Time Validate
                'day'  =>[
                    'required', Rule::unique('teacher_time_table', 'day')->where(function ($query) use ($request) {
                      
                        // End Time Validate
                         $query->where( DB::raw('TIME(class_start_time)'), '<=', date('Y-m-d H:i:s', strtotime($request->class_end_time)));
                         $query->where( DB::raw('TIME(class_end_time)'), '>=', date('Y-m-d H:i:s', strtotime($request->class_end_time)));
                        
                         $query->where('class_id',   '=', $request->class_id);
                         $query->where('teacher_id', '=', $request->teacher_id);
                         $query->where('campus_id',  '=', $request->campus_id);
                         $query->where('day',        '=', $request->day);
                         $query->where('subject_id', '=', $request->subject_id);
                         $query->where('is_enable', '<>', '2');
                         $query->where('id', '<>', $request->id);

                    }),
                ],
                'subject_id' => [
                    'required', Rule::unique('teacher_time_table', 'subject_id')->where(function ($query) use ($request) {
                        
                         $query->where('class_id',   '=', $request->class_id);
                         $query->where('teacher_id', '=', $request->teacher_id);
                         $query->where('campus_id',  '=', $request->campus_id);
                         $query->where('day',        '=', $request->day);
                         $query->where('subject_id', '=', $request->subject_id);
                         $query->where('class_start_time', '=', $request->class_start_time);                         
                         $query->where('class_end_time', '=', $request->class_end_time);
                         $query->where('is_enable', '<>', '2');
                         $query->where('id', '<>', $request->id);
                    }),
                ],
                
            ],




            'PATCH' => [
                'id' => 'required|integer',
                'is_enable' => 'required|numeric|between:0,1'
            ],
            'DELETE' => [
                'id' => 'required|integer',
            ],
            'GET_ONE' => [
                'id' => 'required|integer'
                // 'fields' => ''
            ],
            'GET_ALL' => [
                // 'fields' => ''
            ]
        };

        return $rules;
    }

    /**
     * Get the validation custom messages.
     *
     * @return array
     */
    public function messages($request, $method = null)
    {
        if ($method == null) {
            $method = $request->method();
        }

        $messages = [];

        $commonMessages = [
             'subject_id.required' => [
                 "code" => 10101,
                 "message" => "Please provide Subject Name."
             ],
             'subject_id.unique' => [
                 "code" => 10102,
                 "message" => "Please provide unique Details."
             ],

            'class_id.unique' => [
                "code" => 10104,
                "message" => "Please provide unique Class Start Time"
            ],

            'day.unique' => [
               "code" => 10104,
               "message" => "Please provide unique Class End Time"
           ],

        ];

        $idMessages = [
            'id.required' => [
                "code" => 10107,
                "message" => "Please provide id."
            ],
            'id.integer' => [
                "code" => 10106,
                "message" => "Id must be an integer."
            ]
        ];

        $statusMessage = [
            'activate.required' => [
                "code" => 90101,
                "message" => "Please provide activate flag."
            ],
            'activate.numeric' => [
                "code" => 90102,
                "message" => "Activate flag must be an integer."
            ],
            'activate.between' => [
                'numeric' => [
                    "code" => 90103,
                    "message" => "The activate flag must be between :min and :max."
                ]
            ]
        ];

        $messages = match ($method) {
            'POST' => $commonMessages,
            'PUT' => $commonMessages + $idMessages,
            'PATCH' => $idMessages + $statusMessage,
            'DELETE' => $idMessages,
            'GET_ONE' => $idMessages,
            'GET_ALL' => $messages = []
        };

        return $messages;
    }
}
