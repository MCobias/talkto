<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This is the create/edit page for a hello world instance.
 * @package     block
 * @subpackage  hello_world
 * @copyright   2017 benIT
 * @author      benIT <benoit.works@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require_once('talkto_form.php');

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
$editurl = new moodle_url('/blocks/talkto/edit.php', array('id' => $id, 'courseid' => $courseid, 'userid' => $userid));
$editnode = $settingsnode->add(get_string('editpage', 'block_talkto'), $editurl);
$editnode->make_active();

$PAGE->set_url('/blocks/talkto/edit.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('editpage', 'block_talkto'));

$context = context_course::instance($courseid);
$contextid = $context->id;
$PAGE->set_context($context);

// Create form
$talkto_form = new talkto_form();
$entry = new stdClass;
$entry->userid = $userid;
$entry->courseid = $courseid;
$entry->id = $id;
$entry->roleid = 4;
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