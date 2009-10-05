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

class SFS_Page_SignIn extends CRM_Core_Page {

    protected $_dayOfWeek;
    
    protected $_date;

    protected $_time;

    function __construct( $title = null, $mode = null ) {
        parent::__construct( $title, $mode );

        $this->_dayOfWeek = CRM_Utils_Request::retrieve( 'dayOfWeek', 'String', $this, false, date( 'l'   ) );
        $this->_date      = CRM_Utils_Request::retrieve( 'date'     , 'String', $this, false, date( 'Y-m-d' ) );
        $this->_time      = CRM_Utils_Request::retrieve( 'time'     , 'String', $this, false, date( 'G:i'  ) );

        $this->assign( 'dayOfWeek', $this->_dayOfWeek );
        $this->assign( 'date'     , $this->_date );
        $this->assign( 'time'     , $this->_time );
    }

    function run( ) {
        $sql = "
SELECT  c.id as contact_id, c.display_name as display_name, s.name as course_name
FROM    civicrm_contact c,
        civicrm_value_extended_care_2 s
WHERE   s.entity_id = c.id
AND     s.has_cancelled = 0
AND     s.day_of_week = '{$this->_dayOfWeek}'
ORDER BY s.name";

        $dao = CRM_Core_DAO::executeQuery( $sql );
        
        $studentDetails = array( );
        while( $dao->fetch( ) ) {
            $studentDetails[ ] = array( 'display_name' => $dao->display_name,
                                        'course_name'   => $dao->course_name,
                                        'contact_id'   => $dao->contact_id
                                      );
        }
        
        $this->assign('studentDetails', $studentDetails);
        parent::run( );
    }

    /**
    * Function to add attendance data
    */
    static function addRecord( ) {
        // currently you get contact id, day, if checkbox was checked or unchecked (true or false)
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
        $checked   = CRM_Utils_Request::retrieve( 'checked'  , 'String', 
                                                  CRM_Core_DAO::$_nullObject,
                                                  false, 'true',
                                                  'REQUEST' );

        self::addStudentToClass( $cid, $date, $time, $checked );
    }

    static function addStudentToClass( $cid, $date, $time, $checked = 'true', $course = '' ) {
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
                         4 => array( $course          , 'String'  ) );
        
        $dao    = CRM_Core_DAO::executeQuery( $sql, $params );

        if ( ! $dao->fetch( ) ) {
            if ( $checked != 'false' ) {
                $sql = "
INSERT INTO civicrm_value_extended_care_signout_3 ( entity_id, signin_time, class )
VALUES ( %1, %3, %4 )
";
                $dao = CRM_Core_DAO::executeQuery( $sql, $params );
            }
        } else {
            if ( $checked == 'false' ) {
                $sql = "
DELETE FROM civicrm_value_extended_care_signout_3
WHERE  id = {$dao->id}
";
                $dao = CRM_Core_DAO::executeQuery( $sql, $params );
            }
        }
        exit( );
    }

    /**
     * Get students who have not signed up for any courses
     */
    static function getStudents( ) {
        $name = CRM_Utils_Type::escape( $_GET['s'], 'String' );

        $dayOfWeek = CRM_Utils_Request::retrieve( 'dayOfWeek', 'String', 
                                                  CRM_Core_DAO::$_nullObject,
                                                  false, date( 'l'   ),
                                                  'REQUEST' );
        
        $limit = '10';
        if ( CRM_Utils_Array::value( 'limit', $_GET) ) {
            $limit = CRM_Utils_Type::escape( $_GET['limit'], 'Positive' );
        }

        $query = "
SELECT c.id, c.display_name
FROM   civicrm_contact c,
       civicrm_value_school_information_1 s
WHERE  s.entity_id = c.id
AND    s.grade_sis >= 1
AND    s.subtype = 'Student'

AND    c.id NOT IN (
    SELECT DISTINCT(c.id)
    FROM   civicrm_contact c,
           civicrm_value_extended_care_2 s
    WHERE  s.entity_id = c.id
    AND    s.has_cancelled = 0
    AND    s.day_of_week = %1
  )
AND c.sort_name LIKE '%$name%'
ORDER BY sort_name
LIMIT 0, {$limit}
";
        $params = array( 1 => array( $dayOfWeek, 'String' ) );
        $dao = CRM_Core_DAO::executeQuery( $query, $params );
        $contactList = null;
        while ( $dao->fetch( ) ) {
            echo $contactList = "$dao->display_name|$dao->id\n";
        }
        exit();        
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

        self::addStudentToClass( $cid, $date, $time, 'true', $course );

        exit();
    }
}
