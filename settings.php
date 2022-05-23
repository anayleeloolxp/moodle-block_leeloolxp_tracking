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
 *  leeloolxp_tracking block settings
 *
 * @package    block_leeloolxp_tracking
 * @copyright  2020 leelolxp.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;
if ($ADMIN->fulltree) {
    $name = 'block_leeloolxp_tracking/leeloolxp_block_tracking_licensekey';
    $title = get_string('leeloolxp_licensekey_block', 'block_leeloolxp_tracking');
    $description = get_string('leeloolxp_licensekey_block_desc', 'block_leeloolxp_tracking');
    $setting = new admin_setting_configtext($name, $title, $description, 0, PARAM_TEXT);
    $settings->add($setting);
}
