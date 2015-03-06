<?php

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$act = optional_param('act', 'det', PARAM_ALPHANUMEXT);
$sch_id = optional_param('sch_id', null, PARAM_ALPHANUMEXT);

if(!empty($act) && !empty($sch_id)){
    $sid = base64_decode($sch_id);
    switch($act){
        case 'del'  :
            if($DB->delete_records('local_school', array('id' => $sid))){
                $message = get_string('del_success', 'local_schoolreg');
                $actres = "&actres=1";
            }else{
                $message = get_string('del_failed', 'local_schoolreg');
                $actres = "&actres=1";
            }
            redirect('verified.php?msg='.base64_encode($message).$actres);
            break;
        case 'det'  :
            $sch = $DB->get_record('local_school', array('id' => $sch_id));
            echo '<table width="100%" cellpadding="2" border="0" class="schoolreg_table">';
            echo '<tr><td class="schoolreg_label" width="150">'.get_string('school_name','local_schoolreg').'</td><td>'.$sch->school_name.'</td></tr>';
            echo '<tr><td class="schoolreg_label" width="150">'.get_string('school_address','local_schoolreg').'</td><td>'.$sch->school_address.'</td></tr>';
            echo '<tr><td class="schoolreg_label" width="150">'.get_string('registrar','local_schoolreg').'</td><td>'.$sch->pic_title.'. '.$sch->pic_name.'</td></tr>';
            echo '<tr><td class="schoolreg_label" width="150">'.get_string('email','local_schoolreg').'</td><td>'.$sch->pic_email.'</td></tr>';
            if(!empty($sch->school_id) && !empty($sch->school_key)){
                echo '<tr><td colspan="2">&nbsp;</td></tr>';
                echo '<tr><td class="schoolreg_label" width="150">'.get_string('school_id','local_schoolreg').'</td><td>'.$sch->school_id.'</td></tr>';
                echo '<tr><td class="schoolreg_label" width="150">'.get_string('school_key','local_schoolreg').'</td><td>'.$sch->school_key.'</td></tr>';
            }
            echo '<tr><td colspan="2">&nbsp;</td></tr>';
            echo '<tr><td colspan="2" style="text-align: center"><input type="reset" id="id_resetbutton" onclick="Modal.close(); return false;" value="Close" name="resetbutton"></td></tr>';
            echo '</table>';
            break;
    }
}else{
    redirect('verified.php');
}