<?php

namespace MS\Wopi;

use Illuminate\Support\Facades\Facade;

/**
 * @see \MS\Wopi\
 */
class WopiFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Wopi::class;
    }
}
