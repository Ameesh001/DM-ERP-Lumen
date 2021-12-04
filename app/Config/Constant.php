<?php
namespace App\Config;

class Constant
{

    const RequestType = [
        'GET_ONE' => "GET_ONE",
        'GET_ALL' => "GET_ALL"
    ];

    const RecordType = [
        'DISABLED' => 0,
        'ENABLED' => 1,
        'DELETED' => 2
    ];
    
    const LangDir = [
        'RTL' => 1,
        'LTR' => 2,
    ];
    
    const Frequencies = [
        'YEAR', 'MONTH', 'WEEK', 'DAY'
    ];
    
    const Page = 1;
    const PageSize = 10;
    const MaxPageSize = 500;
    const OrderType = 'desc';
    const OrderBy = 'id';
    
    
    //Table name Constant
    const Tables = [
        'organization'           => 'organization_list',
        'region'                 => 'region',
        'city'                   => 'city',
        'campus'                 => 'campus',
        'campus_session'         => 'campus_session',
        'campus_section'         => 'campus_section',
        'campus_subject'         => 'campus_subject',
        'campus_fee'             => 'campus_fee',
        'slip_setup'             => 'slip_setup',
        'student_registration'   => 'student_registration',
    ];
    
}

