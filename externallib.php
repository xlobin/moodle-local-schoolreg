<?php

/**
 * PLUGIN external file
 *
 * @package    local_PLUGIN
 * @copyright  20XX YOURSELF
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . "/externallib.php");
require_once($CFG->libdir . '/coursecatlib.php');
require_once(__DIR__ . '/lib/moodlelib.php');

class local_schoolreg_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function getcontent_parameters() {
        // FUNCTIONNAME_parameters() always return an external_function_parameters(). 
        // The external_function_parameters constructor expects an array of external_description.
        return new external_function_parameters(
                array(
            'courseid' => new external_value(PARAM_ALPHANUMEXT, 'Course Id', VALUE_DEFAULT, NULL),
            'type' => new external_value(PARAM_ALPHANUM, 'Type', VALUE_DEFAULT, 0),
                )
        );
    }

    /**
     * The function itself
     * @return string welcome message
     */
    public static function getcontent($courseid, $type = '0') {

        global $USER, $DB, $CFG;

        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(self::getcontent_parameters(), array(
                    'courseid' => $courseid,
                    'type' => $type
                        )
        );
        $courseid = $params['courseid'];
        $type = $params['type'];

        if (!$type) {
            $listCourse = explode('_', $courseid);
            $listParam = '';
            $listId = array();
            foreach ($listCourse as $key => $courses) {
                $course = explode('-', $courses);
                if (isset($course[0], $course[1])) {
                    $listId[$course[0]] = $course[1];
                }
            }
            $courses = $DB->get_records_sql('SELECT *from {course} left join {ls_course_version} on {ls_course_version}.course_id = {course}.id where category != 0');
            foreach ($courses as $key => $value) {
                if (isset($listId[$value->course_id])) {
                    if ($listId[$value->course_id] == $value->version) {
                        unset($courses[$key]);
                    } else if ($value->version == '-1') {
                        $courses[$key]->status = 'u';
                    } else if ($listId[$value->course_id] < $value->version) {
                        $courses[$key]->status = 'u';
                    } else if ($listId[$value->course_id] > $value->version) {
                        $courses[$key]->status = 'u';
                    }
                    unset($listId[$value->course_id]);
                } else {
                    $courses[$key]->status = 'c';
                }
            }

            foreach ($listId as $id => $version) {
                $course = new stdClass();
                $course->course_id = $id;
                $course->fullname = '';
                $course->shortname = '';
                $course->summary = '';
                $course->category = '';
                $course->status = 'd';
                $courses[] = $course;
            }
        } else {
            $courses = $DB->get_records_sql('SELECT *from {course} left join {ls_course_version} on {ls_course_version}.course_id = {course}.id where {course}.id = :course_id', array('course_id' => $courseid));
        }

        //Context validation
        //OPTIONAL but in most web service it should present
        $context = get_context_instance(CONTEXT_USER, $USER->id);
        self::validate_context($context);

        $result = array();

        $listSql = array(
            'course_categories',
            'course',
            'course_completion_aggr_methd',
            'course_completion_crit_compl',
            'course_completion_criteria',
            'course_completions',
            'course_format_options',
            'course_modules',
            'course_modules_completion',
            'course_published',
            'course_request',
            'course_sections'
        );

        foreach ($courses as $key => $course) {
            $result[$key] = array(
                'id' => $course->course_id,
                'fullname' => $course->fullname,
                'shortname' => $course->shortname,
                'course_summary' => $course->summary,
                'category' => $course->category,
                'course_categories' => $course->category,
                'course' => '',
                'course_completion_aggr_methd' => '',
                'course_completion_crit_compl' => '',
                'course_completion_criteria' => '',
                'course_completions' => '',
                'course_format_options' => '',
                'course_modules' => '',
                'course_modules_completion' => '',
                'course_published' => '',
                'course_request' => '',
                'course_sections' => '',
                'status' => $course->status,
                'files' => '',
                'course_data' => '',
                'course_params_data' => '',
                'course_params_overview' => '',
                'version' => $course->version,
                'query' => '',
            );

            if ($type) {
                $sections = $DB->get_records('course_sections', array('course' => $course->course_id));
                foreach ($sections as $keySection => $section) {
                    if ($section->sequence != "" || $section->sequence) {
                        $modlist = explode(',', $section->sequence);
                        $modules = array();
                        foreach ($modlist as $mod) {
                            $module = $DB->get_record('course_modules', array('id' => $mod));
                            $mod_type = $DB->get_record('modules', array('id' => $module->module));
                            $module_item = $DB->get_records($mod_type->name, array('id' => $module->instance));
                            foreach ($module_item as $keyItem => $item) {
                                $functionName = 'getMy' . ucfirst(strtolower($mod_type->name));
                                if (function_exists($functionName)) {
                                    if ($moduleItem = $functionName($item))
                                        $module_item[$keyItem]->my_item = $moduleItem;
                                }
                            }
                            $module->my_item[$mod_type->name] = $module_item;
                            $modules[$module->id] = $module;
                        }
                        $section->my_item = array('course_modules' => $modules);
                    }
                    $sections[$keySection] = $section;
                }

                $sections = array(
                    'course_sections' => $sections,
                    'course_categories' => $DB->get_records('course_categories', array('id' => $course->category))
                );
                $result[$key]['query'] = json_encode($sections);

                $query = "select {files}.*, {context}.instanceid as modules_id from {files} join {context} on {files}.contextid = {context}.id where contextid in (
                        select id from {context} where path like (select concat(context_child.path,'/%') from {context} as context_child where contextlevel = " . CONTEXT_COURSE . " and instanceid = $courseid) and contextlevel = " . CONTEXT_MODULE . ")";

                $files = $DB->get_records_sql($query);
                $fs = get_file_storage();
                foreach ($files as $keyFile => $file_record) {
                    $file = $fs->get_file_instance($file_record);
                    if ($file->get_content()){
                        $files[$keyFile]->my_url = (string)new moodle_url('/local/schoolreg/getfile.php?id='.$file_record->id);
                    }
                }
                $files = json_encode($files);

                $result[$key]['files'] = $files;

                foreach ($listSql as $row) {
                    $inserts = array();
                    if ($row == 'course_categories') {
                        $condition = array('id' => $result[$key]['course_categories']);
                        $inserts = $DB->get_records($row, $condition);
                    } else if ($row == 'course') {
                        $condition = array('id' => $result[$key]['id']);
                        $inserts = $DB->get_records_sql('SELECT {course}.*, {ls_course_version}.version as sync_version from {course} left join {ls_course_version} on {ls_course_version}.course_id = {course}.id where {course}.id = :course_id', array('course_id' => $courseid));
                        $course_posted = $DB->get_record('ls_course_version', array('course_id' => $courseid));
                        $result[$key]['course_data'] = json_encode($inserts);
                        if ($course_posted) {
                            $result[$key]['course_params_data'] = $course_posted->course_data;
                            $result[$key]['course_params_overview'] = $course_posted->course_overviewfiles;
                        }
                    } else {
                        $condition = array('course' => $result[$key]['id']);
                    }
                    $sql = 'insert into {' . $row . '} ';
                    $valueSql = '';
                    foreach ($inserts as $insert) {
                        $valueSql .= ((strlen($valueSql) > 0) ? ',' : '') . '(';
                        $columnSql = '(';
                        foreach ($insert as $keyInsert => $valueInsert) {
                            $valueContainer = $valueInsert;
                            $columnContainer = "`" . addslashes($keyInsert) . "`";
                            if (!is_integer($valueContainer)) {
                                $valueContainer = "'" . addslashes($valueContainer) . "'";
                            }
                            $valueSql .= $valueContainer . ', ';
                            $columnSql .= $columnContainer . ', ';
                        }
                        $valueSql = substr($valueSql, 0, count($valueSql) - 3);
                        $columnSql = substr($columnSql, 0, count($columnSql) - 3);
                        $valueSql .= ')';
                        $columnSql .= ')';
                    }
                    if ($valueSql !== '') {
                        $sql .= $columnSql . ' values ' . $valueSql;
                    } else {
                        $sql = '';
                    }
                    $result[$key][$row] = $sql;
                }
            }
        }
        //Capability checking
        //OPTIONAL but in most web service it should present
        return $result;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function getcontent_returns() {
        $column = array(
            'id' => new external_value(PARAM_INT, 'Course ID'),
            'fullname' => new external_value(PARAM_TEXT, 'Course Name'),
            'course_summary' => new external_value(PARAM_CLEANHTML, 'Course Name'),
            'shortname' => new external_value(PARAM_TEXT, 'Course Short Name'),
            'category' => new external_value(PARAM_TEXT, 'Course Short Name'),
            'course_categories' => new external_value(PARAM_RAW_TRIMMED, ''),
            'course' => new external_value(PARAM_RAW_TRIMMED, ''),
            'course_completion_aggr_methd' => new external_value(PARAM_RAW_TRIMMED, ''),
            'course_completion_crit_compl' => new external_value(PARAM_RAW_TRIMMED, ''),
            'course_completion_criteria' => new external_value(PARAM_RAW_TRIMMED, ''),
            'course_completions' => new external_value(PARAM_RAW_TRIMMED, ''),
            'course_format_options' => new external_value(PARAM_RAW_TRIMMED, ''),
            'course_modules' => new external_value(PARAM_RAW_TRIMMED, ''),
            'course_modules_completion' => new external_value(PARAM_RAW_TRIMMED, ''),
            'course_published' => new external_value(PARAM_RAW_TRIMMED, ''),
            'course_request' => new external_value(PARAM_RAW_TRIMMED, ''),
            'course_sections' => new external_value(PARAM_RAW_TRIMMED, ''),
            'status' => new external_value(PARAM_RAW_TRIMMED, ''),
            'files' => new external_value(PARAM_RAW_TRIMMED, ''),
            'course_data' => new external_value(PARAM_RAW_TRIMMED, ''),
            'course_params_data' => new external_value(PARAM_RAW_TRIMMED, ''),
            'course_params_overview' => new external_value(PARAM_RAW_TRIMMED, ''),
            'version' => new external_value(PARAM_RAW_TRIMMED, ''),
            'query' => new external_value(PARAM_RAW, ''),
        );

        return new external_multiple_structure(
                new external_single_structure($column), 'List of Course'
        );
    }

}
