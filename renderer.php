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

 * Renderer for outputting the splash course format.

 *

 * @package format_splash

 * @copyright 2014 T Orbasido with modifications to topics format

 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

 */





defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/format/renderer.php');



/**

 * Basic renderer for splash format.

 *

 * @copyright 2012 Dan Poltawski

 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

 */

class format_splash_renderer extends format_section_renderer_base {



    /**

     * Constructor method, calls the parent constructor

     *

     * @param moodle_page $page

     * @param string $target one of rendering target constants

     */

    public function __construct(moodle_page $page, $target) {

        parent::__construct($page, $target);



        // Since format_splash_renderer::section_edit_controls() only displays the 'Set current section' control when editing mode is on

        // we need to be sure that the link 'Turn editing mode on' is available for a user who does not have any other managing capability.

        $page->set_other_editing_capability('moodle/course:setcurrentsection');

    }



    /**

     * Generate the starting container html for a list of sections

     * @return string HTML to output.

     */

    protected function start_section_list() {

        return html_writer::start_tag('ul', array('class' => 'splash'));

    }



    /**

     * Generate the closing container html for a list of sections

     * @return string HTML to output.

     */

    protected function end_section_list() {

        return html_writer::end_tag('ul');

    }



    /**

     * Generate the title for this section page

     * @return string the page title

     */

    protected function page_title() {

        return get_string('topicoutline');

    }



    /**

     * Generate the display of the header part of a section before

     * course modules are included

     *

     * @param stdClass $section The course_section entry from DB

     * @param stdClass $course The course entry from DB

     * @param bool $onsectionpage true if being printed on a single-section page

     * @param int $sectionreturn The section to return to after an action

     * @return string HTML to output.

     */

    protected function section_header($section, $course, $onsectionpage, $sectionreturn=null) {

        global $PAGE;



        $o = '';

        $currenttext = '';

        $sectionstyle = '';



        if ($section->section != 0) {

            // Only in the non-general sections.

            if (!$section->visible) {

                $sectionstyle = ' hidden';

            } else if (course_get_format($course)->is_section_current($section)) {

                $sectionstyle = ' current';

            }

        }



        $o.= html_writer::start_tag('li', array('id' => 'section-'.$section->section,

            'class' => 'section main clearfix'.$sectionstyle, 'role'=>'region',

            'aria-label'=> get_section_name($course, $section)));



        $leftcontent = $this->section_left_content($section, $course, $onsectionpage);

        $o.= html_writer::tag('div', $leftcontent, array('class' => 'left side'));



        $rightcontent = $this->section_right_content($section, $course, $onsectionpage);

        $o.= html_writer::tag('div', $rightcontent, array('class' => 'right side'));

        $o.= html_writer::start_tag('div', array('class' => 'content'));



        // When not on a section page, we display the section titles except the general section if null

        $hasnamenotsecpg = (!$onsectionpage && ($section->section != 0 || !is_null($section->name)));



        // When on a section page, we only display the general section title, if title is not the default one

        $hasnamesecpg = ($onsectionpage && ($section->section == 0 && !is_null($section->name)));



        $classes = ' accesshide';

        if ($hasnamenotsecpg || $hasnamesecpg) {

            $classes = '';

        }

        $o.= $this->output->heading($this->section_title($section, $course), 3, 'sectionname' . $classes);

        $context = context_course::instance($course->id);

        if ($PAGE->user_is_editing() && has_capability('moodle/course:update', $context)) {

            $url = new moodle_url('/course/editsection.php', array('id'=>$section->id, 'sr'=>$sectionreturn));

            $o.= html_writer::link($url,

                html_writer::empty_tag('img', array('src' => $this->output->pix_url('i/settings'),

                    'class' => 'iconsmall edit', 'alt' => get_string('edit'))),

                array('title' => get_string('editsummary')));
        }

        $o.= html_writer::start_tag('div', array('class' => 'summary'));

        $o.= $this->format_summary_text($section);



        $o.= html_writer::end_tag('div');



        $o .= $this->section_availability_message($section,

                has_capability('moodle/course:viewhiddensections', $context));



        return $o;

    }



    /**

     * Generate the edit controls of a section

     *

     * @param stdClass $course The course entry from DB

     * @param stdClass $section The course_section entry from DB

     * @param bool $onsectionpage true if being printed on a section page

     * @return array of links with edit controls

     */

