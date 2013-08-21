<?php

/**
 * This block is to be used in combination with the LinkedIn authentication plugin
 *
 * @package    block
 * @subpackage linkedin
 * @copyright  2013 Bas Brands
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

        if (!isloggedin() or isguestuser()) {

            $popupscript = '
            function linkedinpop() {
              var newwindow=window.open(\''.new moodle_url('/blocks/linkedin/popup.php').'\',\'LinkedIn\',\'height=450,width=600\');
              if (window.focus) {newwindow.focus()}
            }';

            $content = html_writer::tag('script', $popupscript, array('type'=>'text/javascript'));

            $attr = array(
                'src' => $OUTPUT->pix_url('logo', 'block_linkedin'),
                'alt' => 'Linkedinlogo',
                'class' => 'linkedinlogo',
                'onclick'=> 'linkedinpop()'
            );
            $linkedinlogo = html_writer::empty_tag('img', $attr);

            $content .= html_writer::link('#', $linkedinlogo, array("onclick"=>'linkedinpop()'));
            $content .= '<br>';
            $content .= html_writer::link('#', get_string('loginlinkedin','block_linkedin'), array("onclick"=>'linkedinpop()'));
            $content .= '<br>';
            $content .= html_writer::link(new moodle_url('/login') ,get_string('loginmoodle','block_linkedin'));

        } else {

            $logout = html_writer::link(new moodle_url('/login/logout.php',
            array('sesskey'=>sesskey(),'alt'=>'edit profile')), '<i class="icon-lock"></i>' . get_string('logout'), array('title'=>'Logout'));

            $viewprofile = html_writer::link(new moodle_url('/user/profile.php',
            array('id'=>$USER->id)), '' . '<i class="icon-user"></i>' . get_string('view'), array('title'=>'View your profile'));

            $editprofile  = html_writer::link(new moodle_url('/user/edit.php',
            array('id'=>$USER->id)), '' . '<i class="icon-cog"></i>' . get_string('edit'), array('title'=>'Edit your profile'));

            $picture = $OUTPUT->user_picture($USER, array('size'=>60));

            $description = $DB->get_field('user', 'description', array('id'=>$USER->id));

            $content = html_writer::start_tag('div',array('class'=>'linkedinuser'));

            $content .= html_writer::tag('p', $picture, array('class'=>'linkedinpic'));
            $content .= html_writer::tag('p', fullname($USER), array('class'=>'linkedinuser clearfix'));
            $content .= html_writer::tag('p', $logout . ' / ' .$viewprofile . ' / ' . $editprofile);

            if (isset($description)) {
                $content .= html_writer::tag('p', $description , array('class'=>'linkedindesc'));
            }

            if (isset($USER->city)) {
                $content .= html_writer::tag('p', $USER->city, array('class'=>'linkedincity'));
            }

            $content .= html_writer::end_tag('div');
        }
        $this->content->text = $content;
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
