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

require_once 'CRM/Report/Form.php';

class SFS_Report_Form_Custom_Attendance extends CRM_Report_Form {
    const 
        EXTRA_ROWS_MIN = 0,
        EXTRA_ROWS_MAX = 10,
        EXTRA_ROWS_DEFAULT = 5;
    
    // set custom table name
    protected $_customTable   = 'civicrm_value_extended_care_2';  
    
    // col mapper
    protected $_colMapper = array ( 'dayOfWeek'   => 'day_of_week_10',
                                    'sessionName' => 'name_3',
                                    'isCancelled' => 'has_cancelled_12',
                                    );
    function __construct( ) {
        $this->_columns = array( );
        
        $query  = "
SELECT column_name, label , option_group_id
FROM civicrm_custom_field
WHERE is_active = 1 AND column_name = '{$this->_colMapper['dayOfWeek']}'";
        $dao_column = CRM_Core_DAO::executeQuery( $query );
        $this->_optionFields = array( );
        while ( $dao_column->fetch( ) ) {
            if( $dao_column->option_group_id ) {
                $query   = "SELECT label , value FROM civicrm_option_value 
WHERE option_group_id = {$dao_column->option_group_id} AND is_active=1";
                $dao     = CRM_Core_DAO::executeQuery( $query );
                while( $dao->fetch() ) {
                    $this->_optionFields[$dao_column->column_name][$dao->value] = $dao->label;
                }
            }
        }

        $query   = "
SELECT distinct {$this->_colMapper['sessionName']} as session_name 
FROM   civicrm_value_extended_care_2 value_extended_care_2_civireport";
        $dao      = CRM_Core_DAO::executeQuery( $query );
        $sOptions = array( );
        while( $dao->fetch( ) ) {
            $sOptions[$dao->session_name] = $dao->session_name;
        }

        $eOptions = array( );
        for ( $i = self::EXTRA_ROWS_MIN; $i <= self::EXTRA_ROWS_MAX; $i++ ) {
            $eOptions[$i] = $i;
        }
        $this->_columns[$this->_customTable] = 
            array( 'dao'     => 'CRM_Contact_DAO_Contact',
                   'filters' =>             
                   array( 
                         'weekday'       => 
                         array( 'title'   => ts( 'Day Of Week' ),
                                'operatorType' => CRM_Report_Form::OP_SELECT,
                                'options'      => $this->_optionFields[$this->_colMapper['dayOfWeek']] ),
                         'extra_rows'    => 
                         array( 'title'   => ts( 'Extra Rows' ),
                                'default' => self::EXTRA_ROWS_DEFAULT,
                                'type'         => CRM_Utils_Type::T_INT,
                                'operatorType' => CRM_Report_Form::OP_SELECT,
                                'options'      => $eOptions ),
                          ),
                   );
        parent::__construct( );
    }

    function preProcess( ) {
        parent::preProcess( );
        if ( !$this->_id ) {
            $this->assign('reportTitle', "EXTENDED CARE FOR " . strtoupper($_POST['weekday_value']));
        }
    }
    
    function postProcess( ) {
        $this->beginPostProcess( );

        $sql  = "
SELECT distinct {$this->_colMapper['sessionName']} as session_name 
FROM   civicrm_value_extended_care_2 value_extended_care_2_civireport";
        $sname = CRM_Core_DAO::executeQuery( $sql );
        $rows  = array( ); 

        while( $sname->fetch( ) ) {
            $sql  = "
SELECT contact_civireport.id as contact_civireport_id, 
       contact_civireport.display_name as contact_civireport_display_name, '' as SignIn, '' as SignOut 
FROM   civicrm_value_extended_care_2 value_extended_care_2_civireport
INNER  JOIN civicrm_contact as contact_civireport ON value_extended_care_2_civireport.entity_id = contact_civireport.id
WHERE  value_extended_care_2_civireport.{$this->_colMapper['sessionName']} = '{$sname->session_name}' AND 
       value_extended_care_2_civireport.{$this->_colMapper['dayOfWeek']} = '{$this->_params['weekday_value']}' AND
       value_extended_care_2_civireport.{$this->_colMapper['isCancelled']} != 1";

            $this->_columnHeaders = 
                array( 'contact_civireport_id' => array( 'no_display' => true ),
                       'contact_civireport_display_name' => array( 'title' => 'Name' ),
                       'SignIn'  => array( 'title' => 'Sign In&nbsp;' ),
                       'SignOut' => array( 'title' => 'Sign Out' ),
                   );
            $rows[$sname->session_name] = array( );
            $dao  = CRM_Core_DAO::executeQuery( $sql );
            
            while( $dao->fetch( ) ) {
                $row = array( );
                foreach ( $this->_columnHeaders as $key => $value ) {
                    if ( property_exists( $dao, $key ) ) {
                        $row[$key] = $dao->$key;
                    }
                }
                $rows[$sname->session_name][] = $row;
            }
            for ($i = 1; $i <= $this->_params['extra_rows_value']; $i++) {
                $rows[$sname->session_name][] = array('contact_civireport_display_name' => '&nbsp;');
            }
        }
        $this->formatDisplay( $rows );

        $this->doTemplateAssignment( $rows );

        $this->endPostProcess( $rows );
    }

    function alterDisplay( &$rows ) {
        foreach ( $rows as $name => $nrows ) {
            foreach ( $nrows as $rowNum => $row ) {
                // convert display name to links
                if ( array_key_exists('contact_civireport_display_name', $row) &&
                     array_key_exists('contact_civireport_id', $row) ) {
                    $url = CRM_Utils_System::url( "civicrm/contact/view",  
                                                  'reset=1&cid=' . $row['contact_civireport_id'] );                      
                    $rows[$name][$rowNum]['contact_civireport_display_name_link' ] = $url;
                    $rows[$name][$rowNum]['contact_civireport_display_name_hover'] =
                        ts("View contact summary");
                    $entryFound = true;
                }
            } // foreach ends
        }
    }
}
