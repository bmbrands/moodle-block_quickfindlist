<?php
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
 * This block is based on Mark Johnson's search_users block
 *
 * @package    block_search_users
 * @copyright  2013 Bas Brands, www.basbrands.nl
 * @author     Bas Brands, bas@sonsbeekmedia.nl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
require_once('../../config.php');

$name = required_param('name', PARAM_TEXT);
$courseformat = required_param('courseformat', PARAM_TEXT);
$courseid = required_param('courseid', PARAM_TEXT);


$context = get_context_instance(CONTEXT_COURSE, $courseid);
global $OUTPUT;
if (isloggedin() && has_capability('block/search_users:use', $context) && confirm_sesskey()) {

    $output = new stdClass;
    if (!empty($name)) {

        $params = array("%$name%", "%$name%", "%$name%");

        $select = "SELECT u.id as id, c.id as contextid, firstname, lastname, username, picture
                     FROM {user} u
                LEFT JOIN {context} c
                       ON u.id = c.instanceid
                    WHERE u.deleted = 0
                      AND ( CONCAT(u.firstname, ' ', u.lastname)  LIKE ? OR u.idnumber LIKE ? OR CONCAT(u.firstnamephonetic, ' ', u.lastnamephonetic) LIKE ? )
                      AND c.contextlevel = 30 ";
        $order = 'ORDER BY lastname LIMIT 10';


        if ($people = $DB->get_records_sql($select . $order, $params)) {

            $output->people = $people;
        }
    }
    echo json_encode($output);

} else {
    fwrite($fh,'not auth');
    header('HTTP/1.1 401 Not Authorised');
}
