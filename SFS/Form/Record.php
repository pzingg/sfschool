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

class SFS_Form_Record extends CRM_Core_Form {
    protected $_grade;
    protected $_students;

    function buildQuickForm( ) {
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
        for ( $i = 1; $i <= 4; $i++ ) {
            $required = $i == 1 ? true : false;
            $studentElement[$i] =& $this->add( 'hierselect',
                                               "grade_student_id_$i",
                                               ts( "Student $i" ),
                                               null,
                                               $required );
            $studentElement[$i]->setOptions( array( $this->_grade, $this->_students ) );
        }

        $this->addDefaultButtons( 'Sign Out', 'next', null, true );

        $this->addFormRule( array( 'SFS_Form_Record', 'formRule' ), $this );
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
                if ( ( $message = self::hasBeenPickedUp( $fields["grade_student_id_$i"][1], $today, true ) ) != null ) {
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
AND    time_of_pickup_17 LIKE '{$date}%'
";
        $params = array( 1 => array( $studentID, 'Integer' ) );
        
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );
        if ( $dao->fetch( ) ) {
            if ( $returnErrorMessage ) {
                return "Student {$dao->display_name} was picked up by {$dao->pickup_person_name_15} at {$dao->time_of_pickup_17}";
            } else {
                return true;
            }
        }
        return null;
    }

    function postProcess( ) {
        $params = $this->controller->exportValues( $this->_name );

        $rightNow  = CRM_Utils_Date::getToday( null, 'YmdHis' );

        for ( $i = 1; $i <= 4; $i++ ) {
            if ( ! empty( $params["grade_student_id_$i"] )    &&
                 ! empty( $params["grade_student_id_$i"][0] ) &&
                 ! empty( $params["grade_student_id_$i"][1] ) ) {
                $this->postProcessStudent( $params['pickup_name'],
                                           $params["grade_student_id_$i"][0],
                                           $params["grade_student_id_$i"][1],
                                           $rightNow );
            }
        }

        $session =& CRM_Core_Session::singleton( );
        $session->pushUserContext( CRM_Utils_System::url( 'civicrm/sfschool/record',
                                                          'reset=1' ) );
    }

    function postProcessStudent( $pickupName,
                                 $grade,
                                 $studentID,
                                 $now ) {
        $sql = "
INSERT INTO civicrm_value_extended_care_signout_3
( entity_id, pickup_person_name_15, grade_16, time_of_pickup_17 )
VALUES
( %1, %2, %3, %4 )
";
        $params = array( 1 => array( $studentID , 'Integer'   ),
                         2 => array( $pickupName, 'String'    ),
                         3 => array( $grade     , 'Integer'   ),
                         4 => array( $now       , 'Timestamp' ) );
        CRM_Core_DAO::executeQuery( $sql, $params );
    }

}



