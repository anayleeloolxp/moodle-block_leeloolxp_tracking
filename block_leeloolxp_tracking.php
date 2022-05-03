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
defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . '/course/lib.php');

/**
 * Block attendance info
 *
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_leeloolxp_tracking extends block_base {
    /**
     * Show attendance information of user.
     */

    /**
     * Block initialization
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_leeloolxp_tracking');
    }

    /**
     * Return contents of block_leeloolxp_tracking block
     *
     * @return string of block
     */
    public function get_content() {
        global $USER;
        if ($this->content !== null) {
            return $this->content;
        }
        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        global $CFG;
        require_once($CFG->dirroot . '/lib/filelib.php');

        $configsetting = get_config('block_leeloolxp_tracking');
        $liacnsekey = $configsetting->leeloolxp_block_tracking_licensekey;
        $postdata = array('license_key' => $liacnsekey);
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
        $url = $teamniourl . '/admin/sync_moodle_course/check_user_llt_status_by_email/' . base64_encode($useremail);
        $curl = new curl;
        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => count($postdata),
            'CURLOPT_HTTPHEADER' => array(
                'LeelooLXPToken: '.get_config('local_leeloolxpapi')->leelooapitoken.''
            )
        );
        $output = $curl->post($url, $postdata, $options);
        $userstatus = $output;
        if ($userstatus == 0) {
            return true;
        }

        $url = $teamniourl . '/admin/sync_moodle_course/check_user_by_email/' . base64_encode($useremail);

        $curl = new curl;
        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => count($postdata),
            'CURLOPT_HTTPHEADER' => array(
                'LeelooLXPToken: '.get_config('local_leeloolxpapi')->leelooapitoken.''
            )
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

        $url = $teamniourl . '/admin/sync_moodle_course/get_attendance_info/' . $userid;

        $curl = new curl;
        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => count($postdata),
            'CURLOPT_HTTPHEADER' => array(
                'LeelooLXPToken: '.get_config('local_leeloolxpapi')->leelooapitoken.''
            )
        );
        $output = $curl->post($url, $postdata, $options);

        $starttime = $output;

        $url = $teamniourl . '/admin/sync_moodle_course/get_clockin_info/' . $userid;
        $curl = new curl;
        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => count($postdata),
            'CURLOPT_HTTPHEADER' => array(
                'LeelooLXPToken: '.get_config('local_leeloolxpapi')->leelooapitoken.''
            )
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
            'CURLOPT_HTTPHEADER' => array(
                'LeelooLXPToken: '.get_config('local_leeloolxpapi')->leelooapitoken.''
            )
        );
        $output = $curl->post($url, $postdata, $options);

        $totalbreack = $output;

        $trackedtime = '00:00:00';

        $taskname = '';

        $estimates = '00:00:00';

        $trackingtotalseconds = 0;

        $reqid = optional_param('id', null, PARAM_RAW);
        if (isset($reqid)) {
            $activityid = $reqid;
            $url = $teamniourl . '/admin/sync_moodle_course/get_activity_tracking_info/' . $userid . '/'
            . $activityid;
            $curl = new curl;
            $options = array(
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_HEADER' => false,
                'CURLOPT_POST' => count($postdata),
                'CURLOPT_HTTPHEADER' => array(
                    'LeelooLXPToken: '.get_config('local_leeloolxpapi')->leelooapitoken.''
                )
            );
            $output = $curl->post($url, $postdata, $options);

            $trackedtime = $output;
            $trakingtimearr = explode(":", $trackedtime);

            $trackingh = $trakingtimearr[0];

            $trackingm = $trakingtimearr[1];

            $trackings = $trakingtimearr[2];

            $trackingtotalseconds = ($trackingh * 60 * 60) + ($trackingm * 60) + $trackings;

            if (strlen($trakingtimearr[0]) <= 1) {
                $trackingtimehours = "0" . $trakingtimearr[0];
            } else {
                $trackingtimehours = $trakingtimearr[0];
            }

            if (strlen($trakingtimearr[1]) <= 1) {
                $trackingtimeminuts = "0" . $trakingtimearr[1];
            } else {
                $trackingtimeminuts = $trakingtimearr[1];
            }

            if (strlen($trakingtimearr[2]) <= 1) {
                $trackingtimeseconds = "0" . $trakingtimearr[2];
            } else {
                $trackingtimeseconds = $trakingtimearr[2];
            }

            $trackedtime = $trackingtimehours . ":" . $trackingtimeminuts . ":" . $trackingtimeseconds;

            $url = $teamniourl . '/admin/sync_moodle_course/get_task_estimate_and_name/' . $userid . '/' . $activityid;

            $curl = new curl;
            $options = array(
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_HEADER' => false,
                'CURLOPT_POST' => count($postdata),
                'CURLOPT_HTTPHEADER' => array(
                    'LeelooLXPToken: '.get_config('local_leeloolxpapi')->leelooapitoken.''
                )
            );
            $output = $curl->post($url, $postdata, $options);

            $tasknameandestimates = $output;

            if ($tasknameandestimates != '0') {
                $arrtaskdetails = explode('||', $tasknameandestimates);

                $taskname = $arrtaskdetails[0];

                $estimates = $arrtaskdetails[1];

                $estimatesarr = explode(':', $estimates);

                if (strlen($estimatesarr[0]) <= 1) {
                    $estimatesh = "0" . $estimatesarr[0];
                } else {

                    $estimatesh = $estimatesarr[0];
                }

                if (strlen($estimatesarr[1]) <= 1) {
                    $estimatesm = "0" . $estimatesarr[1];
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
            'CURLOPT_HTTPHEADER' => array(
                'LeelooLXPToken: '.get_config('local_leeloolxpapi')->leelooapitoken.''
            )
        );
        $outputtimezone = $curl->post($url, $postdata, $options);
        date_default_timezone_set($outputtimezone);

        if ($sdetail->status == 'true') {
            @$shiftstarttime = strtotime($sdetail->data->start);
            @$shiftendtime = strtotime($sdetail->data->end);

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
            $postdata = array('user_id' => $userid, 'start_status' => $starttimestatus, 'end_status' => $endtimestatus);
            $url = $teamniourl . '/admin/sync_moodle_course/update_attendance_status/';
            $curl = new curl;
            $options = array(
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_HEADER' => false,
                'CURLOPT_POST' => count($postdata),
                'CURLOPT_HTTPHEADER' => array(
                    'LeelooLXPToken: '.get_config('local_leeloolxpapi')->leelooapitoken.''
                )
            );
            $curl->post($url, $postdata, $options);
            $this->content = new stdClass;
            $configloginlogout = get_config('local_leeloolxp_web_login_tracking');
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

                                var tracking_on = sessionStorage.getItem('tracked');

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
                                document.getElementById('Tcountdown').innerHTML =Tpad(hours) + ':' + Tpad(minutes) + ':' + Tpad
                                (remainingSeconds);
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
                                var clock_intracking_on = sessionStorage.getItem('tracked');
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
                                var clock_intracking_on = sessionStorage.getItem('tracked');
                                if(clock_intracking_on=='1') {
                                    document.getElementById('Tcountdown').style.display = 'inline-block';
                                }

                        }

                    }
                    </script>";

            $html .= '<b><hr></b><br> <b> '.get_string('shift_today', 'block_leeloolxp_tracking').' </b> ' . $outputtimezone . ' <br>';

            if (!empty($sdetail->data)) {
                $html .= get_string('starttime', 'block_leeloolxp_tracking').' : ' . $sdetail->data->start;

                $html .= '<br> '.get_string('endtime', 'block_leeloolxp_tracking').' : ' . $sdetail->data->end;

                $html .= '<b><hr></b><br> <b>'.get_string('attendance', 'block_leeloolxp_tracking').':</b> ';

                $html .= '<br> '.get_string('start', 'block_leeloolxp_tracking').': ' . date('h:i A', strtotime($starttime)) . "  <span>" . $starttimestatus . "</span>";

                date_default_timezone_set($outputtimezone);

                $html .= '<br> '.get_string('end', 'block_leeloolxp_tracking').': ' . date('h:i A') . "<span> " . $endtimestatus . "</span>";
            } else {

                $html .= get_string('askschedule', 'block_leeloolxp_tracking');
            }

            $html .= '<br> <br> <br>';

            if (strpos($clockintime, '-') === false) {
                $html .= '';

                $html .= '<div id="countdown_div" style="display:none;">'.get_string('time', 'block_leeloolxp_tracking').': <span id="countdown"></
                span>';
                if ($sdetail->data) {
                    $html .= "/" . $sdetail->data->minimum_hours . ":00";
                };
                $html .= '</div>';
                $html .= '<div id = "clockin_break_span_main" style="display:none">'.get_string('break', 'block_leeloolxp_tracking').':
                <span id="clockin_break_span" >' .
                $totalbreack . ' </span>';
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

                $html .= get_string('time_not_updated', 'block_leeloolxp_tracking');

                $html .= '<br>'.get_string('break_not_updated', 'block_leeloolxp_tracking');
            }

            $html .= '<br> <br> <br>';

            $reqid = optional_param('id', null, PARAM_RAW);
            if (isset($reqid)) {
                if ($this->page->pagetype == 'mod-leeloolxpvc-conference' ||
                $this->page->pagetype == 'mod-leeloolxpvc-view' ||
                $this->page->pagetype == 'mod-resource-view' ||
                $this->page->pagetype == 'mod-leeloolxpvimeo-view' ||
                $this->page->pagetype == 'mod-forum-view' ||
                $this->page->pagetype == 'mod-book-view' ||
                $this->page->pagetype == 'mod-assign-view' ||
                $this->page->pagetype == 'mod-survey-view' ||
                $this->page->pagetype == 'mod-page-view' ||
                $this->page->pagetype == 'mod-quiz-view' ||
                $this->page->pagetype == 'mod-quiz-attempt' ||
                $this->page->pagetype == 'mod-quiz-summary' ||
                $this->page->pagetype == 'mod-quiz-summary' ||
                $this->page->pagetype == 'mod-chat-view' ||
                $this->page->pagetype == 'mod-choice-view' ||
                $this->page->pagetype == 'mod-lti-view' ||
                $this->page->pagetype == 'mod-feedback-view' ||
                $this->page->pagetype == 'mod-data-view' ||
                $this->page->pagetype == 'mod-forum-view' ||
                $this->page->pagetype == 'mod-glossary-view' ||
                $this->page->pagetype == 'mod-scorm-view' ||
                $this->page->pagetype == 'mod-wiki-view' ||
                $this->page->pagetype == 'mod-workshop-view' ||
                $this->page->pagetype == 'mod-folder-view' ||
                $this->page->pagetype == 'mod-imscp-view' ||
                $this->page->pagetype == 'mod-label-view' ||
                $this->page->pagetype == 'mod-url-view') {
                    $html .= '<b>' . $taskname . '</b> <br>';
                    $html .= get_string('time', 'block_leeloolxp_tracking').': <span id="Tcountdown" style="display:none;"></span>';

                    $html .= '<br> '.get_string('estimated', 'block_leeloolxp_tracking').': ' . $estimates;
                }
            }

            $this->content->text = $html;

            $this->content->footer = '';

            return $this->content;
        }
    }
    /**
     * Allow the block to have a configuration page
     *
     * @return boolean
     */
    public function has_config() {
        return true;
    }
}
