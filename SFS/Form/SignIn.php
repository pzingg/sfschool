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

class SFS_Form_SignIn extends CRM_Core_Form {

    protected $_dayOfWeek;
    
    protected $_date;

    protected $_time;

    function preProcess( ) {
        parent::preProcess( );

        $this->_date      = CRM_Utils_Request::retrieve( 'date'     , 'String' , $this, false, date( 'Y-m-d' ) );
        $this->_time      = CRM_Utils_Request::retrieve( 'time'     , 'String' , $this, false, date( 'G:i'  ) );
        $this->_signOut   = CRM_Utils_Request::retrieve( 'signOut'  , 'Integer', $this, false, 0 );

        // get the dayOfWeek from the date
        $this->_dayOfWeek = date( 'l', strtotime( $this->_date ) );

        $this->assign( 'displayDate',
                       date( 'l - F d, Y', strtotime( $this->_date ) ) );

        $this->assign( 'dayOfWeek', $this->_dayOfWeek );
        $this->assign( 'date'     , $this->_date      );
        $this->assign( 'time'     , $this->_time      );
        $this->assign( 'signOut'  , $this->_signOut   );

    }

    function buildQuickForm( ) {
        $sql = "
( 
SELECT     c.id as contact_id, c.display_name as display_name, s.name as course_name, v.grade as grade,
           0 as sout_id, 0 as signout_time
FROM       civicrm_contact c
INNER JOIN civicrm_value_school_information_1 v ON v.entity_id = c.id
INNER JOIN civicrm_value_extended_care_2 s ON ( s.entity_id = c.id AND s.has_cancelled = 0 AND s.day_of_week = '{$this->_dayOfWeek}' )
WHERE      v.subtype = 'Student'
AND        v.grade_sis >= 1
)
UNION
(
SELECT     c.id as contact_id, c.display_name as display_name, sout.class as course_name, v.grade as grade,
           sout.id as sout_id, sout.signout_time as signout_time
FROM       civicrm_contact c
INNER JOIN civicrm_value_school_information_1 v ON v.entity_id = c.id
INNER JOIN civicrm_value_extended_care_signout_3 sout ON sout.entity_id = c.id
WHERE      v.subtype = 'Student'
AND        v.grade_sis >= 1
AND        ( sout.is_morning = 0 OR sout.is_morning IS NULL )
AND        DATE( sout.signin_time ) = %1
)
ORDER BY contact_id, sout_id, course_name, display_name, signout_time
";

        $params = array( 1 => array( $this->_date, 'String' ) );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );
        
        $studentDetails = array( );
        while( $dao->fetch( ) ) {
            $studentDetails[$dao->contact_id] = array( 'display_name'  => $dao->display_name,
                                                       'course_name'   => $dao->sout_class ? $dao->sout_class : $dao->course_name,
                                                       'grade'         => $dao->grade,
                                                       'contact_id'    => $dao->contact_id,
                                                       'is_marked'     => $dao->sout_id ? 1 : 0,
                                                       'signout_block' => self::signoutBlock( $dao->signout_time ),
                                                     );
        }
        
        $this->assign('studentDetails', $studentDetails);

        require_once 'SFS/Utils/Query.php';

        $students = array( '' => '- Select Student -' ) + SFS_Utils_Query::getStudentsByGrade( true, false );
        $this->add( 'select',
                    "student_id",
                    ts( 'Student' ),
                    $students );

        $classes = array( '' => '- Select Class -' ) + SFS_Utils_Query::getClasses( );
        $this->add( 'select',
                    "course_name",
                    ts( 'Course' ),
                    $classes );

