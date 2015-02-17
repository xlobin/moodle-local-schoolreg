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
 * Accept uploading files by web service token
 *
 * POST params:
 *  token => the web service user token (needed for authentication)
 *  filepath => the private file aera path (where files will be stored)
 *  [_FILES] => for example you can send the files with <input type=file>,
 *              or with curl magic: 'file_1' => '@/path/to/file', or ...
 *  filearea => 'private' or 'draft' (default = 'private'). These are the only 2 areas we are allowing
 *              direct uploads via webservices. The private file area is deprecated - please don't use it.
 *  itemid   => For draft areas this is the draftid - this can be used to add a list of files
 *              to a draft area in separate requests. If it is 0, a new draftid will be generated.
 *              For private files, this is ignored.
 *
 * @package    core_webservice
 * @copyright  2011 Dongsheng Cai <dongsheng@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/**
 * AJAX_SCRIPT - exception will be converted into JSON
 */
define('AJAX_SCRIPT', true);

/**
 * NO_MOODLE_COOKIES - we don't want any cookie
 */
define('NO_MOODLE_COOKIES', true);

require_once('../../config.php');
require_once('lib/SyncWebService.php');
$filepath = optional_param('filepath', '/', PARAM_PATH);
// The default file area is 'private' for user private files. This
// area is actually deprecated and only supported for backwards compatibility with
// the mobile app.
$filearea = optional_param('filearea', 'private', PARAM_ALPHA);
$itemid = optional_param('itemid', 0, PARAM_INT);
echo $OUTPUT->header();

// authenticate the user
//$token = required_param('token', PARAM_ALPHANUMEXT);
$token = optional_param('token', '', PARAM_RAW_TRIMMED);
$webservicelib = new SyncWebService();
$authenticationinfo = $webservicelib->authenticate_user($token);
if (!$authenticationinfo) {
    throw new moodle_exception('invalidtoken', 'webservice');
}
$filepath = $authenticationinfo['path'];

// check the user can manage his own files (can upload)
$USER->id = 2;
$context = context_user::instance($USER->id);
require_capability('moodle/user:manageownfiles', $context);

$fs = get_file_storage();

$totalsize = 0;
$files = array();
foreach ($_FILES as $fieldname => $uploaded_file) {
    // check upload errors
    if (!empty($_FILES[$fieldname]['error'])) {
        switch ($_FILES[$fieldname]['error']) {
            case UPLOAD_ERR_INI_SIZE:
                throw new moodle_exception('upload_error_ini_size', 'repository_upload');
                break;
            case UPLOAD_ERR_FORM_SIZE:
                throw new moodle_exception('upload_error_form_size', 'repository_upload');
                break;
            case UPLOAD_ERR_PARTIAL:
                throw new moodle_exception('upload_error_partial', 'repository_upload');
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new moodle_exception('upload_error_no_file', 'repository_upload');
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                throw new moodle_exception('upload_error_no_tmp_dir', 'repository_upload');
                break;
            case UPLOAD_ERR_CANT_WRITE:
                throw new moodle_exception('upload_error_cant_write', 'repository_upload');
                break;
            case UPLOAD_ERR_EXTENSION:
                throw new moodle_exception('upload_error_extension', 'repository_upload');
                break;
            default:
                throw new moodle_exception('nofile');
        }
    }
    $file = new stdClass();
    $file->filename = clean_param($_FILES[$fieldname]['name'], PARAM_FILE);
    // check system maxbytes setting
    if (($_FILES[$fieldname]['size'] > get_max_upload_file_size($CFG->maxbytes))) {
        // oversize file will be ignored, error added to array to notify
        // web service client
        $file->errortype = 'fileoversized';
        $file->error = get_string('maxbytes', 'error');
    } else {
        $file->filepath = $_FILES[$fieldname]['tmp_name'];
        // calculate total size of upload
        $totalsize += $_FILES[$fieldname]['size'];
    }
    $files[] = $file;
}

$fs = get_file_storage();

// Get any existing file size limits.
$maxareabytes = FILE_AREA_MAX_BYTES_UNLIMITED;
$maxupload = get_user_max_upload_file_size($context, $CFG->maxbytes);

$results = array();
foreach ($files as $file) {
    if (!empty($file->error)) {
        // including error and filename
        $results[] = $file;
        continue;
    }
    $file_record = new stdClass;
    $file_record->component = 'user';
    $file_record->contextid = $context->id;
    $file_record->userid = $USER->id;
    $file_record->filearea = $filearea;
    $file_record->filename = $file->filename;
    $file_record->filepath = $filepath;
    $file_record->itemid = $itemid;
    $file_record->license = $CFG->sitedefaultlicense;
    $file_record->author = $authenticationinfo['id'];
    $file_record->source = '';

    $contents = file_get_contents($file->filepath);
    $path = $CFG->dirroot . $filepath . '/' . $file->filename;
    $hasil = file_exists($path) ? false : file_put_contents($path, $contents);
    if ($hasil) {
        $backup = new stdClass();
        $backup->file_location = $path;
        $backup->school_id = $authenticationinfo['id'];
        $backup->time = date('Y-m-d H:i:s');
        $DB->insert_record('ls_backupdatabaselog', $backup, false);
        $results[] = $file_record;
        $results['success'] = true;
    }else{
        $results['success'] = false;
        $results['message'] = get_string('filenameexist', 'webservice', $path);
    }
}
echo json_encode($results);
