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

class SFS_Utils_ExtendedCare {
    const
        TERM_POSITION = 0,
        MIN_GRADE_POSITION = 1,
        MAX_GRADE_POSITION = 2,
        DAY_POSITION = 3,
        SESSION_POSITION = 4,
        NAME_POSITION = 5,
        DESC_POSITION = 6,
        INSTR_POSITION = 7,
        MAX_POSITION = 8,
        FEE_POSITION = 9,
        START_POSITION = 10,
        END_POSITION = 11,
        TERM = 'Fall 2009',
        COORDINATOR_NAME  = 'Gilbert Bagaman',
        COORDINATOR_EMAIL = 'anybagaman@aol.com';

    static
        $_extendedCareElements = null,
        $_registeredElements   = null;

    static function buildForm( &$form,
                               $childID ) {
        
        $excare = CRM_Utils_Request::retrieve( 'excare', 'Integer', $form, false, null, $_REQUEST );
        if ( $excare != 1 ) {
            return;
        }

        require_once 'SFS/Utils/Query.php';
        $grade  = SFS_Utils_Query::getGrade( $childID );
        if ( ! is_numeric( $grade ) ) {
            return;
        }

        $parentID = CRM_Utils_Request::retrieve( 'parentID', 'Integer', $form, false, null, $_REQUEST );
        if ( $parentID ) {
            $sess =& CRM_Core_Session::singleton( );
            $url  =  CRM_Utils_System::url( 'civicrm/profile/view',
                                            "reset=1&gid=3&id=$parentID" );
            $form->removeElement( 'cancelURL' );
            $form->add( 'hidden', 'cancelURL', $url );
            $sess->pushUserContext( $url );
        }

        $classInfo = self::getClassCount( $grade );
        self::getCurrentClasses( $childID, $classInfo );

        $activities = self::getActivities( $grade, $classInfo );
        self::$_extendedCareElements = array( );
        self::$_registeredElements   = array( );

        foreach ( $activities as $day => $dayValues ) {
            foreach ( $dayValues as $session => $values ) {
                if ( ! empty( $values['select'] ) ) {
                    $time = $session == 'First' ? '3:30 pm - 4:30 pm' : "4:30 pm - 5:30 pm";
                    $select = array( '' => '- select -' ) + $values['select'];

                    $element =& $form->addElement( 'select',
                                                   "sfschool_activity_{$day}_{$session}",
                                                   "{$day} - {$time}",
                                                   $select );

                    self::$_extendedCareElements[] = "sfschool_activity_{$day}_{$session}";
                }
            }
        }
        
        $form->assign_by_ref( 'extendedCareElements',
                              self::$_extendedCareElements );

        self::setDefaults( $form, $activities, $childID );
    }

    static function setDefaults( &$form,
                                 &$activities,
                                 $childID ) {
        $sql = "
SELECT entity_id, term, day_of_week, session, name, description, instructor, fee_block, start_date, end_date
FROM   civicrm_value_extended_care_2
WHERE  entity_id = %1 AND has_cancelled = 0
";
        $params = array( 1 => array( $childID, 'Integer' ) );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );

        while ( $dao->fetch( ) ) {
            $id   = self::makeID( $dao, 'Custom' );
            $name = "sfschool_activity_{$dao->day_of_week}_{$dao->session}";
            $defaults[$name] = $id;
            $form->addElement( 'checkbox', "{$name}_cancel", ts( 'Cancel this activity?' ) );

            self::$_registeredElements[] = $name;
        }

