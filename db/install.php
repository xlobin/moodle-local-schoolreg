<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Post-install code for the synchronization plugin.
 *
 * @package    local
 * @subpackage synchronization
 * @copyright  2015 Arie Dwiyana
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function xmldb_local_schoolreg_install() {

    global $DB, $CFG;

//    $dblist = $DB->get_tables();
//    $dbman = $DB->get_manager();
//
//    $set_ai = "ALTER TABLE ".$CFG->prefix."local_school AUTO_INCREMENT = 10001";
//    $DB->execute($set_ai);
//
//    $sql = '';
//    foreach($dblist as $row){
//        $tablename = $CFG->prefix . $row;
//        if($row != "local_school" && $row != "local_synchronization"){
//            $check = $dbman->field_exists($row, 'school_id');
//            if(!$check){
//                //$sql .= "ALTER TABLE $tablename ADD school_id INT; ";
//                $xmldb_t = new xmldb_table($row);
//                $xmldb_f = new xmldb_field('school_id', XMLDB_TYPE_INTEGER);
//
//                $dbman->add_field($xmldb_t, $xmldb_f);
//            }
//        }
//    }
    //$DB->execute($sql);
    
}
