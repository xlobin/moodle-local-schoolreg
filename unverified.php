<?php

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/tablelib.php');

$strscname = get_string('school_name', 'local_schoolreg');
$strreg_date = get_string('reg_date', 'local_schoolreg');
$strpictitle = get_string('title', 'local_schoolreg');
$strpicname = get_string('full_name', 'local_schoolreg');
$strpicemail = get_string('email');
$strview = get_string('view', 'local_schoolreg');
$strdelete = get_string('delete', 'local_schoolreg');
$straccept = get_string('accept', 'local_schoolreg');

require_login();
admin_externalpage_setup('unverifiedschool');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('unverified_school', 'local_schoolreg'));

if(!empty($_GET['msg']) && !empty($_GET['actres'])){
    if($_GET['actres'] == "1"){
        $stat = "alert-success";
    }else{
        $stat = "alert-error";
    }
    echo '<div class="alert '.$stat.'">'.base64_decode($_GET['msg']);
    echo "</div>";
}

$spage = optional_param('spage', 0, PARAM_INT);
$ssort = optional_param('ssort', 'school_name', PARAM_ALPHANUMEXT);
$perpage = 20;

$table = new flexible_table('admin-blocks-compatible');
$table->define_columns(array('school_name', 'pic_name', 'pic_email', 'reg_date', 'view', 'delete', 'accept'));
$table->define_headers(array($strscname, $strpicname, $strpicemail, $strreg_date, $strview, $strdelete, $straccept));
$table->set_control_variables(array(
    TABLE_VAR_SORT => 'ssort',
    TABLE_VAR_IFIRST => 'sifirst',
    TABLE_VAR_ILAST => 'silast',
    TABLE_VAR_PAGE => 'spage'
));
$table->define_baseurl($CFG->wwwroot.'/local/schoolreg/unverified.php');
$table->set_attribute('class', 'admintable blockstable generaltable');
$table->set_attribute('id', 'compatibleblockstable');

$jumlah = $DB->count_records('local_school', array('verified' => 0));

$table->pagesize($perpage, $jumlah);
$table->sortable(true, 'reg_date', SORT_ASC);
$table->no_sorting('view');
$table->no_sorting('accept');
$table->no_sorting('delete');
$table->set_attribute('cellspacing', '0');

$table->setup();
$sort = $table->get_sql_sort();

if (!$unverified = $DB->get_records('local_school', array('verified' => 0), $sort, '*', ($spage * $perpage), $perpage)) {
    //print_error('noblocks', 'error');  // Should never happen
    echo '<div class="alert alert-error">'.get_string('data_empty', 'local_schoolreg').'</div>';
}

$tablerows = array();

if(count($unverified)>0){
    foreach ($unverified as $rows) {
        $view_btn = '<a href="javascript:void(0)" class="view_btn" sid="'.$rows->id.'" title="View '.$rows->school_name.'">'.'<img src="'.$OUTPUT->pix_url('t/hide') . '" class="iconsmall" /></a>';
        $delete_btn = '<a href="javascript:void(0)" onclick="delcon(\''.base64_encode($rows->id).'\')" title="Delete '.$rows->school_name.'">'.'<img src="'.$OUTPUT->pix_url('t/delete') . '" class="iconsmall" /></a>';
        $accept_btn = '<a href="action.php?act=acc&sch_id='.base64_encode($rows->id).'" title="Accept '.$rows->school_name.' Registration">'.'<img src="'.$OUTPUT->pix_url('t/check') . '" class="iconsmall" /></a>';
        $row = array(
            $rows->school_name,
            $rows->pic_title.'. '.$rows->pic_name,
            $rows->pic_email,
            date('d/m/Y', strtotime($rows->reg_date)),
            $view_btn,
            $delete_btn,
            $accept_btn
        );
        $table->add_data($row);
    }

    $table->print_html();
}
?>
<link href="asset/css/jsmodal-dark.css" media="screen" rel="stylesheet" type="text/css" />
<link href="asset/css/schoolreg.css" media="screen" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="asset/js/jsmodal-1.0d.min.js"></script>
<script type="text/javascript" src="http://code.jquery.com/jquery-1.11.2.min.js"></script>
<script>
    function delcon(eid){
        if(confirm("<?php echo get_string('delete_conf', 'local_schoolreg'); ?>")){
            window.location.href = "action.php?act=del&sch_id="+eid;
        }
    }

    $(".view_btn").click(function(){
        var sid = $(this).attr('sid');
        $.post(
            'action_verified.php',
            {sid:sid, act:'det'},
            function(response){
                Modal.open({
                    content: response,
                    width: '600px',
                    height: '200px',
                    hideclose: true,
                    draggable: false
                });
            }
        );
    });
</script>
<?php
echo $OUTPUT->footer();