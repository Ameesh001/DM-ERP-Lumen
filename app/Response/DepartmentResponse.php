<?php
namespace App\Response;

class DepartmentResponse
{

    private $departments;

    /**
     *
     * @return array
     */
    public function getDepartments()
    {
        return $this->departments;
    }

    /**
     *
     * @param array $departments
     */
    public function setDepartments($departments)
    {
        $this->departments = $departments;
    }
}
