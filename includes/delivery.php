<?php

/**
 * Registers the `delivery` post type.
 * 
 * @author   Thierry_Charriot@chez.lui
 */
function delivery_init() {
	register_post_type(
		'delivery',
		[
			'labels'                => [
				'name'                  => __( 'Deliveries', 'moving-forward' ),
				'singular_name'         => __( 'Delivery', 'moving-forward' ),
				'all_items'             => __( 'All Deliveries', 'moving-forward' ),
				'archives'              => __( 'Delivery Archives', 'moving-forward' ),
				'attributes'            => __( 'Delivery Attributes', 'moving-forward' ),
				'insert_into_item'      => __( 'Insert into Delivery', 'moving-forward' ),
				'uploaded_to_this_item' => __( 'Uploaded to this Delivery', 'moving-forward' ),
				'featured_image'        => _x( 'Featured Image', 'delivery', 'moving-forward' ),
				'set_featured_image'    => _x( 'Set featured image', 'delivery', 'moving-forward' ),
				'remove_featured_image' => _x( 'Remove featured image', 'delivery', 'moving-forward' ),
				'use_featured_image'    => _x( 'Use as featured image', 'delivery', 'moving-forward' ),
				'filter_items_list'     => __( 'Filter Deliveries list', 'moving-forward' ),
				'items_list_navigation' => __( 'Deliveries list navigation', 'moving-forward' ),
				'items_list'            => __( 'Deliveries list', 'moving-forward' ),
				'new_item'              => __( 'New Delivery', 'moving-forward' ),
				'add_new'               => __( 'Add New', 'moving-forward' ),
				'add_new_item'          => __( 'Add New Delivery', 'moving-forward' ),
				'edit_item'             => __( 'Edit Delivery', 'moving-forward' ),
				'view_item'             => __( 'View Delivery', 'moving-forward' ),
				'view_items'            => __( 'View Deliveries', 'moving-forward' ),
				'search_items'          => __( 'Search Deliveries', 'moving-forward' ),
				'not_found'             => __( 'No Deliveries found', 'moving-forward' ),
				'not_found_in_trash'    => __( 'No Deliveries found in trash', 'moving-forward' ),
				'parent_item_colon'     => __( 'Parent Delivery:', 'moving-forward' ),
				'menu_name'             => __( 'Deliveries', 'moving-forward' ),
			],
			'public'                => true,
			'hierarchical'          => false,
			'show_ui'               => true,
			'show_in_nav_menus'     => true,
			'supports'              => [ 'title', 'editor', 'thumbnail' ],
			'has_archive'           => true,
			'rewrite'               => true,
			'query_var'             => true,
			'menu_position'         => 6,
			'menu_icon'             => 'dashicons-hammer',
			'show_in_rest'          => true,
			'rest_base'             => 'deliveries',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
		]
	);

	#echo '<h1>GLOP GLOP</h1>'; 
	#die(); # debug ok

}

#add_action( 'init', 'delivery_init' );

/**
 * Sets the post updated messages for the `delivery` post type.
 *
 * @param  array $messages Post updated messages.
 * @return array Messages for the `delivery` post type.
 */
function delivery_updated_messages( $messages ) {
	global $post;

	$permalink = get_permalink( $post );

	$messages['delivery'] = [
		0  => '', // Unused. Messages start at index 1.
		/* translators: %s: post permalink */
		1  => sprintf( __( 'Delivery updated. <a target="_blank" href="%s">View Delivery</a>', 'moving-forward' ), esc_url( $permalink ) ),
		2  => __( 'Custom field updated.', 'moving-forward' ),
		3  => __( 'Custom field deleted.', 'moving-forward' ),
		4  => __( 'Delivery updated.', 'moving-forward' ),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Delivery restored to revision from %s', 'moving-forward' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false, // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		/* translators: %s: post permalink */
		6  => sprintf( __( 'Delivery published. <a href="%s">View Delivery</a>', 'moving-forward' ), esc_url( $permalink ) ),
		7  => __( 'Delivery saved.', 'moving-forward' ),
		/* translators: %s: post permalink */
		8  => sprintf( __( 'Delivery submitted. <a target="_blank" href="%s">Preview Delivery</a>', 'moving-forward' ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
		/* translators: 1: Publish box date format, see https://secure.php.net/date 2: Post permalink */
		9  => sprintf( __( 'Delivery scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Delivery</a>', 'moving-forward' ), date_i18n( __( 'M j, Y @ G:i', 'moving-forward' ), strtotime( $post->post_date ) ), esc_url( $permalink ) ),
		/* translators: %s: post permalink */
		10 => sprintf( __( 'Delivery draft updated. <a target="_blank" href="%s">Preview Delivery</a>', 'moving-forward' ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
	];

	return $messages;
}

add_filter( 'post_updated_messages', 'delivery_updated_messages' );

/**
 * Sets the bulk post updated messages for the `delivery` post type.
 *
 * @param  array $bulk_messages Arrays of messages, each keyed by the corresponding post type. Messages are
 *                              keyed with 'updated', 'locked', 'deleted', 'trashed', and 'untrashed'.
 * @param  int[] $bulk_counts   Array of item counts for each message, used to build internationalized strings.
 * @return array Bulk messages for the `delivery` post type.
 */
function delivery_bulk_updated_messages( $bulk_messages, $bulk_counts ) {
	global $post;

	$bulk_messages['delivery'] = [
		/* translators: %s: Number of Deliveries. */
		'updated'   => _n( '%s Delivery updated.', '%s Deliveries updated.', $bulk_counts['updated'], 'moving-forward' ),
		'locked'    => ( 1 === $bulk_counts['locked'] ) ? __( '1 Delivery not updated, somebody is editing it.', 'moving-forward' ) :
						/* translators: %s: Number of Deliveries. */
						_n( '%s Delivery not updated, somebody is editing it.', '%s Deliveries not updated, somebody is editing them.', $bulk_counts['locked'], 'moving-forward' ),
		/* translators: %s: Number of Deliveries. */
		'deleted'   => _n( '%s Delivery permanently deleted.', '%s Deliveries permanently deleted.', $bulk_counts['deleted'], 'moving-forward' ),
		/* translators: %s: Number of Deliveries. */
		'trashed'   => _n( '%s Delivery moved to the Trash.', '%s Deliveries moved to the Trash.', $bulk_counts['trashed'], 'moving-forward' ),
		/* translators: %s: Number of Deliveries. */
		'untrashed' => _n( '%s Delivery restored from the Trash.', '%s Deliveries restored from the Trash.', $bulk_counts['untrashed'], 'moving-forward' ),
	];

	return $bulk_messages;
}

add_filter( 'bulk_post_updated_messages', 'delivery_bulk_updated_messages', 10, 2 );
