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

class sfschool_Utils_ExtendedCare {
    const
        TERM_POSITION = 0,
        SECTION_POSITION = 1,
        DAY_POSITION = 2,
        SESSION_POSITION = 3,
        NAME_POSITION = 4,
        DESC_POSITION = 5,
        INSTR_POSITION = 6,
        MAX_POSITION = 7,
        FEE_POSITION = 8,
        START_POSITION = 9,
        END_POSITION = 10,
        TERM = 'Fall 2009';

    static function buildForm( &$form,
                               $childID ) {
        
        // need to figure out if child is in elementary or middle school
        $activities = self::getSection( );

        $extendedCareElements = array( );
        foreach ( $activities as $day => $dayValues ) {
            foreach ( $dayValues as $session => $values ) {
                if ( ! empty( $values['select'] ) ) {
                    $select = array( '' => '- select -' ) + $values['select'];
                    $form->addElement( 'select',
                                       "sfschool_activity_{$day}_{$session}",
                                       "{$day} - {$session}",
                                       $select );
                    $extendedCareElements[] = "sfschool_activity_{$day}_{$session}";
                }
            }
        }
        
        $form->assign_by_ref( 'extendedCareElements',
                              $extendedCareElements );

        self::setDefaults( $form, $activities, $childID );
    }

    static function setDefaults( &$form,
                                 &$activities,
                                 $childID ) {
        if ( empty( $childID ) ||
             ! CRM_Utils_Rule::positiveInteger( $childID ) ) {
            return;
        }

        $sql = "
SELECT entity_id, term_4, day_of_week_10, session_11, name_3, description_9, instructor_5, fee_block_6, start_date_7, end_date_8
FROM   civicrm_value_extended_care_2
WHERE  entity_id = %1
";
        $params = array( 1 => array( $childID, 'Integer' ) );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );

        while ( $dao->fetch( ) ) {
            $id = preg_replace('/\s+|\W+/', '_', strtolower("{$dao->day_of_week_10}_{$dao->session_11}_{$dao->name_3}"));
            $defaults["sfschool_activity_{$dao->day_of_week_10}_{$dao->session_11}"] = $id;
        }

