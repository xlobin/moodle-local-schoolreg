<?php

defined('MOODLE_INTERNAL') || die();

class local_synchronization_event_handler {

    public static function course_created($event) {
        global $DB, $CFG;

        $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'trusttext' => false, 'noclean' => true);
        // Editor should respect category context if course context is not set.
        $coursecontext = context_course::instance($event->objectid, MUST_EXIST);
        $editoroptions['context'] = $coursecontext;
        $editoroptions['subdirs'] = file_area_contains_subdirs($coursecontext, 'course', 'summary', 0);

        $record = new stdClass();
        $record->course_id = $event->objectid;
        $data = $_POST;
        unset($data['sesskey']);
        unset($data['_qf__course_edit_form']);
        $record->course_data = json_encode((object) $data);
        $record->course_overviewfiles = json_encode($editoroptions);
        $DB->insert_record('ls_course_version', $record);

        return true;
    }

    public static function course_updated($event) {
        global $DB, $CFG;

        $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'trusttext' => false, 'noclean' => true);
        $coursecontext = context_course::instance($event->objectid, MUST_EXIST);
        $editoroptions['context'] = $coursecontext;
        $editoroptions['subdirs'] = file_area_contains_subdirs($coursecontext, 'course', 'summary', 0);

        $record = $DB->get_record('ls_course_version', array('course_id' => $event->objectid));
        $data = $_POST;
        unset($data['sesskey']);
        unset($data['_qf__course_edit_form']);
        $record->course_data = json_encode((object) $data);
        $record->course_overviewfiles = json_encode($editoroptions);
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
    
    public static function base_event($event){
        exit();
    }

}
