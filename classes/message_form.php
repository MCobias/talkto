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
 * @package    Block talk to
 * @copyright  2019 Marcelo Cobias
 * @author     Marcelo Cobias <marcelocobias18@fmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_talkto;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Custom messaging form.
 */
class message_form extends \moodleform {

    /**
     * Define the form.
     */
    public function definition() {
        $mform = $this->_form;
        $strrequired = get_string('required');

        $header = get_string('messageheader',
                             'block_talkto',
                             fullname($this->_customdata['recipient']));
        $mform->addElement('header', 'general', $header);

        $mform->addElement('textarea',
                            'message',
                            get_string('messagetext', 'block_talkto'),
                            array('rows' => 6, 'cols' => 60));
        $mform->setType('message', PARAM_TEXT);

        $mform->addRule('message', $strrequired, 'required', null, 'client');

        $mform->addElement('hidden', 'referurl', $this->_customdata['referurl']);
        $mform->setType('referurl', PARAM_URL);

        $mform->addElement('hidden', 'recipientid', $this->_customdata['recipient']->id);
        $mform->setType('recipientid', PARAM_INT);

        $mform->addElement('hidden', 'courseid', $this->_customdata['courseid']);
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('submit', 'send', get_string('send', 'block_talkto'));

    }

    /**
     * Validate and send the message.
     *
     * @param stdClass $data Form data
     * @return true
     */
    public function process($data) {
        global $DB, $USER, $COURSE;
        if (!$recipient = $DB->get_record('user', array('id' => $data->recipientid))) {
            throw new no_recipient_exception($data->recipientid);
        }

        $appendurl = get_config('block_talkto', 'appendurl');
        if ($appendurl) {
            $data->message .= "\n\n".get_string('sentfrom', 'block_talkto', $data->referurl);
        }

        $eventdata = new \core\message\message();
        $eventdata->component = 'block_talkto';
        $eventdata->name = 'message';
        $eventdata->userfrom = $USER;
        $eventdata->userto = $recipient;
        $eventdata->subject = get_string('messagefrom', 'block_talkto', fullname($USER));
        $eventdata->fullmessage = $data->message;
        $eventdata->fullmessageformat = FORMAT_PLAIN;
        $eventdata->fullmessagehtml = '';
        $eventdata->smallmessage = '';
        $eventdata->notification = 0;
        $eventdata->courseid = $COURSE->id;

        if (!message_send($eventdata)) {
            throw new message_failed_exception();
        }
        return true;
    }
}
