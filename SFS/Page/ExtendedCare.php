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

class SFS_Page_ExtendedCare extends CRM_Core_Page {

    function run( ) {
        $id = CRM_Utils_Request::retrieve( 'id',
                                           'Integer',
                                           $this,
                                           true );

        
        $currentYear  = date( 'Y' );
        $currentMonth = date( 'm' );
        if ( $currentMonth < 9 ) {
            $currentYear--;
        }
        
        // for this year ONLY lets start from december
        $currentMonth = ( $currentYear == 2009 ) ? '12' : '09';
        $startDate = CRM_Utils_Request::retrieve( 'startDate',
                                                  'String',
                                                  $this,
                                                  false,
                                                  "{$currentYear}{$currentMonth}01" );

        $endDate = CRM_Utils_Request::retrieve( 'endDate',
                                                'String',
                                                $this,
                                                false,
                                                date( 'Ymd' ) );

        require_once 'SFS/Utils/ExtendedCare.php';
        $details = SFS_Utils_ExtendedCare::signoutDetails( $startDate,
                                                           $endDate,
                                                           true,
                                                           true,
                                                           $id );
        $this->assign( 'detail', array_pop( $details ) );
        $this->assign( 'displayName',
                       CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact',
                                                    $id,
                                                    'display_name' ) );

        parent::run( );
    }

}
