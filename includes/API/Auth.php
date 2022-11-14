<?php

namespace WebToApp\API;

/**
 * Class Auth
 * @package WebToApp\API
 */

class Auth extends \WP_REST_Controller
{

    /**
     * Endpoint namespace.
     *
     * @var string
     */
    protected $namespace = 'web-to-app/v1';

    /**
     * Route base.
     *
     * @var string
     */
    protected $rest_base = 'auth';


    /**
     * Register the routes for products.
     */
    public function register_routes()
    {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/signin',
            array(
                array(
                    'methods'             => \WP_REST_Server::CREATABLE,
                    'callback'            => array($this, 'auth_check'),
                ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/register',
            array(
                array(
                    'methods'             => \WP_REST_Server::CREATABLE,
                    'callback'            => array($this, 'auth_create'),
                ),
            )
        );
    }

    public function auth_create($request)
    {
        $username = $request['username'];
        $password = $request['password'];

        wp_create_user($username, $password);

        return new \WP_REST_Response(array('status' => 'ok'), 200);
    }


    /**
     * Auth.
     *
     * @param \WP_REST_Request $request Full data about the request.
     *
     * @return \WP_REST_Response
     */
    public function auth_check($request)
    {
		$params = $request->get_params();

		$username = $params['username'];
		$password = $params['password'];

		$user = wp_authenticate($username, $password);

		if (is_wp_error($user)) {
			return new \WP_REST_Response(array(
				'status' => 'error',
				'message' => 'Invalid username or password',
			), 401);
		}

		$token = \WebToApp\User\Token::get_user_access_token($user->ID);

		$data = [
			'status' => '1',
			'access_token' => $token,
			'user_id' => $user->ID,
			'user' => [
				'id' => $user->ID,
				'nicename' => $user->user_nicename,
				'email' => $user->user_email,
				'status' => $user->user_status,
				'display_name' => $user->display_name,
				'avatar' => get_avatar_url($user->ID),
			],
		];

		return new \WP_REST_Response($data, 200);
    }

    /**
     * Check API permissions.
     *
     * @param \WP_REST_Request $request Full data about the request.
     *
     * @return bool|\WP_Error
     */

    public function api_permissions_check($request)
    {
        if (current_user_can('manage_options')) {
            return true;
        }

        return true;
    }
}
