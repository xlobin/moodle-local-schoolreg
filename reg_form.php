<?php

require_once("../../lib/formslib.php");

class reg_form extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG;
        $title_array = array(
            "Mr" => "Mr.",
            "Mrs" => "Mrs."
        );

        $mform = $this->_form;

        $mform->addElement('hidden', 'reg', '1');

        $mform->addElement('text', 'email', get_string('email'), 'style="width:300px"');
        $mform->setType('email', PARAM_NOTAGS);
        $mform->addRule('email', get_string('missing_email', 'local_schoolreg'), 'required', null, 'server');
        $mform->setDefault('email',$this->_customdata['email']);

        $mform->addElement('select', 'title', get_string('title', 'local_schoolreg'), $title_array);
        $mform->addRule('title', get_string('missing_title', 'local_schoolreg'), 'required', null, 'server');

        $mform->addElement('text', 'full_name', get_string('full_name', 'local_schoolreg'), 'style="width:300px"');
        $mform->setType('full_name', PARAM_NOTAGS);
        $mform->addRule('full_name', get_string('missing_name', 'local_schoolreg'), 'required', null, 'server');
        $mform->setDefault('full_name',$this->_customdata['full_name']);

        $mform->addElement('header', 'school_data_header', get_string('school_data', 'local_schoolreg'));

        $mform->addElement('text', 'school_name', get_string('school_name', 'local_schoolreg'), 'style="width:80%"');
        $mform->setType('school_name', PARAM_NOTAGS);
        $mform->addRule('school_name', get_string('missing_schoolname', 'local_schoolreg'), 'required', null, 'server');
        $mform->setDefault('school_name',$this->_customdata['school_name']);

        $mform->addElement('textarea', 'school_address', get_string("school_address", "local_schoolreg"), 'wrap="virtual" rows="2" cols="60" style="resize:none;"');
        $mform->setType('school_address', PARAM_NOTAGS);
        $mform->addRule('school_address', get_string('missing_schooladdress', 'local_schoolreg'), 'required', null, 'server');
        $mform->setDefault('school_address',$this->_customdata['school_address']);

        /*if (!empty($CFG->recaptchapublickey) && !empty($CFG->recaptchaprivatekey)) {
            $mform->addElement('recaptcha', 'capcay');
            $mform->addRule('recaptcha', get_string('missing_recaptcha', 'local_schoolreg'), 'required', null, 'client');
        }*/

        $buttonarray=array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('submit'));
        $buttonarray[] = &$mform->createElement('reset', 'resetbutton', get_string('reset'));
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }
    //Custom validation should be added here
    function validation($data, $files) {
        $email = $_POST['email'];
        $error = array();
        if(! filter_var($email, FILTER_VALIDATE_EMAIL)){
            $error['email'] = get_string('missing_email', 'local_schoolreg');
        }
        return $error;
    }
}