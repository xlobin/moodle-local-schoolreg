<?php
require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');

$strscname = get_string('school_name', 'local_schoolreg');
$all = optional_param('all', 0, PARAM_INT);
$course_id = optional_param('course_id', 0, PARAM_INT);
$category = optional_param('category', 0, PARAM_INT);
$redirectUrl = new moodle_url('/local/schoolreg/package.php');
if (!empty($all)) {
    $schools = $DB->get_records('local_school');
    foreach ($schools as $school) {
        if ($update = $DB->get_record('ls_version', array('category' => $school->category))) {
            $update->version++;
            $DB->update_record('ls_version', $update);
        } else {
            $new = new stdClass();
            $new->category = $school->category;
            $new->description = "Initial Package";
            $new->version = 0;
            $new->status = 1;
            $DB->insert_record('ls_version', $new);
        }

        $courses = $DB->get_records('course', array('category' => $school->category));
        foreach ($courses as $course) {

            $course_id = $course->id;
            $user_doing_the_backup = 2;

            $bc = new backup_controller(backup::TYPE_1COURSE, $course_id, backup::FORMAT_MOODLE, backup::INTERACTIVE_NO, backup::MODE_GENERAL, $user_doing_the_backup);
            $bc->execute_plan();

            if ($update = $DB->get_record('ls_version', array('course_id' => $course_id))) {
                $update->version++;
                $DB->update_record('ls_version', $update);
            } else {
                $new = new stdClass();
                $new->course_id = $course_id;
                $new->description = "Initial Package";
                $new->version = 0;
                $new->status = 1;
                $DB->insert_record('ls_version', $new);
            }
        }
    }

    redirect($redirectUrl, 'Successfully ' . get_string('repackage_all', 'local_schoolreg') . '.', 2);
}
if (!empty($category)) {
    if ($update = $DB->get_record('ls_version', array('category' => $category))) {
        $update->version++;
        $DB->update_record('ls_version', $update);
    } else {
        $new = new stdClass();
        $new->category = $category;
        $new->description = "Initial Package";
        $new->version = 0;
        $new->status = 1;
        $DB->insert_record('ls_version', $new);
    }

    $courses = $DB->get_records('course', array('category' => $category));
    foreach ($courses as $course) {

        $course_id = $course->id;
        $user_doing_the_backup = 2;

        $bc = new backup_controller(backup::TYPE_1COURSE, $course_id, backup::FORMAT_MOODLE, backup::INTERACTIVE_NO, backup::MODE_GENERAL, $user_doing_the_backup);
        $bc->execute_plan();

        if ($update = $DB->get_record('ls_version', array('course_id' => $course_id))) {
            $update->version++;
            $DB->update_record('ls_version', $update);
        } else {
            $new = new stdClass();
            $new->course_id = $course_id;
            $new->description = "Initial Package";
            $new->version = 0;
            $new->status = 1;
            $DB->insert_record('ls_version', $new);
        }
    }
    redirect($redirectUrl, 'Successfully ' . get_string('repackage_category', 'local_schoolreg') . '.', 2);
}
if (!empty($course_id)) {
    $user_doing_the_backup = 2;

    $bc = new backup_controller(backup::TYPE_1COURSE, $course_id, backup::FORMAT_MOODLE, backup::INTERACTIVE_NO, backup::MODE_GENERAL, $user_doing_the_backup);
    $bc->execute_plan();

    if ($update = $DB->get_record('ls_version', array('course_id' => $course_id))) {
        $update->version++;
        $DB->update_record('ls_version', $update);
    } else {
        $new = new stdClass();
        $new->course_id = $course_id;
        $new->description = "Initial Package";
        $new->version = 0;
        $new->status = 1;
        $DB->insert_record('ls_version', $new);
    }
    redirect($redirectUrl, 'Successfully ' . get_string('repackage_course', 'local_schoolreg') . '.', 2);
}

require_login();
admin_externalpage_setup('coursepackage');
$urlRepackage = new moodle_url('/local/schoolreg/package.php', array(
    'all' => 1,
        ));
$newSynchronization = html_writer::link($urlRepackage, get_string('repackage_all', 'local_schoolreg'), array(
            'class' => 'btn pull-right upload_btn'
        ));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('course_package', 'local_schoolreg') . ' ' . $newSynchronization);

