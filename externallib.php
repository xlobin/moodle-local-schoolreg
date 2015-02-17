<?php

/**
 * PLUGIN external file
 *
 * @package    local_PLUGIN
 * @copyright  20XX YOURSELF
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . "/externallib.php");

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
            'type' => new external_value(PARAM_BOOL, 'Type', VALUE_DEFAULT, 0),
                )
        );
    }

    /**
     * The function itself
     * @return string welcome message
     */
    public static function getcontent($courseid, $type='0') {

        global $USER, $DB;

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
            foreach ($listCourse as $courseid) {
                $listParam .= ((strlen($listParam) > 0) ? "," : "") . ':courseid' . $courseid;
                $listId['courseid' . $courseid] = $courseid;
            }
            $courses = $DB->get_records_sql('SELECT *from {course} where category != 0 and id not in (' . $listParam . ')', $listId);
        } else {
            $courses = array($DB->get_record('course', array('id' => $courseid)));
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
                'id' => $course->id,
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
                'course_sections' => ''
            );
            
            if ($type) {
                foreach ($listSql as $row) {
                    $inserts = array();
                    if ($row == 'course_categories') {
                        $condition = array('id' => $result[$key]['course_categories']);
                        $inserts = $DB->get_records($row, $condition);
                    } else if ($row == 'course') {
                        $condition = array('id' => $result[$key]['id']);
                        $inserts = $DB->get_records($row, $condition);
                    } else {
                        $condition = array('course' => $result[$key]['id']);
                    }
                    $sql = '';
                    foreach ($inserts as $insert) {
                        $valueSql = '(';
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
                        $sql = $columnSql . ' values ' . $valueSql;
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
            'category' => new external_value(PARAM_INT, 'Course Short Name'),
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
            'course_sections' => new external_value(PARAM_RAW_TRIMMED, '')
        );

        return new external_multiple_structure(
                new external_single_structure($column), 'List of Course'
        );
    }

}