    protected function section_edit_controls($course, $section, $onsectionpage = false) {

        global $PAGE;



        if (!$PAGE->user_is_editing()) {

            return array();

        }



        $coursecontext = context_course::instance($course->id);



        if ($onsectionpage) {

            $url = course_get_url($course, $section->section);

        } else {

            $url = course_get_url($course);

        }

        $url->param('sesskey', sesskey());



        $controls = array();

        if (has_capability('moodle/course:setcurrentsection', $coursecontext)) {

            if ($course->marker == $section->section) {  // Show the "light globe" on/off.

                $url->param('marker', 0);

                $controls[] = html_writer::link($url,

                                    html_writer::empty_tag('img', array('src' => $this->output->pix_url('i/marked'),

                                        'class' => 'icon ', 'alt' => get_string('markedthistopic'))),

                                    array('title' => get_string('markedthistopic'), 'class' => 'editing_highlight'));

            } else {

                $url->param('marker', $section->section);

                $controls[] = html_writer::link($url,

                                html_writer::empty_tag('img', array('src' => $this->output->pix_url('i/marker'),

                                    'class' => 'icon', 'alt' => get_string('markthistopic'))),

                                array('title' => get_string('markthistopic'), 'class' => 'editing_highlight'));

            }

        }



        return array_merge($controls, parent::section_edit_controls($course, $section, $onsectionpage));

    }

	

	   /**

     * Output the html for a multiple section page

     *

     * @param stdClass $course The course entry from DB

     * @param array $sections (argument not used)

     * @param array $mods (argument not used)

     * @param array $modnames (argument not used)

     * @param array $modnamesused (argument not used)

     */

