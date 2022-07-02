<?php

/**
 * Class for register verified user card
 *
 * @since    0.0.1
 * @author   Thierry_Charriot@chez.lui
 */
# https://developer.wordpress.org/reference/functions/add_user_meta/

class IDCardVerifierAdminPage
{

	/**
	 * Register route for form select
	 * /wp-json/wp/v2/idverifier
	 *
	 * @since    0.0.1
	 * @author   Thierry_Charriot@chez.lui
	 */
	public static function route_form_select()
	{
		# https://developer.wordpress.org/reference/functions/register_rest_route/
		# register_rest_route( string $namespace, string $route, array $args = array(), bool $override = false )
		# http://moving-forward.local/wp-json/wp/v2/idverifier
		register_rest_route( 'wp/v2', '/idverifier/(?<id>\d+)', array(
			'methods' => ['POST', 'GET', 'PUT', 'DELETE'],
			'callback' => function ( $request ) {
				#return ['idverifier' => 'GET']; # debug ok
				$method = $request->get_method();
				#var_dump( $method ); die(); # debug
				if ($method === 'POST') {
					#return ['method: ' => 'POST']; # debug
					self::put_idverifier( $request );
					return; 
				}
			},
			"permission_callback" => '__return_true'
		) );
	}

	public static function put_idverifier( $request ) {

		#echo('<h1>DEBUG !!!</h1>'); # debug insomnia ok

		global $wpdb;

		#var_dump( $request ); die (); # debug ok
		$user_id = ( int )$request['id'];
		$description = $request['selectidcardverifier'];
		#var_dump( $user_id ); die (); # debug ok id
		#var_dump( $description ); die ();

		# https://developer.wordpress.org/reference/functions/update_user_meta/
		# update_user_meta( int $user_id, string $meta_key, mixed $meta_value, mixed $prev_value = '' )
		update_user_meta( $user_id , $key='description',  $description, false ); 

		# https://developer.wordpress.org/reference/functions/add_user_meta/
		# add_user_meta( int $user_id, string $meta_key, mixed $meta_value, bool $unique = false )
		#add_user_meta( $user_id, 'idcardverifier', $description , false );

		# https://developer.wordpress.org/reference/functions/admin_url/
		# admin_url( string $path = '', string $scheme = 'admin' )
		# Retrieves the URL to the admin area for the current site.

		$url = '/wp/wp-admin/admin.php?page=moving-user';
		wp_redirect( $url );
		exit;
	}
		
}