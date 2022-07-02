<?php

class UserDelivery
{
    public $id;
    public $customer_id;
    public $provider_id;
    public $delivery_id;
    public $has_customer_validated;
    public $has_provider_validated;
    public $status;
    public $conversation;
    public static $wp_table = 'wp_moving_forward_user_delivery';


    public static function moving_forward_handle_user_delivery_crud ()
    {
        register_rest_route('wp/v2', 'user_deliveries(?:/(?P<id>\d+))?(?:/(?P<delivery_id>\d+))?', array(
        'methods' => ['GET', 'POST'],
        'callback' => function ($request) {
                    self::moving_forward_handle_user_delivery_crud_request($request);
                },
        "permission_callback" => '__return_true'
        ));
    }


    public static function moving_forward_handle_user_delivery_crud_request(WP_REST_Request $request)
    {
        header("Access-Control-Allow-Origin: *");

        $user_id = (int)$request['id'];
        $delivery_id = (int)$request['delivery_id'];
        $method = $request->get_method();
        $data = json_decode($request->get_body(), true);
        $response = [];

        if ($method === 'GET') {
            if (!$user_id) {
                var_dump('pas d\'id user'); die;
            }
            // If the URL contains both user_id and delivery_id, front-end checks if
            // a user_delivery already exists
            if ($user_id && $delivery_id) {
                $exists = self::checkIfExists($user_id, $delivery_id);
                wp_send_json_success($exists, 200);
                return;
            }
            $user_deliveries = self::findAllForUser($user_id);
            wp_send_json_success($user_deliveries, 200);
            return;
        }

        if ($method === 'POST') {
            $udid = UserDelivery::create($data['customer_id'], $data['provider_id'], $data['delivery_id']);
            $user_delivery = self::find($udid);
            wp_send_json_success($user_delivery, 200);
            return;
        }
    }



    public static function create (int $customer_id, int $provider_id, int $delivery_id)
    {
        global $wpdb;

        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO " . self::$wp_table . " SET `customer_id` = %d, `provider_id` = %d, `delivery_id` = %d, `initiated_at` = NOW(), `has_customer_validated` = false, `has_provider_validated` = false, `status` = 'ongoing';",
                esc_sql($customer_id), esc_sql($provider_id), esc_sql($delivery_id)
            )
        );

        $user_delivery_id = $wpdb->insert_id;
        return $user_delivery_id;
    }



    public static function find(int $user_delivery_id)
    {
        global $wpdb;
        $user_delivery = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . self::$wp_table . " WHERE `id` = %d",
                esc_sql($user_delivery_id)
            )
        )[0];

        return $user_delivery;
    }



    public static function findAllForUser(int $user_id)
    {
        global $wpdb;
        $user_deliveries = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . self::$wp_table . " WHERE `customer_id` = %d OR `provider_id` = %d",
                esc_sql($user_id), esc_sql($user_id)
            )
        );

        return $user_deliveries;
    }


    
    public static function checkIfExists(int $user_id, int $delivery_id)
    {
        global $wpdb;
        $user_deliveries = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . self::$wp_table . " WHERE (`customer_id` = %d OR `provider_id` = %d) AND `delivery_id` = %d",
                esc_sql($user_id), esc_sql($user_id), esc_sql($delivery_id)
            )
        );

        if (count($user_deliveries) > 0) {
            return true;
        }

        return false;
    }
}