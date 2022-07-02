<?php
require_once dirname(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'Mailbox.php';
require_once dirname(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'Delivery.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'class/Itinerary.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'class/Role.php';

/**
 * Fired during plugin activation
 *
 * @link       https://oclock.io/
 * @since      1.0.0
 *
 * @package    Moving_Forward
 * @subpackage Moving_Forward/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Moving_Forward
 * @subpackage Moving_Forward/includes
 * @author     O'Clock <O'clock@chez.lui>
 */
class Moving_Forward_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		Mailbox::moving_forward_create_message_table();
		Delivery::moving_forward_create_user_delivery_table();
		Role::add_role_classic_user();
		Role::add_role_provider();
		Itinerary::create_table_itinerary();
	}

}
