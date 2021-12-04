<?php
namespace App\Response;

class CountryResponse
{

    private $countries;

    /**
     *
     * @return array
     */
    public function getCountries()
    {
        return $this->countries;
    }

    /**
     *
     * @param array $countries
     */
    public function setCountries($countries)
    {
        $this->countries = $countries;
    }
}
