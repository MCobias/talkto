<?php
/**
 * @package    block talkto
 * @copyright  2020 Marcelo Cobias
 * @author     Marcelo Cobias <marcelocobias18@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require_once('talkto_form_box.php');

global $DB, $OUTPUT, $PAGE;
// Check for all required variables.
$courseid = required_param('courseid', PARAM_INT);
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_talkto', $courseid);
}
require_login($course);
require_capability('block/talkto:managepages', context_course::instance($courseid));

//breadcrumb
$userid = required_param('userid', PARAM_INT);
$id = optional_param('id', 0, PARAM_INT);
$viewpage = optional_param('viewpage', false, PARAM_BOOL);
$settingsnode = $PAGE->settingsnav->add(get_string('talktosettings', 'block_talkto'));
$editurl = new moodle_url('/blocks/talkto/editbox.php', array('id' => $id, 'courseid' => $courseid, 'userid' => $userid));
$editnode = $settingsnode->add(get_string('editbox', 'block_talkto'), $editurl);
$editnode->make_active();

$PAGE->set_url('/blocks/talkto/editbox.php', array('id' => $id));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('editbox', 'block_talkto'));

$context = context_course::instance($courseid);
$contextid = $context->id;
$PAGE->set_context($context);

// Create form
$talkto_form = new talkto_form_box();
$entry = new stdClass;
$entry->userid = $userid;
$entry->courseid = $courseid;
$entry->id = $id;
$talkto_form->set_data($entry);

if ($talkto_form->is_cancelled()) {
    // Cancelled forms redirect to the course main page.
    $courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
    redirect($courseurl);
} else if ($form_submitted_data = $talkto_form->get_data()) {
    //form has been submitted
    if ($form_submitted_data->id != 0) {
        if (!$DB->update_record('block_talkto', $form_submitted_data)) {
            print_error('updateerror', 'block_talkto');
        }
    } else {
        if (!$DB->insert_record('block_talkto', $form_submitted_data)) {
            print_error('inserterror', 'block_talkto');
        }
    }
    $courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
    redirect($courseurl);
} else {
    // form didn't validate or this is the first display
    $site = get_site();
    echo $OUTPUT->header();
    if ($id) {
        $talktopage = $DB->get_record('block_talkto', array('id' => $id));
        $talkto_form->set_data($talktopage);
    }
    $talkto_form->display();
    echo $OUTPUT->footer();
}