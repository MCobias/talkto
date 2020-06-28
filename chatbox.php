<?php
/**
 * @package    block talkto
 * @copyright  2020 Marcelo Cobias
 * @author     Marcelo Cobias <marcelocobias18@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__.'/../../config.php');
require_once(__DIR__.'/../../message/lib.php');

$courseid = $COURSE->id;
$recipientid = $teacher->id;
$referurl = $this->page->url->out();

$coursecontext = context_course::instance($courseid);
$PAGE->set_context($coursecontext);

require_login();
require_capability('moodle/site:sendmessage', $coursecontext);

$recipient = $DB->get_record('user', array('id' => $recipientid));

$customdata = array(
    'recipient' => $recipient,
    'referurl' => $referurl,
    'courseid' => $courseid
);

// The id of the user we want to view messages from.
$id = $recipient->id;

// It's possible for someone with the right capabilities to view a conversation between two other users. For BC
// we are going to accept other URL parameters to figure this out.
$user1id = optional_param('user1', $USER->id, PARAM_INT);
$user2id = optional_param('user2', $id, PARAM_INT);
$contactsfirst = optional_param('contactsfirst', 0, PARAM_INT);

$user1 = null;
$currentuser = true;
if ($user1id != $USER->id) {
    $user1 = core_user::get_user($user1id, '*', MUST_EXIST);
    $currentuser = false;
} else {
    $user1 = $USER;
}

$user2 = null;
if (!empty($user2id)) {
    $user2 = core_user::get_user($user2id, '*', MUST_EXIST);
}

$user2realuser = !empty($user2) && core_user::is_real_user($user2->id);
$systemcontext = context_system::instance();
if ($currentuser === false && !has_capability('moodle/site:readallmessages', $systemcontext)) {
    print_error('accessdenied', 'admin');
}

// Get the renderer and the information we are going to be use.
$renderer = $PAGE->get_renderer('core_message');

$messages = [];
$conversations = array();
$requestedconversation = true;

// Mark the conversation as read.
if (!empty($user2->id)) {
    if ($currentuser && isset($conversations[$user2->id])) {
        // Mark the conversation we are loading as read.
        if ($conversationid = \core_message\api::get_conversation_between_users([$user1->id, $user2->id])) {
            \core_message\api::mark_all_messages_as_read($user1->id, $conversationid);
        }
        // Ensure the UI knows it's read as well.
        $conversations[$user2->id]->isread = 1;
    }

    $messages = \core_message\api::get_messages($user1->id, $user2->id, 0, 20, 'timecreated DESC');
}

$pollmin = !empty($CFG->messagingminpoll) ? $CFG->messagingminpoll : MESSAGE_DEFAULT_MIN_POLL_IN_SECONDS;
$pollmax = !empty($CFG->messagingmaxpoll) ? $CFG->messagingmaxpoll : MESSAGE_DEFAULT_MAX_POLL_IN_SECONDS;
$polltimeout = !empty($CFG->messagingtimeoutpoll) ? $CFG->messagingtimeoutpoll : MESSAGE_DEFAULT_TIMEOUT_POLL_IN_SECONDS;
$messagearea = new \core_message\output\messagearea\message_area($user1->id, $user2->id, $conversations, $messages,
    $requestedconversation, $contactsfirst, $pollmin, $pollmax, $polltimeout);

$html = $renderer->render($messagearea);