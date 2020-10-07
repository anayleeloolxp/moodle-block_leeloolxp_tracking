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

 * class block_leeloolxp_tracking

 *

 * @package    block_leeloolxp_tracking

 * @copyright  2020 leeloolxp.com

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

 */

require_once($CFG->dirroot . '/course/lib.php');

/**

 * class block_leeloolxp_tracking

 *

 * @package    block_leeloolxp_tracking

 *

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

 */
/*
plugin for show info about trtacking
 */
class block_leeloolxp_tracking extends block_base {

    protected $timestart = null;

    /**

     * Initialises the block.

     */

    function init() {
       $this->title = get_string('pluginname', 'block_leeloolxp_tracking');
    }

    /**

     * Content of the block.

     */
    function get_content() {
        global $USER;
        global $PAGE;
        
        if ($this->content !== NULL) {
            return $this->content;
        }
        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $configsetting = get_config('block_leeloolxp_tracking');
        $liacnsekey = $configsetting->leeloolxp_block_tracking_licensekey;
        $postdata = '&license_key=' . $liacnsekey;
        $url = 'https://leeloolxp.com/api_moodle.php/?action=page_info';
        $curl = new curl;
        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => count($postdata),
        );
        if (!$output = $curl->post($url, $postdata, $options)) {
            return true;
        }
        $infoteamnio = json_decode($output);
        
        if ($infoteamnio->status != 'false') {
            $teamniourl = $infoteamnio->data->install_url;
        } else {
            return true;
        }
        $useremail = $USER->email;

        $url = $teamniourl . '/admin/sync_moodle_course/check_user_status_by_email/' . $useremail;

