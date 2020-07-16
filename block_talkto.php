<?php
/**
 * @package    Block talkto
 * @copyright  2019 Marcelo Cobias
 * @author     Marcelo Cobias <marcelo.amorim@projecao.br>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/filelib.php');

class block_talkto extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_talkto');
    }

    public function get_content() {
        global $COURSE, $USER, $DB, $PAGE;
        if ($this->content !== null or !$this->view_only_course()) {
            return $this->content;
        }

        $role = get_config('talkto', 'role');
        $this->content = new stdClass();

        if(!get_config('talkto', 'isglobal')){
            $settingsrolelocal = $DB->get_record('block_talkto_role_course', ['courseid'=>$COURSE->id]);
            if(!empty($settingsrolelocal))
                $role = $settingsrolelocal->roleid;
        }

        $this->content->text = "";
        $teachers = $this->get_teacher($role);

        if (!is_string($teachers)) {
            $editrolelocal = '';
            if (is_siteadmin() and !get_config('talkto', 'isglobal')) {
                $pageparam = array('courseid' => $COURSE->id,
                    'id' => $role);
                //edit role local
                $editurl = new moodle_url('/blocks/talkto/editrole.php', $pageparam);
                $editrolelocal = html_writer::link($editurl, html_writer::tag('span', '', array('class' => 'fas fa-2x fa-user-tag', 'alt' => get_string('edit'))));
                $this->content->text .= $editrolelocal;
            }

            foreach ($teachers as $teacher) {
                $picture = '';
                $picture = new user_picture($teacher);
                $picture->size = 200;
                $profile = $picture->get_url($PAGE);

                $idbox = 0;
                $titlerole = get_string('titledefault', 'block_talkto');
                $settingsbox = $DB->get_record('block_talkto', ['userid' => $teacher->id, 'courseid' => $COURSE->id]);

                if (!empty($settingsbox)) {
                    $idbox = $settingsbox->id;
                    if ($settingsbox->titlerole != '') {
                        $titlerole = $settingsbox->titlerole;
                    }
                }
                $edit = '';
                if (is_siteadmin()) {
                    $pageparam = array('courseid' => $COURSE->id,
                        'userid' => $teacher->id,
                        'id' => $idbox);

                    //edit
                    $editurl = new moodle_url('/blocks/talkto/editbox.php', $pageparam);
                    $edit = html_writer::link($editurl, html_writer::tag('span', '', array('class' => 'fas fa-wrench icon-editbox', 'alt' => get_string('edit'))));
                }

                $headcolor = preg_replace('/\s+/', '',get_config('talkto','panelheadcolor'));
                $bodycolor = preg_replace('/\s+/', '',get_config('talkto','panelbodycolor'));
                $buttoncolor = preg_replace('/\s+/', '',get_config('talkto','panelbuttoncolor'));

                if($headcolor == '') $headcolor = '#302F51';
                if($bodycolor == '') $bodycolor = '#CAE4FB';
                if($buttoncolor == '') $buttoncolor = '#51A351';

                //Render box
                $now = strtotime(date("Y-m-d H:i:s"));
                $lastacess = strtotime(date(gmdate("Y-m-d H:i:s", $teacher->lastaccess)));
                $secs = $now - $lastacess;

                $name = $teacher->firstname;
                preg_replace('/\s(d[A-z]{1,2}|a(.){1,2}?|e(.){1,2}?|le{1}|[A-z.]{1,2}\s)/i', ' ', $name);
                preg_replace('/\s+/i', ' ', $name);
                $name = explode(" ", $teacher->firstname);

                $this->content->text .= '<div class="row mb-lg-5"><div class="col-md-3 ml-lg-5 panel-box"><div style="border: none;background-color: '.$headcolor.';vertical-align: top;box-shadow: 5px 5px 5px 0 #bdbdbd;">';

                if ($secs < 350) $this->content->text .= '<p class="text-online"><i class="fas fa-circle"></i> ' . $edit . " " . $titlerole . ' (online) <i class="fas fa-headset"></i></p>';
                else $this->content->text .= '<p class="text-offline">' . $edit . " " . $titlerole . ' (offline)</p>';

                $this->content->text .= '<div style="background-color:'.$bodycolor.';border-radius: 0;"><div class="inner-all"><br/><ul class="list-unstyled">';
                $this->content->text .= '<li class="text-center"><img width="40%" class="img-circle img-bordered-primary" src="' . $profile . '" alt="Marint month"></li>';
                $this->content->text .= '<li class="text-center"><h8 class=""><a href="#" class="brand close-modal-small" data-toggle="modal" data-target="#modalSupervisor'.$teacher->id.'">' . get_string('openprofile', 'block_talkto') . '</a></h8>';
                $this->content->text .= '<li class="text-center"><h5 class="text-capitalize"><a href="#" class="brand close-modal-small" data-toggle="modal" data-target="#modalSupervisor">' . $name[count($name) - 1] . '</a></h5>';
                if($USER->id != $teacher->id) {
                    $this->content->text .= '<li><a style="color:#ffffff;background-color: ' . $buttoncolor . '; background:' . $buttoncolor . ';" href="#" data-toggle="modal" data-target="#modalSupervisorChat'.$teacher->id.'" class="btn text-center btn-block no-hover">' . get_string('presentationother', 'block_talkto') . ' ' . $titlerole . ' <span class="far fa-comment"></span></a></li>';
                }
                else {
                    $this->content->text .= '<li><a style="color:#ffffff;background-color: ' . $buttoncolor . '; background:' . $buttoncolor . ';" data-target="" class="btn text-center btn-block no-hover">' . get_string('presentationme', 'block_talkto') . ' ' . $titlerole . ' <span class="far fa-comment"></span></a></li>';
                }
                $this->content->text .= '</ul></div>';
                $this->content->text .= '</div></div></div>';
                include 'chatbox.php';
                $this->content->text .= '<div style="width: 60%;" id="modalSupervisorChat'.$teacher->id.'" class="modal modal-perfil fade hide" role="dialog" aria-hidden="true">';
                $this->content->text .= '<div class="" role="document">';
                $this->content->text .= '<div class="modal-content">';
                $this->content->text .= '<div class="modal-body">';
                $this->content->text .= '<button class="fas fa-window-close fa-1x" data-dismiss="modal" aria-label="Fechar"></button>';
                $this->content->text .= '<div id="page-header">';
                $this->content->text .= $chatbox;
                $this->content->text .= '</div></div></div></div></div>';

                $this->content->text .= '<div style="width: 60%;" id="modalSupervisor'.$teacher->id.'" class="modal modal-perfil fade hide" role="dialog" aria-hidden="true">';
                $this->content->text .= '<div class="" role="document">';
                $this->content->text .= '<div class="modal-content">';
                $this->content->text .= '<div class="modal-body">';
                $this->content->text .= '<div id="page-header">';
                $this->content->text .= '<button class="fas fa-window-close fa-2x pull-right" data-dismiss="modal" aria-label="Fechar"></button>';
                $this->content->text .= '<div class="page-context-header">';
                $this->content->text .= '<div class="page-header-image">';
                $this->content->text .= '<a href="/ava/user/profile.php?id= ' . $teacher->id . '">';
                $this->content->text .= '<img style="width: 30%;" src="' . $profile . '">';
                $this->content->text .= '<div class="description"><p></p>' . $teacher->email . '</div>';
                $this->content->text .= '</a></div><div class="page-header-headings"><h6>' . $teacher->firstname . '</h6></div>';
                $this->content->text .= '</div>';
                $this->content->text .= '<div class="description"><p></p>' . $teacher->description . '</div>';
                $this->content->text .= '</div></div></div></div></div>';
            }
        }else {
            $this->content->text .= '<div class="alert alert-danger" role="alert"><h8 class="alert-heading">Oops</h8><p class="mb-0">'.$teachers.'</p></div>';
        }
        return $this->content;
    }

    public function applicable_formats() {
        return array('all' => true, 'my' => false);
    }

    public function has_config() {
        return true;
    }

    public function instance_allow_multiple()
    {
        return false;
    }

    function hide_header() {
        return true;
    }

    public function view_only_course() {
        global $PAGE;

        $context = context::instance_by_id($this->instance->parentcontextid, IGNORE_MISSING);

        if ($context->contextlevel == CONTEXT_COURSE) {
            if (strpos($PAGE->url, 'course/view')) {
                return true;
            } else {
                return false;
            }
        }
        return true;
    }

    public function instance_delete(){
        global $DB;
        $DB->delete_records('block_talkto', array('id' => $this->instance->id));
    }

    public function get_name_role($id){
        $systemcontext = context_system::instance();
        $roles = role_fix_names(get_all_roles(), $systemcontext, ROLENAME_ORIGINAL);
        $options = array();
        foreach ($roles as $key => $value) {
            if($value->id == $id)
                return $value->localname;
        }
        return '';
    }

    public function get_teacher($role)
    {
        global $DB, $COURSE, $USER;
        list($usql, $uparams) = $DB->get_in_or_equal($role);
        $params = array($COURSE->id, CONTEXT_COURSE);

        $select = 'SELECT DISTINCT u.id, u.firstname, u.lastname, u.lastaccess, u.picture, u.description, u.email ';
        $from = 'FROM {role_assignments} ra
		JOIN {context} c ON ra.contextid = c.id
		JOIN {user} u ON u.id = ra.userid ';
        $where = 'WHERE ((c.instanceid = ? AND c.contextlevel = ?))';

        $params = array_merge($params, $uparams);
        $where .= ' AND roleid ' . $usql;
        $order = ' ORDER BY u.firstname ASC, u.lastname';

        $teachers = $DB->get_records_sql($select . $from . $where . $order, $params);

        if (get_config('talkto', 'usegroup')) {
            $coursehasgroups = groups_get_all_groups($COURSE->id);
            ### filtro para grupos ###
            if (!empty($teachers)) {
                if ($coursehasgroups) {
                    $groupteachers = array();
                    $usergroupings = groups_get_user_groups($COURSE->id, $USER->id);
                    if (empty($usergroupings)) {
                        return get_string('messageegrouperror', 'block_talkto');
                    } else {
                        foreach ($usergroupings as $usergroups) {
                            if (empty($usergroups)) {
                                return get_string('messageegrouperror', 'block_talkto');
                            } else {
                                foreach ($usergroups as $usergroup) {
                                    foreach ($teachers as $teacher) {
                                        if ((groups_is_member($usergroup, $teacher->id))) {
                                            $groupteachers[$teacher->id] = $teacher;
                                        }
                                    }
                                }
                            }
                        }
                        if (empty($groupteachers)) {
                            return get_string('messageegrouperror', 'block_talkto');
                        } else {
                            return $groupteachers;
                        }
                    }
                }
            }
        }
        return $teachers;
    }
}
