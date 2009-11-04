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

class SFS_Form_Morning extends CRM_Core_Form {
    protected $_students;

    function buildQuickForm( ) {

        for ( $i = 1; $i <= 6; $i++ ) {
            $this->add( 'text',
                        "student_$i",
                        ts( 'Student' ) );
            $this->add( 'hidden', "student_id_$i",  0);
        }

        $this->addDefaultButtons( 'Morning Extended Care Signup', 'next', null, true );
    }

    static function postProcessStudent( $studentID, $date ) {
        $sql = "
SELECT e.id, e.class
FROM   civicrm_value_extended_care_signout_3 e
WHERE  entity_id = %1
AND    signin_time LIKE '{$_date}%'
AND    is_morning = 1
";
        $params = array( 1 => array( $studentID, 'Integer' ) );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );

        $params = array( 1 => array( $studentID     , 'Integer' ),
                         2 => array( "{$date} 07:00", 'String'  ),
                         3 => array( "{$date} 08:30", 'String'  ) );

        if ( $dao->fetch( ) ) {
            $params[4] = array( $dao->id, 'Integer' );
            $sql = "
UPDATE civicrm_value_extended_care_signout_3 
SET    signin_time        = %2,
       signout_time       = %3
WHERE  id = %4
";
        } else {
            $sql = "
INSERT INTO civicrm_value_extended_care_signout_3
( entity_id, signin_time, signout_time, is_morning )
VALUES
( %1, %2, %3, 1 )
";
        }
        CRM_Core_DAO::executeQuery( $sql, $params );
        return;
    }

    static function addMorningRecord( ) {
        $date = CRM_Utils_Request::retrieve( 'date',
                                             'String',
                                             CRM_Core_DAO::$_nullObject,
                                             false,
                                             date( 'Y-m-d' ),
                                             'REQUEST' );

        $result = null;
        for ( $i = 1; $i <= 6; $i++ ) {
            $studentID = CRM_Utils_Request::retrieve( "studentID_$i",
                                                      'Positive',
                                                      CRM_Core_DAO::$_nullObject,
                                                      false,
                                                      null,
                                                      'REQUEST' );
            if ( ! empty( $studentID ) ) {
                self::postProcessStudent( $studentID, $date );
                $result[] = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact',
                                                         $studentID,
                                                         'display_name' );
            }
        }

        echo implode( ", ", $result );
        exit( );
    }

}



