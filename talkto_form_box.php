<?php
/**
 * @package    block talkto
 * @copyright  2020 Marcelo Cobias
 * @author     Marcelo Cobias <marcelocobias18@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once("../lib/formslib.php");

class talkto_form_box extends moodleform
{
    function definition()
    {
        $mform =& $this->_form;
        $mform->addElement('header', 'displayinfo', get_string('titleformbox', 'block_talkto'));

        $mform->addElement('text', 'titlerole', get_string('titleinputrole', 'block_talkto'));
        $mform->setType('title', PARAM_RAW);
        $mform->addRule('title', null, 'required', null, 'client');

        $mform->addElement('hidden', 'id', '0');

        $mform->addElement('hidden', 'userid');

        $mform->addElement('hidden', 'courseid');

        $this->add_action_buttons();

    }
}