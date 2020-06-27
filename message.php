<?php
/**
 * @package    block Fale com o tutor
 * @copyright  2019 UniProjecao
 * @author     Marcelo Cobias <marcelo.amorim@projecao.br>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



@$ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']);

if ($ajax) {
    define('AJAX_SCRIPT', true);
}

require_once(__DIR__.'/../../config.php');

$courseid = required_param('courseid', PARAM_INT);
$recipientid = required_param('recipientid', PARAM_INT);
$referurl = required_param('referurl', PARAM_URL);

$coursecontext = context_course::instance($courseid);
$PAGE->set_context($coursecontext);

require_login();
require_capability('moodle/site:sendmessage', $coursecontext);

$url = '/blocks/talk_to/message.php';
$PAGE->set_url($url);

$recipient = $DB->get_record('user', array('id' => $recipientid));

$customdata = array(
    'recipient' => $recipient,
    'referurl' => $referurl,
    'courseid' => $courseid
);
$mform = new block_talk_to\message_form(null, $customdata);

if ($mform->is_cancelled()) {
    // Form cancelled, redirect.
    redirect($referurl);
    exit();
} else if (($data = $mform->get_data())) {
    try {
        $mform->process($data);
    } catch (talk_to_no_recipient_exception $e) {
        if ($ajax) {
            header('HTTP/1.1 400 Bad Request');
            die($e->getMessage());
        } else {
            throw $e;
        }
    } catch (talk_to_message_failed_exception $e) {
        if ($ajax) {
            header('HTTP/1.1 500 Internal Server Error');
            die($e->getMessage());
        } else {
            throw $e;
        }
    }
    if ($ajax) {
        $output = html_writer::tag('p',
                                    get_string('messagesent', 'block_talk_to'),
                                    array('class' => 'talk_to_confirm'));
        echo json_encode(array('state' => 1, 'output' => $output));
    } else {
        redirect($data->referurl);
    }
    exit();
} else {

    // Form has not been submitted, just display it.
    if ($ajax) {
        ob_start();
        $mform->display();
        $form = ob_get_clean();
        if (strpos($form, '</script>') !== false) {
            $outputparts = explode('</script>', $form);
            $output = $outputparts[1];
            $script = str_replace('<script type="text/javascript">', '', $outputparts[0]);
        } else {
            $output = $form;
        }

        // Now it gets a bit tricky, we need to get the libraries and init calls for any Javascript used
        // by the form element plugins.
        $headcode = $PAGE->requires->get_head_code($PAGE, $OUTPUT);
        $loadpos = strpos($headcode, 'M.yui.loader');
        $cfgpos = strpos($headcode, 'M.cfg');
        $script .= substr($headcode, $loadpos, $cfgpos - $loadpos);
        $endcode = $PAGE->requires->get_end_code();
        $script .= preg_replace('/<\/?(script|link)[^>]*>/', '', $endcode);

        $output = html_writer::tag('div', $form, array('id' => 'talk_to_form'));

        echo json_encode(array('state' => 0, 'output' => $output, 'script' => $script));

    } else {
        echo $OUTPUT->header();
        $mform->display();
        echo $OUTPUT->footer();
    }
}
