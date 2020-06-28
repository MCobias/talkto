<?php
/**
 * @package    block talkto
 * @copyright  2020 Marcelo Cobias
 * @author     Marcelo Cobias <marcelocobias18@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once("{$CFG->libdir}/formslib.php");

class talkto_form_role extends moodleform
{
    function definition()
    {
        $mform =& $this->_form;
        $mform->addElement('header', 'displayinfo', get_string('titleformbox', 'block_talkto'));

        $systemcontext = context_system::instance();
        $roles = role_fix_names(get_all_roles(), $systemcontext, ROLENAME_ORIGINAL);
        $options = array();
        foreach ($roles as $key => $value) {
            if($value->id == 3 or $value->id == 4)
                $options[$value->id] = $value->localname;
        }
        $mform->addElement('select', 'roleid', get_string('rolecourse', 'block_talkto'), $options);
        $mform->addHelpButton('roleid', 'roleid', 'block_talkto');

        $mform->addElement('hidden', 'id', '0');

        $mform->addElement('hidden', 'courseid');

        $this->add_action_buttons();

    }
}