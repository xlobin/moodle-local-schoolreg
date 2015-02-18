<?php

defined('MOODLE_INTERNAL') || die();

class local_synchronization_event_handler {
   public static function course_created($event){
        global $DB, $CFG;
        $record = new stdClass();
        $record->course_id = $event->objectid;
        $DB->insert('ls_course_version', $record);
        
        return true;
    }
}