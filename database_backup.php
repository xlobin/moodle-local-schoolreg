<?php

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');

admin_externalpage_setup('localdatabasebackup');

$newBackup = optional_param('newbackup', 0, PARAM_INT);
$uploadBackup = optional_param('upload', 0, PARAM_INT);
$id = optional_param('id', 0, PARAM_INT);
$spage = optional_param('spage', 0, PARAM_INT);
$ssort = optional_param('ssort', 'time', PARAM_ALPHANUMEXT);
$perpage = 20;
$baseUrl = new moodle_url('/local/schoolreg/database_backup.php');

echo $OUTPUT->header();

echo $OUTPUT->heading('Database Backup');

$table = new flexible_table('tbl_backupdatabaselog');

$table->define_columns(array('time', 'school_id', 'file_location'));
$table->define_headers(array('Time', get_string('school_name', 'local_schoolreg'), 'File Location'));
$table->set_control_variables(array(
    TABLE_VAR_SORT => 'ssort',
    TABLE_VAR_IFIRST => 'sifirst',
    TABLE_VAR_ILAST => 'silast',
    TABLE_VAR_PAGE => 'spage'
));
$table->define_baseurl($baseUrl);
$table->set_attribute('class', 'admintable blockstable generaltable');
$table->set_attribute('id', 'ls_backupdatabase_table');

$jumlahBackupLog = $DB->count_records('ls_backupdatabaselog');
$table->pagesize($perpage, $jumlahBackupLog);
$table->sortable(true, 'time', SORT_DESC);
$table->set_attribute('cellspacing', '0');
$table->setup();
$sort = $table->get_sql_sort();
$backupLog = $DB->get_records('ls_backupdatabaselog', array(), $sort, '*', ($spage * $perpage), $perpage);
$urlUploadBackup = new moodle_url('/local/schoolreg/database_backup.php', array(
    'upload' => 1,
        ));
foreach ($backupLog as $key => $value) {
    $school = $DB->get_record('local_school', array('id' => $value->school_id),'school_name');
    $table->add_data(array(
        $value->time,
        $school->school_name,
        $value->file_location,
    ));
}
$table->print_html();

echo $OUTPUT->footer();