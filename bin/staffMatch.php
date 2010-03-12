<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once 'Utils.php';
// require_once 'CRM/Core/Config.php';

// Look up Drupal user table by email, returning Drupal user id
// Ideally Drupal users could have additional attributes attached, such
// as staff id.  There is a "data" field in the Drupal table, but I 
// can't find any UI to modify it.
function &findUFMatch( $email ) {
    require_once 'DB.php';

    $config =& CRM_Core_Config::singleton( );
    $db_cms = DB::connect($config->userFrameworkDSN);
    if ( DB::isError( $db_cms ) ) { 
        die( "Cannot connect to UF db via $dsn, " . $db_cms->getMessage( ) ); 
    }

    // Fetch id of user with this email address
    $id_sql   = "SELECT uid FROM {$config->userFrameworkUsersTableName} WHERE mail = '{$email}'";
    $id_query = $db_cms->query( $id_sql );
    $uid = NULL;
    if ( $id_row   = $id_query->fetchRow( DB_FETCHMODE_ASSOC ) ) {
        $uid = $id_row['uid'];
    }
    return $uid;
}

// Update the Drupal-CiviCRM contact-user sync table with a match
// Create a CRM_Core_DAO_UFMatch object and update it
function &saveUFMatch( $contact_id, $uf_id, $uf_name ) {
    require_once 'CRM/Core/DAO/UFMatch.php';

    $ufmatch =& new CRM_Core_DAO_UFMatch( );
    $ufmatch->domain_id = CRM_Core_Config::domainID( );
    $ufmatch->contact_id = $contact_id;
    $ufmatch->uf_id = $uf_id;
    $ufmatch->uf_name = $uf_name;
    return $ufmatch->save( );
}



// Loop through contacts that have a staff_id value between 1 and 104999
// JOIN civicrm_email for primary email (see getPrimaryEmail function)
function matchAllStaffContacts( ) {
    require_once 'CRM/Core/DAO.php';

    static $results = array( 'matched' => 0, 'unmatched' => 0 );

    $query = "SELECT c.id AS contact_id, ce.email AS email,
    ci.staff_id_5 AS staff_id,
    c.first_name, c.last_name, c.contact_type, c.contact_sub_type
    FROM civicrm_contact c 
    LEFT JOIN civicrm_email ce  
    ON ( c.id = ce.contact_id )
    LEFT JOIN civicrm_value_constituent_information_1 ci
    ON ( c.id = ci.entity_id )
    WHERE ce.is_primary = 1 AND ci.staff_id_5 >= %1 AND ci.staff_id_5 <= %2";

    $p = array( 1 => array( 1, 'Integer' ), 2 => array( 104999, 'Integer' ) );
    $dao =& CRM_Core_DAO::executeQuery( $query, $p );

    while ( $dao->fetch( ) ) {
        // For each staff contact, look up Drupal account with same email
        $contact_id = $dao->contact_id;
        $email = $dao->email;
        // $email = CRM_Contact_BAO_Contact::getPrimaryEmail( $contact_id );

        $uf_id = findUFMatch( $email );
	if ( $uf_id ) {
	  saveUFMatch( $contact_id, $uf_id, $email );
	  $results['matched'] += 1;
	} else {
	  $results['unmatched'] += 1;
	}
    }
    $dao->free( );
    return $results;
}

function run( ) {
    SFS_bin_Utils_auth( );

    $results = matchAllStaffContacts( );
    echo $results['matched'] . " matched; " . $results['unmatched'] . " unmatached";
}

run( );

?>
