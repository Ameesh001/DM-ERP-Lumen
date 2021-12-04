<?php

use Illuminate\Support\Facades\Http;

/** @var \Laravel\Lumen\Routing\Router $router */

/*
 * |--------------------------------------------------------------------------
 * | Application Routes
 * |--------------------------------------------------------------------------
 * |
 * | Here is where you can register all of the routes for an application.
 * | It is a breeze. Simply tell Lumen the URIs it should respond to
 * | and give it the Closure to call when that URI is requested.
 * |
 */
$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'], function () use ($router) {

    /*//////////////////////// Summary FOR Routes Method: ////////////////////////////////////
    * POST For Add
    */
    /**************************
     * PUT For Update
     */
    /**************************
     * PATCH For Activate/De-Activate
     */
    /**************************
     * DELETE For Delete
     * Notes: Pass id to delete record.
     */
    /**************************
     * GET (With id) For Get Single Row Data
     * Notes: Pass id to fetch a record.
     */
    /**************************
     * GET Fetch list of records by searching with optional filters
     * Notes: By passing in the appropriate options, you can search for available clients in the system.
     */

    ///////////////////////////////////////// Country Routes ////////////////////////////////////

    $router->get('foo', function () {
        return 'Hello World';
    });

    $router->post('country', ['uses' => 'CountryApi@addCountry']);
    $router->put('country', ['uses' => 'CountryApi@updateCountry']);
    $router->patch('country', ['uses' => 'CountryApi@patchDisableEnableCountry']);
    $router->delete('country/{id}', ['uses' => 'CountryApi@deleteCountry']);
    $router->get('country/{id}', ['uses' => 'CountryApi@getOneCountry']);
    $router->get('country', ['uses' => 'CountryApi@getAllCountry']);
    $router->get('all_country', ['uses' => 'CountryApi@getAllData']);

    ///////////////////////////////////////// Setup State Routes ////////////////////////////////////

    $router->post('state', ['uses' => 'StateApi@add']);
    $router->put('state', ['uses' => 'StateApi@update']);
    $router->patch('state', ['uses' => 'StateApi@patchDisableEnable']);
    $router->delete('state/{id}', ['uses' => 'StateApi@delete']);
    $router->get('state/{id}', ['uses' => 'StateApi@getOne']);
    $router->get('state', ['uses' => 'StateApi@getAll']);
    $router->get('all_state', ['uses' => 'StateApi@getState']);


    ///////////////////////////////////////// Setup Currency Routes ////////////////////////////////////

    $router->post('currency', ['uses' => 'CurrencyApi@add']);
    $router->put('currency', ['uses' => 'CurrencyApi@update']);
    $router->patch('currency', ['uses' => 'CurrencyApi@patchDisableEnable']);
    $router->delete('currency/{id}', ['uses' => 'CurrencyApi@delete']);
    $router->get('currency/{id}', ['uses' => 'CurrencyApi@getOne']);
    $router->get('currency', ['uses' => 'CurrencyApi@getAll']);

    ///////////////////////////////////////// Setup Session Routes ////////////////////////////////////

    $router->post('session', ['uses' => 'SessionApi@add']);
    $router->put('session', ['uses' => 'SessionApi@update']);
    $router->patch('session', ['uses' => 'SessionApi@patchDisableEnable']);
    $router->delete('session/{id}', ['uses' => 'SessionApi@delete']);
    $router->get('session/{id}', ['uses' => 'SessionApi@getOne']);
    $router->get('session', ['uses' => 'SessionApi@getAll']);
    $router->get('all_session_master', ['uses' => 'SessionApi@getAllMaster']);
    $router->get('all_session', ['uses' => 'SessionApi@getSession']);
    ///////////////////////////////////////// Setup Class Routes ////////////////////////////////////

    $router->post('class', ['uses' => 'ClassesApi@add']);
    $router->put('class', ['uses' => 'ClassesApi@update']);
    $router->patch('class', ['uses' => 'ClassesApi@patchDisableEnable']);
    $router->delete('class/{id}', ['uses' => 'ClassesApi@delete']);
    $router->get('class/{id}', ['uses' => 'ClassesApi@getOne']);
    $router->get('class', ['uses' => 'ClassesApi@getAll']);
    $router->get('all_class', ['uses' => 'ClassesApi@getClass']);

    ///////////////////////////////////////// Setup Section Routes ////////////////////////////////////

    $router->post('section', ['uses' => 'SectionApi@add']);
    $router->put('section', ['uses' => 'SectionApi@update']);
    $router->patch('section', ['uses' => 'SectionApi@patchDisableEnable']);
    $router->delete('section/{id}', ['uses' => 'SectionApi@delete']);
    $router->get('section/{id}', ['uses' => 'SectionApi@getOne']);
    $router->get('section', ['uses' => 'SectionApi@getAll']);
    $router->get('all_section_master', ['uses' => 'SectionApi@getAllMaster']);
    $router->get('get_active_section_by_org_id', ['uses' => 'SectionApi@get_active_section_by_org_id']);

    ///////////////////////////////////////// Setup Department Routes ////////////////////////////////////

    $router->post('department', ['uses' => 'DepartmentApi@add']);
    $router->put('department', ['uses' => 'DepartmentApi@update']);
    $router->patch('department', ['uses' => 'DepartmentApi@patchDisableEnable']);
    $router->delete('department/{id}', ['uses' => 'DepartmentApi@delete']);
    $router->get('department/{id}', ['uses' => 'departmentApi@getOne']);
    $router->get('department', ['uses' => 'departmentApi@getAll']);
    $router->get('all_department_master', ['uses' => 'DepartmentApi@getAllMaster']);

    ///////////////////////////////////////// Setup Designation Routes ////////////////////////////////////

    $router->post('designation', ['uses' => 'DesignationApi@add']);
    $router->put('designation', ['uses' => 'DesignationApi@update']);
    $router->patch('designation', ['uses' => 'DesignationApi@patchDisableEnable']);
    $router->delete('designation/{id}', ['uses' => 'DesignationApi@delete']);
    $router->get('designation/{id}', ['uses' => 'DesignationApi@getOne']);
    $router->get('designation', ['uses' => 'DesignationApi@getAll']);
    $router->get('all_designation_master', ['uses' => 'DesignationApi@getAllMaster']);

    ///////////////////////////////////////// Setup Subject Routes ////////////////////////////////////

    $router->post('subject', ['uses' => 'SubjectApi@add']);
    $router->put('subject', ['uses' => 'SubjectApi@update']);
    $router->patch('subject', ['uses' => 'SubjectApi@patchDisableEnable']);
    $router->delete('subject/{id}', ['uses' => 'SubjectApi@delete']);
    $router->get('subject/{id}', ['uses' => 'SubjectApi@getOne']);
    $router->get('subject', ['uses' => 'SubjectApi@getAll']);
    $router->get('all_subject_master', ['uses' => 'SubjectApi@getAllMaster']);

    ////////////////////////////////////////// Setup Fee Type Routes ////////////////////////////////////
    $router->post('feetype',        ['uses' => 'FeetypeApi@add']);
    $router->put('feetype',         ['uses' => 'FeetypeApi@update']);
    $router->patch('feetype',       ['uses' => 'FeetypeApi@patchDisableEnable']);
    $router->delete('feetype/{id}', ['uses' => 'FeetypeApi@delete']);
    $router->get('feetype/{id}',    ['uses' => 'FeetypeApi@getOne']);
    $router->get('feetype',         ['uses' => 'FeetypeApi@getAll']);
    $router->get('all_feetype_master',      ['uses' => 'FeetypeApi@getAllMaster']);
    

