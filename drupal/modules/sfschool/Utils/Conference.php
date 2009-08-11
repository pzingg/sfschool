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

class sfschool_Utils_Conference {
    static function buildForm( &$form, $gid ) {
        $advisorID = CRM_Utils_Request::retrieve( 'advisorID', 'Integer', $form, false, null, $_REQUEST );
        $parentID  = CRM_Utils_Request::retrieve( 'parentID' , 'Integer', $form, false, null, $_REQUEST );
        $ptc       = CRM_Utils_Request::retrieve( 'ptc'      , 'Integer', $form, false, null, $_REQUEST );

        if ( empty( $advisorID ) || empty( $parentID ) || $ptc != 1 ) {
            return;
        }

        // add scheduling information if any
        $sql = "
SELECT     r.contact_id_b, a.id as activity_id, a.activity_date_time, a.subject, a.location, aac.display_name, aac.id as advisor_id
FROM       civicrm_activity a
INNER JOIN civicrm_activity_assignment aa ON a.id = aa.activity_id
INNER JOIN civicrm_contact            aac ON aa.assignee_contact_id = aac.id
INNER JOIN civicrm_relationship         r ON r.contact_id_a = aac.id
LEFT  JOIN civicrm_activity_target     at ON a.id = at.activity_id
WHERE      a.activity_type_id = 1
AND        r.relationship_type_id = 10
AND        r.contact_id_a = %1
AND        r.contact_id_b = %2
AND        a.status_id = 1
AND        at.target_contact_id IS NULL
";

        $params = array( 1 => array( $advisorID            , 'Integer' ),
                         2 => array( $form->getVar( '_id' ), 'Integer' ) );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );
        $elements = array( );
        while ( $dao->fetch( ) ) {
            $elements[$dao->activity_id] = $dao->activity_date_time;
        }
        
        if ( ! empty( $elements ) ) {
            $form->addElement( 'select', 'sfschool_activity_id', "Choose a Meeting time for<br/> {$dao->subject}", $elements, true );
        }
    }


    static function postProcess( $class, &$form, $gid ) {
        $advisorID = CRM_Utils_Request::retrieve( 'advisorID', 'Integer', $form, false, null, $_REQUEST );
        $parentID  = CRM_Utils_Request::retrieve( 'parentID' , 'Integer', $form, false, null, $_REQUEST );
        $ptc       = CRM_Utils_Request::retrieve( 'ptc'      , 'Integer', $form, false, null, $_REQUEST );

        if ( empty( $advisorID ) || empty( $parentID ) || $ptc != 1 ) {
            return;
        }

        $params = $form->controller->exportValues( $form->getVar( '_name' ) );

        $activityID = CRM_Utils_Array::value( 'sfschool_activity_id', $params );
        $childID    = $form->getVar( '_id' );

        if ( empty( $activityID ) || empty( $childID ) ) {
            return;
        }

        // insert these two into civicrm_target
        // we actually need to lock this and then ensure the space is available
        // lets do that at a later stage
        $sql = "
REPLACE INTO civicrm_activity_target (activity_id, target_contact_id)
VALUES
( %1, %2 ),
( %1, %3 )
";
        $params = array( 1 => array( $activityID, 'Integer' ),
                         2 => array( $childID   , 'Integer' ),
                         3 => array( $parentID  , 'Integer' ) );
        CRM_Core_DAO::executeQuery( $sql, $params );
    }

    function &getValues( $parentID, $childrenIDs, &$values ) {
        // check if we need to schedule this parent for a meeting
        // or display any future scheduled meetings
        if ( empty( $childrenIDs ) ) {
            return;
        }

        $single = false;
        if ( ! is_array( $childrenIDs ) ) {
            $childrenIDs = array( $childrenIDs );
            $single = true;
        }

        $childrenIDString = implode( ',', array_values( $childrenIDs ) );

        // first first all scheduled meetings
        $sql = "
SELECT     a.id, a.activity_date_time, a.subject, a.location, r.contact_id_b, aac.id as advisor_id, aac.display_name
FROM       civicrm_activity a
INNER JOIN civicrm_activity_assignment aa ON a.id = aa.activity_id
INNER JOIN civicrm_activity_target     at ON a.id = at.activity_id
INNER JOIN civicrm_contact            aac ON aa.assignee_contact_id = aac.id
INNER JOIN civicrm_relationship         r ON r.contact_id_a         = aac.id
WHERE      a.activity_type_id = 1
AND        a.status_id = 1
AND        r.relationship_type_id = 10
AND        r.contact_id_b IN ( $childrenIDString )
AND        aa.assignee_contact_id = r.contact_id_a
AND        at.target_contact_id = r.contact_id_b;
";
        $dao = CRM_Core_DAO::executeQuery( $sql );
        while ( $dao->fetch( ) ) {
            $url = CRM_Utils_System::url( 'civicrm/profile/edit', "reset=1&gid=4&id={$dao->contact_id_b}&advisorID={$dao->advisor_id}&parentID={$parentID}&ptc=1" );
            $values[$dao->contact_id_b]['meeting']['title'] = "Your {$dao->subject} is scheduled for{$dao->activity_date_time} with {$dao->display_name}";
            $values[$dao->contact_id_b]['meeting']['edit'] = "<a href=\"{$url}\">Modify conference time</a>";
            unset( $childrenIDs[$dao->contact_id_b] );
        }

        // check if other children left to schedule a meeting
        if ( empty( $childrenIDs ) ) {
            return;
        }

        $childrenIDString = implode( ',', array_keys( $childrenIDs ) );

        $sql = "
SELECT     r.contact_id_b, a.subject, a.location, aac.display_name, aac.id as advisor_id
FROM       civicrm_activity a
INNER JOIN civicrm_activity_assignment aa ON a.id = aa.activity_id
INNER JOIN civicrm_contact            aac ON aa.assignee_contact_id = aac.id
INNER JOIN civicrm_relationship         r ON r.contact_id_a = aac.id
LEFT  JOIN civicrm_activity_target     at ON a.id = at.activity_id
WHERE      a.activity_type_id = 1
AND        r.relationship_type_id = 10
AND        r.contact_id_b IN ($childrenIDString)
AND        a.status_id = 1
AND        at.target_contact_id IS NULL
GROUP BY r.contact_id_b
";

        $dao = CRM_Core_DAO::executeQuery( $sql );
        while ( $dao->fetch( ) ) {
            $url = CRM_Utils_System::url( 'civicrm/profile/edit', "reset=1&gid=4&id={$dao->contact_id_b}&advisorID={$dao->advisor_id}&parentID={$parentID}&ptc=1" );
            $values[$dao->contact_id_b]['meeting'] = "<a href=\"{$url}\">Please schedule your {$dao->subject}<br/> with {$dao->display_name}</a>";
            $values[$dao->contact_id_b]['meeting']['edit'] = "<a href=\"{$url}\">Schedule the conference</a>";
        }
    
    }

  }



