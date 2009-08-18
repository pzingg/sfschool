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
    const
        ADVISOR_RELATIONSHIP_TYPE_ID = 10,
        CONFERENCE_ACTIVITY_TYPE_ID  = 20;

    static function buildForm( &$form, $gid ) {
        $advisorID = CRM_Utils_Request::retrieve( 'advisorID', 'Integer', $form, false, null, $_REQUEST );
        $ptc       = CRM_Utils_Request::retrieve( 'ptc'      , 'Integer', $form, false, null, $_REQUEST );

        if ( empty( $advisorID ) || $ptc != 1 ) {
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
WHERE      a.activity_type_id = %4
AND        r.relationship_type_id = %3
AND        r.is_active = 1
AND        r.contact_id_b = %2
AND        a.status_id = 1
AND        a.activity_date_time > NOW()
AND        ( at.target_contact_id IS NULL OR at.target_contact_id = %2 )
ORDER BY   a.activity_date_time asc
";

        $childID = $form->getVar( '_id' );
        $params  = array( 2 => array( $childID   , 'Integer' ),
                          3 => array( self::ADVISOR_RELATIONSHIP_TYPE_ID, 'Integer' ),
                          4 => array( self::CONFERENCE_ACTIVITY_TYPE_ID , 'Integer' ) );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );
        $elements = array( );
        while ( $dao->fetch( ) ) {
            $elements[$dao->activity_id] = "{$dao->activity_date_time} w/{$dao->display_name}";
        }

        $parentID = CRM_Utils_Request::retrieve( 'parentID', 'Integer', $form, false, null, $_REQUEST );

        if ( ! empty( $elements ) ) {
            $form->addElement( 'select', 'sfschool_activity_id', "Choose a Meeting time for {$dao->subject}", $elements, true );

            // get the default values
            $values = array( );
            self::getValues( $childID, $values, true, $parentID );
            if ( isset( $values[$childID] ) ) {
                $defaults = array( 'sfschool_activity_id' => $values[$childID]['meeting']['id'] );
                $form->setDefaults( $defaults );
            }
        }
    }


    static function postProcess( $class, &$form, $gid ) {
        $advisorID = CRM_Utils_Request::retrieve( 'advisorID', 'Integer', $form, false, null, $_REQUEST );
        $ptc       = CRM_Utils_Request::retrieve( 'ptc'      , 'Integer', $form, false, null, $_REQUEST );

        if ( empty( $advisorID ) || $ptc != 1 ) {
            return;
        }

        $params = $form->controller->exportValues( $form->getVar( '_name' ) );

        $activityID = CRM_Utils_Array::value( 'sfschool_activity_id', $params );
        $childID    = $form->getVar( '_id' );

        if ( empty( $activityID ) || empty( $childID ) ) {
            return;
        }

        // first we need to delete all the existing meetings for this childID
        self::deleteAll( $childID );

        // insert these two into civicrm_target
        // we actually need to lock this and then ensure the space is available
        // lets do that at a later stage
        $sql = "
REPLACE INTO civicrm_activity_target (activity_id, target_contact_id)
VALUES
( %1, %2 )
";
        $params = array( 1 => array( $activityID, 'Integer' ),
                         2 => array( $childID   , 'Integer' ) );
        CRM_Core_DAO::executeQuery( $sql, $params );

        $parentID = CRM_Utils_Request::retrieve( 'parentID', 'Integer', $form, false, null, $_REQUEST );
        if ( $parentID ) {
            $sess =& CRM_Core_Session::singleton( );
            $sess->pushUserContext( CRM_Utils_System::url( 'civicrm/profile/view',
                                                              "reset=1&gid=3&id=$parentID" ) );
        }
    }

    function &getValues( $childrenIDs,
                         &$values,
                         $onlyScheduled = false,
                         $parentID = null ) {
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

        // find first all scheduled meetings in the future
        $sql = "
SELECT     a.id, a.activity_date_time, a.subject, a.location, r.contact_id_b,
           aac.id as advisor_id, aac.display_name as aac_display_name,
           rcb.display_name as rcb_display_name
FROM       civicrm_activity a
INNER JOIN civicrm_activity_assignment aa ON a.id = aa.activity_id
INNER JOIN civicrm_activity_target     at ON a.id = at.activity_id
INNER JOIN civicrm_contact            aac ON aa.assignee_contact_id = aac.id
INNER JOIN civicrm_relationship         r ON r.contact_id_a         = aac.id
INNER JOIN civicrm_contact            rcb ON r.contact_id_b         = rcb.id
WHERE      a.activity_type_id = %2
AND        a.status_id = 1
AND        a.activity_date_time > NOW()
AND        r.relationship_type_id = %1
AND        r.is_active = 1
AND        r.contact_id_b IN ( $childrenIDString )
AND        aa.assignee_contact_id = r.contact_id_a
AND        at.target_contact_id = r.contact_id_b;
";

        $parent = null;
        if ( $parentID ) {
            $parent = "parentID=$parentID";
        }
        $params = array( 1 => array( self::ADVISOR_RELATIONSHIP_TYPE_ID, 'Integer' ),
                         2 => array( self::CONFERENCE_ACTIVITY_TYPE_ID , 'Integer' ) );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );
        while ( $dao->fetch( ) ) {
            $url = CRM_Utils_System::url( 'civicrm/profile/edit', "reset=1&gid=4&id={$dao->contact_id_b}&advisorID={$dao->advisor_id}&ptc=1&$parent" );
            $values[$dao->contact_id_b]['meeting']['title'] = "Your {$dao->subject} is scheduled for {$dao->activity_date_time} with {$dao->aac_display_name}";
            $values[$dao->contact_id_b]['meeting']['edit']  = "<a href=\"{$url}\">Modify conference time for {$dao->rcb_display_name}</a>";
            $values[$dao->contact_id_b]['meeting']['id']    = $dao->id;
            // FIXME when we have access to the web :)
            $newChildrenIDs = array( );
            foreach ( $childrenIDs as $childID ) {
                if ( $dao->contact_id_b != $childID ) {
                    $newChildrenIDs[] = $childID;
                }
            }
            $childrenIDs = $newChildrenIDs;
        }

        // check if other children left to schedule a meeting
        if ( $onlyScheduled ||
             empty( $childrenIDs ) ) {
            return;
        }

        $childrenIDString = implode( ',', array_values( $childrenIDs ) );

        $sql = "
