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

class sfschool_Utils_Query {

    static function checkSubType( $id, $subType = 'Student', $abort = true ) {
        $entitySubType = self::getSubType( $id );

        if ( ! is_array( $subType ) ) {
            $subType = array( $subType );
        }

        if ( ! in_array( $entitySubType, $subType ) ) {
            if ( $abort ) {
                $subType = implode( ',', $subType );
                CRM_Core_Error::fatal( "The subtypes of the contact and the profile do not match: ( $id, Expected: $subType Actual: $entitySubType )" );
                exit( );
            }
            return false;
        }
        return true;
    }

    static function getSubType( $id, $subType = 'Student' ) {
        $sql = "
SELECT subtype_1
FROM   civicrm_value_school_information_1
WHERE  entity_id = %1
";
        $params = array( 1 => array( $id, 'Integer' ) );
        return CRM_Core_DAO::singleValueQuery( $sql, $params );
    }

}
