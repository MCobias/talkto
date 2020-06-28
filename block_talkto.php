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
        $context = context_course::instance($COURSE->id);

        if ($this->content !== null or !$this->view_only_course()) {
            return $this->content;
        }
        $this->content = new stdClass();

        if(!is_siteadmin()) {
            if (user_has_role_assignment($USER->id,4)) {

                $urlparams = array(
                    'courseid' => $COURSE->id,
                    'referurl' => $this->page->url->out(),
                    'recipientid' => $USER->id
                );

                $picture = '';
                $picture = new user_picture($USER);
                $picture->size = 200;
                $profile = $picture->get_url($PAGE);

                //Render box tutor
                $this->content->text = '<div class="box-content">';
                $this->content->text .= '<ul class="boxes">';
                $this->content->text .= '<li class="box">';

                $this->content->text .= '<p class="pull-left">Tutoria</p>';

                $now = strtotime(date("Y-m-d H:i:s"));
                $lastacess = strtotime(date(gmdate("Y-m-d H:i:s", $USER->lastaccess)));
                $secs = $now - $lastacess;

                if ($secs < 350) {
                    $this->content->text .= '<span style="margin-right: 10px;" class="text-success pull-right">(Online)</span></br>';
                } else {
                    $this->content->text .= '<span style="margin-left: -10px;" class="text-danger pull-right">(Offline)</span></br>';
                }

                $this->content->text .= '<img src="' . $profile . '"/>';

                $this->content->text .= '<div class="row pull-right">';

                $name = $USER->firstname;
                preg_replace('/\s(d[A-z]{1,2}|a(.){1,2}?|e(.){1,2}?|le{1}|[A-z.]{1,2}\s)/i', ' ', $name);
                preg_replace('/\s+/i', ' ', $name);
                $name = explode(" ", $USER->firstname);

                $this->content->text .= '<span class="pull-right">' . $name[0] . ' ' . $name[count($name) - 1] . '</span></br>';
                $this->content->text .= '<span class="pull-right"><a class="talkto_link">Eu sou o Tutor</a></span>';
                $this->content->text .= '</div">';

                $this->content->text .= '</li>';
                $this->content->text .= '</ul>';
                $this->content->text .= '</div>';
                return $this->content;
            }
        }

        //Lista de usuarios com perfil de tutor
        list($usql, $uparams) = $DB->get_in_or_equal('4');
        $params = array($COURSE->id, CONTEXT_COURSE);
        $coursehasgroups = groups_get_all_groups($COURSE->id);
        //print_r(array_values ($coursehasgroups));

        $select = 'SELECT DISTINCT u.id, u.firstname, u.lastname, u.lastaccess, u.picture, u.description, u.email ';
        $from = 'FROM {role_assignments} ra
		JOIN {context} c ON ra.contextid = c.id
		JOIN {user} u ON u.id = ra.userid ';
        $where = 'WHERE ((c.instanceid = ? AND c.contextlevel = ?))';

        $params = array_merge($params, array($USER->id), $uparams);
        $where .= ' AND userid != ? AND roleid '.$usql;
        $order = ' ORDER BY u.firstname ASC, u.lastname';

        $msgrr = '<div class="alert alert-danger" role="alert"><h8 class="alert-heading">Oops</h8><p class="mb-0">MSG</p></div>';

        if ($teachers = $DB->get_records_sql($select.$from.$where.$order, $params)) {
            if (!is_siteadmin() and !user_has_role_assignment($USER->id,3)) {
                if ($coursehasgroups) {
                    try {
                        $groupteachers = array();
                        $usergroupings = groups_get_user_groups($COURSE->id, $USER->id);

                        if (empty($usergroupings)) {
                            throw new Exception(str_replace('MSG', 'Nenhum grupo definido. Entre em contato com o suporte.', $msgrr));
                        } else {
                            foreach ($usergroupings as $usergroups) {
                                if (empty($usergroups)) {
                                    throw new Exception(str_replace('MSG', 'Nenhum grupo definido. Entre em contato com o suporte.', $msgrr));
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
                                throw new Exception(str_replace('MSG', 'Nenhum grupo definido. Entre em contato com o suporte.', $msgrr));
                            } else {
                                $teachers = $groupteachers;
                            }
                        }
                    } catch (Exception $e) {
                        $this->content->text = $e->getMessage();
                        return $this->content;
                    }

                    foreach ($teachers as $teacher) {
                        $picture = '';
                        $picture = new user_picture($teacher);
                        $picture->size = 200;
                        $profile = $picture->get_url($PAGE);

                        //Render box tutor
                        $this->content->text = '<div class="box-content">';
                        $this->content->text .= '<ul class="boxes">';
                        $this->content->text .= '<li class="box">';

                        $this->content->text .= '<p class="pull-left">Tutoria</p>';

                        $now = strtotime(date("Y-m-d H:i:s"));
                        $lastacess = strtotime(date(gmdate("Y-m-d H:i:s", $teacher->lastaccess)));
                        $secs = $now - $lastacess;

                        if ($secs < 350) {
                            $this->content->text .= '<span style="margin-right: 10px;" class="text-success pull-right">(Online)</span></br>';
                        } else {
                            $this->content->text .= '<span style="margin-left: -10px;" class="text-danger pull-right">(Offline)</span></br>';
                        }

                        $this->content->text .= '<img src="' . $profile . '"/>';

                        $this->content->text .= '<div class="row pull-right">';

                        $name = $teacher->firstname;
                        preg_replace('/\s(d[A-z]{1,2}|a(.){1,2}?|e(.){1,2}?|le{1}|[A-z.]{1,2}\s)/i', ' ', $name);
                        preg_replace('/\s+/i', ' ', $name);
                        $name = explode(" ", $teacher->firstname);

                        setcookie('fale_tutor_' . $urlparams['courseid'], $name[0] . ' ' . $name[1]);
                        setcookie('fale_tutor_url_' . $urlparams['courseid'], $url);
                        setcookie('fale_tutor_img_' . $urlparams['courseid'], $profile);

                        $this->content->text .= '<span class="pull-right">' . $name[0] . ' ' . $name[count($name) - 1] . '</span></br>';
                        $this->content->text .= '<span class="pull-right"><a href="#" class="talkto_link">Fale com o tutor</a></span>';
                        $this->content->text .= '</div">';

                        $this->content->text .= '</li>';
                        $this->content->text .= '</ul>';
                        $this->content->text .= '</div>';
                    }
                } else {
                    $this->content->text = str_replace('MSG', 'Nenhum grupo definido. Entre em contato com o suporte.', $msgrr);
                }
            }
            else
            {
                $this->content->text = "";
                foreach ($teachers as $teacher) {
                    $picture = '';
                    $picture = new user_picture($teacher);
                    $picture->size = 200;
                    $profile = $picture->get_url($PAGE);

                    $helloworldpages = $DB->get_records('block_helloworld');

                    $edit = '';
                    if (is_siteadmin()) {
                        $pageparam = array('blockid' => $this->instance->id,
                            'courseid' => $COURSE->id,
                            'id' => '');

                        //edit
                        $editurl = new moodle_url('/blocks/talkto/edit.php', $pageparam);
                        $editpicurl = new moodle_url('/pix/t/edit.png');
                        $edit = html_writer::link($editurl, html_writer::tag('img', '', array('src' => $editpicurl, 'alt' => get_string('edit'))));
                    }

                    //Render box tutor
                    $this->content->text .= '<div class="box-content">';
                    $this->content->text .= '<ul class="boxes">';
                    $this->content->text .= $edit;
                    $this->content->text .= '<li class="box">';

                    $this->content->text .= '<p class="pull-left">Tutoria</p>';

                    $now = strtotime(date("Y-m-d H:i:s"));
                    $lastacess = strtotime(date(gmdate("Y-m-d H:i:s", $teacher->lastaccess)));
                    $secs = $now - $lastacess;

                    if ($secs < 350) {
                        $this->content->text .= '<span style="margin-right: 10px;" class="text-success pull-right">(Online)</span></br>';
                    } else {
                        $this->content->text .= '<span style="margin-left: -10px;" class="text-danger pull-right">(Offline)</span></br>';
                    }

                    $this->content->text .= '<img src="' . $profile . '"/>';

                    $this->content->text .= '<div class="row pull-right">';

                    $name = $teacher->firstname;
                    preg_replace('/\s(d[A-z]{1,2}|a(.){1,2}?|e(.){1,2}?|le{1}|[A-z.]{1,2}\s)/i', ' ', $name);
                    preg_replace('/\s+/i', ' ', $name);
                    $name = explode(" ", $teacher->firstname);

                    $this->content->text .= '<span class="pull-right"><a href="#" class="perfil_supervisor_link brand close-modal-small" data-toggle="modal" data-target="#modalSupervisor">' . $name[0] . ' ' . $name[count($name) - 1] . '</a></span></br>';
                    $this->content->text .= '<span class="pull-right"><a href="#" class="perfil_supervisor_link brand close-modal-small" data-toggle="modal" data-target="#modalSupervisorChat">Fale com o tutor</a></span>';
                    $this->content->text .= '</div">';

                    $this->content->text .= '</li>';
                    $this->content->text .= '</ul>';
                    $this->content->text .= '</div>';


                    include 'chatbox.php';
                    $this->content->text .= '<div style="width: 60%;" id="modalSupervisorChat" class="modal modal-perfil fade hide" role="dialog" aria-hidden="true">';
                    $this->content->text .= '<div class="" role="document">';
                    $this->content->text .= '<div class="modal-content">';
                    $this->content->text .= '<div class="modal-body">';
                    $this->content->text .= '<button class="fas fa-window-close fa-1x pull-right" data-dismiss="modal" aria-label="Fechar"></button>';
                    $this->content->text .= '<div id="page-header">';
                    $this->content->text .= $html;
                    $this->content->text .= '</div></div></div></div></div>';

                    $this->content->text .= '<div style="width: 60%;" id="modalSupervisor" class="modal modal-perfil fade hide" role="dialog" aria-hidden="true">';
                    $this->content->text .= '<div class="" role="document">';
                    $this->content->text .= '<div class="modal-content">';
                    $this->content->text .= '<div class="modal-body">';
                    $this->content->text .= '<div id="page-header">';
                    $this->content->text .= '<button class="fas fa-window-close fa-2x pull-right" data-dismiss="modal" aria-label="Fechar"></button>';
                    $this->content->text .= '<div class="page-context-header">';
                    $this->content->text .= '<div class="page-header-image">';
                    $this->content->text .= '<a href="/ava/user/profile.php?id= ' . $teacher->id . '">';
                    $this->content->text .= '<img style="width: 30%;" src="' . $profile. '">';
                    $this->content->text .= '<div class="description"><p></p>' . $teacher->email . '</div>';
                    $this->content->text .= '</a></div><div class="page-header-headings"><h6>' . $teacher->firstname . '</h6></div>';
                    $this->content->text .= '</div>';

                    $this->content->text .= '<div class="description"><p></p>' . $teacher->description . '</div>';

                    $this->content->text .= '</div></div></div></div></div>';
                }
            }
        }
        else {
            $this->content->text = str_replace('MSG', 'Nenhum Tutor definido. Entre em contato com o suporte.', $msgrr);
        }

        //$PAGE->requires->css('/blocks/menu_mural_virtual/style.css');
        return $this->content;
    }

    public function html_attributes()
    {
        $attributes = parent::html_attributes();
        if (get_config('helloworld', 'Colored_Text')) {
            $attributes['class'] .= ' colored-text';
        }
        return $attributes;
    }

    public function applicable_formats() {
        return array('all' => true, 'my' => false);
    }

    public function has_config() {
        return false;
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
        $DB->delete_records('block_talkto', array('blockid' => $this->instance->id));
    }
}
