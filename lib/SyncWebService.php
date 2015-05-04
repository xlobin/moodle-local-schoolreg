<?php

/**
 * Sync Web Service.
 *
 * @package    local_schoolreg
 * @copyright  2015 Arie Dwiyana
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class SyncWebService {

    public $schoolToken = '';
    public $schoolID = '';
    private $_responses;

    /**
     * Request to server
     */
    public function authenticate_user($token) {
        global $DB;
        $token = explode(',', $token);
        $schoolToken = $token[0];
        $schoolID = $token[1];


        $sch = $DB->get_record('local_school', array('school_id' => $schoolID, 'school_key' => $schoolToken));
        if ($sch) {
            $this->_responses = array(
                'id' => $sch->id,
                'school_name' => $sch->school_name,
                'category' => $sch->category,
                'school_id' => $sch->school_id,
                'school_key' => $sch->school_key,
                'path' => '/schdir/' . $sch->school_id,
            );
        } else {
            $this->_responses = false;
        }

        return $this->_responses;
    }

}
