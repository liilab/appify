<?php
namespace WebToApp;

use WebToApp\Traits\Singleton;

/**
 * Class Admin
 * @package WebToApp
 */

class Admin
{
    use Singleton;

	public function __construct()
	{
		Admin\Admin_Page::get_instance();
	}

}