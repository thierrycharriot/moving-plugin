<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://oclock.io/
 * @since      1.0.0
 *
 * @package    Moving_Forward
 * @subpackage Moving_Forward/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Moving_Forward
 * @subpackage Moving_Forward/admin
 * @author     O'Clock <O'clock@chez.lui>
 */
class Moving_Forward_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Moving_Forward_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Moving_Forward_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/moving-forward-admin.css', array(), $this->version, 'all');

        /**
         * Include bootstrap css only for plugin page
         *
         * @since    0.0.1
         * @author   Thierry_Charriot@chez.lui
         */
        $moving_pages = array( 'moving-user' );
        $page = isset($_REQUEST['page']) ? ( $_REQUEST['page'] ) : '';
        if (in_array($page, $moving_pages)) {
            wp_enqueue_style('moving-bootstrap-css', MOVING_FORWARD_PLUGIN_URL . 'node_modules/bootstrap/dist/css/bootstrap.min.css', array(), $this->version, 'all');
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Moving_Forward_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Moving_Forward_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/moving-forward-admin.js', array( 'jquery' ), $this->version, false);

        /**
         * Include bootstrap js only for plugin page
         *
         * @since    0.0.1
         * @author   Thierry_Charriot@chez.lui
         */
        $moving_pages = array( 'moving-user' );
        $page = isset($_REQUEST['page']) ? ( $_REQUEST['page'] ) : '';
        if (in_array($page, $moving_pages)) {
            wp_enqueue_style('moving-bootstrap-js', MOVING_FORWARD_PLUGIN_URL . 'node_modules/bootstrap/dist/js/bootstrap.min.js', array(), $this->version, 'all');
        }
    }


	/**
	 * Load the custom post-types
	 *
	 * @since    0.0.1
	 * @author   Thierry_Charriot@chez.lui
	 */
	public function load_cpt_delivery() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'class/DeliveryCPT.php';	
		DeliveryCPT::delivery_init();	
	}

	/**
	 * Register the user menu in admin area
	 *
	 * @since    0.0.1
	 * @author   Thierry_Charriot@chez.lui
	 */
	public function moving_forward_menu() 
	{
		# Create menu method 
		# https://developer.wordpress.org/reference/functions/add_menu_page/
		# add_menu_page( string $page_title, string $menu_title, string $capability, string $menu_slug, callable $function = '', string $icon_url = '', int $position = null )
		# Adds a top-level menu page.
		# https://developer.wordpress.org/resource/dashicons/#media-interactive
		add_menu_page( 'Moving Forward User', 'Moving User', 'manage_options', 'moving-user', array( $this, 'moving_menu' ), 'dashicons-car', 9 );
	}

	/**
	 * Load template page on buffer
	 *
	 * @since    0.0.1
	 * @author   Thierry_Charriot@chez.lui
	 */
	// Menu callback function
	public function moving_menu() 
	{
	#	echo '<h3>Welcome to Plugin Moving Forward</h3>';
		# Started buffer
		ob_start();
		# Included template file
		include_once( MOVING_FORWARD_PLUGIN_PATH . 'admin/partials/id-card-verifier-admin-display.php' );
		# Reading content
		$template = ob_get_contents();
		# Closing and cleaning buffer
		ob_end_clean();

		echo( $template );
	}
			
	/**
	 * 
	 *
	 * @since    0.0.1
	 * @author   Thierry_Charriot@chez.lui
	 */
	public function id_card_verifier() 
	{	
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'class/IDCardVerifierAdminPage.php';
		IDCardVerifierAdminPage::route_form_select();
	}

	public function add_routes_itinerary() 
	{
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'class/Itinerary.php';
		Itinerary::routes_itinerary();
	}

}
