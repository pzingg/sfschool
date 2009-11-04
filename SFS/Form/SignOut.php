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
    const
        MAX_NUMBER = 10;

    protected $_students;

    protected $_maxNumber;

    function buildQuickForm( ) {

        $this->_maxNumber = self::MAX_NUMBER;
        $this->assign( 'maxNumber', $this->_maxNumber );

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
        }

        $this->addDefaultButtons( 'Sign Out', 'next', null, true );
    }

    static function postProcessStudent( $pickupName,
                                        $studentID,
                                        $isMorning = 0 ) {
        static $_now  = null;
        static $_date = null;

        if ( ! $_now ) {
            $_now = CRM_Utils_Date::getToday( null, 'YmdHis' );
        }

        if ( ! $_date ) {
            $_date = CRM_Utils_Date::getToday( null, 'Y-m-d' );
        }

        $sql = "
SELECT e.id, e.class
FROM   civicrm_value_extended_care_signout_3 e
WHERE  entity_id = %1
AND    signin_time LIKE '{$_date}%'
AND    ( is_morning = 0 OR is_morning IS NULL )
";
        $params = array( 1 => array( $studentID, 'Integer' ) );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );

        $params = array( 1 => array( $studentID , 'Integer'   ),
                         2 => array( $pickupName, 'String'    ),
                         3 => array( $_now      , 'Timestamp' ),
                         4 => array( $isMorning , 'Integer'   ) );

        $class = null;
        if ( $dao->fetch( ) ) {
            $class = $dao->class;
            $sql = "
UPDATE civicrm_value_extended_care_signout_3 
SET    pickup_person_name = %2,
       signout_time       = %3
WHERE  id = %5
";
            $params[5] = array( $dao->id, 'Integer' );
        } else {
            $sql = "
INSERT INTO civicrm_value_extended_care_signout_3
( entity_id, pickup_person_name, signout_time, is_morning )
VALUES
( %1, %2, %3, %4 )
";
        }
        CRM_Core_DAO::executeQuery( $sql, $params );
        return $class;
    }

    static function addSignOutRecord( ) {
        $pickup    = CRM_Utils_Request::retrieve( 'pickupName',
                                                  'String',
                                                  CRM_Core_DAO::$_nullObject,
                                                  true,
                                                  null,
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
                $className = self::postProcessStudent( $pickup,
                                                       $studentID );
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



