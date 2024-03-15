<?php
// This file is part of Moodle - http://moodle.org/
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/*
 - called from the /blocks/cc_workspace block
 - auth against alfresco repos. (ticket handshake / user sync)
 - opens external edu-sharingWorkspace in iFrame
 */

/**
 * Get workspace within iframe
 *
 * @package    block_edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_edusharing\EduSharingService;
use mod_edusharing\UtilityFunctions;

require_once('../../../config.php');

global $DB;
global $CFG;
global $PAGE;
global $USER;
global $SESSION;
global $OUTPUT;

try {
    require_sesskey();
    // Course id.
    $id = optional_param('id', 0, PARAM_INT);
    if (!$id) {
        trigger_error(get_string('error_invalid_course_id', 'block_edusharing'), E_USER_WARNING);
        exit();
    }
    $PAGE->set_url('/blocks/edusharing_workspace/helper/cc_workspace.php', ['id' => $id]);
    $course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);
    require_login($course->id);
    echo $OUTPUT->header();
    $service = new EduSharingService();
    $ticket  = $service->get_ticket();
    $link    = trim(get_config('edusharing', 'application_cc_gui_url'), '/');
    $link    .= '/?mode=1';
    $utils   = new UtilityFunctions();
    $user    = $utils->get_auth_key();
    $link    .= '&user=' . urlencode($user);
    $link    .= '&locale=' . current_language();
    $link    .= '&ticket=' . urlencode($ticket);
} catch (Exception $exception) {
    debugging($exception->getMessage());
    echo 'Error: ' . $exception->getMessage();
    exit();
}

// Open the external edu-sharingSearch page in iframe.
?>

    <div id="esContent">
        <div class="esOuter">
            <div id="closer"><a href="<?php echo $_SERVER['HTTP_REFERER']; ?>">&times;</a></div>
            <iframe id="childFrame" name="mainContent" src="<?php echo htmlentities($link, ENT_COMPAT); ?>" width="100%"
                    height="100%" scrolling="yes"
                    marginwidth="0" marginheight="0" frameborder="0">
            </iframe>
        </div>
    </div>

    <script>
        document.getElementById("esContent").style.opacity = '1';
    </script>

<?php

exit();
