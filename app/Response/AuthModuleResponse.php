<?php
namespace App\Response;

class AuthModuleResponse
{

    private $modules;

    /**
     *
     * @return array
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     *
     * @param array $modules
     */
    public function setModules($modules)
    {
        $this->modules = $modules;
    }
}
