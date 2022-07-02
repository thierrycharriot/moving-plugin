<?php
require_once dirname(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'Mailbox.php';
require_once dirname(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'Delivery.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'class/Itinerary.php';

/**
 * Fired during plugin deactivation
 *
 * @link       https://oclock.io/
 * @since      1.0.0
 *
 * @package    Moving_Forward
 * @subpackage Moving_Forward/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Moving_Forward
 * @subpackage Moving_Forward/includes
 * @author     O'Clock <O'clock@chez.lui>
 */
class Moving_Forward_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		Mailbox::moving_forward_drop_message_table();
		Delivery::moving_forward_drop_user_delivery_table();
		# https://developer.wordpress.org/reference/functions/remove_role/
		remove_role( 'classicUser' );
		remove_role( 'provider' );
		Itinerary::remove_table_itinerary();

	}

}
