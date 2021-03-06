<?php
require_once($CFG->libdir .'/accesslib.php');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of moodle_relational_table
 *
 * @author eBiz marketing
 */
class moodle_relational_table {

    public $aturan = array();
    public $table;
    public $childTable;
    public $tableData;
    public $childTableData;
    public $DB;
    
    public function __construct() {
        global $DB;
        $this->DB = $DB;
    }

    public function setTable($table, $childTable) {
        $this->table = $table['tableName'];
        $this->childTable = $childTable['tableName'];
        $this->tableData = $table['tableData'];
        $this->childTableData = $childTable['tableData'];
    }

    public function get_course_sections_to_course_modules() {
        return array(
            'fk' => 'section'
        );
    }

    public function get_course_sections_update_relation() {
        return array(
            'course_modules' => array(
                'fk' => 'section', 
                'table_fk' => 'sequence',
                'type' => 'string_concat'
            )
        );
    }

    public function getRelation() {
        $methodName = (string) 'get_' . $this->table . '_to_' . $this->childTable;
        if (method_exists($this, $methodName)) {
            return $this->$methodName();
        }
        return false;
    }

    public function create_context_table($table, $data) {
        switch ($table) {
            case 'course_categories':
                context_coursecat::instance($data->id);
                break;
            case 'course':
                context_course::instance($data->id);
                break;
            case 'course_modules':
                context_module::instance($data->id);
                break;
            case 'user':
                context_user::instance($data->id);
                break;
            default:
                break;
        }
    }

    public function fixRelation() {
        if ($relation = $this->getRelation()) {
            $id = $this->tableData->id;
            if (isset($relation['type'])) {
                
            } else {
                $propertyFK = $relation['fk'];
                if (property_exists($this->childTableData, $propertyFK))
                    $this->childTableData->$propertyFK = $id;
            }
        }

        return $this->childTableData;
    }
    
    public function updateRelation($table, $data){
        $methodName = 'get_'.$table.'_update_relation';
        if (method_exists($this, $methodName)) {
            foreach ($this->$methodName() as $key => $value){
                if ($value['type'] == 'string_concat'){
                    $fields = $this->DB->get_record($key, array($value['fk'] => $data->id), 'group_concat(id) as '.$value['fk']);
                }else{
                    $fields = $this->DB->get_record($key, array($value['fk'] => $data->id), 'id as '.$value['fk']);
                }
                $data->$value['table_fk'] = $fields->$value['fk'];
            }
            return $data;
        }

        return false;
    }

}
