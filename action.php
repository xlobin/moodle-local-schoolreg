<?php

require_once('../../config.php');

$act = $_GET['act'];
$sch_id = $_GET['sch_id'];
if (!empty($act) && !empty($sch_id)) {
    $sid = base64_decode($sch_id);
    switch ($act) {
        case 'del' :
            if ($DB->delete_records('local_school', array('id' => $sid))) {
                $message = get_string('del_success', 'local_schoolreg');
                $actres = "&actres=1";
            } else {
                $message = get_string('del_failed', 'local_schoolreg');
                $actres = "&actres=1";
            }
            break;
        case 'acc' :
            //$sch = $DB->get_record('local_school', array("verified" => 1, ""), 'id DESC', '*', 0, 1);
            $sql = "SELECT school_id FROM " . $CFG->prefix . "local_school WHERE verified = 1 ORDER BY school_id DESC LIMIT 1";
            $sch = $DB->get_record_sql($sql, array(), $strictness = IGNORE_MISSING);
            $last_id = 0;
            if (count($sch) > 0) {
                if (!empty($sch->school_id) && $sch->school_id != "" && $sch->school_id != null) {
                    $last_id = $sch->school_id;
                }
            }
            $school_id = gen_schoolid($last_id);
            $school_key = gen_schoolkey($sid, $school_id);

            $upd = new stdClass();
            $upd->id = $sid;
            $upd->verified = 1;
            $upd->school_id = $school_id;
            $upd->school_key = $school_key;
            if ($DB->update_record('local_school', $upd, $bulk = false)) {
                $sch_det = $DB->get_record('local_school', array('id' => $sid));
                gen_folder($school_id);
                send_email($sch_det);
                $message = get_string('acc_success', 'local_schoolreg');
                $actres = "&actres=1";
            } else {
                $message = get_string('acc_failed', 'local_schoolreg');
                $actres = "&actres=0";
            }
            break;
    }
    redirect('unverified.php?msg=' . base64_encode($message) . $actres);
} else {
    redirect('unverified.php');
}

function gen_schoolid($last_id) {
    $pref = "SCHID";
    $Id = substr($last_id, 5, 5);
    $newId = (int) $Id + 1;
    $newId = $pref . str_pad($newId, 5, 0, STR_PAD_LEFT);

    return $newId;
}

function gen_schoolkey($sid, $school_id) {
    return md5($sid . "-" . $school_id);
}

function gen_folder($school_id) {
    if (!file_exists("../../schdir")) {
        mkdir("../../schdir");
    }
    if ("../../schdir/" . $school_id) {
        mkdir("../../schdir/" . $school_id);
    }
}

function send_email($sch) {
    $to = $sch->pic_email;
    $subject = 'School Registration';
    $message = "Dear " . $sch->pic_title . ". " . $sch->pic_name . ",\n\n";
    $message .= "Congratulations, your registration for " . $sch->school_name . " are accepted.\n";
    $message .= "We would like to say welcome aboard. Here are \n";
    $message .= "School ID : " . $sch->school_id . "\n";
    $message .= "School Secret Key : " . $sch->school_key . "\n";
    $message .= "Keep this information only for your self.\n\n";
    $message .= "Please don't be hesitate to email us (support@CLOUDLMS.com) if you need further information.\n\n";
    $message .= "Best Regards,\n";
    $message .= "Webmaster";

    $headers = 'From: webmaster@CLOUDLMS.com' . "\r\n" .
            'Reply-To: noreply@CLOUDLMS.com' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

    mail($to, $subject, $message, $headers);
}
