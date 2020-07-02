<?php
/**
 * @package    block talkto
 * @copyright  2020 Marcelo Cobias
 * @author     Marcelo Cobias <marcelocobias18@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$systemcontext = context_system::instance();
$roles = role_fix_names(get_all_roles(), $systemcontext, ROLENAME_ORIGINAL);
$options = array();
foreach ($roles as $key => $value) {
    if($value->id == 3 or $value->id == 4)
        $options[$value->id] = $value->localname;
}

$settings->add(new admin_setting_configselect('talkto/role',
                                           get_string('roleinclude', 'block_talkto'),
                                           get_string('roledesc', 'block_talkto'),null,
                                           $options));

$settings->add(new admin_setting_configcheckbox(
    'talkto/isglobal',
    get_string('settigsroleglobal', 'block_talkto'),
    get_string('descsettigsroleglobal', 'block_talkto'),
    '1'
));