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
require_once('/lib/MySynchronizationServer.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
$filepath = optional_param('filepath', '/', PARAM_PATH);
// The default file area is 'private' for user private files. This
// area is actually deprecated and only supported for backwards compatibility with
// the mobile app.
$filearea = optional_param('filearea', 'private', PARAM_ALPHA);
$itemid = optional_param('itemid', 0, PARAM_INT);
$version = optional_param('version', 0, PARAM_INT);
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

$contents = file_get_contents($filepath);

$fs = get_file_storage();

// Get any existing file size limits.
$maxareabytes = FILE_AREA_MAX_BYTES_UNLIMITED;
$maxupload = get_user_max_upload_file_size($context, $CFG->maxbytes);

$results = array(
    'success' => false,
    'message' => 'Failed to create new synchronization.'
);
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

    if ($contents = file_get_contents($file->filepath)) {

        $syncLog = new stdClass();
        $syncLog->time = date('Y-m-d H:i:s');
        $syncLog->version = $version;
        $syncLog->school_id = $authenticationinfo['id'];
        $syncLog->path = $CFG->dirroot . $filepath . '/' . $file->filename;

        $hasil = file_put_contents($syncLog->path, $contents);

        $tempFile = $syncLog->path;

        if ($hasil) {
            $zip = new ZipArchive();

            if ($zip->open($tempFile) === TRUE) {

                $path = $CFG->dataroot . '/temp/backup/';
                if (!file_exists($path)) {
                    mkdir($path);
                }

                function Delete($path, $parentDelete = true) {
                    if (is_dir($path) === true) {
                        $files = array_diff(scandir($path), array('.', '..'));
                        foreach ($files as $file) {
                            Delete(realpath($path) . '/' . $file);
                        }
                        return (($parentDelete) ? rmdir($path) : true);
                    } else if (is_file($path) === true) {
                        return unlink($path);
                    }
                    return false;
                }

                $parentPath = $path . '1234567890';

                $zip->extractTo($parentPath);
                $zip->close();

                $files = scandir($parentPath);
                $transaction = $DB->start_delegated_transaction();

                $returnId = array();
                $match = true;
                foreach ($files as $file) {

                    if (strpos($file, 'mbz') !== false) {

                        $courseid = str_replace("course_", "", $file);
                        $courseid = str_replace(".mbz", "", $courseid);
                        $folder = '10000' . $courseid;
                        if ($zip->open($parentPath . DIRECTORY_SEPARATOR . $file) === TRUE) {
                            $zip->extractTo($path . DIRECTORY_SEPARATOR . $folder);
                            $zip->close();

                            $categoryid = $authenticationinfo['category']; // e.g. 1 == Miscellaneous
                            $jumlahCourse = $DB->count_records('course', array('id' => $courseid));
                            if ($jumlahCourse > 0) {
                                delete_course($courseid, false);
                                $userdoingrestore = 2; // e.g. 2 == admin
                                $course_id = restore_dbops::create_new_course('', '', $categoryid);
                                $controller = new restore_controller($folder, $course_id, backup::INTERACTIVE_NO, backup::MODE_GENERAL, $userdoingrestore, backup::TARGET_NEW_COURSE);
                                $controller->execute_precheck();
                                $controller->execute_plan();

                                $update = $DB->get_record('ls_version', array('course_id' => $courseid));
                                $update->course_id = $course_id;
                                $DB->update_record('ls_version', $update);

                                $returnId[$courseid] = $course_id;
                                
                            } else {
                                $match = false;
                                break;
                            }
                            Delete($path . DIRECTORY_SEPARATOR . $folder, false);
                        } else {
                            $match = false;
                            break;
                        }
                    }
                }
                Delete($parentPath, false);
                if ($match) {
                    $transaction->allow_commit();
                    if ($hasil && $DB->insert_record('ls_synchronizelog', $syncLog, false)) {
                        purge_all_caches();
                        $results = array(
                            'success' => true,
                            'message' => 'Successfully create new synchronization',
                            'result' => $returnId
                        );
                    }
                } else {
                    $results['message'] = $results['message'] . ' Package not recognized, please try to create another package.';
                }
            }
        }
    }
}
echo json_encode($results);
