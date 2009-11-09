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

class SFS_Form_ExtendedCareSummary extends CRM_Core_Form {
    protected $_startDate;

    protected $_endDate;
    
    protected $_includeMorning;

    protected $_showDetails;

    function preProcess( ) {
        parent::preProcess( );

        $this->_startDate      = CRM_Utils_Request::retrieve( 'startDate', 'String' , $this, false,
                                                         date( 'Y-m-d', time( ) - 7 * 24 * 60 * 60 ) );
        $this->_endDate        = CRM_Utils_Request::retrieve( 'endDate'  , 'String' , $this, false,
                                                         date( 'Y-m-d' ) );
        $this->_includeMorning = CRM_Utils_Request::retrieve( 'includeMorning'  , 'Integer', $this, false, 1 );
        $this->_showDetails    = CRM_Utils_Request::retrieve( 'showDetails'  , 'Integer', $this, false, 1 );
    }

    function buildQuickForm( ) {
        $this->add( 'date', 'start_date', ts('Start Date'), CRM_Core_SelectValues::date( 'custom', 10, 2 ) );
        $this->add( 'date', 'end_date'  , ts('End Date'  ), CRM_Core_SelectValues::date( 'custom', 10, 2 ) );

        $this->add('checkbox', 'include_morning', ts( 'Include Morning Blocks?' ) );
        $this->add('checkbox', 'show_details'   , ts( 'Show Detailed breakdown for each student?' ) );

        require_once 'SFS/Utils/Query.php';
        $students = array( '' => '- Select Student -' ) + SFS_Utils_Query::getStudentsByGrade( true, false );
        
        $this->add( 'select',
                    "student_id",
                    ts( 'Student' ),
                    $students );

        $this->addButtons(array( 
                                array ( 'type'      => 'submit', 
                                        'name'      => ts( 'Process' ),
                                        'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 
                                        'isDefault' => true   ), 
                                array ( 'type'      => 'cancel', 
                                        'name'      => ts('Cancel') ), 
                                 )
                          );
    }

    function setDefaultValues( ) {
        $defaults = array( 'start_date'     => $this->_startDate,
                           'end_date'       => $this->_endDate,
                           'include_morning' => $this->_includeMorning,
                           'show_details'    => $this->_showDetails );
        return $defaults;
    }

    function postProcess( ) {
        $params = $this->controller->exportValues( $this->_name );

        require_once 'SFS/Utils/ExtendedCare.php';
        $summary =& SFS_Utils_ExtendedCare::signoutDetails( CRM_Utils_Date::format( $params['start_date'] ),
                                                            CRM_Utils_Date::format( $params['end_date'  ] ),
                                                            CRM_Utils_Array::value( 'include_morning', $params, false ),
                                                            CRM_Utils_Array::value( 'show_details'   , $params, false ),
                                                            $params['student_id'] );
        
        $this->assign( 'summary'    , $summary );
        $this->assign( 'showDetails', CRM_Utils_Array::value( 'show_details', $params ) );
    }

}