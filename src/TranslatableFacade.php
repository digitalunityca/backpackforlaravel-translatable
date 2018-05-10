<?php

namespace Spatie\Skeleton;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Spatie\Skeleton\TranslatableClass
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
