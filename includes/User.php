<?php

namespace WebToApp;
use WebToApp\traits\Singleton;

class User
{
    use Singleton;
    public function __construct()
    {
        new User\Token();
    }
}
