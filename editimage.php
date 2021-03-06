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

 * Splash Format - Columns based format that allows customization of the header

 *

 * @package    course/format

 * @subpackage splash

 * @copyright  2014 T Orbasido in respect to modifications of standard topics format and grid format.

 * @author     T Orbasido t.orbasido at gmail.com

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

 */

/* Imports */

require_once('../../../config.php');

require_once($CFG->dirroot . '/repository/lib.php');

require_once($CFG->dirroot . '/course/format/splash/editimage_form.php');

require_once($CFG->dirroot . '/course/format/splash/lib.php');


global $course;


/* Page parameters */

$contextid = required_param('contextid', PARAM_INT);

$sectionid = optional_param('id', null, PARAM_INT);



/* No idea, copied this from an example. Sets form data options but I don't know what they all do exactly */

$form_data = new stdClass();



$url = new moodle_url('/course/format/splash/editimage.php', array(

    'contextid' => $contextid,

    'sectionid' => $sectionid));



/* Not exactly sure what this stuff does, but it seems fairly straightforward */

list($context, $courseE, $cm) = get_context_info_array($contextid);



require_login($course, true, $cm);

if (isguestuser()) {

    print_error("You must be an admin to access this page.");

}



$PAGE->set_url($url);

$PAGE->set_context($context);



/* Functional part. Create the form and display it, handle results, etc */

$options = array(

    'subdirs' => 0,

    'maxfiles' => 1,

    'accepted_types' => array('image'),

    'return_types' => FILE_INTERNAL);



$mform = new splash_image_form(null, array(

    'contextid' => $contextid,

    'sectionid' => $sectionid,

    'options' => $options));



$draftitemid = file_get_submitted_draft_itemid('imagefile');




file_prepare_draft_area($draftitemid, $contextid, 'format_splash', "section", $sectionid,

                        array('subdirs' => 0, 'maxfiles' => 1));

						

if ($mform->is_cancelled()) {

    // Someone has hit the 'cancel' button.

    redirect(new moodle_url($CFG->wwwroot . '/course/view.php?id=' . $course->id));

	

} else if ($form_data = $mform->get_data()) { // Form has been submitted.

    

    //redirect(new moodle_url($CFG->wwwroot . '/course/view.php?id=' . $course->id));


    if ($draftitemid = file_get_submitted_draft_itemid('imagefile')) {
		print_r($_POST);

    	file_save_draft_area_files($draftitemid, $contextid, 'format_splash', "section", $sectionid, array('subdirs' => false, 'maxfiles' => 1));

	}

    

}



/* Draw the form */

$PAGE->set_heading("Header Image Upload");

echo $OUTPUT->header();

echo $OUTPUT->box_start('generalbox');

$mform->display();

echo $OUTPUT->box_end();

echo $OUTPUT->footer();