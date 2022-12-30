<?php

namespace WebToApp;
use WebToApp\Traits\Singleton;

class User
{
    use Singleton;
    public function __construct()
    {
        new User\Token();
        //new User\Activate_Plugin();
    }
}