////////////////////////////////////////// Setup Slip Type Routes ////////////////////////////////////
    $router->post('sliptype',        ['uses' => 'SliptypeApi@add']);
    $router->put('sliptype',         ['uses' => 'SliptypeApi@update']);
    $router->patch('sliptype',       ['uses' => 'SliptypeApi@patchDisableEnable']);
    $router->delete('sliptype/{id}', ['uses' => 'SliptypeApi@delete']);
    $router->get('sliptype/{id}',    ['uses' => 'SliptypeApi@getOne']);
    $router->get('sliptype',         ['uses' => 'SliptypeApi@getAll']);
    $router->get('all_slip_type_master',      ['uses' => 'SliptypeApi@getAllMaster']);
    $router->get('get_slip_setup',            ['uses' => 'SliptypeApi@getSlipSetup']);
    
    

    ////////////////////////////////////////// Setup Admission Type Routes ////////////////////////////////////
    $router->post('admissiontype',        ['uses' => 'AdmissiontypeApi@add']);
    $router->put('admissiontype',         ['uses' => 'AdmissiontypeApi@update']);
    $router->patch('admissiontype',       ['uses' => 'AdmissiontypeApi@patchDisableEnable']);
    $router->delete('admissiontype/{id}', ['uses' => 'AdmissiontypeApi@delete']);
    $router->get('admissiontype/{id}',    ['uses' => 'AdmissiontypeApi@getOne']);
    $router->get('admissiontype',         ['uses' => 'AdmissiontypeApi@getAll']);

    ////////////////////////////////////////// Setup Discount Type Routes ////////////////////////////////////
    $router->post('discounttype',        ['uses' => 'DiscountTypeApi@add']);
    $router->put('discounttype',         ['uses' => 'DiscountTypeApi@update']);
    $router->patch('discounttype',       ['uses' => 'DiscountTypeApi@patchDisableEnable']);
    $router->delete('discounttype/{id}', ['uses' => 'DiscountTypeApi@delete']);
    $router->get('discounttype/{id}',    ['uses' => 'DiscountTypeApi@getOne']);
    $router->get('discounttype',         ['uses' => 'DiscountTypeApi@getAll']);

    ///////////////////////////////////////// New Admission Policy Routes ////////////////////////////////////

    $router->post('new_admission_policy', ['uses' => 'NewAdmissionPolicyApi@add']);
    $router->put('new_admission_policy', ['uses' => 'NewAdmissionPolicyApi@update']);
    $router->patch('new_admission_policy', ['uses' => 'NewAdmissionPolicyApi@patchDisableEnable']);
    $router->delete('new_admission_policy/{id}', ['uses' => 'NewAdmissionPolicyApi@delete']);
    $router->get('new_admission_policy/{id}', ['uses' => 'NewAdmissionPolicyApi@getOne']);
    $router->get('new_admission_policy', ['uses' => 'NewAdmissionPolicyApi@getAll']);
    $router->get('all_new_admission_policy', ['uses' => 'NewAdmissionPolicyApi@getAllData']);

    ///////////////////////////////////////// New Class Assign Routes ////////////////////////////////////

    $router->post('class_assign', ['uses' => 'ClassAssignApi@add']);
    $router->put('class_assign', ['uses' => 'ClassAssignApi@update']);
    $router->patch('class_assign', ['uses' => 'ClassAssignApi@patchDisableEnable']);
    $router->delete('class_assign/{id}', ['uses' => 'ClassAssignApi@delete']);
    $router->get('class_assign/{id}', ['uses' => 'ClassAssignApi@getOne']);
    $router->get('class_assign', ['uses' => 'ClassAssignApi@getAll']);
    $router->get('all_class_assign', ['uses' => 'ClassAssignApi@getAllData']);
    $router->get('all_campus_class', ['uses' => 'ClassAssignApi@getClassAssign']);

    ///////////////////////////////////////// Language Routes ////////////////////////////////////

    $router->post('language', ['uses' => 'LanguageApi@addLanguage']);
    $router->put('language', ['uses' => 'LanguageApi@updateLanguage']);
    $router->patch('language', ['uses' => 'LanguageApi@patchDisableEnableLanguage']);
    $router->delete('language/{lang_code}', ['uses' => 'LanguageApi@deleteLanguage']);
    $router->get('language/{lang_code}', ['uses' => 'LanguageApi@getOneLanguage']);
    $router->get('language', ['uses' => 'LanguageApi@getAllLanguage']);
    $router->get('all_language', ['uses' => 'LanguageApi@getAllData']);
    ///////////////////////////////////////// Users Routes ////////////////////////////////////

    $router->post('authUser', ['uses' => 'AuthUserApi@addAuthUser']);
    $router->put('authUser', ['uses' => 'AuthUserApi@updateAuthUser']);
    $router->patch('authUser', ['uses' => 'AuthUserApi@patchDisableEnableAuthUser']);
    $router->delete('authUser/{id}', ['uses' => 'AuthUserApi@deleteAuthUser']);
    $router->delete('authUser/{id}', ['uses' => 'AuthUserApi@deleteAuthUser']);
    $router->get('authUser/{id}', ['uses' => 'AuthUserApi@getOneAuthUser']);
    $router->get('authUser', ['uses' => 'AuthUserApi@getAllAuthUser']);
    $router->get('userRolesLevel/{kc_id}', ['uses' => 'AuthUserApi@getUserRolesLevels']);
    $router->get('userLang/{kc_id}', ['uses' => 'AuthUserApi@getUserLang']);
    
    $router->get('all_user_master', ['uses' => 'AuthUserApi@getUserMaster']);

