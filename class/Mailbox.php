<?php
require_once plugin_dir_path(__FILE__) . DIRECTORY_SEPARATOR . 'Message.php';
require_once plugin_dir_path(__FILE__) . DIRECTORY_SEPARATOR . 'Conversation.php';
require_once plugin_dir_path(__FILE__) . DIRECTORY_SEPARATOR . 'UserDelivery.php';

/**
 * This class manages all messages related functionnailities
 * 
 * @author Anthony Perrier <perrier_anthony@live.fr>
 * @since 0.1.0
 */
class Mailbox 
{
  private static $table_name = 'wp_moving_forward_messages';

  /**
   * Handles the creation in DB of the table
   *
   * @return void
   */
  public static function moving_forward_create_message_table ()
  {
    global $wpdb;

    $charset = $wpdb->collate;

    $sql = "CREATE TABLE " . self::$table_name . " (
            `id` mediumint NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `sender_id` int NOT NULL,
            `receiver_id` int NOT NULL,
            `user_delivery_id` int NOT NULL,
            `sent_at` datetime NOT NULL,
            `content` longtext NOT NULL
            ) COLLATE '" . $charset . "';";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }


  /**
   * Handles the removal from DB of the table
   *
   * @return void
   */
  public static function moving_forward_drop_message_table ()
  {
    global $wpdb;
    
    $wpdb->query("DROP TABLE IF EXISTS " . self::$table_name);
  }


  /**
   * Register a new REST route to retrieve all messages where a user is sollicited
   *
   * @return void
   */
  public static function moving_forward_handle_mailbox ()
  {
    register_rest_route('wp/v2', 'users/(?P<id>\d+)/messages', array(
      'methods' => ['GET', 'POST'],
      'callback' => function ($request) {
                  self::moving_forward_handle_mailbox_request($request);
              },
      "permission_callback" => '__return_true'
    ));
  }


  private static function moving_forward_handle_mailbox_request (WP_REST_Request $request) 
  {
    header("Access-Control-Allow-Origin: *");
    // En V2/V3 on consolidera le procédé en vérifiant qu'un token est présent dans la requête
    // et que ce dernier est valide. On contactera jwt-auth pour s'en assurer
    /* $token = $request->get_headers()['authorization'][0];
    $ch = curl_init('http://moving-forward.local/wp-json/jwt-auth/v1/token/validate');
     curl_setopt(
      $ch, CURLOPT_POST
    ); 
    $res = curl_exec($ch);
    var_dump($ch);die;*/

    $user_id = (int)$request['id'];
    $method = $request->get_method();
    $data = json_decode($request->get_body(), true);
    $messages = [];
    $response = [];

    if ($method === 'GET') {
      $user_deliveries = UserDelivery::findAllForUser($user_id);

      foreach ($user_deliveries as $user_delivery) {
        $conversation = self::findConversationForUserDelivery($user_delivery->id, $user_id);
        $user_delivery->conversation = $conversation;
      }

      wp_send_json_success($user_deliveries, 200);
      return;
    } else if ($method === 'POST') {
      $message_id = self::create_message_for($user_id, $data);
      wp_send_json_success($message_id, 201);
      return;
    }
  }


  public static function findConversationForUserDelivery(int $user_delivery_id, int $user_id)
  {
    global $wpdb;
    $rawMessages = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT * FROM " . self::$table_name . " WHERE `user_delivery_id` = %d",
        esc_sql($user_delivery_id)
      )
    );
    
    $messages = [];
    foreach ($rawMessages as $rawMessage) {
      $messages[] = new Message($rawMessage->sender_id, $rawMessage->receiver_id, $rawMessage->content, $rawMessage->user_delivery_id, $rawMessage->sent_at);
    }

    $conversation = new Conversation($messages, $user_id);
    return $conversation;
  }
  
  

  /**
   * From an array of messages, sort them to usable conversations
   *
   * @param integer $user_id
   * @param array $messages
   * @return array $conversations
   */
  private static function get_all_conversations_from(int $user_id, array $messages)
  {
    $rawConversations = Conversation::getRawConversations($messages, $user_id);
    $conversations = [];

    foreach ($rawConversations as $rawConv) {
      $conversations[] = new Conversation($rawConv, $user_id);
    }

    return $conversations;
  }


  /**
   * Parse data into a Message object and save it into a database
   *
   * @param integer $user_id
   * @param array $data
   * @return integer $message_id
   */
  private static function create_message_for(int $user_id, array $data)
  { 
    $message = new Message($data['sender_id'], $data['receiver_id'], $data['content'], $data['user_delivery_id']);
    $message_id = $message->save();
    $newMessage = Message::find($message_id);
    wp_send_json_success($newMessage, 201);
    return $message_id;
  }
}