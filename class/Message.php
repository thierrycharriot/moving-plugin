<?php

/**
 * @author Anthony Perrier <perrier_anthony@live.fr>
 * @since 0.1.0
 */
class Message 
{
    private static $wp_table_static = 'wp_moving_forward_messages';
    private $wp_table = 'wp_moving_forward_messages';
    public $sender_id;
    public $receiver_id;
    public $content;
    public $user_delivery;
    public $sent_at = null;

    public function __construct($sender_id, $receiver_id, $content, $user_delivery, $sent_at = null)
    {
        $this->throwJsonResponseIfInvalid($sender_id, $receiver_id, $content, $user_delivery);
        $this->sender_id = $sender_id;
        $this->receiver_id = $receiver_id;
        $this->content = $content;
        $this->user_delivery = $user_delivery;
        $this->sent_at = $sent_at;
    }

    /**
     * The first filter the data is submitting to.
     * Rather than asking in constructor for a typed value,
     * which is throwing an error, we better want to send
     * back a JSON response to front if the value is invalid.
     *
     * @param integer $sourceSenderId
     * @param integer $sourceReceiverId
     * @param string $sourceContent
     * @return void
     */
    private function throwJsonResponseIfInvalid ($sourceSenderId, $sourceReceiverId, $sourceContent, $sourceUserDelivery)
    {
        $error = new \WP_Error();
        # Check both IDs are relating to existing users
        if (!get_userdata($sourceSenderId) || !get_userdata($sourceReceiverId)) {
            $error->add(400, __("wrong_user_id", 'wp-rest-user'), array('status' => 400));
            wp_send_json_error($error, 400);
            return;
        }

        if (!is_int((int)$sourceSenderId)) {
            $error->add(400, __("wrong_sender_id", 'wp-rest-user'), array('status' => 400));
            wp_send_json_error($error, 400);
            return;
        }

        if (!is_int((int)$sourceReceiverId)) {
            $error->add(400, __("wrong_receiver_id", 'wp-rest-user'), array('status' => 400));
            wp_send_json_error($error, 400);
            return;
        }

        if (!is_string($sourceContent)) {
            $error->add(400, __("wrong_message_content", 'wp-rest-user'), array('status' => 400));
            wp_send_json_error($error, 400);
            return;
        }

        if (!is_int((int)$sourceUserDelivery)) {
            $error->add(400, __("wrong_user_delivery_id", 'wp-rest-user'), array('status' => 400));
            wp_send_json_error($error, 400);
            return;
        }
    }

    public function getSenderId(): int
    {
        return $this->sender_id;
    }

    public function getReceiverId(): int
    {
        return $this->receiver_id;
    }

    public function getContent(): string 
    {
        return $this->content;
    }

    public function getUserDelivery()
    {
        return $this->user_delivery;
    }

    public function getSentAt()
    {
        return $this->sent_at;
    }

    public function isCurrentUserSender(): bool
    {
        return $this->sender_id === get_current_user_id();
    }


    public static function find ($id) 
    {
        global $wpdb;
        
        $message = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM `". self::$wp_table_static . "` WHERE id = %d",
                esc_sql($id)
            )
        )[0];

        return $message;
    }


    /**
     * Insert the message in the database
     *
     * @return integer $message_id
     */
    public function save () 
    {
        global $wpdb;
       
        $wpdb->query($wpdb->prepare(
            "INSERT INTO `$this->wp_table` SET `sender_id` = %d, `receiver_id` = %d, `content` = %s, `user_delivery_id` = %d, sent_at = NOW()",
            esc_sql($this->sender_id), esc_sql($this->receiver_id), esc_sql($this->content), esc_sql($this->user_delivery)
        ));

        $message_id = $wpdb->insert_id;
        return $message_id;
    }


    /**
     * Requesting to the database for all the messages attributed
     * to the current user
     * 
     * @return array $unsortedMessages
     */
    public static function findAllMessages($requesting_user_id)
    {
        global $wpdb;
        $unsortedMessages = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM `" . self::$wp_table_static . "` WHERE `sender_id` = %d OR `receiver_id` = %d",
                $requesting_user_id, // The first %d
                $requesting_user_id // The second %d
            )
        );

        # Ok, maybe am I a bit tired
        $unsortedMessagesButAtLeastInAnObject = [];
        foreach ($unsortedMessages as $message) {
            $unsortedMessagesButAtLeastInAnObject[] = new Message(
                intval($message->sender_id), 
                intval($message->receiver_id), 
                $message->content,
                $message->sent_at
            );
        }

        return $unsortedMessagesButAtLeastInAnObject;
    }
}