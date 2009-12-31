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

require_once 'CRM/Core/Form.php';

class SFS_Form_Conference extends CRM_Core_Form {

    function preProcess( ) {
        parent::preProcess( );

    }

    function buildQuickForm( ) {

        // get all the potential advisors
        $sql = "
SELECT     DISTINCT(c.id), c.display_name
FROM       civicrm_contact c
INNER JOIN civicrm_relationship r ON r.contact_id_a = c.id
WHERE      r.relationship_type_id = 10
ORDER BY   c.display_name
";

        $advisors = array( '' => '- Select a Teacher -' );
        $dao = CRM_Core_DAO::executeQuery( $sql );
        while ( $dao->fetch( ) ) {
            $advisors[$dao->id] = $dao->display_name;
        }
        $this->add( 'select',
                    'advisor_id',
                    ts( 'Advisor' ),
                    $advisors,
                    true );

        $this->addDate('ptc_date',
                       ts( 'Conference Date' ),
                       true );
        $this->add( 'text',
                    'ptc_duration',
                    ts( 'Conference Duration' ),
                    true );

        for ( $i = 1; $i < 10; $i++ ) {
            $this->addDateTime("ptc_date_$i",
                               ts( 'Conference Start Time' ),
                               false );
        }

        $this->addButtons(array( 
                                array ( 'type'      => 'refresh', 
                                        'name'      => ts( 'Process' ),
                                        'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 
                                        'isDefault' => true   ), 
                                array ( 'type'      => 'cancel', 
                                        'name'      => ts('Cancel') ), 
                                 )
                          );

        $this->addFormRule( array( 'SFS_Form_Conference', 'formRule' ) );
    }

    static function formRule( &$fields ) 
    {  
        $errors = array( );

        if  ( !CRM_Utils_Array::value( 'ptc_date_1_time',$fields) ) {
            $errors['ptc_date_1_time'] = ts('Conference Start Time is a required field.');
        }
        
        return $errors;
    }

    function setDefaultValues( ) {
        $defaults = array( );

        list($defaults['ptc_date'], $defaults['ptc_date_time'])
             = CRM_Utils_Date::setDateDefaults(date("Y-m-d", time( ) + 14 * 24 * 60 * 60 ));
        $defaults['ptc_duration'] = 25;

        for ( $i = 1; $i < 6; $i++ ) {
            $time = (int ) ( $i + 1 ) / 2;
            $defaults["ptc_date_{$i}_time"] = "$time:00 PM";
            $i++;
            $defaults["ptc_date_{$i}_time"] = "$time:30 PM";
        }
        return $defaults;
    }


    function postProcess( ) {
        $params = $this->controller->exportValues( $this->_name );
        require_once 'SFS/Utils/Conference.php';
        
        $session =& CRM_Core_Session::singleton( );
        $userID = $session->get( 'userID' );

        for ( $i = 1 ; $i < 10; $i++ ) {
            if ( empty( $params["ptc_date_{$i}_time"] ) ) {
                continue;
            }
            $mysqlDate = CRM_Utils_Date::processDate( $params['ptc_date'], $params["ptc_date_{$i}_time"] );
            SFS_Utils_Conference::createConference( $userID,
                                                    $params['advisor_id'],
                                                    SFS_Utils_Conference::CONFERENCE_ACTIVITY_TYPE_ID,
                                                    $mysqlDate,
                                                    SFS_Utils_Conference::SUBJECT,
                                                    SFS_Utils_Conference::LOCATION,
                                                    SFS_Utils_Conference::STATUS,
                                                    $params['duration'] );
        }

    }

}