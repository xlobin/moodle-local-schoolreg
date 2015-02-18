<?php

defined('MOODLE_INTERNAL') || die();

class local_synchronization_event_handler {

    public static function course_created($event) {
        global $DB, $CFG;
        $record = new stdClass();
        $record->course_id = $event->objectid;
        $DB->insert_record('ls_course_version', $record);

        return true;
    }

    public static function course_updated($event) {
        global $DB, $CFG;

        var_dump($event->objectid);
        $record = $DB->get_record('ls_course_version', array('course_id' => $event->objectid));
        if ($record) {
            $record->version = $record->version + 1;
            $DB->update_record('ls_course_version', $record);
        } else {
            $record = new stdClass();
            $record->course_id = $event->objectid;
            $record->version = 1;
            $DB->insert_record('ls_course_version', $record);
        }

        return true;
    }

    public static function course_deleted($event) {
        global $DB;
        $record = $DB->get_record('ls_course_version', array('course_id' => $event->objectid));
        
        if ($record) {
            $record->version = -1;
            $DB->update_record('ls_course_version', $record);
        } else {
            $record = new stdClass();
            $record->course_id = $event->objectid;
            $record->version = -1;
            $DB->insert_record('ls_course_version', $record);
        }

        return true;
    }

}
