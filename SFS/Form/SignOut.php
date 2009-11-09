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

require_once 'CRM/Core/Form.php';

class SFS_Form_SignOut extends CRM_Core_Form {
    protected $_students;

    function buildQuickForm( ) {

        $this->add( 'text',
                    'pickup_name',
                    ts( 'Pickup Person Name' ),
                    null,
                    true );

        for ( $i = 1; $i <= 6; $i++ ) {
            $this->add( 'text',
                        "student_$i",
                        ts( 'Student' ) );
            $this->add( 'hidden', "student_id_$i",  0);
            $this->add( 'checkbox', "at_school_meeting_$i", ts( 'School Meeting?' ) );
        }

        $this->addDefaultButtons( 'Sign Out', 'next', null, true );
    }

    static function addSignOutRecord( ) {
        $pickup    = CRM_Utils_Request::retrieve( 'pickupName',
                                                  'String',
                                                  CRM_Core_DAO::$_nullObject,
                                                  true,
                                                  null,
                                                  'REQUEST' );

        $result = null;
        require_once 'SFS/Utils/ExtendedCare.php';
        for ( $i = 1; $i <= 6; $i++ ) {
            $studentID       = CRM_Utils_Request::retrieve( "studentID_$i",
                                                            'Positive',
                                                            CRM_Core_DAO::$_nullObject,
                                                            false,
                                                            null,
                                                            'REQUEST' );
            $atSchoolMeeting = CRM_Utils_Request::retrieve( "atSchoolMeeting_$i",
                                                            'Boolean',
                                                            CRM_Core_DAO::$_nullObject,
                                                            false,
                                                            false,
                                                            'REQUEST' );
            if ( ! empty( $studentID ) ) {
                $className = SFS_Utils_ExtendedCare::processSignOut( $pickup,
                                                                     $studentID,
                                                                     $atSchoolMeeting );
                if ( empty( $className ) ) {
                    $className = 'Yard Play';
                }

                $studentName = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact',
                                                            $studentID,
                                                            'display_name' );
                $result[] = "{$studentName} @ {$className}";
            }
        }

        echo implode( ", ", $result );
        exit( );
    }

}



