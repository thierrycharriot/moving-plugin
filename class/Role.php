<?php

/**
 * Register the user role for the plugin
 *
 * @since    0.0.1
 * @author   Thierry_Charriot@chez.lui
 */
# https://developer.wordpress.org/reference/functions/add_role/
# https://developer.wordpress.org/reference/functions/register_activation_hook/
# https://developer.wordpress.org/plugins/users/roles-and-capabilities/
# https://wordpress.org/support/article/roles-and-capabilities/#capability-vs-role-table


class Role
{
	public static function add_role_classic_user() 
	{
		add_role( 'classicUser', 'Classic User',
			[
				'read'  => true,
				'delete_posts'  => false,
				'delete_published_posts' => false,
				'edit_posts'   => true,
				'publish_posts' => false,
				'upload_files'  => false,
				'edit_pages'  => false,
				'edit_published_pages'  =>  false,
				'publish_pages'  => false,
				'delete_published_pages' => false, 
			]
		);
	}

	public static function add_role_provider() 
	{
		add_role( 'provider', 'Provider',
			[
				'read'  => true,
				'delete_posts'  => true,
				'delete_published_posts' => true,
				'edit_posts'   => true,
				'publish_posts' => true,
				'upload_files'  => false,
				'edit_pages'  => false,
				'edit_published_pages'  =>  false,
				'publish_pages'  => false,
				'delete_published_pages' => false, 
			]
		);
	}

}