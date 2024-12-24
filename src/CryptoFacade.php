<?php

namespace Jacknguyen\Crypto;

use Illuminate\Support\Facades\Facade;

class CryptoFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'crypto';
    }
}
