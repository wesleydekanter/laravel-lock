<?php

namespace WesleyDeKanter\LaravelLock;

use Illuminate\Support\Facades\Facade;

class LockFacade extends Facade
{
    /**
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return LockManager::class;
    }
}