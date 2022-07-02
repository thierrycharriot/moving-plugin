<?php

/**
 * Create table Itinerary for the plugin
 *
 * @since    0.0.1
 * @author   Thierry_Charriot@chez.lui
 */

class Itinerary
{
    public static function create_table_itinerary() 
	{
        global $wpdb;

			$table_query = "
			CREATE TABLE `wp_itinerary` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`name` varchar(255) NOT NULL,
				`checkpoints` LONGTEXT NOT NULL,
				`provider_id` int NOT NULL,
				`created_at` timestamp NOT NULL DEFAULT current_timestamp(),
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
			";
			require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($table_query);
    }

    public static function remove_table_itinerary() 
	{
		global $wpdb;

		# Dropping table on plugin uninstall
		$wpdb->query( "DROP TABLE IF EXISTS " . 'wp_itinerary' );		
    }

    public static function routes_itinerary()
    {
        /**
		 * https://developer.wordpress.org/reference/functions/register_rest_route/
		 * register_rest_route( string $namespace, string $route, array $args = array(), bool $override = false )
		 * Registers a REST API route.
		 */
        register_rest_route('wp/v2', 'users/(?P<id>\d+)/itineraries(?:/(?P<itinerary_id>\d+))?', [
        'methods' => ['GET', 'POST', 'PUT', 'DELETE'],
        'callback' => function( $request ) {
			#return [ 'debug' => 'methode DEBUG!']; # ok
			self::itinerary_request( $request );
		},
        "permission_callback" => '__return_true'
		] );
    }

	public static function itinerary_request ( WP_Rest_request $request )
	{
		# https://developer.mozilla.org/fr/docs/Web/HTTP/CORS
		header("Access-Control-Allow-Origin: *");

		$method = $request->get_method();

		# https://developer.wordpress.org/reference/classes/wp_rest_request/get_body/
		# WP_REST_Request::get_body()
		# Retrieves the request body content.
		$body = json_decode( $request->get_body(), true );

        $itinerary = [];
        $response = [];

		$user_id = (int)$request['id'];
		$itinerary_id = ( int )$request['itinerary_id'];

		if( $method === 'POST' ) {
			$itinerary = self::create($user_id, $body);
			wp_send_json_success($itinerary, 201);
			return;
		}
		
		if( $method === 'GET' ) {
			if ($itinerary_id) {
				$itinerary = self::find($user_id, $itinerary_id);
				wp_send_json_success($itinerary, 200);
				return;
			} else {
				$itineraries = self::findAll($user_id);
				wp_send_json_success($itineraries, 200);
				return;
			}
		}

		if( $method === 'PUT' ) {
			$itinerary = self::edit($user_id, $itinerary_id, $body);
			wp_send_json_success($itinerary, 200);
			return;
		}

		if( $method === 'DELETE' ) {
			global $wpdb;

			# https://developer.wordpress.org/reference/classes/wpdb/delete/
			# wpdb::delete( string $table, array $where, array|string $where_format = null )
			# Deletes a row in the table.
			$wpdb->delete( "wp_itinerary", array( 'id' => $itinerary_id ) );
			wp_send_json_success(null, 200);
			return;
		}
	}



	/**
	 * Allows to fetch one itinerary for a user
	 *
	 * @param integer $user_id
	 * @param integer $itinerary_id
	 * @return object
	 */
	public static function find ($user_id, $itinerary_id)
	{
		global $wpdb;

		$itinerary = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM `wp_itinerary` WHERE `provider_id` = %d AND `id` = %d",
				esc_sql($user_id),
				esc_sql($itinerary_id)
			)
		);
		return $itinerary[0];
	}

	/**
	 * Allows to fetch all itineraries for a user
	 *
	 * @param integer $user_id
	 * @param integer $itinerary_id
	 * @return array
	 */
	public static function findAll ($user_id) 
	{
		global $wpdb;

		$itineraries = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM `wp_itinerary` WHERE `provider_id` = %d",
				esc_sql($user_id)
			)
		);
		return $itineraries; # KO
	}

	/**
	 * Allows to create one itinerary for a user
	 *
	 * @param integer $user_id
	 * @param array $data
	 * @return object
	 */
	public static function create ($user_id, $data)
	{
		global $wpdb;

		$wpdb->query(
			# https://developer.wordpress.org/reference/classes/wpdb/prepare/
			# wpdb::prepare( string $query, mixed $args )
			# Prepares a SQL query for safe execution.
			$wpdb->prepare(
				# https://developer.wordpress.org/reference/classes/wpdb/prepare/
				# wpdb::prepare( string $query, mixed $args )
				# Prepares a SQL query for safe execution.
				"INSERT INTO `wp_itinerary` 
				SET `name` = %s, `checkpoints` = %s, `provider_id` = %d, `created_at` = NOW(); ", 
				esc_sql( $data['name'] ), 
				json_encode( $data['checkpoints'] ), 
				esc_sql( $user_id )		
			)
		);	

		$itinerary_created_id = $wpdb->insert_id;
		return self::find($user_id, $itinerary_created_id);
	}

	/**
	 * Allows to edit one itinerary for a user
	 *
	 * @param integer $user_id
	 * @param integer $itinerary_id
	 * @param array $data
	 * @return object
	 */
	public static function edit ($user_id, $itinerary_id, $data)
	{
		global $wpdb;
			# https://developer.wordpress.org/reference/classes/wpdb/update/
			# wpdb::update( string $table, array $data, array $where, array|string $format = null, array|string $where_format = null )
			# Updates a row in the table.
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE `wp_itinerary`
				SET `name` = %s, `checkpoints` = %s, `provider_id` = %d, `created_at` = NOW() 
				WHERE `id` = $itinerary_id", 
				esc_sql( $data['name'] ),
				json_encode( $data['checkpoints'] ),
				esc_sql( $user_id )	 		
			)
		);

		$itinerary_edited = self::find($user_id, $itinerary_id);
		return $itinerary_edited;
	}
}