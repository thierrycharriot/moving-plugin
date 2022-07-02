<?php
require_once plugin_dir_path(__FILE__) . DIRECTORY_SEPARATOR . 'Itinerary.php';

/**
 * @author Anthony Perrier <perrier_anthony@live.fr>
 * @since 0.4.0
 */
class Delivery 
{
    private static $table_name = 'wp_moving_forward_user_delivery';

    /**
     * Handles the creation in DB of the table
     *
     * @return void
     */
    public static function moving_forward_create_user_delivery_table ()
    {
        global $wpdb;

        $charset = $wpdb->collate;

        $sql = "CREATE TABLE " . self::$table_name . " (
                `id` mediumint NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `customer_id` int NOT NULL,
                `provider_id` int NOT NULL,
                `delivery_id` int NOT NULL,
                `initiated_at` datetime NOT NULL,
                `has_customer_validated` boolean DEFAULT false,
                `has_provider_validated` boolean DEFAULT false,
                `status` varchar(255) DEFAULT 'ongoing'
                ) COLLATE '" . $charset . "';";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }


    /**
     * Handles the removal from DB of the table
     *
     * @return void
     */
    public static function moving_forward_drop_user_delivery_table ()
    {
        global $wpdb;
    
        $wpdb->query("DROP TABLE IF EXISTS " . self::$table_name);
    }


    /**
     * Register two new REST routes to retrieve all messages where a user is sollicited
     * or to handle the research of a specific delivery
     *
     * @return void
     */
    public static function moving_forward_handle_delivery ()
    {
        // This route handles CRUD considering a user
        register_rest_route('wp/v2', 'users/(?P<id>\d+)/deliveries(?:/(?P<delivery_id>\d+))?(?:/attachment/(?P<attachment_id>\d+))?', array(
        'methods' => ['GET', 'POST', 'PUT', 'DELETE'],
        'callback' => function ($request) {
                    self::moving_forward_handle_delivery_request($request);
                },
        "permission_callback" => '__return_true'
        ));


        // This route handles research of deliveries
        register_rest_route('wp/v2', 'deliveries/research', array(
            'methods' => 'GET',
            'callback' => function ($request) {
                        self::moving_forward_handle_delivery_research_request($request);
                    },
            "permission_callback" => '__return_true'
            ));
    }



    /**
     * Handles all the logic of CRUD for a delivery, associated to a user
     *
     * @param WP_REST_Request $request
     * @return void
     */
    private static function moving_forward_handle_delivery_request (WP_REST_Request $request): void
    {
        header("Access-Control-Allow-Origin: *");

        // On doit s'assurer qu'on possède bien le rôle Provider
        
        $user_id = (int)$request['id'];
        $delivery_id = (int)$request['delivery_id'];
        $attachment_id = (int)$request['attachment_id'];

        $method = $request->get_method();
        $data = json_decode($request->get_body(), true);
        $deliveries = [];
        $response = [];

        
        if ($method === 'GET') {
            if ($delivery_id > 0) {
                $delivery = get_post($delivery_id);
                if ((int)$delivery->post_author !== $user_id) {
                    $error = new \WP_Error();
                    $error->add(404, __("not_found", 'wp-rest-user'), array('status' => 404));
                    wp_send_json_error($error, 404);
                    return;
                }

                $response['success'] = true;
                $response['code'] = 201;
                $response['message'] = __("item_created", "wp-rest-user");
                $response['data'] = json_encode($delivery);
                wp_send_json_success($delivery, 201);
            } else {
                $deliveries = self::findAllDeliveriesByUser($user_id);
                $response['success'] = true;
                $response['code'] = 201;
                $response['message'] = __("item_fetched", "wp-rest-user");
                $response['data'] = json_encode($deliveries);
                wp_send_json_success($deliveries, 201);
            }
        } else if ($method === 'POST') {
            $delivery = self::createDelivery($user_id, $data);
            $response['success'] = true;
            $response['code'] = 201;
            $response['message'] = __("item_created", "wp-rest-user");
            $response['data'] = json_encode($delivery);
            wp_send_json_success($delivery, 201);
            return;
        } else if ($method === 'PUT' && $delivery_id > 0) {
            if ($attachment_id) {
                $delivery = self::attachPicture($user_id, $delivery_id, $attachment_id);
            } else {
                $delivery = self::editDelivery($user_id, $data, $delivery_id);
                $response['success'] = true;
                $response['code'] = 200;
                $response['message'] = __("item_created", "wp-rest-user");
                $response['data'] = json_encode($delivery);
                wp_send_json_success($delivery, 200);
            }
            return;
        } else if ($method === 'DELETE' && $delivery_id > 0) {
            self::deleteDelivery($user_id, $data, $delivery_id);
            $response['success'] = true;
            $response['code'] = 200;
            $response['message'] = __("item_deleted", "wp-rest-user");
            wp_send_json_success($response, 200);
            return;
        }
    }


