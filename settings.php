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
//
/**
 * Defines global settings for the Message My Teacher block
 *
 * Allows selection of roles to be considered "Teachers", and thus displayed in the block
 *
 * @package    block_messageteacher
 * @author     Mark Johnson <mark@barrenfrozenwasteland.com>
 * @copyright  2010-2012 Tauntons College, UK. 2012 onwards Mark Johnson.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$systemcontext = context_system::instance();
$roles = role_fix_names(get_all_roles(), $systemcontext, ROLENAME_ORIGINAL);
foreach ($roles as $key => $value) {
    if($value->id == 3 or $value->id == 4)
        $options[$value->id] = $value->localname;
}

$settings->add(new admin_setting_configselect('block_talkto/role',
                                           get_string('roleinclude', 'block_talkto'),
                                           get_string('roledesc', 'block_talkto'),null,
                                           $options));