<?php

namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Utilities\Utilities;
use Illuminate\Database\Eloquent\Model;

class TeacherSubAssign extends Model
{
    // use HasFactory;

    protected $table = 'teacher_subject_assign';

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
    public function Classes()
    {
        return $this->belongsTo(Classes::class, 'class_id')->select('id','class_name');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'teacher_id',
        'campus_id',
        'class_id',
        'subject_id',
        'is_enable',
        'created_by',
        'created_at' ,
        'updated_by' ,
        'updated_at',
        'deleted_at' 
    ];

    protected $tableColumnList = [
        'data_user_id' => 'data_user_id',
        'id' => 'id',
        'teacher_id' => 'teacher_id',
        'campus_id' => 'campus_id',
        'class_id'=>'class_id',
        'subject_id'=>'subject_id',
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
            'POST' => [
                // 'subject_name' => [
                //     'required', Rule::unique('subject', 'subject_name')->where(function ($query) use ($request) {
                //         $query->where('is_enable', '<>', '2');
                //         $query->where('countries_id', '=', $request->countries_id);
                //         $query->where('state_id', '=', $request->state_id);
                //     }),
                //     'min:3', 'max:100'
                // ],
                // 'subject_code' => [
                //     'required', Rule::unique('subject', 'subject_code')->where(function ($query) use ($request) {
                //         $query->where('is_enable', '<>', '2');
                //     }),
                //     'min:3', 'max:6'
                // ],
                // 'countries_id' => 'required',
                // 'state_id' => 'required',
                // 'subject_desc' => 'required',
                //'id' => 'required|integer',
                'teacher_id' => 'required',
                'campus_id' => 'required',
                'class_id' => 'required',
//                'subject_id' => 'required',
                'subject_id' => [
                     'required', Rule::unique('teacher_subject_assign', 'subject_id')->where(function ($query) use ($request) {
                         $query->where('is_enable', '<>', '2');
                         $query->where('class_id',   '=', $request->class_id);
                         $query->where('teacher_id', '=', $request->teacher_id);
                         $query->where('campus_id',  '=', $request->campus_id);
                     }),
                 ],
                
            ],
            'PUT' => [

                
                // 'teacher_id' => [
                //     'required', Rule::unique('subject', 'subject_name')->where(function ($query) use ($request) {
                //         $query->where('is_enable', '<>', '2')
                //         ->where('id', '<>', $request->id);
                //         $query->where('countries_id', '=', $request->countries_id);
                //          $query->where('state_id', '=', $request->state_id);
                //     }),
                //     'min:3', 'max:100'
                // ],
                // 'subject_id' => [
                //     'required', Rule::unique('subject', 'subject_code')->where(function ($query) use ($request) {
                //         $query->where('is_enable', '<>', '2')
                //         ->where('id', '<>', $request->id);
                //     }),
                //     'min:3', 'max:6'
                // ],
                 'id' => 'required|integer',
                 'teacher_id' => 'required',
                 'campus_id' => 'required',
                 'class_id' => 'required',
//                 'subject_id' => 'required',
                'subject_id' => [
                     'required', Rule::unique('teacher_subject_assign', 'subject_id')->where(function ($query) use ($request) {
                         $query->where('is_enable', '<>', '2');
                         $query->where('class_id',   '=', $request->class_id);
                         $query->where('teacher_id', '=', $request->teacher_id);
                         $query->where('campus_id',  '=', $request->campus_id);
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
                 "message" => "Please provide unique Subject name."
             ],

            // 'subject_name.min' => [
            //     "string" => [
            //         "code" => 10104,
            //         "message" => "The Subject name must be at least :min characters."
            //     ]
            // ],
            // 'subject_name.max' => [
            //     "string" => [
            //         "code" => 10105,
            //         "message" => "The Subject name may not be greater than :max characters."
            //     ]
            // ],
            // 'subject_Code.required' => [
            //     "code" => 10101,
            //     "message" => "Please provide Subject Code."
            // ],
            // 'subject_code.unique' => [
            //     "code" => 10102,
            //     "message" => "Please provide unique Subject Code."
            // ],
            
            // 'subject_desc.required' => [
            //     "code" => 10101,
            //     "message" => "Please provide Subject Description."
            // ],

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
