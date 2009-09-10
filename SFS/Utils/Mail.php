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

class SFS_Utils_Mail {
    
    const
        SFS_FROM_EMAIL = "SFS Parent Portal <info@sfschool.org>",
        SFS_BCC_EMAIL  = "SFS Parent Archival <archive.civicrm@sfschool.org>";
    
    static function sendMailToParents( $childID,
                                       $subjectTPL,
                                       $messageTPL,
                                       $templateVars ) {

        require_once 'SFS/Utils/Relationship.php';
        $parentInfo = array( );
        SFS_Utils_Relationship::getParents( $childID, $parentInfo, false );

        $count = 1;
        $toDisplayName = $toEmail = $cc = null;
        foreach ( $parentInfo as $parent ) {
            $templateVars["parent_{$count}_Name"] = $parent['name'];
            if ( $parent['email'] ) {
                if ( ! $toEmail ) {
                    $toDisplayName = $parent['name'];
                    $toEmail       = $parent['email'];
                } else {
                    $cc .= "{$parent['name']} <{$parent['email']}>";
                }
            }
            $count++;
        }

        // return if we dont have a toEmail
        if ( ! $toEmail ) {
            return;
        }

        list( $templateVars['childName'],
              $templateVars['childEmail'] ) = SFS_Utils_Query::getNameAndEmail( $childID );

        $template = CRM_Core_Smarty::singleton( );
        $template->assign( $templateVars );

        echo "Sending email to $toDisplayName, $toEmail<p>";

        require_once 'CRM/Utils/Mail.php';
        require_once 'CRM/Utils/String.php';
        CRM_Utils_Mail::send( self::SFS_FROM_EMAIL,
                              $toDisplayName,
                              $toEmail,
                              $template->fetch( $subjectTPL ),
                              $template->fetch( $messageTPL ),
                              $cc,
                              self::SFS_BCC_EMAIL
                              );
        
    }

}
