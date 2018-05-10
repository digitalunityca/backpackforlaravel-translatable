<?php

namespace DigitalUnity\Translatable;

use Illuminate\Support\Facades\Facade;

/**
 * @see \DigitalUnity\Translatable\TranslatableClass
 */
class TranslatableFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'translatable';
    }
}
