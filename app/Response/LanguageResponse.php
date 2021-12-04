<?php
namespace App\Response;

class LanguageResponse
{

    private $languages;

    /**
     *
     * @return array
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     *
     * @param array $languages
     */
    public function setLanguages($languages)
    {
        $this->languages = $languages;
    }
}