        $curl = new curl;
        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => count($postdata),
        );
        $output = $curl->post($url, $postdata, $options);
        $userstatus = $output;
        if ($userstatus == 0) {
            return true;
        }

        $url = $teamnio_url . '/admin/sync_moodle_course/check_user_by_email/' . $useremail;

        $curl = new curl;
        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => count($postdata),
        );
        $output = $curl->post($url, $postdata, $options);

        $userid = $output;

        if ($userid == '0') {
            return true;
        }

        $url = $teamniourl . '/login_api/get_shift_details_api/' . $userid;

        $curl = new curl;
        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => count($postdata),
        );
        $output = $curl->post($url, $postdata, $options);

        $shiftdetails = $output;

        $sdetail = json_decode($shiftdetails);

        $url = $teamnio_url . '/admin/sync_moodle_course/get_attendance_info/' . $userid;

        $curl = new curl;
        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => count($postdata),
        );
        $output = $curl->post($url, $postdata, $options);

        $starttime = $output;

        $url = $teamniourl . '/admin/sync_moodle_course/get_clockin_info/' . $userid;
        $curl = new curl;
        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => count($postdata),
        );
        $output = $curl->post($url, $postdata, $options);

        $clockintime = $output;

        $clockinsecondsarr = explode(":", $clockintime);

        $clockinh = $clockinsecondsarr[0];

        $clockinm = $clockinsecondsarr[1];

        $clockins = $clockinsecondsarr[2];

        $clockintotalseconds = ($clockinh * 60 * 60) + ($clockinm * 60) + $clockins;

        $url = $teamniourl . '/admin/sync_moodle_course/get_breacks/' . $userid;

        $curl = new curl;
        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => count($postdata),
        );
        $output = $curl->post($url, $postdata, $options);

        $totalbreack = $output;

        $trackedtime = '00:00:00';

        $taskname = '';

        $estimates = '00:00:00';

        if (isset($_REQUEST['id'])) {
            $activityid = $_REQUEST['id'];
            $url = $teamniourl . '/admin/sync_moodle_course/get_activity_tracking_info/' . $userid . '/
            ' . $activityid;
            $curl = new curl;
            $options = array(
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_HEADER' => false,
                'CURLOPT_POST' => count($postdata),
            );
            $output = $curl->post($url, $postdata, $options);

            $trackedtime = $output;

            $trakingtimearr = explode(":", $trackedtime);

            $trackingh = $trakingtimearr[0];

            $trackingm = $trakingtimearr[1];

            $trackings = $trakingtimearr[2];

            $trackingtotalseconds = ($trackingh * 60 * 60) + ($trackingm * 60) + $trackings;

            if (strlen($trakingtimearr[0]) <= 1) {$trackingtimehours = "0" . $trakingtimearr[0];} else {

                $trackingtimehours = $trakingtimearr[0];
            }

            if (strlen($trakingtimearr[1]) <= 1) {$trackingtimeminuts = "0" . $trakingtimearr[1];} else {

                $trackingtimeminuts = $trakingtimearr[1];
            }

            if (strlen($trakingtimearr[2]) <= 1) {$trackingtimeseconds = "0" . $trakingtimearr[2];} else {

                $trackingtimeseconds = $trakingtimearr[2];
            }

            $trackedtime = $trackingtimehours . ":" . $trackingtimeminuts . ":" . $trackingtimeseconds;

            $url = $teamniourl . '/admin/sync_moodle_course/get_task_estimate_and_name/' . $userid . '/' . $activityid;

            $curl = new curl;
            $options = array(
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_HEADER' => false,
                'CURLOPT_POST' => count($postdata),
            );
            $output = $curl->post($url, $postdata, $options);

            $tasknameandestimates = $output;

            if ($tasknameandestimates != '0') {
                $arrtaskdetails = explode('||', $task_name_and_estimates);

                $taskname = $arrtaskdetails[0];

                $estimates = $arrtaskdetails[1];

                $estimatesarr = explode(':', $estimates);

                if (strlen($estimates_arr[0]) <= 1) {
                    $estimatesh = "0" . $estimatesarr[0];
                } else {

                    $estimatesh = $estimatesarr[0];
                }

                if (strlen($estimatesarr[1]) <= 1) {
                    $estimatesm = "0" . $estimates_arr[1];
                } else {

                    $estimatesm = $estimatesarr[1];
                }

                $estimates = $estimatesh . ":" . $estimatesm . ":" . "00";
            }
        }

        $url = $teamniourl . '/admin/sync_moodle_course/get_timezone/';

        $curl = new curl;
        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => count($postdata),
        );
        $outputtimezone = $curl->post($url, $postdata, $options);
        date_default_timezone_set($outputtimezone);

        if ($sdetail->status == 'true') {
            $shiftstarttime = strtotime($sdetail->data->start);
            $shiftendtime = strtotime($sdetail->data->end);

            if ($starttime == '0') {
                $starttime = date("Y-m-d h:i:s");
            }
            $actualstarttime = strtotime(date('h:i A', strtotime($starttime)));
            $actualendtime = strtotime("now");

            if ($actualstarttime >= $shiftendtime) {
                $starttimestatus = 'Absent';
            } else {
                if ($actualstarttime < $shiftstarttime) {
                    $starttimestatus = 'On Time';
                } else {
                    if ($actualstarttime >= $shiftstarttime) {
                        $starttimestatus = 'Late';
                    }
                }
            }

            if ($starttimestatus == 'Absent') {
                $endtimestatus = 'Absent';
            } else {
                if ($shiftendtime > $actualendtime) {
                    $endtimestatus = 'On Time (Learning now)';
                }
            }

            $postdata = '&user_id=' . $userid . '&start_status=' . $starttimestatus . '&end_status=' . $endtimestatus;

            

            $url = $teamniourl . '/admin/sync_moodle_course/update_attendance_status/';

            $curl = new curl;
            $options = array(
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_HEADER' => false,
                'CURLOPT_POST' => count($postdata),
            );
            $curl->post($url, $postdata, $options);

           $this->content = new stdClass;

            $configloginlogout = get_config('local_teamnio_web_login_tracking');

            $popupison = $configloginlogout->web_loginlogout_popup;

            $html = '';

            $html .= "<script>

                    var upgradeTime = '" . $clockintotalseconds . "';

                    var popup_is_on = '" . $popupison . "';

                    if(upgradeTime=='0') {   upgradeTime = 1; }

                    var seconds = upgradeTime;

                    var clock_i = new Array();

                    function timer() {

                      clock_i.push = 1;

                      //var already_clockintime =" . $clockintotalseconds . ";

                      var days        = Math.floor(seconds/24/60/60);

                      var hoursLeft  =  Math.floor((seconds) - (days*86400));

                      var hours      =  Math.floor(hoursLeft/3600);

                      var minutesLeft = Math.floor((hoursLeft) - (hours*3600));

                      var minutes    = Math.floor(minutesLeft/60);

                      var remainingSeconds = seconds % 60;

                      function pad(n) {

                        return (n < 10 ? '0' + n : n);



                      }



                      document.getElementById('countdown').innerHTML =pad(hours) + ':' + pad(minutes) + ':' + pad(remainingSeconds);

                      if (seconds == 0) {

                        /*clearInterval(countdownTimer);

                        document.getElementById('countdown').innerHTML = 'Completed';*/

                      } else {

                            if(popup_is_on=='1') {

                                var tracking_on = localStorage.getItem('tracked');

                                console.log(tracking_on);

                                if(tracking_on=='1') {

                                    seconds++;

                                } else {



                                }

                            } else {

                                 seconds++;

                            }

                      }



                    }



                    var countdown = setInterval('timer()', 1000);





                    var TupgradeTime = '" . $trackingtotalseconds . "';



                    if(TupgradeTime=='0') {   TupgradeTime = 1; }

                    var Tseconds = TupgradeTime;

                    function Ttimer() {

                      var days        = Math.floor(Tseconds/24/60/60);

                      var hoursLeft  = Math.floor((Tseconds) - (days*86400));

                      var hours      = Math.floor(hoursLeft/3600);

                      var minutesLeft = Math.floor((hoursLeft) - (hours*3600));

                      var minutes    = Math.floor(minutesLeft/60);

                      var remainingSeconds = Tseconds % 60;

                      function Tpad(n) {

                        return (n < 10 ? '0' + n : n);



                      }

                      var t_C =  document.getElementById('Tcountdown');



                      if (typeof t_C !== 'undefined' ) {

                            if(t_C !== null) {



                                document.getElementById('Tcountdown').innerHTML =Tpad(hours) + ':' + Tpad(minutes) + ':' + Tpad(remainingSeconds);

                                if (Tseconds == 0) {

                                    clearInterval(countdownTimer);

                                    document.getElementById('countdown').innerHTML = 'Completed';

                                } else {

                                    Tseconds++;

                                }

                            }

                      }

                    }

                    var countdown = setInterval('Ttimer()', 1000);



                    setInterval(show_clockin_timer, 2000);





                    function show_clockin_timer() {

                        var y = document.getElementById('countdown_div');
                        var b_div_main = document.getElementById('clockin_break_span_main');
                        if(y) {
                                var clock_intracking_on = localStorage.getItem('tracked');

                                if(clock_intracking_on=='1') {
                                    document.getElementById('countdown_div').style.display = 'inline-block';
                                    if(b_div_main) {
                                        document.getElementById('clockin_break_span_main').style.display = 'block';
                                    }
                                }
                        }

                    }



                    setInterval(show_tracking_timer, 2000);



                    function show_tracking_timer() {

                            var x =  document.getElementById('Tcountdown');

                            if ( x ) {
                                var clock_intracking_on = localStorage.getItem('tracked');
                                if(clock_intracking_on=='1') {
                                    document.getElementById('Tcountdown').style.display = 'inline-block';
                                }

                        }

                    }


                    </script>";

            $html .= '<b><hr></b><br> <b> Shift today </b> ' . $outputtimezone . ' <br>';

            if (!empty($sdetail->data)) {
                $html .= 'Start time : ' . $sdetail->data->start;

                $html .= '<br> End time : ' . $sdetail->data->end;

                $html .= '<b><hr></b><br> <b>Attendance:</b> ';

                $html .= '<br> Start: ' . date('h:i A', strtotime($starttime)) . "  <span>" . $starttimestatus . "</span>";

                date_default_timezone_set($outputtimezone);

                $html .= '<br> End: ' . date('h:i A') . "<span> " . $endtimestatus . "</span>";
            } else {

                $html .= 'Please set a schedule or ask your teacher';
            }

            $html .= '<br> <br> <br>';

            if (strpos($clockintime, '-') === false) {
                $html .= '';

                $html .= '<div id="countdown_div" style="display:none;">Time: <span id="countdown"></span>';if ($sdetail->data) {$html .= "/" . $sdetail->data->minimum_hours . ":00";};

                $html .= '</div>';

                $html .= '<div id = "clockin_break_span_main" style="display:none">Break: <span id="clockin_break_span" >' . $totalbreack . ' </span>';

                if ($sdetail->data) {
                    if (strpos($sdetail->data->allow_breack_time, '.') === false) {
                        $html .= " /0" . $sdetail->data->allow_breack_time . ":00:00";
                    } else {

                        $breacktimearr = explode('.', $sdetail->data->allow_breack_time);

                        $html .= " /0" . $breacktimearr[0] . ":" . $breacktimearr[1] . ":00";
                    }
                    $html .= "</div>";
                }
            } else {

                $html .= 'Time: Not updated yet.';

                $html .= '<br>Break: Not updated yet.';
            }

            $html .= '<br> <br> <br>';

            if (isset($_REQUEST['id'])) {
                if ($PAGE->pagetype == 'mod-wespher-conference' || $PAGE->pagetype == 'mod-wespher-view' || $PAGE->pagetype == 'mod-resource-view' || $PAGE->pagetype == 'mod-regularvideo-view' || $PAGE->pagetype == 'mod-forum-view' || $PAGE->pagetype == 'mod-book-view' || $PAGE->pagetype == 'mod-assign-view' || $PAGE->pagetype == 'mod-survey-view' || $PAGE->pagetype == 'mod-page-view' || $PAGE->pagetype == 'mod-quiz-view' || $PAGE->pagetype == 'mod-quiz-attempt' || $PAGE->pagetype == 'mod-quiz-summary' || $PAGE->pagetype == 'mod-quiz-summary' || $PAGE->pagetype == 'mod-chat-view' || $PAGE->pagetype == 'mod-choice-view' || $PAGE->pagetype == 'mod-lti-view' || $PAGE->pagetype == 'mod-feedback-view' || $PAGE->pagetype == 'mod-data-view' || $PAGE->pagetype == 'mod-forum-view' || $PAGE->pagetype == 'mod-glossary-view' || $PAGE->pagetype == 'mod-scorm-view' || $PAGE->pagetype == 'mod-wiki-view' || $PAGE->pagetype == 'mod-workshop-view' || $PAGE->pagetype == 'mod-folder-view' || $PAGE->pagetype == 'mod-imscp-view' || $PAGE->pagetype == 'mod-label-view' || $PAGE->pagetype == 'mod-url-view') {
                    $html .= '<b>' . $taskname . '</b> <br>';
                    $html .= 'Time: <span id="Tcountdown" style="display:none;"></span>';

                    $html .= '<br> Estimated: ' . $estimates;
                }
            }

            $this->content->text = $html;

            $this->content->footer = '';

            return $this->content;
        }
    }
    function has_config() {
        return true;
    }
}