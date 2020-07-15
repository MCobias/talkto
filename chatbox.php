<?php
/**
 * @package    block talkto
 * @copyright  2020 Marcelo Cobias
 * @author     Marcelo Cobias <marcelocobias18@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../config.php');

require_login(null, false);

// The id of the user we want to view messages from.
$id = $teacher->id;
$view = $COURSE->id;
// It's possible a user may come from a link where these parameters are specified.
// We no longer support viewing another user's messaging area (that can be achieved
// via the 'Log-in as' feature). The 'user2' value takes preference over 'id'.
$userid = optional_param('user2', $id, PARAM_INT);
$conversationid = optional_param('convid', null, PARAM_INT);

if (!core_user::is_real_user($userid)) {
    $userid = null;
}
// You can specify either a user, or a conversation, not both.
if ($userid) {
    $conversationid = \core_message\api::get_conversation_between_users([$USER->id, $userid]);
} else if ($conversationid) {
    // Check that the user belongs to the conversation.
    if (!\core_message\api::is_user_in_conversation($USER->id, $conversationid)) {
        $conversationid = null;
    }
}

if ($userid) {
    if (!\core_message\api::can_send_message($userid, $USER->id)) {
        throw new moodle_exception('Can not contact user');
    }
}

$chatbox = \core_message\helper::render_messaging_widget(false, $userid, $conversationid, $view);