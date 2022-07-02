<?php
require_once plugin_dir_path(__FILE__) . DIRECTORY_SEPARATOR . 'Message.php';

/**
 * @author Anthony Perrier <perrier_anthony@live.fr>
 * @since 0.2.0
 */
class Conversation
{
    public $messages = [];
    private $interlocutor_id;
    public $interlocutor;
    private $requesting_user_id;

    public function __construct(array $messages, int $requesting_user_id)
    {
        $this->setInterlocutor($messages, $requesting_user_id);
        $this->messages = $messages;
        $this->sortBySentAt();
    }

    private function setInterlocutor ($messages, $requesting_user_id)
    {
        if (count($messages) > 0) {
            if ($messages[0]->getSenderId() !== $requesting_user_id): $this->interlocutor_id = $messages[0]->getSenderId(); 
            else: $this->interlocutor_id = $messages[0]->getReceiverId();
            endif;
    
            $user = get_userdata($this->interlocutor_id);
            $this->interlocutor = [
                'id'            => intval($user->data->ID),
                'display_name'  => $user->data->display_name,
                'user_email'    => $user->data->user_email,
                'roles'         => $user->roles
            ]; 
        }
    }


    public function getMessages (): array
    {
        return $this->messages;
    }


 
    private function sortBySentAt ()
    {
        $sent_ats = [];
        $sortedMessages = [];
        foreach ($this->messages as $key => $row) {
            $sent_ats[$key] = $row->getSentAt();
        }
        array_multisort($sent_ats, SORT_ASC, $this->messages);

        foreach ($sent_ats as $i => $sent_at) {
            $msg = array_filter(
                $this->messages, 
                fn ($message) => $message->getSentAt() === $sent_at
            );
            array_push($sortedMessages, $msg);
        }

        return $sortedMessages;
    }


    /**
     * From an array of messages, parse to retrieve all interlocutors
     * ids who had interact with the $user_id
     *
     * @param array $messages
     * @param integer $user_id
     * @return array $allInterlocutors
     */
    public static function getAllInterlocutorsIds (array $messages, int $user_id) 
    {
        $allInterlocutors = [];

        foreach ($messages as $message) {
            if ($message->getSenderId() !== $user_id) {
                if (!in_array($message->getSenderId(), $allInterlocutors)) {
                array_push($allInterlocutors, $message->getSenderId());
                }
            }

            if ($message->getReceiverId() !== $user_id) {
                if (!in_array($message->getReceiverId(), $allInterlocutors)) {
                array_push($allInterlocutors, $message->getReceiverId());
                }
            }
        }

        return $allInterlocutors;
    }


    /**
     * Parse an unsorted array of messages related to a user and
     * returns an array of uninstanciated conversations sorted by
     * interlocutors id.
     *
     * @param array $messages
     * @param integer $user_id
     * @return array $rawConversations
     */
    public static function getRawConversations (array $messages, $user_id)
    {
        $rawConversations = [];
        foreach ($messages as $message) {
            if ($message->getSenderId() !== $user_id) {
                // If sender is our interlocutor
                $rawConversations['interlocutor-' . $message->getSenderId()][] = $message;
            }

            if ($message->getReceiverId() !== $user_id) {
                // If receiver is our interlocutor
                $rawConversations['interlocutor-' . $message->getReceiverId()][] = $message;
            }
        }

        return $rawConversations;
    }
}