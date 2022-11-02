<?php

namespace WebToApp\User;

class Token
{
    public static function get_user_access_token($user_id, $force_new = false)
    {
        $access_token = get_user_meta($user_id, 'wta_wc_access_token', true);
        if (empty($access_token) || $force_new) {
            $access_token = 'token_' . wc_rand_hash();
            update_user_meta($user_id, 'wta_wc_access_token', $access_token);
        }

        return $access_token;
    }
}
