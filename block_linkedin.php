<?php

/**
 * This block is to be used in combination with the LinkedIn authentication plugin
 *
 * @package    block
 * @subpackage linkedin
 * @copyright  2012 Bas Brands
 *  marc.alier@upc.edu
 * @copyright  2012 Bright Alley Knowledge and learning
 * @author     Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


class block_linkedin extends block_base {
	/**
	 * block initializations
	 */
	public function init() {
		$this->title   = get_string('pluginname', 'block_linkedin');
	}

	/**
	 * block contents
	 *
	 * @return object
	 */
	public function get_content() {
		global $CFG, $USER, $DB, $OUTPUT, $PAGE, $COURSE, $SESSION;

		if ($this->content !== NULL) {
			return $this->content;
		}

		require_once( $CFG->dirroot . "/auth/linkedin/linkedin.php");


		$this->content = new stdClass;

		$error = '';
		$authplugin = 'linkedin';

		if (!file_exists("{$CFG->dirroot}/auth/$authplugin/auth.php")) {
			$error = get_string('notinstalled','block_linkedin');
		}
		if (!is_readable("{$CFG->dirroot}/auth/$authplugin/auth.php")) {
			if (empty($error)) {
				$error = get_string('notreadable','block_linkedin');
			}
		}
		if (!is_enabled_auth($authplugin)) {
			if (empty($error)) {
				$error = get_string('notenabled','block_linkedin');
			}
		}
		if (!empty($error)) {
			if (is_siteadmin($USER)) {
				$this->content->text .= get_string('errors','block_linkedin');
				$this->content->text .= $error;
			}
			return $this->content;
		}

		$access = get_config("auth/linkedin", 'linkedin_access');
		$secret = get_config("auth/linkedin", 'linkedin_secret');

		$linkedin = new LinkedInAuth($access, $secret, $CFG->wwwroot.'/login/index.php');
		$linkedin_response = '';


		// LOGIN WITH LINKEDIN BOX
		if (!isloggedin() or isguestuser()) {
            $linkedin->getRequestToken();
            //$_SESSION['requestToken'] = serialize($linkedin->request_token);

            $this->content->text .= html_writer::start_tag('div',array('id'=>'linkedincontainer'));
            $this->content->text .= html_writer::start_tag('div',array('id'=>'linkedinwrapper'));
            $this->content->text .= html_writer::start_tag('script',array('type'=>'text/javascript'));
            $this->content->text .= '
			    var newwindow;
			    function pop(url)
			        {
				    newwindow=window.open(\''.$CFG->wwwroot.'/blocks/linkedin/popup.php\',\'LinkedIn\',\'height=450,width=600\');
				    if (window.focus) {newwindow.focus()}
			        }';
            $this->content->text .= html_writer::end_tag('script');
            $this->content->text .= html_writer::empty_tag('br');
            $this->content->text .= html_writer::link('#', html_writer::empty_tag('img', array('src'=>$CFG->wwwroot . '/blocks/linkedin/logo.png', 'alt'=>'linkedinlogo', 'class'=>'linkedinlogo')),array("onclick"=>'pop()'));
            $this->content->text .= html_writer::empty_tag('br');

            $this->content->text .= html_writer::start_tag('div',array('class'=>'thnk-login-info'));
            $this->content->text .= html_writer::start_tag('span',array('class'=>'linkedinlogin'));
            
            $popuplink = html_writer::link('#', get_string('linkedin','block_linkedin'),array("onclick"=>'pop()'));
            $this->content->text .= get_string('login_linkedin','block_linkedin',$popuplink);
            
            $this->content->text .= html_writer::end_tag('span');
            $this->content->text .= html_writer::empty_tag('br');
            $this->content->text .= html_writer::start_tag('span',array('class'=>'linkedinlogin'));
            
            $moodlelink = html_writer::link($CFG->wwwroot . '/login', get_string('systemname','block_linkedin'));
            $this->content->text .= get_string('login_moodle','block_linkedin',$moodlelink);
            
            $this->content->text .= html_writer::end_tag('span');
            
            $this->content->text .= $PAGE->headingmenu;
            $this->content->text .= html_writer::end_tag('div');
            $this->content->text .= html_writer::end_tag('div');
            $this->content->text .= html_writer::end_tag('div');

		} else {
		    
			$this->content->text .= html_writer::start_tag('div',array('id'=>'linkedincontainer'));
            $this->content->text .= html_writer::start_tag('div',array('id'=>'linkedinwrapper'));
            $this->content->text .= html_writer::start_tag('div',array('id'=>'linkedininfo'));

            $this->content->text .= html_writer::start_tag('div',array('id'=>'linkedinname'));
			$this->content->text .= $USER->firstname . ' ' .$USER->lastname;
			$this->content->text .= html_writer::end_tag('div');
			$this->content->text .= html_writer::start_tag('p');
			$description = $DB->get_field('user', 'description', array('id'=>$USER->id));
			if (isset($description)) {
				$this->content->text .= html_writer::start_tag('span',array('class'=>'linkedintitle')); 
				$this->content->text .= $description;
				$this->content->text .= html_writer::end_tag('span');
			}
			if (isset($USER->city)) {
			    $this->content->text .= html_writer::start_tag('span',array('class'=>'linkedinlocation')); 
				$this->content->text .= $USER->city;
				$this->content->text .= html_writer::end_tag('span');
			}			    
			$this->content->text .= html_writer::start_tag('span',array('class'=>'linkedinlogout')); 
			$this->content->text .= html_writer::link($CFG->wwwroot . '/login/logout.php?sesskey='. sesskey(), get_string("logout"));
			$this->content->text .= html_writer::end_tag('span');
			
			$this->content->text .= html_writer::end_tag('p');
			

			$this->content->text .= html_writer::start_tag('div',array('class'=>'clearer'));
			$this->content->text .= html_writer::end_tag('div');
			$this->content->text .= html_writer::end_tag('div');
			$this->content->text .= html_writer::start_tag('div',array('id'=>'linkedinpic'));
			$this->content->text .= html_writer::empty_tag('img', array('src'=>$CFG->wwwroot.'/user/pix.php?file=/'.$USER->id.'/f1.jpg', 'alt'=>$USER->firstname.' '.$USER->lastname, 'class'=>'linkedinlogin'));
			$this->content->text .= html_writer::end_tag('div');
			$this->content->text .= html_writer::start_tag('div',array('class'=>'clearer'));
			$this->content->text .= html_writer::end_tag('div');
			$this->content->text .= html_writer::end_tag('div');
			$this->content->text .= html_writer::end_tag('div');
				
		}

		$this->content->footer = '';
		return $this->content;
	}

	/**
	 * allow the block to have a configuration page
	 *
	 * @return boolean
	 */
	public function has_config() {
		return false;
	}

	/**
	 * allow more than one instance of the block on a page
	 *
	 * @return boolean
	 */
	public function instance_allow_multiple() {
		//allow more than one instance on a page
		return false;
	}

	/**
	 * allow instances to have their own configuration
	 *
	 * @return boolean
	 */
	function instance_allow_config() {
		//allow instances to have their own configuration
		return false;
	}

	/**
	 * instance specialisations (must have instance allow config true)
	 *
	 */
	public function specialization() {
	}

	/**
	 * displays instance configuration form
	 *
	 * @return boolean
	 */
	function instance_config_print() {
		return false;

	}

	/**
	 * locations where block can be displayed
	 *
	 * @return array
	 */
	public function applicable_formats() {
		return array('all'=>true);
	}

	/**
	 * post install configurations
	 *
	 */
	public function after_install() {
	}

	/**
	 * post delete configurations
	 *
	 */
	public function before_delete() {
	}

}