//    $router->delete('user_delete_hardly/{id}', ['uses' => 'AuthUserApi@hardDeleteAuthUser']);
    
    ///////////////////////////////////////// Users Permission Routes ////////////////////////////////////

    $router->post('authUserPerms', ['uses' => 'AuthUserPermsApi@addAuthUserPerms']);
    $router->get('authUserPerms/{id}', ['uses' => 'AuthUserPermsApi@getOneAuthUserPerms']);
    $router->get('authUserPerms', ['uses' => 'AuthUserPermsApi@getAllAuthUserPerms']);
    $router->get('data_permission_master', ['uses' => 'AuthUserPermsApi@getAllUserPermsMaster']);



    ///////////////////////////////////////// Module Routes ////////////////////////////////////

    $router->post('authModule', ['uses' => 'AuthModuleApi@addAuthModule']);
    $router->put('authModule', ['uses' => 'AuthModuleApi@updateAuthModule']);
    $router->patch('authModule', ['uses' => 'AuthModuleApi@patchDisableEnableAuthModule']);
    $router->delete('authModule/{id}', ['uses' => 'AuthModuleApi@deleteAuthModule']);
    $router->get('authModule/{id}', ['uses' => 'AuthModuleApi@getOneAuthModule']);
    $router->get('authModule', ['uses' => 'AuthModuleApi@getAllAuthModule']);
    $router->get('ModuleParent', ['uses' => 'AuthModuleApi@getAllModulesParents']);
    $router->get('ModuleParent_workflow', ['uses' => 'AuthModuleApi@getAllModulesParents_workflow']);
    $router->get('ModuleChild_workflow', ['uses' => 'AuthModuleApi@getAllModulesChild_workflow']);

    /////////////============ Hierarchy =================================
    $router->get('getHierarachy', ['uses' => 'HierarchyApi@getAll']);



    //====================== Workflow Setup ==============================
    $router->post('workflow_setup', ['uses' => 'WorkflowApi@add']);
    $router->get('workflow_setup', ['uses' => 'WorkflowApi@getAll']);
    $router->get('workflow_setup/{id}', ['uses' => 'WorkflowApi@getOne']);
    $router->patch('workflow_setup', ['uses' => 'WorkflowApi@patchDisableEnable']);
    $router->delete('workflow_setup/{id}', ['uses' => 'WorkflowApi@delete']);

    //=========================== Drop Down list ==================
    $router->get('dd_list',  ['uses' => 'DropdownApi@getAll']);




    ///////////////////////////////////////// User Roles Routes ////////////////////////////////////

    $router->post('userRoles', ['uses' => 'UserRolesApi@addUserRoles']);
    $router->put('userRoles', ['uses' => 'UserRolesApi@updateUserRoles']);
    $router->patch('userRoles', ['uses' => 'UserRolesApi@patchDisableEnableUserRoles']);
    $router->delete('userRoles/{id}', ['uses' => 'UserRolesApi@deleteUserRoles']);
    $router->get('userRoles/{id}', ['uses' => 'UserRolesApi@getOneUserRoles']);
    $router->get('userRoles', ['uses' => 'UserRolesApi@getAllUserRoles']);
    $router->get('all_roles', ['uses' => 'UserRolesApi@getAllRoles']);
    $router->get('getAllRoles_org', ['uses' => 'UserRolesApi@getAllRoles_org']);

    ///////////////////////////////////////// MODULE ROLE PERMISSION Routes ////////////////////////////////////

    $router->post('rolePerms', ['uses' => 'RolePermissionApi@addRolePermission']);
    $router->get('rolePerms/{client_id}/{role_id}', ['uses' => 'RolePermissionApi@getOneRolePermission']);
    // $router->get('roleModulePerms/{role_id}', ['uses' => 'RolePermissionApi@getRoleModulePermission']);
    $router->get('roleModulePerms/{user_id}', ['uses' => 'RolePermissionApi@getRoleModulePermission']);
    $router->get('roleModulePerms', ['uses' => 'RolePermissionApi@getAdminModulePermission']);
    $router->get('rolePerms', ['uses' => 'RolePermissionApi@getAllRolePermission']);
    $router->get('allowUrl', ['uses' => 'RolePermissionApi@getAllowedUrl']);
    $router->get('allowData', ['uses' => 'RolePermissionApi@getAllowedData']);
    $router->get('getCountryHirarcy', ['uses' => 'RolePermissionApi@getCountryHirarcy']);
    $router->get('getStateHirarcy', ['uses' => 'RolePermissionApi@getStateHirarcy']);
    $router->get('getRegionHirarcy', ['uses' => 'RolePermissionApi@getRegionHirarcy']);
    $router->get('getCityHirarcy', ['uses' => 'RolePermissionApi@getCityHirarcy']);
    $router->get('getCampusHirarcy', ['uses' => 'RolePermissionApi@getCampusHirarcy']);
    $router->get('getCampusSession', ['uses' => 'RolePermissionApi@getCampusSession']);
    $router->get('getCampusSection', ['uses' => 'RolePermissionApi@getCampusSection']);
    $router->get('getUserCampus', ['uses' => 'RolePermissionApi@getUserCampus']);
    $router->get('get_occupation_list', ['uses' => 'RolePermissionApi@get_occupation_list']);


    
    ///organaization
    $router->post('organization',        ['uses' => 'OrganizationApi@add']);
    $router->put('organization',         ['uses' => 'OrganizationApi@update']);
    $router->patch('organization',       ['uses' => 'OrganizationApi@patchDisableEnable']);
    $router->delete('organization/{id}', ['uses' => 'OrganizationApi@delete']);
    $router->get('organization/{id}',    ['uses' => 'OrganizationApi@getOne']);
    $router->get('organization',         ['uses' => 'OrganizationApi@getAll']);
    $router->get('all_organization',         ['uses' => 'OrganizationApi@getAllAcitve']);
    $router->get('all_country_organization',  ['uses' => 'OrganizationApi@getAllCountryOrg']);
    
    $router->delete('organaization_revert/{id}', ['uses' => 'OrganizationApi@deleteByPrefix']);
    
    
    //region
    $router->post('region',             ['uses' => 'RegionApi@add']);
    $router->put('region',              ['uses' => 'RegionApi@update']);
    $router->patch('region',            ['uses' => 'RegionApi@patchDisableEnable']);
    $router->delete('region/{id}',      ['uses' => 'RegionApi@delete']);
    $router->get('region/{id}',         ['uses' => 'RegionApi@getOne']);
    $router->get('region',              ['uses' => 'RegionApi@getAll']);
    $router->get('all_region',          ['uses' => 'RegionApi@getRegions']);
    
    //city
    $router->post('city',           ['uses' => 'CityApi@add']);
    $router->put('city',            ['uses' => 'CityApi@update']);
    $router->patch('city',          ['uses' => 'CityApi@patchDisableEnable']);
    $router->delete('city/{id}',    ['uses' => 'CityApi@delete']);
    $router->get('city/{id}',       ['uses' => 'CityApi@getOne']);
    $router->get('city',            ['uses' => 'CityApi@getAll']);
    $router->get('all_city',        ['uses' => 'CityApi@getCities']);
    
    
    //affiliation
    $router->get('all_affiliation', ['uses' => 'AffiliationApi@getAllData']);
    //user type
    $router->get('all_user_type_master', ['uses' => 'UserTypeApi@getAllData']);
    
    
    //campus create 
    $router->post('campus',         ['uses' => 'CampusApi@add']);
    $router->put('campus',          ['uses' => 'CampusApi@update']);
    $router->patch('campus',        ['uses' => 'CampusApi@patchDisableEnable']);
    $router->delete('campus/{id}',  ['uses' => 'CampusApi@delete']);
    $router->get('campus/{id}',     ['uses' => 'CampusApi@getOne']);
    $router->get('campus',          ['uses' => 'CampusApi@getAll']);
    $router->get('all_campus',      ['uses' => 'CampusApi@getCampus']);
    $router->get('all_campus_with_session_org',      ['uses' => 'CampusApi@getCampusWithSessionOrg']);
    
    
    //campus session
    $router->post('campus_session',         ['uses' => 'CampusSessionApi@add']);
    $router->put('campus_session',          ['uses' => 'CampusSessionApi@update']);
    $router->patch('campus_session',        ['uses' => 'CampusSessionApi@patchDisableEnable']);
    $router->delete('campus_session/{id}',  ['uses' => 'CampusSessionApi@delete']);
    $router->get('campus_session/{id}',     ['uses' => 'CampusSessionApi@getOne']);
    $router->get('campus_session',          ['uses' => 'CampusSessionApi@getAll']);
    $router->get('all_campus_session',      ['uses' => 'CampusSessionApi@getCampusSession']);

    //campus Seating Capacity
    $router->post('campus_seating_capacity',         ['uses' => 'CampusSeatingCapacityApi@add']);
    $router->put('campus_seating_capacity',          ['uses' => 'CampusSeatingCapacityApi@update']);
    $router->patch('campus_seating_capacity',        ['uses' => 'CampusSeatingCapacityApi@patchDisableEnable']);
    $router->delete('campus_seating_capacity/{id}',  ['uses' => 'CampusSeatingCapacityApi@delete']);
    $router->get('campus_seating_capacity/{id}',     ['uses' => 'CampusSeatingCapacityApi@getOne']);
    $router->get('campus_seating_capacity',          ['uses' => 'CampusSeatingCapacityApi@getAll']);

    //campus Student Registration
    $router->post('student_registration',         ['uses' => 'StudentRegistrationApi@add']);
    $router->put('student_registration',          ['uses' => 'StudentRegistrationApi@update']);
    $router->patch('student_registration',        ['uses' => 'StudentRegistrationApi@patchDisableEnable']);
    $router->delete('student_registration/{id}',  ['uses' => 'StudentRegistrationApi@delete']);
    $router->get('student_registration/{id}',     ['uses' => 'StudentRegistrationApi@getOne']);
    $router->get('getOnevalidation/{id}',         ['uses' => 'StudentRegistrationApi@getOnevalidation']);
    $router->get('student_registration',          ['uses' => 'StudentRegistrationApi@getAll']);
    $router->get('reg_note',                      ['uses' => 'StudentRegistrationApi@regNote']);
    $router->get('entry_test_reg_students',       ['uses' => 'StudentRegistrationApi@getStdWithInterviewTest']);
  
    
    $router->get('getSeatAlloted/{id}',     ['uses' => 'StudentRegistrationApi@getSeatAlloted']);

    $router->get('getInterviewMeritList',       ['uses' => 'StudentRegistrationApi@getInterviewMeritList']);
    $router->get('all_final_result_list',         ['uses' => 'StudentRegistrationApi@getFinalResultList']);
    $router->get('check_age_student',             ['uses' => 'StudentRegistrationApi@check_age_student']);
    $router->get('check_arrears',             ['uses' => 'StudentRegistrationApi@check_arrears']);
    
    $router->put('student_test_interview',        ['uses' => 'StudentRegistrationApi@updateStdTestInterview']);
    
    $router->put('student_seat_allot',        ['uses' => 'StudentRegistrationApi@updateStdSeatAllot']);
    
    
    //campus Student Profile
    $router->get('student_profile',          ['uses' => 'StudentProfileApi@getAll']);
    $router->get('student_profile/{id}',     ['uses' => 'StudentProfileApi@getOne']);
    $router->put('student_profile',          ['uses' => 'StudentProfileApi@update']);
    

    //grading_exam =====================
    $router->post('grading_exam',             ['uses' => 'GradingExamApi@add']);
    $router->put('grading_exam',              ['uses' => 'GradingExamApi@update']);
    $router->patch('grading_exam',            ['uses' => 'GradingExamApi@patchDisableEnable']);
    $router->delete('grading_exam/{id}',      ['uses' => 'GradingExamApi@delete']);
    $router->get('grading_exam/{id}',         ['uses' => 'GradingExamApi@getOne']);
    $router->get('grading_exam',              ['uses' => 'GradingExamApi@getAll']);
    $router->get('grading_type_list',         ['uses' => 'GradingExamApi@getGradeType']);
    $router->get('get_grading_remarks_list',  ['uses' => 'GradingExamApi@getGradeRemarks']);
    

     //exam_type =====================
     $router->post('exam_type',             ['uses' => 'ExamTypeApi@add']);
     $router->put('exam_type',              ['uses' => 'ExamTypeApi@update']);
     $router->patch('exam_type',            ['uses' => 'ExamTypeApi@patchDisableEnable']);
     $router->delete('exam_type/{id}',      ['uses' => 'ExamTypeApi@delete']);
     $router->get('exam_type/{id}',         ['uses' => 'ExamTypeApi@getOne']);
     $router->get('exam_type',              ['uses' => 'ExamTypeApi@getAll']);
     $router->get('exam_type_list',         ['uses' => 'ExamTypeApi@getExamType']);

     //exam_setup =====================
     $router->post('exam_setup',             ['uses' => 'ExamSetupApi@add']);
     $router->put('exam_setup',              ['uses' => 'ExamSetupApi@update']);
     $router->patch('exam_setup',            ['uses' => 'ExamSetupApi@patchDisableEnable']);
     $router->delete('exam_setup/{id}',      ['uses' => 'ExamSetupApi@delete']);
     $router->get('exam_setup/{id}',         ['uses' => 'ExamSetupApi@getOne']);
     $router->get('exam_setup',              ['uses' => 'ExamSetupApi@getAll']);


     //exam_subject =====================
     $router->post('exam_subject',             ['uses' => 'AssignExamSubjectApi@add']);
     $router->put('exam_subject',              ['uses' => 'AssignExamSubjectApi@update']);
     $router->patch('exam_subject',            ['uses' => 'AssignExamSubjectApi@patchDisableEnable']);
     $router->delete('exam_subject/{id}',      ['uses' => 'AssignExamSubjectApi@delete']);
     $router->get('exam_subject/{id}',         ['uses' => 'AssignExamSubjectApi@getOne']);
     $router->get('exam_subject',              ['uses' => 'AssignExamSubjectApi@getAll']);
     $router->get('exam_type_list_assign',     ['uses' => 'AssignExamSubjectApi@exam_type_list_assign']);
     $router->get('get_assign_exam_subject',     ['uses' => 'AssignExamSubjectApi@get_assign_exam_subject']);


     //exam_marks_register ==================================
     
     $router->get('get_register_marks_std',     ['uses' => 'ExamMarksRegisterApi@get_register_marks_std']);
     $router->put('get_register_marks_std',     ['uses' => 'ExamMarksRegisterApi@update']);

     //student_marks_register_signle
     $router->get('student_marks_register_signle',     ['uses' => 'ExamMarksRegisterApi@get_student_register_marks']);
     

     //assessment_category =====================
     $router->post('assessment_category',             ['uses' => 'AssessmentCategoryApi@add']);
     $router->put('assessment_category',              ['uses' => 'AssessmentCategoryApi@update']);
     $router->patch('assessment_category',            ['uses' => 'AssessmentCategoryApi@patchDisableEnable']);
     $router->delete('assessment_category/{id}',      ['uses' => 'AssessmentCategoryApi@delete']);
     $router->get('assessment_category/{id}',         ['uses' => 'AssessmentCategoryApi@getOne']);
     $router->get('assessment_category',              ['uses' => 'AssessmentCategoryApi@getAll']);
     $router->get('assessment_category_list',         ['uses' => 'AssessmentCategoryApi@getAssessmentCategory']);


     //assessment_type =====================
     $router->post('assessment_type',             ['uses' => 'AssessmentTypeApi@add']);
     $router->put('assessment_type',              ['uses' => 'AssessmentTypeApi@update']);
     $router->patch('assessment_type',            ['uses' => 'AssessmentTypeApi@patchDisableEnable']);
     $router->delete('assessment_type/{id}',      ['uses' => 'AssessmentTypeApi@delete']);
     $router->get('assessment_type/{id}',         ['uses' => 'AssessmentTypeApi@getOne']);
     $router->get('assessment_type',              ['uses' => 'AssessmentTypeApi@getAll']);
     $router->get('assessment_type_list',         ['uses' => 'AssessmentTypeApi@getAssessmentType']);

     
     //assessment_master =====================
     $router->post('assessment_master',             ['uses' => 'AssessmentMasterApi@add']);
     $router->put('assessment_master',              ['uses' => 'AssessmentMasterApi@update']);
     $router->patch('assessment_master',            ['uses' => 'AssessmentMasterApi@patchDisableEnable']);
     $router->delete('assessment_master/{id}',      ['uses' => 'AssessmentMasterApi@delete']);
     $router->get('assessment_master/{id}',         ['uses' => 'AssessmentMasterApi@getOne']);
     $router->get('assessment_master',              ['uses' => 'AssessmentMasterApi@getAll']);
     $router->get('assessment_master_list',         ['uses' => 'AssessmentMasterApi@getAssessmentMaster']);


     //assessment_setup =====================
     $router->post('assessment_setup',             ['uses' => 'AssessmentSetupApi@add']);
     $router->put('assessment_setup',              ['uses' => 'AssessmentSetupApi@update']);
     $router->patch('assessment_setup',            ['uses' => 'AssessmentSetupApi@patchDisableEnable']);
     $router->delete('assessment_setup/{id}',      ['uses' => 'AssessmentSetupApi@delete']);
     $router->get('assessment_setup/{id}',         ['uses' => 'AssessmentSetupApi@getOne']);
     $router->get('assessment_setup',              ['uses' => 'AssessmentSetupApi@getAll']);
     $router->get('assessment_setup_list',         ['uses' => 'AssessmentSetupApi@getAssessmentSetup']);

    

    // students admisison data 
    $router->post('student_admission',         ['uses' => 'StudentAdmissionApi@add']);
    $router->put('student_admission',          ['uses' => 'StudentAdmissionApi@update']);
    $router->patch('student_admission',        ['uses' => 'StudentAdmissionApi@patchDisableEnable']);
    $router->delete('student_admission/{id}',  ['uses' => 'StudentAdmissionApi@delete']);
    $router->get('student_admission/{id}',     ['uses' => 'StudentAdmissionApi@getOne']);
    $router->get('student_admission',          ['uses' => 'StudentAdmissionApi@getAll']);
    
    
    $router->post('student_monthly_fee',         ['uses' => 'GenMonthlyVoucherApi@add']);
    $router->post('student_advance_fee',         ['uses' => 'GenMonthlyVoucherApi@advanceFeeAdd']);


    $router->get('student_monthly_fee/{id}',                    ['uses' => 'GenMonthlyVoucherApi@getOne']);
    
    $router->get('student_generated_monthly_vouchers',          ['uses' => 'GenMonthlyVoucherApi@getAll']);
    $router->get('student_generated_advance_vouchers',          ['uses' => 'GenMonthlyVoucherApi@getAllAdvanceVoucher']);
    
    
    $router->get('student_generated_customize_vouchers',        ['uses' => 'GenMonthlyVoucherApi@getChallanCustomize']);
    $router->get('student_generated_vouchers_for_posting',      ['uses' => 'GenMonthlyVoucherApi@getChallanForPosting']);
    $router->post('student_generated_vouchers_for_posting',     ['uses' => 'GenMonthlyVoucherApi@monthlyChallanPosting']);
    $router->post('student_generated_vouchers_bulk_posting',    ['uses' => 'GenMonthlyVoucherApi@monthlyBulkChallanPosting']);
    $router->get('collection_file_uploaded',                    ['uses' => 'GenMonthlyVoucherApi@monthlyCollectionFileUploaded']);
    $router->post('monthly_bulk_challan_posting',               ['uses' => 'GenMonthlyVoucherApi@setMonthlyBulkChallanPosting']);
    
    
    
    

    // Admission Voucher Generate
    $router->post('gen_adm_voucher',         ['uses' => 'GenAdmissionVoucherApi@add']);
    $router->put('gen_adm_voucher',          ['uses' => 'GenAdmissionVoucherApi@update']);
    $router->patch('gen_adm_voucher',        ['uses' => 'GenAdmissionVoucherApi@patchDisableEnable']);
    $router->delete('gen_adm_voucher/{id}',  ['uses' => 'GenAdmissionVoucherApi@delete']);
    $router->get('gen_adm_voucher/{id}',     ['uses' => 'GenAdmissionVoucherApi@getOne']);
    $router->get('gen_adm_voucher',          ['uses' => 'GenAdmissionVoucherApi@getAll']);
    $router->get('get_month_list_by_session',['uses' => 'GenAdmissionVoucherApi@getMonth']);
    $router->get('get_bank_list',            ['uses'=> 'GenAdmissionVoucherApi@getBankByOrgID']);
    $router->get('reg_slip_master' ,         ['uses' => 'GenAdmissionVoucherApi@reg_slip_master_one']);


    // Admission Voucher Posting
    $router->post('adm_voucher_posting',        ['uses' => 'AdmissionVoucherPostingApi@add']);
    $router->get('adm_voucher_posting_search',  ['uses'=> 'AdmissionVoucherPostingApi@searchAdmVoucher']);
    $router->get('adm_voucher_posting',         ['uses'=> 'AdmissionVoucherPostingApi@getAll']);
    $router->get('adm_voucher_posting_view',    ['uses'=> 'AdmissionVoucherPostingApi@cardView']);



    // Student Transfer Request
    $router->post('student_transfer_request',      ['uses' => 'StudentTransferRequestApi@add']);
    $router->put('student_transfer_request',       ['uses' => 'StudentTransferRequestApi@update']);
    $router->delete('student_transfer_request/{id}',  ['uses' => 'StudentTransferRequestApi@delete']);
    $router->get('student_transfer_request/{id}',  ['uses' => 'StudentTransferRequestApi@getOne']);
    $router->get('student_transfer_request',       ['uses'=> 'StudentTransferRequestApi@getAll']);
    $router->get('get_state_list_org',             ['uses'=> 'StudentTransferRequestApi@get_state_list_org']);
    $router->get('get_region_list_org',            ['uses'=> 'StudentTransferRequestApi@get_region_list_org']);
    $router->get('get_campus_list_org_by_city',            ['uses'=> 'StudentTransferRequestApi@get_campus_list_org_by_city']);
    $router->get('get_city_list_org',              ['uses'=> 'StudentTransferRequestApi@get_city_list_org']);
    $router->get('get_campus_list_org',            ['uses'=> 'StudentTransferRequestApi@get_campus_list_org']);
    $router->get('get_campus_session_list_org',    ['uses'=> 'StudentTransferRequestApi@get_campus_session_list_org']);
    $router->get('get_campus_class_list_org',    ['uses'=> 'StudentTransferRequestApi@get_campus_class_list_org']);
   
    // Student Left Request
    $router->post('student_left_request',      ['uses' => 'StudentLeftRequestApi@add']);
    $router->put('student_left_request',       ['uses' => 'StudentLeftRequestApi@update']);
    $router->delete('student_left_request/{id}',  ['uses' => 'StudentLeftRequestApi@delete']);
    $router->get('student_left_request/{id}',  ['uses' => 'StudentLeftRequestApi@getOne']);
    $router->get('student_left_request',       ['uses'=> 'StudentLeftRequestApi@getAll']);
    
   
    //campus section
    $router->post('campus_section',         ['uses' => 'CampusSectionApi@add']);
    $router->put('campus_section',          ['uses' => 'CampusSectionApi@update']);
    $router->patch('campus_section',        ['uses' => 'CampusSectionApi@patchDisableEnable']);
    $router->delete('campus_section/{id}',  ['uses' => 'CampusSectionApi@delete']);
    $router->get('campus_section/{id}',     ['uses' => 'CampusSectionApi@getOne']);
    $router->get('campus_section',          ['uses' => 'CampusSectionApi@getAll']);
    $router->get('all_campus_section',      ['uses' => 'CampusSectionApi@getCampusSection']);
    $router->get('get_campus_section',      ['uses' => 'CampusSectionApi@get_campus_section']);
    $router->get('get_campus_student',      ['uses' => 'CampusSectionApi@get_campus_student']);
    $router->get('get_single_student',      ['uses' => 'CampusSectionApi@get_single_student']);
    $router->get('get_progress_list',      ['uses' => 'CampusSectionApi@get_progress_list']);

    $router->get('get_campus_student_by_section_id',      ['uses' => 'CampusSectionApi@get_campus_student_by_section_id']);
    
    
    
    //campus subject
    $router->post('campus_subject',         ['uses' => 'CampusSubjectApi@add']);
    $router->put('campus_subject',          ['uses' => 'CampusSubjectApi@update']);
    $router->patch('campus_subject',        ['uses' => 'CampusSubjectApi@patchDisableEnable']);
    $router->delete('campus_subject/{id}',  ['uses' => 'CampusSubjectApi@delete']);
    $router->get('campus_subject/{id}',     ['uses' => 'CampusSubjectApi@getOne']);
    $router->get('campus_subject',          ['uses' => 'CampusSubjectApi@getAll']);
    $router->get('all_campus_subject',      ['uses' => 'CampusSubjectApi@getCampusSubject']);

    //teacher_subject
    $router->post('teacher_subject',         ['uses' => 'TeacherSubjectApi@add']);
    $router->put('teacher_subject',          ['uses' => 'TeacherSubjectApi@update']);
    $router->patch('teacher_subject',        ['uses' => 'TeacherSubjectApi@patchDisableEnable']);
    $router->delete('teacher_subject/{id}',  ['uses' => 'TeacherSubjectApi@delete']);
    $router->get('teacher_subject/{id}',     ['uses' => 'TeacherSubjectApi@getOne']);
    $router->get('teacher_subject',          ['uses' => 'TeacherSubjectApi@getAll']);
    $router->get('all_teacher_subject',      ['uses' => 'TeacherSubjectApi@getTeacherSubAssign']);
    
      //teacher_time_table
      $router->post('teacher_time',         ['uses' => 'TeacherTimeTableApi@add']);
      $router->put('teacher_time',          ['uses' => 'TeacherTimeTableApi@update']);
      $router->patch('teacher_time',        ['uses' => 'TeacherTimeTableApi@patchDisableEnable']);
      $router->delete('teacher_time/{id}',  ['uses' => 'TeacherTimeTableApi@delete']);
      $router->get('teacher_time/{id}',     ['uses' => 'TeacherTimeTableApi@getOne']);
      $router->get('teacher_time',          ['uses' => 'TeacherTimeTableApi@getAll']);
      $router->get('all_teacher_time',      ['uses' => 'TeacherTimeTableApi@getTeacherSubAssign']);
      
    //campus fee structure
    $router->post('campus_fee',         ['uses' => 'CampusFeeApi@add']);
    $router->put('campus_fee',          ['uses' => 'CampusFeeApi@update']);
    $router->patch('campus_fee',        ['uses' => 'CampusFeeApi@patchDisableEnable']);
    $router->delete('campus_fee/{id}',  ['uses' => 'CampusFeeApi@delete']);
    $router->get('campus_fee/{id}',     ['uses' => 'CampusFeeApi@getOne']);
    $router->get('campus_fee',          ['uses' => 'CampusFeeApi@getAll']);

    //Fee structure master
    $router->post('feestructure',        ['uses' => 'FeeStructureMasterApi@add']);
    $router->put('feestructure',         ['uses' => 'FeeStructureMasterApi@update']);
    $router->patch('feestructure',       ['uses' => 'FeeStructureMasterApi@patchDisableEnable']);
    $router->delete('feestructure/{id}', ['uses' => 'FeeStructureMasterApi@delete']);
    $router->get('feestructure/{id}',    ['uses' => 'FeeStructureMasterApi@getOne']);
    $router->get('feestructure',         ['uses' => 'FeeStructureMasterApi@getAll']);
    $router->get('all_Fees_master',      ['uses' => 'FeeStructureMasterApi@getAllMaster']);

    //Fee structure details
    $router->post('feedetail',        ['uses' => 'FeeStructureDetailApi@add']);
    $router->put('feedetail',         ['uses' => 'FeeStructureDetailApi@update']);
    $router->patch('feedetail',       ['uses' => 'FeeStructureDetailApi@patchDisableEnable']);
    $router->delete('feedetail/{id}', ['uses' => 'FeeStructureDetailApi@delete']);
    $router->get('feedetail/{id}',    ['uses' => 'FeeStructureDetailApi@getOne']);
    $router->get('feedetail',         ['uses' => 'FeeStructureDetailApi@getAll']);
    $router->get('all_feedetail_master',['uses' => 'FeeStructureDetailApi@getAllMaster']);
    
     //discount_policy
     $router->post('discount_policy',         ['uses' => 'DiscountPolicyApi@add']);
     $router->put('discount_policy',          ['uses' => 'DiscountPolicyApi@update']);
     $router->patch('discount_policy',        ['uses' => 'DiscountPolicyApi@patchDisableEnable']);
     $router->delete('discount_policy/{id}',  ['uses' => 'DiscountPolicyApi@delete']);
     $router->get('discount_policy/{id}',     ['uses' => 'DiscountPolicyApi@getOne']);
     $router->get('discount_policy',          ['uses' => 'DiscountPolicyApi@getAll']);
     $router->get('all_discount_policy',      ['uses' => 'DiscountPolicyApi@getDiscountPolicy']);
     
     //Assign_discount_policy
     $router->post('assign_discount_policy',         ['uses' => 'AssignDiscountPolicyApi@add']);
     $router->put('assign_discount_policy',          ['uses' => 'AssignDiscountPolicyApi@update']);
     $router->patch('assign_discount_policy',        ['uses' => 'AssignDiscountPolicyApi@patchDisableEnable']);
     $router->delete('assign_discount_policy/{id}',  ['uses' => 'AssignDiscountPolicyApi@delete']);
     $router->get('assign_discount_policy/{id}',     ['uses' => 'AssignDiscountPolicyApi@getOne']);
     $router->get('assign_discount_policy',          ['uses' => 'AssignDiscountPolicyApi@getAll']);
     $router->get('all_assign_discount_policy',      ['uses' => 'AssignDiscountPolicyApi@getAllMaster']);
    
     //Assign_fee_structure
     $router->post('assign_fee_structure',         ['uses' => 'AssignFeeStructureApi@add']);
     $router->put('assign_fee_structure',          ['uses' => 'AssignFeeStructureApi@update']);
     $router->patch('assign_fee_structure',        ['uses' => 'AssignFeeStructureApi@patchDisableEnable']);
     $router->delete('assign_fee_structure/{id}',  ['uses' => 'AssignFeeStructureApi@delete']);
     $router->get('assign_fee_structure/{id}',     ['uses' => 'AssignFeeStructureApi@getOne']);
     $router->get('assign_fee_structure',          ['uses' => 'AssignFeeStructureApi@getAll']);
     $router->get('all_assign_fee_structure',      ['uses' => 'AssignFeeStructureApi@getAllMaster']);


     //slip_setup
     $router->post('slip_setup',         ['uses' => 'SlipSetupApi@add']);
     $router->put('slip_setup',          ['uses' => 'SlipSetupApi@update']);
     $router->patch('slip_setup',        ['uses' => 'SlipSetupApi@patchDisableEnable']);
     $router->delete('slip_setup/{id}',  ['uses' => 'SlipSetupApi@delete']);
     $router->get('slip_setup/{id}',     ['uses' => 'SlipSetupApi@getOne']);
     $router->get('slip_setup',          ['uses' => 'SlipSetupApi@getAll']);
     $router->get('all_slip_setup',      ['uses' => 'SlipSetupApi@getTeacherSubAssign']);
       

     
    $router->get('one_session',    ['uses' => 'CampusSessionApi@getCampusSessionOne']);

    /////////////////////////////////////////Merge Transfer////////////////////////////////////////
});
