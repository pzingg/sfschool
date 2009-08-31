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

require_once 'CRM/Core/Page.php';

class SFS_Page_Class extends CRM_Core_Page {

    function run( ) {
        require_once 'SFS/Utils/ExtendedCare.php';
        $activities =  array( );
        $activities =& SFS_Utils_ExtendedCare::getActivities( null,
                                                              CRM_Core_DAO::$_nullObject );

        $values = array( );
        foreach ( $activities as $day => &$dayValues ) {
            $values[$day] = array( );
            foreach ( $dayValues as $session => &$sessionValues ) {
                foreach ( $sessionValues['details'] as $id => &$idValues ) {
                    $idValues['session'] = $idValues['session'] == 'First' ? '3:30 pm - 4:30 pm' : "4:30 pm - 5:30 pm";
                    $values[$day][] =& $idValues;
                }
            }
        }

        $this->assign( 'schedule', $values );

        $this->assign( 'editClass', false );
        if( CRM_Core_Permission::check( 'Administer Extended Care Information' ) ) {
            $disableActivities = array( );
            $disableActivities =& SFS_Utils_ExtendedCare::getActivities( null,
                                                                         CRM_Core_DAO::$_nullObject ,
                                                                         false );
            $disable = array( );
            foreach ( $disableActivities as $day => $valueDay ) {
                $values[$day] = array( );
                foreach ( $valueDay as $session => $valueSession ) {
                    foreach ( $valueSession['details'] as $id => $valueId ) {
                        $valueId['session'] = $valueId['session'] == 'First' ? '3:30 pm - 4:30 pm' : "4:30 pm - 5:30 pm";
                        $disable[$day][] = $valueId;
                    }
                }
            }
            if( !empty( $disable ) ) {
                $this->assign('disableActivities', $disable );
            }
            $this->assign( 'editClass', true );
        }
         
        parent::run( );
    }
    
}