    public function print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused) {

        global $PAGE, $USER, $CFG;



        $modinfo = get_fast_modinfo($course);

        $course = course_get_format($course)->get_course();

		$context = context_course::instance($course->id);

		$this->courseformat = course_get_format($PAGE->course);



        $context = context_course::instance($course->id);

        // Title with completion help icon.

        $completioninfo = new completion_info($course);

        echo $completioninfo->display_help_icon();

        echo $this->output->heading($this->page_title(), 2, 'accesshide');



        // Copy activity clipboard..

        echo $this->course_activity_clipboard($course, 0);



        // Now the list of sections..

        echo $this->start_section_list();

		//adjust section width depending on how many sections there are

		$section_width = doubleval(100/$course->numsections)."%";
		//Jacky look here
		if (!$PAGE->user_is_editing()){

			echo "
			<style>
				@media (min-width: 600px){
					.course-content ul.splash li.section.main{
		
		    			width: $section_width;
		
		    			float: left;
		
		    		}
	    		}
	
	    	</style>";
			
			//for ie8 and under
			echo "<!--[if lt IE 9]>
					<style>
						.course-content ul.splash li.section.main{
			
			    			width: $section_width;
			
			    			float: left;
			
			    		}
			    	</style>
				<![endif]-->";
		}

		

		//there zshould only be one real header file if uploaded

		$header = $this->courseformat->get_marginal_image($context->id, 'header');

		

		//style the background of the header

		if($header){

			$fs = get_file_storage();

			$file = $fs->get_file($context->id, 'format_splash', 'header', $course->id, '/', $header);

			$url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());

			echo "<style> #page-header{

				 background:url(\"".$url."\") no-repeat;

		    } </style>";

		}

		

		//there should only be one real footer file if uploaded

		$footer = $this->courseformat->get_marginal_image($context->id, 'footer');



		//use content css to add the footer image

		if($footer){

			$fs = get_file_storage();

			$file = $fs->get_file($context->id, 'format_splash', 'footer', $course->id, '/', $footer);

			$url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());

			echo "<style> #page-footer:before {

    			content: url(\"".$url."\");

			} </style>";

		}

		

		//there should only be one real footer file if uploaded

		$logo = $this->courseformat->get_marginal_image($context->id, 'logo');



		//use content css to add the logo to replace the header

		if($logo){

			$fs = get_file_storage();

			$file = $fs->get_file($context->id, 'format_splash', 'logo', $course->id, '/', $logo);

			$url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());

			$imageinfo = $file->get_imageinfo();

			$width = $imageinfo['width']."px";

			$height = $imageinfo['height']."px";

			

			echo "<style> 

				div#page-header h1 {

	    			text-indent: 100%;

	    			white-space: nowrap;

	    			overflow: hidden;

	    			width:$width;

	    			height:$height;

	    			background:url(\"".$url."\") no-repeat;

	    			margin:0;

	    			padding:0;

	    			top:0px;

				}

				div#page-header{

					height:$height;

				}

			</style>";

		}
		

        foreach ($modinfo->get_section_info_all() as $section => $thissection) {
		
	        //there should only be one real footer file if uploaded
			$sec_image = $this->courseformat->get_section_image($context->id, $thissection->id);

			//use css to add the section image	
			if($sec_image){
	
				$fs = get_file_storage();
	
				$file = $fs->get_file($context->id, 'format_splash', 'section', $thissection->id, '/', $sec_image);
	
				$url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
	
				echo "<style>
					.course-content ul.splash li#section-".$section." .sectionname{
						background:url(\"".$url."\") no-repeat;
					} 
				 </style>";
	
			}

            if ($section == 0) {

            	/* //comment out section 0 for now

                // 0-section is displayed a little different then the others

                if ($thissection->summary or !empty($modinfo->sections[0]) or $PAGE->user_is_editing()) {

                    echo $this->section_header($thissection, $course, false, 0);

                    echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);

                    echo $this->courserenderer->course_section_add_cm_control($course, 0, 0);

                    echo $this->section_footer();

                }*/

                continue;

            }

            if ($section > $course->numsections) {

                // activities inside this section are 'orphaned', this section will be printed as 'stealth' below

                continue;

            }

            // Show the section if the user is permitted to access it, OR if it's not available

            // but showavailability is turned on (and there is some available info text).

            $showsection = $thissection->uservisible ||

                    ($thissection->visible && !$thissection->available && $thissection->showavailability

                    && !empty($thissection->availableinfo));

            if (!$showsection) {

                // Hidden section message is overridden by 'unavailable' control

                // (showavailability option).

                if (!$course->hiddensections && $thissection->available) {

                    echo $this->section_hidden($section);

                }



                continue;

            }



            if (!$PAGE->user_is_editing() && $course->coursedisplay == COURSE_DISPLAY_MULTIPAGE) {

                // Display section summary only.

                echo $this->section_summary($thissection, $course, null);

            } else {

                echo $this->section_header($thissection, $course, false, 0);

                if ($thissection->uservisible) {

                    echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);

                    echo $this->courserenderer->course_section_add_cm_control($course, $section, 0);

                }

                echo $this->section_footer();

            }

        }



        if ($PAGE->user_is_editing() and has_capability('moodle/course:update', $context)) {

            // Print stealth sections if present.

            foreach ($modinfo->get_section_info_all() as $section => $thissection) {

                if ($section <= $course->numsections or empty($modinfo->sections[$section])) {

                    // this is not stealth section or it is empty

                    continue;

                }

                echo $this->stealth_section_header($section);

                echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);

                echo $this->stealth_section_footer();

            }



            echo $this->end_section_list();



            echo html_writer::start_tag('div', array('id' => 'changenumsections', 'class' => 'mdl-right'));



            // Increase number of sections. only if less than 5. design decision

            if ($course->numsections < 5) {

	            $straddsection = get_string('increasesections', 'moodle');

	            $url = new moodle_url('/course/changenumsections.php',

	                array('courseid' => $course->id,

	                      'increase' => true,

	                      'sesskey' => sesskey()));

	            $icon = $this->output->pix_icon('t/switch_plus', $straddsection);

	            echo html_writer::link($url, $icon.get_accesshide($straddsection), array('class' => 'increase-sections'));

			}



            if ($course->numsections > 2) {

                // Reduce number of sections sections.

                $strremovesection = get_string('reducesections', 'moodle');

                $url = new moodle_url('/course/changenumsections.php',

                    array('courseid' => $course->id,

                          'increase' => false,

                          'sesskey' => sesskey()));

                $icon = $this->output->pix_icon('t/switch_minus', $strremovesection);

                echo html_writer::link($url, $icon.get_accesshide($strremovesection), array('class' => 'reduce-sections'));

            }



            echo html_writer::end_tag('div');

        } else {

            echo $this->end_section_list();

        }



    }



}