        $timeSlots = array( '' => '- Select Time -',
                            1  => 'Before 3:30 pm',
                            2  => '3:30 - 4:30 pm',
                            3  => '4:30 - 5:15 pm',
                            4  => '5:15 - 6:00 pm',
                            5  => 'After  6:00 pm' );
        $this->add( 'select',
                    "signout_time",
                    ts( 'Signout Time' ),
                    $timeSlots );
    }

    static function signoutBlock( $time ) {
        if ( empty( $time ) ) {
            return null;
        }

        $dateParts = CRM_Utils_Date::unformat( $time );
        
        if ( $dateParts['H'] < 15 ||
             ( $dateParts['H'] == 15 && $dateParts['i'] <= 30 ) ) {
            return 1;
        }
        
        if ( $dateParts['H'] == 15 ||
             ( $dateParts['H'] == 16 && $dateParts['i'] <= 30 ) ) {
            return 2;
        }

        if ( $dateParts['H'] == 16 ||
             ( $dateParts['H'] == 17 && $dateParts['i'] <= 15 ) ) {
            return 3;
        }

        if ( $dateParts['H'] == 17 ||
             ( $dateParts['H'] == 18 && $dateParts['i'] <= 15 ) ) {
            return 4;
        }

        return 5;
    }

    /**
    * Function to add attendance data
    */
    static function addRecord( ) {
        // currently you get contact id, day, if checkbox was checked or unchecked (true or false)
        $cidString = CRM_Utils_Request::retrieve( 'contactID', 'String',
                                                  CRM_Core_DAO::$_nullObject,
                                                  true,
                                                  null,
                                                  'REQUEST' );
        list( $cid, $course ) = CRM_Utils_System::explode( ':::', $cidString, 2 );
        $date      = CRM_Utils_Request::retrieve( 'date'     , 'String', 
                                                  CRM_Core_DAO::$_nullObject,
                                                  false, date( 'Ymd' ),
                                                  'REQUEST' );
        $time      = CRM_Utils_Request::retrieve( 'time'     , 'String', 
                                                  CRM_Core_DAO::$_nullObject,
                                                  false, date( 'Gi'  ),
                                                  'REQUEST' );
        $checked   = CRM_Utils_Request::retrieve( 'checked'  , 'String', 
                                                  CRM_Core_DAO::$_nullObject,
                                                  false, 'true',
                                                  'REQUEST' );
        $signout   = CRM_Utils_Request::retrieve( 'signout', 'String',
                                                  CRM_Core_DAO::$_nullObject,
                                                  false, null,
                                                  'REQUEST' );

        self::addStudentToClass( $cid, $date, $time, $signout, $checked, $course );
    }

    static function addStudentToClass( $cid, $date, $time, $signout = null, $checked = 'true', $course = '', $isMorning = 0 ) {
        // update the entry if there is one for this contact id on this date
        $sql = "
SELECT id
FROM   civicrm_value_extended_care_signout_3
WHERE  entity_id = %1
AND    DATE( signin_time ) = %2
";
        $params = array( 1 => array( $cid             , 'Integer' ),
                         2 => array( $date            , 'String'  ),
                         3 => array( "{$date} {$time}", 'String'  ),
                         4 => array( $course          , 'String'  ),
                         5 => array( $isMorning       , 'Integer' ) );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );

        $signoutTime = null;
        if ( ! empty( $signout ) ) {
            switch ( $signout ) {
            case 1:
                $signoutTime = "{$date} 15:15"; break;
            case 2:
                $signoutTime = "{$date} 16:15"; break;
            case 3:
                $signoutTime = "{$date} 17:00"; break;
            case 4:
                $signoutTime = "{$date} 17:45"; break;
            case 5:
                $signoutTime = "{$date} 18:15"; break;
            default:
                break;
            }
        }
        if ( $signoutTime ) {
            $params[7] = array( $signoutTime, 'String' );
        }
        
        $sql = null;
        if ( ! $dao->fetch( ) ) {
            if ( $checked != 'false' ) {
                if ( $signoutTime ) {
                    $sql = "
INSERT INTO civicrm_value_extended_care_signout_3 ( entity_id, signin_time, class, is_morning, signout_time )
VALUES ( %1, %3, %4, %5, %7 )
";
                } else {
                    $sql = "
INSERT INTO civicrm_value_extended_care_signout_3 ( entity_id, signin_time, class, is_morning )
VALUES ( %1, %3, %4, %5 )
";
                }
            }
        } else {
            $params[6] = array( $dao->id, 'Integer' );
            if ( $checked == 'false' ) {
                $sql = "
DELETE FROM civicrm_value_extended_care_signout_3
WHERE  id = %6
";
            } else {
                if ( $signoutTime ) {
                    // dont update signin time here, should be set when signed in
                    $sql = "
UPDATE civicrm_value_extended_care_signout_3 
SET    class = %4,
       signout_time = %7
WHERE  id = %6
";
                } else {
                    $sql = "
UPDATE civicrm_value_extended_care_signout_3 
SET    signin_time = %3,
       class = %4
WHERE  id = %6
";
                }
            }
        }

        if ( $sql ) {
            CRM_Core_DAO::executeQuery( $sql, $params );
        }
        exit( );
    }

    /**
    * Function to add attendance data
    */
    static function addNewStudents( ) {
        // currently you get contact id, course and day
        $cid       = CRM_Utils_Request::retrieve( 'contactID', 'Integer',
                                                  CRM_Core_DAO::$_nullObject,
                                                  true,
                                                  null,
                                                  'REQUEST' );
        $date      = CRM_Utils_Request::retrieve( 'date'     , 'String', 
                                                  CRM_Core_DAO::$_nullObject,
                                                  false, date( 'Ymd' ),
                                                  'REQUEST' );
        $time      = CRM_Utils_Request::retrieve( 'time'     , 'String', 
                                                  CRM_Core_DAO::$_nullObject,
                                                  false, date( 'Gi'  ),
                                                  'REQUEST' );
        $course    = CRM_Utils_Request::retrieve( 'course'  , 'String', 
                                                  CRM_Core_DAO::$_nullObject,
                                                  false, null,
                                                  'REQUEST' );
        $signout   = CRM_Utils_Request::retrieve( 'signout', 'String',
                                                  CRM_Core_DAO::$_nullObject,
                                                  false, null,
                                                  'REQUEST' );

        self::addStudentToClass( $cid, $date, $time, $signout, 'true', $course );

        exit();
    }
}