        // also freeze these form element so folks cannot change them
        $form->freeze( self::$_registeredElements );
        $form->setDefaults( $defaults );
    }

    static function &getActivities( $grade, &$classInfo ) {
        static $_all = array( );

        if ( empty( $grade ) ) {
            $grade = 'ALL';
        }

        if ( array_key_exists( $grade, $_all ) ) {
            return $_all[$grade];
        }

        $_all[$grade] = array( );

        $term       =  self::getTerm( $term );

        $sql = "
SELECT * 
FROM   sfschool_extended_care_source
WHERE  term  = %1
AND    is_active = 1
";
        $params = array( 1 => array( $term , 'String'  ) );

        if ( is_numeric( $grade ) ) {
            $sql .= "
AND    %2 >= min_grade
AND    %2 <= max_grade
";
            $params[2] = array( $grade, 'Integer' );
        }

        $daysOfWeek =& self::daysOfWeek( );
        $sessions   =& self::sessions( );

        foreach ( $daysOfWeek as $day )  {
            $_all[$grade][$day] = array( );
            foreach ( $sessions as $session ) {
                $_all[$grade][$day][$session] = array( 'select'  => array( ),
                                                       'details' => array( ) );
            }
        }

        $errors = array( );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );
        while ( $dao->fetch( ) ) {
            $id = self::makeID( $dao, 'Source' );

            if ( $classInfo &&
                 array_key_exists( $id, $classInfo ) ) {
                // check if the person is not enrolled and the class is full
                if ( ! $classInfo[$id]['enrolled'] &&
                     $classInfo[$id]['current'] >= $classInfo[$id]['max'] ) {
                    continue;
                }
            }

            $title = $dao->name;
        
            if ( ! empty( $dao->instructor ) ) {
                $title .= " w/{$dao->instructor}";
            }

            if ( $dao->fee > 1 ) {
                $title .= " - {$dao->fee} activity blocks";
            }

            if ( strstr($dao->url, 'http:') || strstr($dao->url, 'https:') ) {
                $url = $dao->url;
            } else {
                $urlParts = explode(';;', $dao->url);
                $url      = CRM_Utils_System::url( $urlParts[0], $urlParts[1] );
            }

            $_all[$grade][$dao->day_of_week][$dao->session]['select'][$id]  = $title;
            $_all[$grade][$dao->day_of_week][$dao->session]['details'][$id] =
                array( 'id'               => $id,
                       'title'            => $title,
                       'name'             => $dao->name,
                       'term'             => $dao->term,
                       'day'              => $dao->day_of_week,
                       'session'          => $dao->session,
                       'name'             => $dao->name,
                       'instructor'       => $dao->instructor,
                       'max_participants' => $dao->max_participants,
                       'fee_block'        => $dao->fee_block,
                       'start_date'       => $dao->start_date,
                       'end_date'         => $dao->end_date,
                       'min_grade'        => $dao->min_grade,
                       'max_grade'        => $dao->max_grade,
                       'url'              => $url,
                       'location'         => $dao->location
                       );
                   
        }

        return $_all[$grade];
    }

    static function &daysOfWeek( ) {
        static $_daysOfWeek    = null;
        if ( ! $_daysOfWeek ) {
            $_daysOfWeek = array( 'Monday', 'Tuesday',
                                  'Wednesday', 'Thursday',
                                  'Friday' );
        }
        return $_daysOfWeek;
    }

    static function getTerm( $term = null ) {
        static $_term = null;
        if ( $term !== null ) {
            $_term = $term;
        }

        if ( $_term === null ) {
            $_term = defined( 'SFSCHOOL_TERM' ) ? SFSCHOOL_TERM : self::TERM;
        }
        return $_term;
    }

    static function &sessions( ) {
        static $_sessions = null;
        if ( $_sessions === null ) {
            $_sessions = array( 'First', 'Second' );
        }
        return $_sessions;
    }

    static function makeID( &$dao, $class = 'Source' ) {
        $id = $class == 'Source'
            ? "{$dao->day_of_week}_{$dao->session}_{$dao->name}" 
            : "{$dao->day_of_week}_{$dao->session}_{$dao->name}";

        return preg_replace( '/\s+|\W+/', '_',
                             $id );
    }


    function postProcess( $class, &$form, $gid ) {
        $excare = CRM_Utils_Request::retrieve( 'excare', 'Integer', $form, false, null, $_REQUEST );
        if ( $excare != 1 ) {
            return;
        }
        
        $childID   = $form->getVar( '_id' );

        if ( empty( $childID ) ||
             ! CRM_Utils_Rule::positiveInteger( $childID ) ) {
            return;
        }
 
       $params = $form->controller->exportValues( $form->getVar( '_name' ) );

        $daysOfWeek =& self::daysOfWeek( );
        $sessions   =& self::sessions( );

        $classSignedUpFor = array( );
        $classCancelled   = array( );

        foreach ( $daysOfWeek as $day )  {
            foreach ( $sessions as $session ) {
                $name = "sfschool_activity_{$day}_{$session}";
                if ( ! empty( $params["{$name}_cancel"] ) ) {
                    if ( ! array_key_exists( $day, $classCancelled ) ) {
                        $classCancelled[$day] = array( );
                    }
                    $classCancelled[$day][$session] = $params[$name];
                    continue;
                }
                if ( ! in_array( $name, self::$_registeredElements ) &&
                     ! empty( $params[$name] ) ) {
                    if ( ! array_key_exists( $day, $classSignedUpFor ) ) {
                        $classSignedUpFor[$day] = array( );
                    }
                    $classSignedUpFor[$day][$session] = $params[$name];
                }
            }
        }

        if ( empty( $classSignedUpFor ) && empty( $classCancelled ) ) {
            return;
        }

        require_once 'SFS/Utils/Query.php';
        $grade  = SFS_Utils_Query::getGrade( $childID );
        if ( ! is_numeric( $grade ) ) {
            return;
        }

        $classInfo = self::getClassCount( $grade );
        self::getCurrentClasses( $childID, $classInfo );

        $activities = self::getActivities( $grade, $classInfo );

        $templateVars = array( 'term'             => self::getTerm( ),
                               'classCancelled'   => array( ),
                               'classSignedUpFor' => array( ) );

        // first deal with all cancelled classes
        if ( ! empty( $classCancelled ) ) {
            foreach ( $classCancelled as $day => $dayValues ) {
                foreach( $dayValues as $session => $classID ) {
                    if ( array_key_exists( $classID, $activities[$day][$session]['details'] ) ) {
                        self::postProcessClass( $childID,
                                                $activities[$day][$session]['details'][$classID],
                                                'Cancelled' );
                        $templateVars['classCancelled'][$classID] = $activities[$day][$session]['details'][$classID];
                    } else {
                        CRM_Core_Error::fatal( $classID );
                    }
                }
            }
        }

        if ( ! empty( $classSignedUpFor ) ) {
            foreach ( $classSignedUpFor as $day => $dayValues ) {
                foreach( $dayValues as $session => $classID ) {
                    if ( array_key_exists( $classID, $activities[$day][$session]['details'] ) ) {
                        self::postProcessClass( $childID,
                                                $activities[$day][$session]['details'][$classID],
                                                'Added' );
                        $templateVars['classSignedUpFor'][$classID] = $activities[$day][$session]['details'][$classID];
                    } else {
                        CRM_Core_Error::fatal( $classID );
                    }
                }
            }
        }

        self::sendExtendedCareEmail( $childID, $templateVars );
    }

    static function sendExtendedCareEmail( $childID, $templateVars = array( ) ) {
        if ( ! array_key_exists( 'classSignedUpFor', $templateVars ) ) {
            $templateVars['classSignedUpFor'] = $templateVars['classCancelled'] = array( );
        }

        // get all the class enrolled by the child
        $values = array( );
        self::getValues( $childID, $values );
        $templateVars['classEnrolled'] = CRM_Utils_Array::value( 'extendedCare', $values[$childID] );

        $templateVars['extendedCareCoordinatorName' ] = self::COORDINATOR_NAME ;
        $templateVars['extendedCareCoordinatorEmail'] = self::COORDINATOR_EMAIL;

        // now send a message to the parents about what they did
        require_once 'SFS/Utils/Mail.php';
        SFS_Utils_Mail::sendMailToParents( $childID,
                                           'SFS/Mail/ExtendedCare/Subject.tpl',
                                           'SFS/Mail/ExtendedCare/Message.tpl',
                                           $templateVars );
    }

    static function postProcessClass( $childID,
                                      $classValues,
                                      $operation = 'Added' ) {

        $startDate = CRM_Utils_Date::isoToMysql( $classValues['start_date'] );
        $rightNow  = CRM_Utils_Date::getToday( null, 'YmdHis' );

        if ( $operation == 'Added' ) {
            $query = "
INSERT INTO civicrm_value_extended_care_2
( entity_id, term, name, description, instructor, day_of_week, session, fee_block, start_date, end_date, has_cancelled )
VALUES
( %1, %2, %3, %4, %5, %6, %7, %8, %9, %10, 0 )
";
        
            $useStart = ( $startDate > $rightNow ) ? $startDate : $rightNow;
            $params = array( 1  => array( $childID, 'Integer' ),
                             2  => array( $classValues['term'], 'String' ),
                             3  => array( $classValues['name'], 'String' ),
                             4  => array( CRM_Utils_Array::value( 'description', $classValues, '' ),
                                         'String' ),
                             5  => array( CRM_Utils_Array::value( 'instructor', $classValues, '' ),
                                          'String' ),
                             6  => array( $classValues['day'], 'String' ),
                             7  => array( $classValues['session'], 'String' ),
                             8  => array( $classValues['fee_block'], 'Float' ),
                             9  => array( $useStart, 'Timestamp' ),
                             10 => array( CRM_Utils_Date::isoToMysql( $classValues['end_date'] ),
                                          'Timestamp' ) );
        } else if ( $operation == 'Cancelled' ) {
            // check if the class has already started, if so cancel it
            // else delete it

            if ( $startDate > $rightNow ) {
                $query = "
DELETE 
FROM   civicrm_value_extended_care_2
WHERE  entity_id   = %1
AND    term        = %2
AND    name        = %3
AND    day_of_week = %4
AND    session     = %5
";
            } else {
                $query = "
UPDATE civicrm_value_extended_care_2
SET    end_date = %6, has_cancelled = 1
WHERE  entity_id     = %1
AND    term          = %2
AND    name          = %3
AND    day_of_week   = %4
AND    session       = %5
AND    has_cancelled = 0
";
            }
            $params = array( 1  => array( $childID, 'Integer' ),
                             2  => array( $classValues['term'], 'String' ),
                             3  => array( $classValues['name'], 'String' ),
                             4  => array( $classValues['day'], 'String' ),
                             5  => array( $classValues['session'], 'String' ),
                             6  => array( CRM_Utils_Date::getToday( null, 'YmdHis' ), 'Timestamp' ) );
        } else {
            CRM_Core_Error::fatal( );
        }
        CRM_Core_DAO::executeQuery( $query, $params );
    }

    static function getValues( $childrenIDs, &$values, $parentID = null, $term = null ) {
        if ( empty( $childrenIDs ) ) {
            return;
        }

        $single = false;
        if ( ! is_array( $childrenIDs ) ) {
            $childrenIDs = array( $childrenIDs );
            $single = true;
        }

        $childrenIDString = implode( ',', array_values( $childrenIDs ) );
        $term = self::getTerm( $term );

        $query = "
SELECT    c.id as contact_id, e.term, e.name, e.description,
          e.instructor, e.day_of_week, e.session, e.fee_block,
          e.start_date, e.end_date, s.grade
FROM      civicrm_contact c
LEFT JOIN civicrm_value_extended_care_2 e ON ( c.id = e.entity_id AND term = %1 AND has_cancelled = 0 )
LEFT JOIN civicrm_value_school_information_1 s ON c.id = s.entity_id
WHERE     c.id IN ($childrenIDString)
AND       s.subtype = %2
ORDER BY  c.id, e.day_of_week, e.session
";
        $params = array( 1 => array( $term    , 'String' ),
                         2 => array( 'Student', 'String' ) );
        $dao = CRM_Core_DAO::executeQuery( $query, $params );

        while ( $dao->fetch( ) ) {
            if ( ! is_numeric( $dao->grade ) ) {
                continue;
            }

            // check if there is any data for extended care
            if ( $dao->name ) {
                if ( ! $values[$dao->contact_id]['extendedCareDay'] ) {
                    $values[$dao->contact_id]['extendedCare']    = array( );
                    $values[$dao->contact_id]['extendedCareDay'] = array( );
                }

                if ( ! isset( $values[$dao->contact_id]['extendedCareDay'][$dao->day_of_week] ) ) {
                    $values[$dao->contact_id]['extendedCareDay'][$dao->day_of_week] = array( );
                }
            
                $time = $dao->session == 'First' ? '3:30 pm - 4:30 pm' : "4:30 pm - 5:30 pm";
                $title = "{$dao->day_of_week} $time";
                $title .= " : {$dao->name}";
                if ( $dao->instructor ) {
                    $title .= " w/{$dao->instructor}";
                }

                $values[$dao->contact_id]['extendedCareDay'][$dao->day_of_week][] =
                    array( 'day'  => $dao->day_of_week,
                           'time' => $time,
                           'name' => $dao->name,
                           'desc' => $dao->description,
                           'instructor' => $dao->instructor,
                           'title' => $title );
            }
        }

        $daysOfWeek =& self::daysOfWeek( );
        foreach ( $values as $contactID => $value ) {
            foreach ( $daysOfWeek as $day )  {
                if ( ! empty( $values[$contactID]['extendedCareDay'][$day] ) ) {
                    $values[$contactID]['extendedCare'] = 
                        array_merge( $values[$contactID]['extendedCare'],
                                     $values[$contactID]['extendedCareDay'][$day] );
                }
            }
            unset( $values[$contactID]['extendedCareDay'] );
            if ( is_numeric( $values[$contactID]['grade'] ) ) {
                $parent = null;
                if ( $parentID ) {
                    $parent = "&parentID=$parentID";
                }
                $values[$contactID]['extendedCareEdit'] =
                    CRM_Utils_System::url( 'civicrm/profile/edit', "reset=1&gid=4&id={$contactID}&excare=1&$parent" );
            }
        }

    }

    static function &getClassCount( $grade ) {
        $sql = "
SELECT     count(entity_id) as current, s.max_participants as max, s.term, s.day_of_week, s.session, s.name
FROM       civicrm_value_extended_care_2 e
INNER JOIN sfschool_extended_care_source s ON ( s.term = e.term AND s.day_of_week = e.day_of_week AND s.session = e.session AND s.name = e.name ) 
WHERE      e.has_cancelled = 0
AND        %1 >= s.min_grade
AND        %1 <= s.max_grade
AND        s.is_active = 1
GROUP BY term, day_of_week, session, name
";
        $params = array( 1 => array( $grade, 'Integer' ) );

        $values = array( );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );
        while ( $dao->fetch( ) ) {
            $id = self::makeID( $dao, 'Custom' ); 
           $values[$id] = array( 'current'   => $dao->current,
                                  'max'      => $dao->max,
                                  'enrolled' => 0 );
        }

        return $values;
    }

    static function getCurrentClasses( $childID, &$values ) {
        $sql = "
SELECT entity_id, term, day_of_week, session, name
FROM   civicrm_value_extended_care_2
WHERE  entity_id = %1 AND has_cancelled = 0
";
        $params = array( 1 => array( $childID, 'Integer' ) );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );

        while ( $dao->fetch( ) ) {
            $id = self::makeID( $dao, 'Custom' );
            
            if ( ! array_key_exists( $id, $values ) ) {
                CRM_Core_Error::fatal( $id );
            }
            $values[$id]['enrolled'] = 1;
        }
    }

    function sortDetails( &$details ) {
        foreach ( $details as $childID => $detail ) {
            self::sortDetail( $details, $childID );
        }
    }

    function sortDetail( &$details, $childID ) {
        $yesDetail = $noDetail = array( );

        $daysOfWeek =& self::daysOfWeek( );
        $sessions   =& self::sessions( );

        foreach ( $daysOfWeek as $day ) {
            $yesDetail[$day] = array( );
            $noDetail[$day]  = array( );
            foreach ( $sessions as $session ) {
                $yesDetail[$day][$session] = array( );
                $noDetail[$day][$session]  = array( );
            }
        }


        foreach ( $details[$childID] as $id => &$values ) {
            $day     = $values['fields'][10]['field_value'];
            $session = $values['fields'][11]['field_value'];
            $yesno   = trim( $values['fields'][13]['field_value'] );

            if ( $yesno == 'Yes' ) {
                $yesDetail[$day][$session][] = $values;
            } else {
                $noDetail[$day][$session][] = $values;
            }
        }

        $newDetail = array( );

        foreach ( $noDetail as $day => $detailValues ) {
            foreach ( $detailValues as $session =>& $values ) {
                foreach ( $values as $value ) {
                    $newDetail[] = $value;
                }
            }
        }

        foreach ( $yesDetail as $day => $detailValues ) {
            foreach ( $detailValues as $session =>& $values ) {
                foreach ( $values as $value ) {
                    $newDetail[] = $value;
                }
            }
        }

        $details[$childID] = $newDetail;
    }


}
