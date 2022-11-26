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
		Admin\Package::get_instance();
		Admin\Backend::get_instance();
	}

}