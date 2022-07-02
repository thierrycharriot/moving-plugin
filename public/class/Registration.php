<?php

/**
 * This class create and handles a new API route made for registration
 * 
 * @author Anthony Perrier <perrier_anthony@live.fr>
 * @since 0.1.0
 */
class Registration 
{
    /**
     * Create a new API route
     */
    public static function moving_forward_handle_registration ()
    {
        register_rest_field( 'user', 'user_email',
            array(
                'get_callback'    => function ( $user ) {
                    return get_userdata($user['id'])->user_email;
                },
                'update_callback' => null,
                'schema'          => null,
            )
        );

        register_rest_field( 'user', 'role',
            array(
                'get_callback'    => function ( $user ) {
                    return get_userdata($user['id'])->roles;
                },
                'update_callback' => null,
                'schema'          => null,
            )
        );

        // Definit une nouvelle route pour notre inscription d'utilisateur
        register_rest_route('wp/v2', 'users/register', array(
            'methods' => 'POST',
            'callback' => function ($request) {
                        self::moving_forward_handle_registration_form($request);
                    },
            "permission_callback" => '__return_true'
        ));
	}


    /**
	 * Handle the registration from the API call
	 * @author <perrier_anthony@live.fr>
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	private static function moving_forward_handle_registration_form($request)
	{
        header("Access-Control-Allow-Origin: *");
        // Prepation de la réponse HTTP
        $response = array();
            
        $authorized_roles = [
            'classicUser'
        ];
        // Recuperation du formulaire sous un format JSON
        $parameters = $request->get_json_params();
        $username = sanitize_text_field($parameters['username']);
        $email = sanitize_text_field($parameters['email']);
        $password = sanitize_text_field($parameters['password']);
        $role = 'classicUser';
        // Préparation des erreurs en cas de non validation des données
        $error = new \WP_Error();
        // Verification du contenu du formulaire
        if (empty($username)) {
            $error->add(400, __("empty_username", 'wp-rest-user'), array('status' => 400));
            wp_send_json_error($error, 400);
            return;
        }
        if (empty($email)) {
            $error->add(400, __("empty_email", 'wp-rest-user'), array('status' => 400));
            wp_send_json_error($error, 400);
            return;
        }
        if (empty($password)) {
            $error->add(400, __("empty_password", 'wp-rest-user'), array('status' => 400));
            wp_send_json_error($error, 400);
            return;
        }

        // Verification qu'un utilisateur avec le même username n'existe pas.
        $user_id = username_exists($username);

        if ($user_id) {
            $error->add(400, __("username_exists", 'wp-rest-user'), array('status' => 400));
            wp_send_json_error($error, 400);
            return;
        }

        // Idem avec le mail
        if (email_exists($email)) {
            $error->add(400, __("email_exists", 'wp-rest-user'), array('status' => 400));
            wp_send_json_error($error, 400);
            return;
        }

        $user_id = wp_create_user($username, $password, $email);
        wp_insert_post([
            "post_title"    => "Bonjour, moi c'est $username",
            "post_content"  => "Je suis nouvellement inscrit sur Moving Forward !",
            "post_author"   => $user_id,
            "post_type"     => 'post',
            "post_status"   => 'publish'
        ]);

        if (!is_wp_error($user_id)) {
            // Recuperation de l'objet user
            $user = get_user_by('id', $user_id);
            $user->set_role($role);

            $response['success'] = true;
            $response['code'] = 201;
            $response['message'] = __("username_created", "wp-rest-user");
        }
        wp_send_json_success($response, 201);
        return;
	}
}