<?php
// This file is part of ProFormA Question Type for Moodle
//
// ProFormA Question Type for Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ProFormA Question Type for Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ProFormA Question Type for Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Version information for the ProFormA format question importer.
 *
 * @package    qformat_proforma
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'qformat_proforma';
$plugin->version   = 2019061400;
$plugin->requires  = 2016120503;
$plugin->release   = '0.9.2';
$plugin->maturity  = MATURITY_STABLE;

$plugin->dependencies = array(
        'qtype_proforma' => 2019051600
);