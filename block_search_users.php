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
 * Defines the block_search_users class
 * This block is based on Mark Johnson's quickfindlist block
 * Mark Johnson <mark.johnson@tauntons.ac.uk>
 *
 *
 * @package    block_search_users
 * @copyright  2013 Bas Brands, www.basbrands.nl
 * @author     Bas Brands, bas@sonsbeekmedia.nl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_search_users extends block_base {

    public function init() {
        $this->content_type = BLOCK_TYPE_TEXT;
        $this->title = get_string('search_users', 'block_search_users');
    }


    public function instance_allow_multiple() {
        return true;
    }

    public function has_config() {
        return false;
    }

    public function preferred_width() {
        return 180;
    }

    public function get_content() {
        global $COURSE, $DB, $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

       if (isset($this->config)){
            $config = $this->config;
        } else{
            $config = get_config('block_search_users');
        }

        $this->content = new stdClass;

        $roleid = '-1';
        $config->role = '-1';

        $this->title = get_string('blockname', 'block_search_users');

        $context_system =  context_system::instance();

        if (has_capability('block/search_users:use', $context_system)) {


            if (empty($config->url)) {
                $url = new moodle_url('/user/view.php', array('course' => $COURSE->id));
            } else {
                $url = new moodle_url($config->url);
            }

            $name = optional_param('search_userssearch', '', PARAM_TEXT);

            $anchor = html_writer::tag('a', '', array('name' => 'quickfindanchor'));
            $searchparams = array(
                'id' => 'search_userssearch',
                'name' => 'search_userssearch',
                'class' => 'search_userssearch',
                'autocomplete' => 'off',
                'type' => 'text',
            );

            $search = html_writer::empty_tag('input', $searchparams);

            $progressparams = array(
                'id' => 'quickfindprogress',
                'class' => 'quickfindprogress',
                'src' => $this->page->theme->pix_url('i/loading_small', 'moodle'),
                'alt' => get_string('loading', 'block_search_users')
            );
            $progress = html_writer::empty_tag('img', $progressparams);

            $submitparams = array(
                'type' => 'submit',
                'class' => 'submitbutton icon-search',
                'name' => 'quickfindsubmit',
                'value' => get_string('search'),
               'onclick' => 'this.form.submit()'
            );

            $submit = html_writer::tag('button','' ,$submitparams);
            $formparams = array(
                'id' => 'quickfindform',
                'action' => $this->page->url.'#quickfindanchor',
                'method' => 'post',
                'class' => 'adminsearchform'
            );

            $clearfix = html_writer::tag('div','',array('class'=>'clearfix'));
            $form = html_writer::tag('form', $search . $submit . $progress . $clearfix, $formparams);

            $listcontents = '';
            if (!empty($name)) {
                $params = array("%$name%","%$name%", "%$name%");
                $select = "SELECT u.id as id, c.id as contextid, firstname, lastname, username, picture, imagealt, email
                             FROM {user} u
                        LEFT JOIN {context} c
                               ON u.id = c.instanceid
                            WHERE u.deleted = 0
                              AND ( CONCAT(u.firstname, ' ', u.lastname)  LIKE ? OR u.idnumber LIKE ? OR CONCAT(u.firstnamephonetic, ' ' ,u.lastnamephonetic ) LIKE ? )
                              AND c.contextlevel = 30 ";

                if ($people = $DB->get_records_sql($select, $params)) {
                    foreach ($people as $person) {
                        $userpix = $OUTPUT->user_picture($person, array('size'=>35));
                        $linkurl = new moodle_url($url, array('id' => $person->id));
                        $link = html_writer::tag('a', $userpix . $person->firstname . ' ' .$person->lastname , array('href' => $linkurl));
                        $listcontents .= html_writer::tag('li', $link);
                    }
                }
            }

            $list = html_writer::tag('ul', $listcontents, array('id' => 'search_users'));

            $jsmodule = array(
                'name'  =>  'block_search_users',
                'fullpath'  =>  '/blocks/search_users/module.js',
                'requires'  =>  array('base', 'node', 'json', 'io')
            );

            $jsdata = array(
                $COURSE->format,
                $COURSE->id,
                sesskey()
            );

            $this->page->requires->js_init_call('M.block_search_users.init',
                                                $jsdata,
                                                false,
                                                $jsmodule);
            $this->content->footer='';
            $this->content->text = $anchor.$form.$list;
        }

        return $this->content;

    }
}
