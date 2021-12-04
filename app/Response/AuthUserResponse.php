<?php
namespace App\Response;

class AuthUserResponse
{

    private $users;

    /**
     *
     * @return array
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     *
     * @param array $users
     */
    public function setUsers($users)
    {
        $this->users = $users;
    }
}
