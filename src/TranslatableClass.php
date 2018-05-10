<?php

namespace DigitalUnity\Translatable;

class TranslatableClass
{
    const PATTERN = '/^\w{1,}__\w{2}$/';
    /**
     * Create a new Skeleton Instance.
     */
    public function __construct()
    {
        // constructor body
    }

    /**
     * Get active languages
     * @param $withAbbr, if is true return [locale_id => abbr] array, if false - only [locale_id]
     * @return bool
     */
    public function isLocalizedInput(string $inputName): bool
    {
        return preg_match(self::PATTERN, $inputName);
    }

    /**
     *
     * @param string $field
     * @return array
     */
    public function getLocalePatternMatches(string $field):array
    {
        preg_match(self::PATTERN, $field, $matches);
        return $matches;
    }

}