SELECT     r.contact_id_b, a.subject, a.location,
           aac.display_name as aac_display_name, aac.id as advisor_id,
           rcb.display_name as rcb_display_name
FROM       civicrm_activity a
INNER JOIN civicrm_activity_assignment aa ON a.id = aa.activity_id
INNER JOIN civicrm_contact            aac ON aa.assignee_contact_id = aac.id
INNER JOIN civicrm_relationship         r ON r.contact_id_a = aac.id
LEFT  JOIN civicrm_activity_target     at ON a.id = at.activity_id
INNER JOIN civicrm_contact            rcb ON r.contact_id_b = rcb.id
WHERE      a.activity_type_id = %2
AND        r.relationship_type_id = %1
AND        r.is_active = 1
AND        r.contact_id_b IN ($childrenIDString)
AND        a.status_id = 1
AND        a.activity_date_time > NOW()
AND        at.target_contact_id IS NULL
GROUP BY r.contact_id_b
";

        $params = array( 1 => array( self::ADVISOR_RELATIONSHIP_TYPE_ID, 'Integer' ),
                         2 => array( self::CONFERENCE_ACTIVITY_TYPE_ID , 'Integer' ) );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );
        while ( $dao->fetch( ) ) {
            $url = CRM_Utils_System::url( 'civicrm/profile/edit', "reset=1&gid=4&id={$dao->contact_id_b}&advisorID={$dao->advisor_id}&ptc=1&$parent" );
            $values[$dao->contact_id_b]['meeting']['title'] = "Please schedule your {$dao->subject} with {$dao->aac_display_name}";
            $values[$dao->contact_id_b]['meeting']['edit'] = "<a href=\"{$url}\">Schedule a conference for {$dao->rcb_display_name}</a>";
        }
    }

    static function createConferenceSchedule( $statusID, $subject, $location ) {
        require_once 'CRM/Utils/Request.php';
    
        // we need the admin id, teacher id, date, start time and end time
        $adminID   = CRM_Utils_Request::retrieve( 'adminID',
                                                  'Integer', 
                                                  CRM_Core_DAO::$_nullObject,
                                                  true );
        $teacherID = CRM_Utils_Request::retrieve( 'teacherID',
                                                  'Integer', 
                                                  CRM_Core_DAO::$_nullObject,
                                                  true );
        $date      = CRM_Utils_Request::retrieve( 'date',
                                                  'Date',
                                                  CRM_Core_DAO::$_nullObject,
                                                  true );
        $start     = CRM_Utils_Request::retrieve( 'start',
                                                  'Integer',
                                                  CRM_Core_DAO::$_nullObject,
                                                  true );
        $end       = CRM_Utils_Request::retrieve( 'end',
                                                  'Integer',
                                                  CRM_Core_DAO::$_nullObject,
                                                  true );

        // perform validation on the parameters
    
        require_once 'CRM/Activity/DAO/Activity.php';
        require_once 'CRM/Activity/DAO/ActivityAssignment.php';

        for ( $time = $start; $time < $end; $time++ ) {
            // skip lunch hour
            if ( $time == 12 ) {
                continue;
            }

            if ( $time < 10 ) {
                $time = "0{$time}";

            }
            self::createConference( $adminID, $teacherID,
                                    self::CONFERENCE_ACTIVITY_TYPE_ID,
                                    "{$date}{$time}0000",
                                    $subject, $location, $statusID );

            self::createConference( $adminID, $teacherID,
                                    self::CONFERENCE_ACTIVITY_TYPE_ID,
                                    "{$date}{$time}3000",
                                    $subject, $location, $statusID );

        }

    }

    static function createConference( $adminID,
                                      $teacherID,
                                      $activityTypeID,
                                      $activityDateTime,
                                      $subject,
                                      $location,
                                      $statusID ) {

        $activity = new CRM_Activity_DAO_Activity( );

        $activity->source_contact_id  = $adminID;
        $activity->activity_type_id   = $activityTypeID;
        $activity->activity_date_time = $activityDateTime;
        $activity->status_id          = $statusID;
        $activity->subject            = $subject;
        $activity->duration           = 30;
        $activity->location           = $location;
        $activity->save( );

        $assignment = new CRM_Activity_DAO_ActivityAssignment( );
        $assignment->activity_id = $activity->id;
        $assignment->assignee_contact_id = $teacherID;
        $assignment->save( );
    }

    static function deleteAll( $childID ) {
        $sql = "
DELETE     at.*
FROM       civicrm_activity a
INNER JOIN civicrm_activity_assignment aa ON a.id = aa.activity_id
INNER JOIN civicrm_contact            aac ON aa.assignee_contact_id = aac.id
INNER JOIN civicrm_relationship         r ON r.contact_id_a = aac.id
INNER JOIN civicrm_activity_target     at ON a.id = at.activity_id
WHERE      a.activity_type_id = %4
AND        r.relationship_type_id = %3
AND        r.is_active = 1
AND        r.contact_id_b = %1
AND        a.status_id = 1
AND        a.activity_date_time > NOW()
AND        at.target_contact_id = %1
";

        $params  = array( 1 => array( $childID , 'Integer' ),
                          3 => array( self::ADVISOR_RELATIONSHIP_TYPE_ID, 'Integer' ),
                          4 => array( self::CONFERENCE_ACTIVITY_TYPE_ID , 'Integer' ) );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );
    }
    
}
