<?php
namespace App\Response;

class StateResponse
{

    private $state;

    /**
     *
     * @return array
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     *
     * @param array $countries
     */
    public function setState($state)
    {
        $this->state = $state;
    }
}