    /**
     * @param integer $user_id
     * @return array $deliveries
     */
    public static function findAllDeliveriesByUser (int $user_id): array
    {
        $deliveries = get_posts([
            'author' => $user_id,
            'post_type' => 'delivery',
            'posts_per_page' => 1000
        ]);

        // As get_posts() doesn't return the attachment, we find it then
        // adding it to the current $delivery
        foreach ($deliveries as $delivery) {
            $attachment_id = get_post_thumbnail_id($delivery);
            // At this moment all the srcset is returned inside a long string
            $attachmentRawSrcSet = wp_get_attachment_image_srcset($attachment_id);

            $separatedSrcset = explode(', ', $attachmentRawSrcSet);
            $parsedSrcSet = [];

            foreach ($separatedSrcset as $link) {
                $exploded = explode(' ', $link);
                $parsedSrcSet[] = [
                    "link" => $exploded[0],
                    "width" => $exploded[1]
                ];
            }
            
            $delivery->pictures = $parsedSrcSet;
        }

        return $deliveries;
    }
  

    /**
     * Insert a delivery into the database
     * @param integer $user_id
     * @param array $data
     * @return WP_Post
     */
    public static function createDelivery (int $user_id, $data): WP_Post
    {
        $id = wp_insert_post(array(
            'post_type'     => 'delivery', 
            'post_title'    => $data['post_title'], 
            'post_content'  => $data['post_content'],
            'post_author'   => $user_id,
            'post_status'   => 'publish'
          ));

        return get_post($id);
    }


    /**
     * Edit a delivery from the database
     * @param integer $user_id
     * @param integer $delivery_id
     * @return WP_Post|void
     */
    public static function editDelivery(int $user_id, $data, int $delivery_id)
    {
        $delivery = get_post($delivery_id);
       
        if ((int)$delivery->post_author !== $user_id) {
            $error = new \WP_Error();
            $error->add(401, __("unauthorized", 'wp-rest-user'), array('status' => 401));
            wp_send_json_error($error, 400);
            return;
        }

        $id = wp_update_post([
            'ID'            => $delivery_id,
            'post_author'   => $user_id,
            'post_title'    => $data['post_title'],
            'post_content'  => $data['post_content']
        ]);

        return get_post($id);
    }


    /**
     * Remove a delivery from the database
     * @param integer $user_id
     * @param integer $delivery_id
     * @return void
     */
    public static function deleteDelivery(int $user_id, $data, int $delivery_id): void
    {
        $delivery = get_post($delivery_id);
        if ((int)$delivery->post_author !== $user_id) {
            $error = new \WP_Error();
            $error->add(401, __("unauthorized", 'wp-rest-user'), array('status' => 401));
            wp_send_json_error($error, 401);
            return;
        }

        wp_delete_post($delivery_id);
    }



