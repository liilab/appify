<?php

namespace WebToApp;

use WebToApp\Traits\Singleton;

/**
 * Class Admin
 * @package Appify
 */

class Admin
{
	use Singleton;

	public function __construct()
	{
		$this->app_building_methods();
		$this->pages();
		$this->packages();
	}

	public function packages(){
		Admin\Package::get_instance();
	}

	public function pages(){
		Admin\Pages\Admin_Page::get_instance();
	}

	public function app_building_methods(){
		Admin\App_Building_Requests\Create_Request::get_instance();
		Admin\App_Building_Requests\Get_History::get_instance();
		Admin\App_Building_Requests\Get_History_Card::get_instance();
		Admin\App_Building_Requests\Get_Progress::get_instance();
		Admin\App_Building_Requests\Plugin_Activation_Info_Share::get_instance();
	}
}
