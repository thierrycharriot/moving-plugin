<?php
require_once plugin_dir_path(__FILE__) . 'class' . DIRECTORY_SEPARATOR . 'Registration.php';
require_once dirname(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'Mailbox.php';
require_once dirname(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'Delivery.php';
require_once dirname(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'Itinerary.php';
require_once dirname(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'UserProfile.php';
require_once dirname(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'UserDelivery.php';

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://oclock.io/
 * @since      1.0.0
 *
 * @package    Moving_Forward
 * @subpackage Moving_Forward/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Moving_Forward
 * @subpackage Moving_Forward/public
 * @author     O'Clock <O'clock@chez.lui>
 */
class Moving_Forward_Public
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
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
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

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/moving-forward-public.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
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

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/moving-forward-public.js', array( 'jquery' ), $this->version, false);
    }


    /**
     * This method is triggered when the authentication has been successful.
     * Adds more data to the default response provided by JWT-Auth plugin
     *
     * @author Anthony Perrier <perrier_anthony@live.fr>
     * @since 0.1.0
     * @param WP_REST_Response $response
     * @param $user The current user just logged in
     * @return WP_REST_Response $response
     */
    function enlarge_auth_response($response, $user)
    {
        $user_info = get_user_by('email', $user->data->user_email);
        $profile_picture = UserProfile::getProfileSrcset($user_info->data->ID);
        $allPosts = get_posts([
            "author" => $user_info->data->ID
        ]);

        if ($profile_picture === null) {
            $profile_picture = [
                [
                    'link' => ''
                ]
            ];
        }
        
        /*  The profile post is automatically the last current in the database for
         * the selected user. Every new registered user has only one post, and
         * we assume administrators may have multiple WP_Post when they are
         * created by hand with a non-Moving Forward front-end process
        */
        $profilePost = $allPosts[count($allPosts) - 1];

        $response = array_merge(
            $response['data'],
            ['roles' => $user_info->roles],
            ['profile_pictures' => $profile_picture],
            ['post' => $profilePost],
            ['deliveries' => Delivery::findAllDeliveriesByUser($user_info->data->ID)],
            ['itineraries' => Itinerary::findAll($user_info->data->ID)]
        );
        
        return $response;
    }


    /**
     * Open a new REST route to allow users to register from the API
     * @author <perrier_anthony@live.fr>
     * @since 0.1.0
     * @return void
     */
    function moving_forward_handle_registration()
    {
        Registration::moving_forward_handle_registration();
    }


    /**
     * Open new rest routes and give access
     * to unlock all logic related
     * @author <perrier_anthony@live.fr>
     * @since 0.1.0
     * @return void
     */
    function moving_forward_handle_endpoints()
    {
        Mailbox::moving_forward_handle_mailbox();
        Delivery::moving_forward_handle_delivery();
        UserProfile::moving_forward_handle_user_picture();
        UserDelivery::moving_forward_handle_user_delivery_crud();
    }


    function handle_preflight()
    {
        $origin = get_http_origin();
        if ($origin === 'http://localhost:8080') {
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
            header("Access-Control-Allow-Credentials: true");
            header('Access-Control-Allow-Headers: Origin, X-Requested-With, X-WP-Nonce, Content-Type, Accept, Authorization');
            if ('OPTIONS' == $_SERVER['REQUEST_METHOD']) {
                status_header(200);
                exit();
            }
        }
    }



    
    function rest_filter_incoming_connections($errors)
    {
        $request_server = $_SERVER['REMOTE_ADDR'];
        $origin = get_http_origin();
        if ($origin !== 'http://localhost:8080') {
            return new WP_Error('forbidden_access', $origin, array(
            'status' => 403
            ));
        }
        return $errors;
    }



    function get_avatar_filter($avatar, $id_or_email, $size, $default, $alt)
    {
        UserProfile::getProfileSrcset($avatar, $id_or_email, $size, $default, $alt);
    }
}
