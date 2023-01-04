<?php

namespace WebToApp;
use WebToApp\Traits\Singleton;

/**
 * Class User
 * @package Appify
 */

class User
{
    use Singleton;
    public function __construct()
    {
        new User\Token();
    }
}