        $form->setDefaults( $defaults );
    }

    static function &getSection( $section  = 'Elementary',
                                 $term     = null,
                                 $fileName = null ) {
        
        static $_all = null;

        if ( $_all === null ) {
            $_all = array( );
        }

        if ( array_key_exists( $section, $_all ) ) {
            return $_all[$section];
        }

        $_all[$section] = array( );

        $term = self::getTerm( $term );

        if ( empty( $fileName ) ) {
            $fileName =
                dirname( __FILE__ ) . DIRECTORY_SEPARATOR .
                '..'                . DIRECTORY_SEPARATOR .
                'sql'               . DIRECTORY_SEPARATOR .
                'ExtendedCare.csv';
        }

        $fdRead  = fopen( $fileName, 'r' );
        if ( ! $fdRead ) {
            CRM_Core_Error::fatal( );
        }

        // ignore first line
        $fields = fgetcsv( $fdRead );

        $daysOfWeek =& self::daysOfWeek( );
        $sessions   =& self::sessions( );

        foreach ( $daysOfWeek as $day )  {
            $_all[$section][$day] = array( );
            foreach ( $sessions as $session ) {
                $_all[$section][$day][$session] = array( 'select'  => array( ),
                                                         'details' => array( ) );
            }
        }

        $errors = array( );
        while ( $fields = fgetcsv( $fdRead ) ) {
            if ( $fields[self::SECTION_POSITION] != $section ) {
                continue;
            }

            $currentTerm = $fields[self::TERM_POSITION];
            if ( $currentTerm != $term ) {
                continue;
            }

            if ( ! in_array( $fields[self::DAY_POSITION], $daysOfWeek ) ||
                 ! in_array( $fields[self::SESSION_POSITION], $sessions ) ) {
                $errors[] = implode( ',', $fields );
                continue;
            }

            $id = self::makeID( $fields );

            $title = $fields[self::NAME_POSITION];
            if ( ! empty( $fields[self::DESC_POSITION] ) ) {
                $title .= ' (' . $fields[self::DESC_POSITION] . ')';
            }
        
            if ( ! empty( $fields[self::INSTR_POSITION] ) ) {
                $title .= ' w/' . $fields[self::INSTR_POSITION];
            }

            if ( $fields[self::FEE_POSITION] > 1 ) {
                $title .= ' - ' . $fields[self::FEE_POSITION] . ' activity blocks';
            }
            
            $_all[$section][$fields[self::DAY_POSITION]][$fields[self::SESSION_POSITION]]['select'][$id] = $title;

            $_all[$section][$fields[self::DAY_POSITION]][$fields[self::SESSION_POSITION]]['details'][$fields[self::NAME_POSITION]] = 
                array( 'id'               => $id,
                       'title'            => $title,
                       'term'             => $fields[self::TERM_POSITION],
                       'section'          => $fields[self::SECTION_POSITION],
                       'day'              => $fields[self::DAY_POSITION],
                       'session'          => $fields[self::SESSION_POSITION],
                       'name'             => $fields[self::NAME_POSITION],
                       'description'      => $fields[self::DESC_POSITION],
                       'instructor'       => $fields[self::INSTR_POSITION],
                       'max participants' => $fields[self::MAX_POSITION],
                       'fee block'        => $fields[self::FEE_POSITION],
                       'start date'       => $fields[self::START_POSITION],
                       'end date'         => $fields[self::END_POSITION],
                       );
                   
        }

        return $_all[$section];
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

    static function makeID( &$fields ) {
        $id =
            $fields[self::DAY_POSITION]     . "_" .
            $fields[self::SESSION_POSITION] . "_" .
            $fields[self::NAME_POSITION];
        return preg_replace( '/\s+|\W+/', '_', strtolower( $id ) );
    }


    function postProcess( $class, $form, $gid ) {
        $childID   = $form->getVar( '_id' );

        if ( empty( $childID ) ||
             ! CRM_Utils_Rule::positiveInteger( $childID ) ) {
            return;
        }

        // first delete all the child classes for the current term
        $term = self::getTerm( );
        $sql = "
DELETE FROM civicrm_value_extended_care_2
WHERE  term_4 = %1
AND    entity_id = %2
";
        $params = array( 1 => array( $term   , 'String' ),
                         2 => array( $childID, 'Integer' ) );
        CRM_Core_DAO::executeQuery( $sql, $params );

        $params = $form->controller->exportValues( $form->getVar( '_name' ) );

        $daysOfWeek =& self::daysOfWeek( );
        $sessions   =& self::sessions( );

        $classSignedUpFor = array( );
        foreach ( $daysOfWeek as $day )  {
            foreach ( $sessions as $session ) {
                if ( ! empty( $params["sfschool_activity_{$day}_{$session}"] ) ) {
                    if ( ! array_key_exists( $day, $classSignedUpFor ) ) {
                        $classSignedUpFor[$day] = array( );
                    }
                    $classSignedUpFor[$day][$session] = $params["sfschool_activity_{$day}_{$session}"];
                }
            }
        }

        if ( empty( $classSignedUpFor ) ) {
            return;
        }


        CRM_Core_Error::debug( $classSignedUpFor );

        // they have signed up for a class, so now process it
        $activities =& self::getSection( );

        foreach ( $classSignedUpFor as $day => $dayValues ) {
            foreach( $dayValues as $session => $classID ) {
                foreach ( $activities[$day][$session]['details'] as $className => $classValues ) {
                    if ( $classValues['id'] == $classID ) {
                        self::postProcessClass( $childID, $classValues );
                    }
                }
            }
        }

    }

    static function postProcessClass( $childID,
                                      $classValues ) {
        $query = "
INSERT INTO civicrm_value_extended_care_2
( entity_id, term_4, name_3, description_9, instructor_5, day_of_week_10, session_11, fee_block_6, start_date_7 )
VALUES
( %1, %2, %3, %4, %5, %6, %7, %8, %9 )
";

        $params = array( 1 => array( $childID, 'Integer' ),
                         2 => array( $classValues['term'], 'String' ),
                         3 => array( $classValues['name'], 'String' ),
                         4 => array( CRM_Utils_Array::value( 'description', $classValues, '' ),
                                     'String' ),
                         5 => array( CRM_Utils_Array::value( 'instructor', $classValues, '' ),
                                     'String' ),
                         6 => array( $classValues['day'], 'String' ),
                         7 => array( $classValues['session'], 'String' ),
                         8 => array( $classValues['fee block'], 'Float' ),
                         9 => array( CRM_Utils_Date::getToday( null, 'Ymd' ), 'Date' ) );
        CRM_Core_DAO::executeQuery( $query, $params );
    }


    static function getValues( $childrenIDs, &$values, $term = null ) {
        if ( empty( $childrenIDs ) ) {
            return;
        }

        $single = false;
        if ( ! is_array( $childrenIDs ) ) {
            $childrenIDs = array( $childrenIDs => 1 );
            $single = true;
        }

        $childrenIDString = implode( ',', array_keys( $childrenIDs ) );
        $term = self::getTerm( $term );

        $query = "
SELECT entity_id, term_4, name_3, description_9,
       instructor_5, day_of_week_10, session_11, fee_block_6,
       start_date_7, end_date_8
FROM   civicrm_value_extended_care_2
WHERE  entity_id IN ($childrenIDString)
AND    term_4 = %1
ORDER BY entity_id
";
        $params = array( 1 => array( $term, 'String' ) );
        $dao = CRM_Core_DAO::executeQuery( $query, $params );
        while ( $dao->fetch( ) ) {
            if ( ! $values[$dao->entity_id]['extendedCare'] ) {
                $values[$dao->entity_id]['extendedCare'] = array( );
            }
            $session = $dao->session_11 == 'First' ? '' : "({$dao->session_11})";
            $day = "{$dao->day_of_week_10} $session: {$dao->name_3}";
            $values[$dao->entity_id]['extendedCare'][] =
                array( 'day'  => $day,
                       'name' => $dao->name_3,
                       'desc' => $dao->description_9,
                       'instructor' => $dao->instructor_5 );
        }

    }

  }




