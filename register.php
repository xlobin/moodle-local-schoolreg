<?php

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('reg_form.php');

$PAGE->set_title(get_string('register_school', 'local_schoolreg'));
$PAGE->set_pagelayout('standard');
$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/schoolreg/register.php');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('register_school', 'local_schoolreg'));

$mform = new reg_form();

if ($mform->is_cancelled()) { // kalau di cancel
    redirect('../../');
} else if ($fromform = $mform->get_data()) { // kalau di submit
    $reg = optional_param('reg', 0, PARAM_INT);
    if (!empty($reg)) {
        $school_name = optional_param('school_name', '', PARAM_RAW_TRIMMED);
        $school_address = optional_param('school_address', '', PARAM_RAW_TRIMMED);
        $email = optional_param('email', '', PARAM_EMAIL);
        $title = optional_param('title', '', PARAM_RAW_TRIMMED);
        $full_name = optional_param('full_name', '', PARAM_RAW_TRIMMED);
        $record = new stdClass();
        $record->school_name = optional_param($school_name, '', PARAM_RAW_TRIMMED);
        $record->school_address = optional_param($school_address, '', PARAM_RAW_TRIMMED);
        $record->pic_email = $email;
        $record->pic_title = $title;
        $record->pic_name = $full_name;
        $record->reg_date = date('Y-m-d H:i:s');
        $record->verified = 0;

        $jumlah = $DB->count_records('local_school', array('pic_email' => $email));
        if ($jumlah > 0) {
            echo $OUTPUT->notification(get_string('email_unique', 'local_schoolreg'), 'notifyproblem');
            $mform->set_data($toform);
            $mform->display();
        } else {
            if ($DB->insert_record('local_school', $record, false)) {
                echo '<div class="alert alert-success">' . get_string('reg_success', 'local_schoolreg') . '</div>';
            } else {
                echo '<div class="alert alert-error">' . get_string('reg_failed', 'local_schoolreg') . '</div>';
            }
        }
    }
} else {
    $toform = array();
    $mform->set_data($toform);
    $mform->display();
}

echo $OUTPUT->footer();