    /**
     * Attach a _thumbnail_id meta between the current delivery and the current attachment
     *
     * @param integer $user_id
     * @param integer $delivery_id
     * @param integer $attachment_id
     * @return void
     */
    public static function attachPicture(int $user_id, int $delivery_id, int $attachment_id)
    {
        $isAttached = set_post_thumbnail($delivery_id, $attachment_id);
        if ($isAttached) {
            $delivery = get_post($delivery_id); 
            $stringSrcSet = wp_get_attachment_image_srcset($attachment_id); 
            $separatedSrcset = explode(', ', $stringSrcSet);
            $parsedSrcSet = [];
    
            foreach ($separatedSrcset as $link) {
                $exploded = explode(' ', $link);
                $parsedSrcSet[] = [
                    "link" => $exploded[0],
                    "width" => $exploded[1]
                ];
            }
            
            $delivery->pictures = $parsedSrcSet; 
            
            wp_send_json_success($delivery);
            return;
        }
    }




    /**
     * Handles all the logic of the research of deliveries,
     * according to parameters listed in the query then
     * returning back through ajax
     *
     * @param WP_REST_Request $request
     * @return void
     */
    private static function moving_forward_handle_delivery_research_request (WP_REST_Request $request)
    {
        $deliveryNameLike = strtolower($request->get_query_params()['delivery']);
        $around = strtolower($request->get_query_params()['around']);

        $deliveriesMatchingName = [];
        $deliveriesMatching = [];

        $allDeliveries = get_posts([
            'post_type' => 'delivery',
            'posts_per_page' => 99999999999999999999
        ]);

        
        // We start by filtering all deliveries by the type queried by the user
        foreach ($allDeliveries as $delivery) {
            /**
             * We transform both comparaison objects to the same appearance
             */
             if (
            str_contains(
                    strtolower(str_replace('-', ' ', $delivery->post_name)),  
                    urldecode(str_replace('-', ' ', sanitize_title($deliveryNameLike)))
                )
            ) {
                $deliveriesMatchingName[] = $delivery;
            }
        }

        
        // Then, we research if last matching deliveries have their Provider
        // passing through $around destination in their itineraries
        foreach ($deliveriesMatchingName as $delivery) {
            // Insert the filter logic here.
            if (self::isMatchingPlace($delivery, $around)) {
                $attachment_id = get_post_thumbnail_id($delivery);
                // At this moment all the srcset is returned inside a long string
                $attachmentRawSrcSet = wp_get_attachment_image_srcset($attachment_id);

                $separatedSrcset = explode(', ', $attachmentRawSrcSet);
                $parsedSrcSet = [];

                foreach ($separatedSrcset as $link) {
                    $exploded = explode(' ', $link);
                    $parsedSrcSet[] = [
                        "link" => $exploded[0],
                        "width" => $exploded[1]
                    ];
                }
                
                $delivery->pictures = $parsedSrcSet;
                array_push($deliveriesMatching, $delivery);
            }
        }

        $response['success'] = true;
        $response['code'] = 200;
        $response['message'] = __("item_fetched", "wp-rest-user");
        $response['data'] = json_encode($deliveriesMatching);
        wp_send_json_success($deliveriesMatching, 200);
        return;
    }


    /**
     * Searching through delivery if it's creator will passing
     * by the place the user requested. If yes, then return true
     * else false
     *
     * @param object $delivery
     * @param string $place
     * @return boolean
     */
    private static function isMatchingPlace ($delivery, $place) 
    {
        $author = (int)$delivery->post_author;
        $author_itineraries = Itinerary::findAll($author);
        $matching = false;

        foreach ($author_itineraries as $itinerary) {
            $checkpoints = json_decode($itinerary->checkpoints, true);
            foreach ($checkpoints as $checkpoint) {
                
                if (str_contains($place, strtolower(str_replace('-', ' ', $checkpoint['city'])))) {
                    $matching = true;
                }
            }
        }

        return $matching;
    }
}