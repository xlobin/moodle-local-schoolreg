<?php

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {

    require_once($CFG->dirroot . '/local/schoolreg/lib.php');
    $admins = get_admins();
    $isadmin = false;
    foreach ($admins as $admin) {
        if ($USER->id == $admin->id) {
            $isadmin = true;
            break;
        }
    }

    if ($isadmin) {
        $listNodes = array(
            'registerschool' => array(
                'url' => new moodle_url('/local/schoolreg/register.php'),
                'text' => get_string('register_school', 'local_schoolreg')
            ),
            'unverifiedschool' => array(
                'url' => new moodle_url('/local/schoolreg/unverified.php'),
                'text' => get_string('unverified_school', 'local_schoolreg')
            ),
            'verifiedschool' => array(
                'url' => new moodle_url('/local/schoolreg/verified.php'),
                'text' => get_string('verified_school', 'local_schoolreg')
            ),
            'coursepackage' => array(
                'url' => new moodle_url('/local/schoolreg/package.php'),
                'text' => get_string('course_package', 'local_schoolreg')
            ),
            'localdatabasebackup' => array(
                'url' => new moodle_url('/local/schoolreg/database_backup.php'),
                'text' => get_string('database_backup', 'local_schoolreg')
            ),
            'localsynchronization' => array(
                'url' => new moodle_url('/local/schoolreg/synchronization.php'),
                'text' => get_string('log_synchronization', 'local_schoolreg')
            ),
        );

        $ADMIN->add('root', new admin_category('schoolreg', get_string('pluginname', 'local_schoolreg')), 'users');

        foreach ($listNodes as $key => $row) {
            $ADMIN->add('schoolreg', new admin_externalpage($key, $row['text'], $row['url']));
        }
    }
}