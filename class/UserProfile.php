<?php

class UserProfile
{
    private static $static_picture_meta_key = 'moving_forward_profile_picture';
    private $profile_picture_meta_key = 'moving_forward_profile_picture';


    public static function moving_forward_handle_user_picture ()
    {
        register_rest_route('wp/v2', 'users/(?P<id>\d+)/picture(?:/(?P<picture_id>\d+))?', array(
        'methods' => ['GET', 'POST'],
        'callback' => function ($request) {
                    self::moving_forward_handle_user_picture_request($request);
                },
        "permission_callback" => '__return_true'
        ));
    }


    public static function moving_forward_handle_user_picture_request(WP_REST_Request $request)
    {
        $user_id = (int)$request['id'];
        $picture_id = (int)$request['picture_id'];
        $method = $request->get_method();
        $srcset = null;
        $response = [];

        //var_dump($user_id, $picture_id); die;
        if ($method === 'POST') {
            self::update_user_profile_field($user_id, $picture_id);
            $srcset = self::getProfileSrcset($user_id);
        } else if ($method === 'GET') {
            $srcset = self::getProfileSrcset($user_id);
        }
        
        $response['code'] = 200;
        $response['success'] = true;
        $response['message'] = __("item_created", "wp-rest-user");
        $response['data'] = json_encode($srcset);
        wp_send_json_success($srcset, 201);
        return;
    }


    /**
     * Update the user meta when user save
     *
     * @since  1.0
     * @return bool
     */
    public static function update_user_profile_field( $user_id, $file_id ) {
        // If user don't have permissions
        //if ( !current_user_can( 'edit_user', $user_id ) ) {
        //    return false;
        //}

        // Delete old user meta
        delete_user_meta( $user_id, self::$static_picture_meta_key );

        // Then add the new one
        add_user_meta( $user_id, self::$static_picture_meta_key, $file_id );
        
        return true;
    }


    /**
     * Delete user meta when attachment is deleted
     *
     * @since  3.9
     * @return void
     */
    public function custom_delete_attachment( $post_id ) {

        global $wpdb;

        // Delete all user meta where deleted attachment post ID exists
        $wpdb->delete(
            $wpdb->usermeta,
            [
                'meta_key'   => $this->profile_picture_meta_key,
                'meta_value' => (int)$post_id
            ],
            [
                '%s',
                '%d'
            ]
        );

    }



    /**
     * Override of the original WordPress function get_avatar();
     *
     * @since  1.0
     * @return string
     */
    public static function getProfileSrcset( $id_or_email, $avatar = null, $size = null, $default = null, $alt = null ) {

        // Get user ID, if is numeric
        if ( is_numeric($id_or_email) ) {

            $user_id = (int)$id_or_email;

        // If is string, maybe the user email
        } elseif ( is_string($id_or_email) ) {

            // Find user by email
            $user = get_user_by( 'email', $id_or_email );
            
            // If user doesn't exists or this is not an ID
            if ( !isset($user->ID) || !is_numeric($user->ID) ) {
                return $avatar;
            }

            $user_id = (int)$user->ID;

        // If is an object
        } elseif ( is_object($id_or_email) ) {

            // If this is not an ID
            if ( !isset($id_or_email->ID) || !is_numeric($id_or_email->ID) ) {
                return $avatar;
            }

            $user_id = (int)$id_or_email->ID;

        }
        // Get attachment ID from user meta
        $attachment_id = get_user_meta( $user_id, self::$static_picture_meta_key, true );
        if ( empty($attachment_id) || !is_numeric($attachment_id) ) {
            return $avatar;
        }

        
        // Get attachment image src
        $attachment_src = wp_get_attachment_image_src( $attachment_id, 'medium' );
        
        
        // Override WordPress src
        if ( $attachment_src !== false ) {
            $avatar = preg_replace( '/src=("|\').*?("|\')/', "src='{$attachment_src[0]}'", $avatar );
        }
        
        // Get attachment image srcset
        $attachment_srcset = wp_get_attachment_image_srcset( $attachment_id );

        // Override WordPress srcset
        if( $attachment_srcset !== false ) {
            $avatar = preg_replace( '/srcset=("|\').*?("|\')/', "srcset='{$attachment_srcset}'", $avatar );
        }

        $separatedSrcset = explode(', ', $attachment_srcset);
        $parsedSrcSet = [];

        foreach ($separatedSrcset as $link) {
            $exploded = explode(' ', $link);
            $parsedSrcSet[] = [
                "link" => $exploded[0],
                "width" => $exploded[1]
            ];
        }
        

        return $parsedSrcSet;
    }

    
}