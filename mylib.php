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

    public static function base_event($event) {
        return true;
    }

    public static function manager($event) {
        global $DB;
        $listTables = array(
            'course',
            'course_sections',
            'course_modules',
            'course_categories',
            'quiz_slots',
            'question',
            'question_answers',
            'question_hints',
            'question_truefalse',
            'question_numerical',
            'question_numerical_options',
            'question_numerical_units',
            'question_calculated_options',
            'question_calculated',
            'qtype_multichoice_options',
            'question_multianswer',
            'qtype_randomsamatch_options',
            'qtype_shortanswer_options',
            'qtype_essay_options',
            'qtype_match_subquestions',
            'qtype_match_options',
            'question_datasets',
            'quiz_feedback',
            'book_chapters',
            'choice_options',
            'glossary_categories',
            'glossary_entries',
            'glossary_alias',
            'glossary_entries_categories',
            'lesson_pages',
            'lesson_answers',
            'wiki_subwikis',
            'wiki_pages',
            'wiki_versions',
            'wiki_links',
            'forum_discussions',
            'forum_posts',
            'workshop_old',
            'workshop_elements_old',
            'workshop_grades_old',
            'workshop_rubrics_old',
            'workshop_stockcomments_old',
            'workshop_submissions',
            'workshop_assessments',
            'workshop_grades',
            'workshopallocation_scheduled',
            'workshopeval_best_settings',
            'workshopform_accumulative',
            'workshopform_comments',
            'workshopform_numerrors',
            'workshopform_numerrors_map',
            'workshopform_rubric',
            'workshopform_rubric_levels',
            'workshopform_rubric_config',
            'feedback_item',
            'feedback_sitecourse_map',
            'feedback_template',
            'feedback_value',
            'feedback_valuetmp',
            'scorm_scoes',
            'scorm_scoes_data',
            'scorm_seq_mapinfo',
            'scorm_seq_objective',
            'scorm_seq_rolluprule',
            'scorm_seq_rolluprulecond',
            'scorm_seq_rulecond',
            'scorm_seq_ruleconds',
        );

        $newList = array(
            'user', 'role_assignments', 'user_preferences', 'user_enrolments'
        );

        $listEvent = array('created', 'updated', 'deleted');
        
        if (in_array($event->objecttable, $listTables)) {
            if (!empty($event->courseid) && in_array($event->action, $listEvent)) {
                $record = $DB->get_record('ls_course_version', array('course_id' => $event->courseid));
                if ($record) {
                    $record->version = $record->version + 1;
                    $DB->update_record('ls_course_version', $record);
                } else {
                    $record = new stdClass();
                    $record->course_id = $event->objectid;
                    $record->version = 1;
                    $DB->insert_record('ls_course_version', $record);
                }
            }
        }

        return true;
    }

}
