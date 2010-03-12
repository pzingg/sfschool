<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2009                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007.                                       |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2009
 * $Id$
 *
 */

global $civicrm_root;
# $civicrm_root = '/Users/lobo/svn/crm_v3.1/';
# $civicrm_root = '/var/www/sfschool.civicrm.org/public/sites/sfschool.civicrm.org/modules/civicrm/';
# $civicrm_root = '/home/sfschool/www/drupal/sites/all/modules/civicrm/';
$modules_sfschool_bin_directory = substr(__FILE__, 0, strrpos(__FILE__, '/'));
$civicrm_root = $modules_sfschool_bin_directory . "/../../civicrm";

function sfs_bin_Utils_auth( ) {
    session_start( );                               
                                            
    global $civicrm_root;

    require_once "$civicrm_root/civicrm.config.php";
    require_once 'CRM/Core/Config.php'; 
    
    $config =& CRM_Core_Config::singleton(); 

    // this does not return on failure
    CRM_Utils_System::authenticateScript( true );
}
