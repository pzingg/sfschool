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

    function run( ) {
        $sql = "SELECT c.id as contact_id, c.display_name as display_name, s.name as course_name
        FROM   civicrm_contact c,
               civicrm_value_extended_care_2 s
        WHERE  s.entity_id = c.id
        AND    s.has_cancelled = 0
        AND    s.day_of_week = 'Monday'
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
        // crm_core_error::debug( $_POST );
        exit();
    }

    /**
     * Get students who have not signed up for any courses
     */
    static function getStudents( ) {
        $name = CRM_Utils_Type::escape( $_GET['s'], 'String' );

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
                AND    s.day_of_week = 'Monday'
              )
            AND c.sort_name LIKE '%$name%'
            ORDER BY sort_name
            LIMIT 0, {$limit}
            ";

        $dao = CRM_Core_DAO::executeQuery( $query );
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
        // crm_core_error::debug( $_POST );
        exit();
    }
}
