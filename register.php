<?php

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('reg_form.php');


$PAGE->set_pagelayout('standard');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('register_school', 'local_schoolreg'));

$adminroot = admin_get_root(false, false); // settings not required for external pages
$extpage = $adminroot->locate('registerschool', true);

navigation_node::require_admin_tree();

$actualurl = '/local/schoolreg/register.php';
if ($extpage) {
    $actualurl = $extpage->url;
}

$PAGE->set_url($actualurl);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('register_school', 'local_schoolreg'));

$mform = new reg_form();

if ($mform->is_cancelled()) { // kalau di cancel
    redirect('../../');
} else if ($fromform = $mform->get_data()) { // kalau di submit
    $reg = optional_param('reg', 0, PARAM_INT);
    if (!empty($reg)) {
        $record = new stdClass();
        $record->school_name = $fromform->school_name;
        $record->school_address =  $fromform->school_address;
        $record->pic_email =  $fromform->email;
        $record->pic_title = $fromform->title;
        $record->pic_name = $fromform->full_name;
        $record->reg_date = date('Y-m-d H:i:s');
        $record->verified = 0;

        $jumlah = $DB->count_records('local_school', array('pic_email' => $fromform->full_name));
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
