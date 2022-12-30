<?php

namespace WebToApp\User;

class Token
{

    public function __construct()
    {
        $this->create_all_users_token();
        add_action( 'user_register', [$this,'wta_registration_save']);
    }

    public function wta_registration_save( $user_id ) {
        $this->create_user_token($user_id);
    }

    private function create_all_users_token(){
        $users = get_users();
        foreach ($users as $user){
            $this->create_user_token($user->ID);
        }
    }

    private function create_user_token($user_id, $force_new = false)
    {
        $access_token = get_user_meta($user_id, 'wta_wc_access_token', true);
        if (empty($access_token) || $force_new) {
            $access_token = 'token_' . wc_rand_hash();
            update_user_meta($user_id, 'wta_wc_access_token', $access_token);
        }
    }

    public static function get_user_access_token($user_id)
    {
        $access_token = get_user_meta($user_id, 'wta_wc_access_token', true);
        return $access_token;
    }

    public static function get_user_id_by_token($token)
    {
        $user_id = get_users(array(
            'meta_key' => 'wta_wc_access_token',
            'meta_value' => $token,
            'fields' => 'ID'
        ));
        return empty($user_id) ? null : $user_id[0];
    }
}
