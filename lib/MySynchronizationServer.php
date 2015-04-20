<?php

require_once(__DIR__ . '/course.php');
require_once(__DIR__ . '/moodle_relational_table.php');

/**
 * Execute Synchronization
 *
 * @author Muhammad Bahjah
 */
class MySynchronizationServer {

    public $response;
    public $status;
    public $school_id;
    public $courseid;
    public $files = array();
    public $attributes = array();
    private $_moodleRelation;
    public $DB;
    public $updateLocal = array();

    public function __construct($params) {
        global $DB;
        $this->DB = $DB;
        $this->_moodleRelation = new moodle_relational_table();
        $this->response = $params['response'];
        $this->school_id = $params['school_id'];
        $this->parseResponse();
    }

    /**
     * parse xml Response
     */
    public function parseResponse() {
        $this->response = json_decode($this->response);
    }

    /**
     * get Item of object
     * @param type array object
     * @return type
     */
    public function getChild($child, $parent = false) {
        $success = true;
        foreach ($child as $key => $value) {
            foreach ($value as $item) {
                $itemChild = false;
                if (property_exists($item, 'my_item')) {
                    $itemChild = (array) $item->my_item;
                    unset($item->my_item);
                }

                if ($parent) {
                    $this->_moodleRelation->setTable($parent, array(
                        'tableName' => $key,
                        'tableData' => $item
                    ));
                    $item = $this->_moodleRelation->fixRelation();
                }

                $item = $this->executeQuery($item, $key);
                if (isset($parent['tableName']) && $parent['tableName'] == 'course_modules' && $item) {
                    $update = $parent['tableData'];
                    $update->instance = $item->id;
                    $this->DB->update_record($parent['tableName'], $update);
                }
                $this->_moodleRelation->create_context_table($key, $item);
                $success = $success && $item;
                if ($itemChild && $success) {
                    $success = $success && $this->getChild($itemChild, array(
                                'tableName' => $key,
                                'tableData' => $item
                    ));
                    $item = $this->_moodleRelation->updateRelation($key, $item);
                    if ($item) {
                        $this->DB->update_record($key, $item);
                    }
                }
            }
        }

        return $success;
    }

    /**
     * executing query
     * @global type $DB
     * @param object $query
     * @param string $table
     * @return boolean
     */
    public function executeQuery($query, $table = '') {

        $exceptional = array(
            'course', 'course_categories'
        );

        if (!empty($table)) {
            if (in_array($table, $exceptional)) {
                return $query;
            }

            if (property_exists($query, 'my_id') && !empty($query->my_id)) {
                $query->id = $query->my_id;
                unset($query->my_id);
                $condition = array('id' => $query->id);
            } else {
                if ($table == 'course_sections') {
                    $condition = array('course' => $query->course, 'section' => $query->section);
                    $dbData = $this->DB->get_record($table, $condition);
                    if ($dbData) {
                        $query->id = $dbData->id;
                    }
                } else {
                    $condition = array('id' => $query->id);
                }
                $clientId = $query->id;
            }

            foreach ($condition as $key => $cond) {
                $query->$key = $cond;
            }
            $jumlah = $this->DB->count_records($table, $condition);
            if ($jumlah > 0) {

                return ($this->DB->update_record($table, $query)) ? $query : false;
            } else {
                unset($query->id);
                $query->id = $this->DB->insert_record($table, $query);
                if (isset($clientId)){
                    $this->updateLocal[$table][$clientId] = $query->id;
                }
                return ($query) ? $query : false;
            }
        }

        return false;
    }

    /**
     * executing synchronization
     * @return boolean
     */
    public function execute() {
        $files = $this->files;

        if ($files) {
            $files = (array) json_decode($files[0]);
        }

        $query = (array) $this->response;

        try {
            $transaction = $this->DB->start_delegated_transaction();
            $success = true;

            if (isset($query)) {
                $this->getChild($query);
            }

            if (isset($files)) {
                $fs = get_file_storage();
                foreach ($files as $file) {
                    $file = (object) $file;
                    $modules_id = '';
                    if (property_exists($file, 'my_url')) {
                        $modules_id = $file->modules_id;
                        $my_url = $file->my_url;
                        unset($file->my_url);
                    }
                    unset($file->modules_id);
                    if (!empty($modules_id)) {
                        $record = $this->DB->get_record('course_modules', array('my_id' => $modules_id));
                        $context = context_module::instance($record->id);
                        $file->contextid = $context->id;
                        $fs->create_file_from_url($file, $my_url);
                    }
                }
            }
            $transaction->allow_commit();
        } catch (Exception $exc) {
            $transaction->rollback($exc);
        }
        return $success;
    }
    
    public function getUpdateLocal(){
        return $this->updateLocal;
    }

}
