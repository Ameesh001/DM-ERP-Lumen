<?php
namespace App\Response;

class RolePermissionResponse
{

    private $rolePermission;

    /**
     *
     * @return array
     */
    public function getRolePermission()
    {
        $permissionData = [];
        
        foreach ($this->rolePermission as $key => $value) {
            /*if(!isset($permissionData['client_id'][$value['client_id']])){
                $permissionData['client_id'] = [$value['client_id'] => []];
            }
            
            if(!isset($permissionData['client_id'][$value['client_id']]['role_id'][$value['role_id']])){
                $permissionData['client_id'][$value['client_id']]['role_id'] = [$value['role_id'] => ['permissions' => []]];
            }
            
            if(!isset($permissionData['client_id'][$value['client_id']]['role_id'][$value['role_id']])){
                $permissionData['client_id'][$value['client_id']]['role_id'] = [$value['role_id'] => ['permissions' => []]];
            }
            
            $permissionData['client_id'][$value['client_id']]['role_id'][$value['role_id']]['permissions'] = [
                'route' => $value['route'],
                'action' => $value['route']
            ];*/
        }

        return $this->rolePermission;
    }

    /**
     *
     * @param array $rolePermission
     */
    public function setRolePermission($rolePermission)
    {
        $this->rolePermission = $rolePermission;
    }
}
