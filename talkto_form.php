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
 * This is the instance form for create/edit operations.
 * @package     block
 * @subpackage  hello_world
 * @copyright   2017 benIT
 * @author      benIT <benoit.works@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once("{$CFG->libdir}/formslib.php");

class talkto_form extends moodleform
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

        $mform->addElement('hidden', 'roleid');

        $mform->addElement('hidden', 'courseid');

        $this->add_action_buttons();

    }
}