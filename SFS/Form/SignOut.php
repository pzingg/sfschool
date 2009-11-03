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

    protected $_grade;
    protected $_students;

    protected $_maxNumber;

    public    $_date;

    function buildQuickForm( ) {

        $this->_date      = CRM_Utils_Date::getToday( null, 'Y-m-d' );

        $this->_maxNumber = self::MAX_NUMBER;
        $this->assign( 'maxNumber', $this->_maxNumber );

        $this->add( 'text',
                    'pickup_name',
                    ts( 'Pickup Person Name' ),
                    null,
                    true );

        $this->_grade = array( '' => '- Select Grade -',
                               1  => 1,
                               2  => 2,
                               3  => 3,
                               4  => 4,
                               5  => 5,
                               6  => 6,
                               7  => 7,
                               8  => 8 );
        
        require_once 'SFS/Utils/Query.php';
        $this->_students =& SFS_Utils_Query::getStudentsByGrade( true );

        $studentElement = array( );
        for ( $i = 1; $i <= $this->_maxNumber; $i++ ) {
            $required = $i == 1 ? true : false;
            $studentElement[$i] =& $this->add( 'hierselect',
                                               "grade_student_id_$i",
                                               ts( "Student $i" ),
                                               null,
                                               $required );
            $studentElement[$i]->setOptions( array( $this->_grade, $this->_students ) );
        }

        $this->addDefaultButtons( 'Sign Out', 'next', null, true );

        $this->addFormRule( array( 'SFS_Form_SignOut', 'formRule' ), $this );
    }

    /**  
     * global form rule  
     *  
     * @param array $fields the input form values  
     * @param array $files  the uploaded files if any  
     * @param array $self   current form object. 
     *  
     * @return array array of errors / empty array.   
     * @access public  
     * @static  
     */  
    static function formRule( &$fields, &$files, &$self ) 
    {
        $errors = array( );

        $today = CRM_Utils_Date::getToday( null, 'Y-m-d' );

        for ( $i = 1; $i <= 4; $i++ ) {
            if ( ! empty( $fields["grade_student_id_$i"] )    &&
                 ! empty( $fields["grade_student_id_$i"][0] ) &&
                 ! empty( $fields["grade_student_id_$i"][1] ) ) {
                if ( ( $message = self::hasBeenPickedUp( $fields["grade_student_id_$i"][1], $form->_date, true ) ) != null ) {
                    $errors["grade_student_id_$i"] = $message;
                }
            }
        }

        return $errors;
    }

    static function hasBeenPickedUp( $studentID, $date, $returnErrorMessage = false ) {
        $sql = "
SELECT     e.*, c.display_name
FROM       civicrm_value_extended_care_signout_3 e
INNER JOIN civicrm_contact c ON c.id = e.entity_id
WHERE  entity_id = %1
AND    signout_time LIKE '{$date}%'
AND    ( is_morning = 0 OR is_morning IS NULL )
";
        $params = array( 1 => array( $studentID, 'Integer' ) );
        
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );
        if ( $dao->fetch( ) ) {
            if ( $returnErrorMessage ) {
                $dateTime = CRM_Utils_Date::customFormat( $dao->signout_time,
                                                          "%l:%M %P on %b %E%f" );
                return "{$dao->display_name} was picked up by {$dao->pickup_person_name} at {$dateTime}";
            } else {
                return true;
            }
        }
        return null;
    }

    function postProcess( ) {
        $params = $this->controller->exportValues( $this->_name );

        $rightNow  = CRM_Utils_Date::getToday( null, 'YmdHis' );

        for ( $i = 1; $i <= self::MAX_NUMBER; $i++ ) {
            if ( ! empty( $params["grade_student_id_$i"] )    &&
                 ! empty( $params["grade_student_id_$i"][0] ) &&
                 ! empty( $params["grade_student_id_$i"][1] ) ) {
                $this->postProcessStudent( $params['pickup_name'],
                                           $params["grade_student_id_$i"][1] );
            }
        }

        $session =& CRM_Core_Session::singleton( );
        $session->pushUserContext( CRM_Utils_System::url( 'civicrm/sfschool/signout',
                                                          'reset=1' ) );
    }

    static function postProcessStudent( $pickupName,
                                        $studentID,
                                        $isMorning = 0 ) {
        static $_now = null;

        if ( ! $_now ) {
            $_now = CRM_Utils_Date::getToday( null, 'YmdHis' );
        }

        $sql = "
SELECT e.id, e.class
FROM   civicrm_value_extended_care_signout_3 e
WHERE  entity_id = %1
AND    signin_time LIKE '{$this->_date}%'
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
        $studentID = CRM_Utils_Request::retrieve( 'contactID',
                                                  'String',
                                                  CRM_Core_DAO::$_nullObject,
                                                  true,
                                                  null,
                                                  'REQUEST' );

        $pickup    = CRM_Utils_Request::retrieve( 'contactID',
                                                  'String',
                                                  CRM_Core_DAO::$_nullObject,
                                                  true,
                                                  null,
                                                  'REQUEST' );

        $className = self::postProcessStudent( $pickup,
                                               $studentID );

        if ( $className == null ) {
            $className = 'Yard Play';
        }

        echo $className;
        exit( );
    }

}



