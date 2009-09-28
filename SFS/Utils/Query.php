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

class SFS_Utils_Query {

    static function checkSubType( $id, $subType = 'Student', $redirect = true ) {
        $entitySubType = self::getSubType( $id );

        if ( ! is_array( $subType ) ) {
            $subType = array( $subType );
        }

        if ( ! in_array( $entitySubType, $subType ) ) {
            if ( $redirect ) {
                $config = CRM_Core_Config::singleton( );
                CRM_Utils_System::redirect( $config->userFrameworkBaseURL );
            }
            return false;
        }
        return true;
    }

    static function getSubType( $id ) {
        static $_cache = array( );
        
        if ( ! array_key_exists( $id, $_cache ) ) {
            $sql = "
SELECT subtype
FROM   civicrm_value_school_information_1
WHERE  entity_id = %1
";
            $params = array( 1 => array( $id, 'Integer' ) );
            $_cache[$id] = CRM_Core_DAO::singleValueQuery( $sql, $params );
        }
        return $_cache[$id];
    }

    static function getGrade( $id ) {
        static $_cache = array( );
        
        if ( ! array_key_exists( $id, $_cache ) ) {
            $sql = "
SELECT grade
FROM   civicrm_value_school_information_1
WHERE  entity_id = %1
";
            $params = array( 1 => array( $id, 'Integer' ) );
            $_cache[$id] = CRM_Core_DAO::singleValueQuery( $sql, $params );
        }
        return $_cache[$id];
    }

    static function &getStudentsByGrade( $extendedCareOnly = false ) {
        $sql = "
SELECT     c.id, c.display_name, sis.grade 
FROM       civicrm_contact c
INNER JOIN civicrm_value_school_information_1 sis ON sis.entity_id = c.id
";

        if ( $extendedCareOnly ) {
            $sql .= " WHERE sis.grade_sis > 0";
        }
        $sql .= " ORDER BY sis.grade_sis DESC";

        $dao = CRM_Core_DAO::executeQuery( $sql );

        $students = array( '' => array( '' => '- First Select Grade -') );

        while ( $dao->fetch( ) ) {
            if ( ! array_key_exists( $dao->grade, $students ) ) {
                $students[$dao->grade] = array( '' => '- Select Student -' );
            }
            $students[$dao->grade][$dao->id] = $dao->display_name;
        }
        return $students;
    }

    static function getNameAndEmail( $id ) {
        $sql = "
SELECT    c.display_name, e.email
FROM      civicrm_contact c
LEFT JOIN civicrm_email e ON ( e.contact_id = c.id )
WHERE     c.id = %1
ORDER BY  e.is_primary desc
";
        $params = array( 1 => array( $id, 'Integer' ) );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );
        if ( $dao->fetch( ) ) {
            return array( $dao->display_name, $dao->email );
        }
        return array( null, null );
    }

}
