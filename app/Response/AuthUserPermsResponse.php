<?php
namespace App\Response;

class AuthUserPermsResponse
{

    private $userPerms;

    /**
     *
     * @return array
     */
    public function getUserPerms()
    {
        return $this->userPerms;
    }

    /**
     *
     * @param array $userPerms
     */
    public function setUserPerms($userPerms)
    {
        $this->userPerms = $userPerms;
    }
}