if (!empty($_GET['msg']) && !empty($_GET['actres'])) {
    if ($_GET['actres'] == "1") {
        $stat = "alert-success";
    } else {
        $stat = "alert-error";
    }
    echo '<div class="alert ' . $stat . '">' . base64_decode($_GET['msg']);
    echo "</div>";
}

$spage = optional_param('spage', 0, PARAM_INT);
$ssort = optional_param('ssort', 'school_name', PARAM_ALPHANUMEXT);
$perpage = 20;

$table = new flexible_table('admin-blocks-compatible');
$table->define_columns(array('school_name', 'course', 'version', 'description', 'repackage'));
$table->define_headers(array($strscname, get_string('course_name', 'local_schoolreg'),
    get_string('version', 'local_schoolreg'), get_string('description', 'local_schoolreg'), get_string('repackage', 'local_schoolreg')));
$table->set_control_variables(array(
    TABLE_VAR_SORT => 'ssort',
    TABLE_VAR_IFIRST => 'sifirst',
    TABLE_VAR_ILAST => 'silast',
    TABLE_VAR_PAGE => 'spage'
));
$table->define_baseurl($CFG->wwwroot . '/local/schoolreg/unverified.php');
$table->set_attribute('class', 'admintable blockstable generaltable');
$table->set_attribute('id', 'compatibleblockstable');

$jumlah = $DB->count_records('local_school', array('verified' => 0));

$table->pagesize($perpage, $jumlah);
//$table->sortable(true, 'reg_date', SORT_ASC);
$table->no_sorting('view');
$table->no_sorting('accept');
$table->no_sorting('delete');
$table->set_attribute('cellspacing', '0');

$table->setup();
$sort = $table->get_sql_sort();
//
if (!$package = $DB->get_records_sql('select {course_categories}.name, {course_categories}.id, {ls_version}.version, {ls_version}.description from {local_school} '
        . 'left join {course_categories} on {local_school}.category = {course_categories}.id '
        . 'left join {ls_version} on {local_school}.category = {ls_version}.category and {ls_version}.course_id is null '
        . 'where verified = ?', array(1), ($spage * $perpage), $perpage)) {
    //print_error('noblocks', 'error');  // Should never happen
    echo '<div class="alert alert-error">' . get_string('data_empty', 'local_schoolreg') . '</div>';
}

$tablerows = array();

if (count($package) > 0) {
    foreach ($package as $rows) {
        $accept_btn = '<a href="package.php?category=' . $rows->id . '" title="Repackage ' . $rows->name . '">' . '<img src="' . $OUTPUT->pix_url('t/check') . '" class="iconsmall" /></a>';
        $row = array(
            $rows->name,
            '',
            $rows->version,
            $rows->description,
            $accept_btn
        );
        $table->add_data($row);
        $courses = $DB->get_records_sql('select {ls_version}.*, {course}.fullname from {course} '
                . 'left join {ls_version} on {course}.id = {ls_version}.course_id and {ls_version}.category is null ' . 'where {course}.category = ?', array($rows->id));
        foreach ($courses as $course) {
            $accept_btn = '<a href="package.php?course_id=' . $course->course_id . '" title="Repackage ' . $course->fullname . '">' . '<img src="' . $OUTPUT->pix_url('t/check') . '" class="iconsmall" /></a>';
            $row = array(
                '',
                $course->fullname,
                $course->version,
                $course->description,
                $accept_btn
            );
            $table->add_data($row);
        }
    }

    $table->print_html();
}
?>
<link href="asset/css/jsmodal-dark.css" media="screen" rel="stylesheet" type="text/css" />
<link href="asset/css/schoolreg.css" media="screen" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="asset/js/jsmodal-1.0d.min.js"></script>
<script type="text/javascript" src="http://code.jquery.com/jquery-1.11.2.min.js"></script>
<script>
    function delcon(eid) {
        if (confirm("<?php echo get_string('delete_conf', 'local_schoolreg'); ?>")) {
            window.location.href = "action.php?act=del&sch_id=" + eid;
        }
    }

    $(".view_btn").click(function() {
        var sid = $(this).attr('sid');
        $.post(
                'action_verified.php',
                {sid: sid, act: 'det'},
        function(response) {
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
