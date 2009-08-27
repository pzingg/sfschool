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

class SFS_Report_Form_Attendance extends CRM_Report_Form {
    const 
        EXTRA_ROWS_MIN = 0,
        EXTRA_ROWS_MAX = 10,
        EXTRA_ROWS_DEFAULT = 5;
    
    // set custom table name
    protected $_customTable   = 'civicrm_value_extended_care_2';  
    
    // col mapper
    protected $_colMapper = array ( 'dayOfWeek'    => 'day_of_week',
                                    'sessionName'  => 'name',
                                    'sessionOrder' => 'session',
                                    'isCancelled'  => 'has_cancelled',
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
                $query   = "
SELECT label , value
FROM   civicrm_option_value 
WHERE  option_group_id = {$dao_column->option_group_id}
AND    is_active = 1
";
                $dao     = CRM_Core_DAO::executeQuery( $query );
                while( $dao->fetch() ) {
                    $this->_optionFields[$dao_column->column_name][$dao->value] = $dao->label;
                }
            }
        }

        $query   = "
SELECT distinct {$this->_colMapper['sessionName']} as session_name 
FROM   sfschool_extended_care_source value_extended_care_2_civireport
WHERE  is_active = 1
AND    term = %1
";
        require_once 'SFS/Utils/ExtendedCare.php';
        $params = array( 1 => array( SFS_Utils_ExtendedCare::getTerm( ), 'String' ) );
        $dao      = CRM_Core_DAO::executeQuery( $query, $params );

        $sOptions = array( );
        while( $dao->fetch( ) ) {
            $sOptions[$dao->session_name] = $dao->session_name;
        }

        $eOptions = array( );
        for ( $i = self::EXTRA_ROWS_MIN; $i <= self::EXTRA_ROWS_MAX; $i++ ) {
            $eOptions[$i] = $i;
        }

        $query   = "
SELECT value,label  
FROM  civicrm_option_value op_value 
WHERE option_group_id =(SELECT option_group_id FROM civicrm_custom_field where column_name='".$this->_colMapper['sessionOrder']."')";
        
        $dao = CRM_Core_DAO::executeQuery( $query );
        $oOptions = array();
        while( $dao->fetch( ) ) {
            $oOptions[$dao->value] = $dao->label;
        }

        $this->_columns[$this->_customTable] = 
            array( 'dao'     => 'CRM_Contact_DAO_Contact',
                   'filters' =>             
                   array( 
                         'weekday'       => 
                         array( 'title'   => ts( 'Day Of Week' ),
                                'operatorType' => CRM_Report_Form::OP_SELECT,
                                'options'      => $this->_optionFields[$this->_colMapper['dayOfWeek']] ),
                         'session'       =>
                         array( 'title'        => ts('Sign Out Columns'),
                                'default'      => array( 'First', 'Second' ),
                                'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                'options'      => $oOptions ),
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
        
        //set time for session order keys
        $sesionOrder = array( 'First'  => '3.30pm- 4.30pm',
                              'Second' => '4.30pm- 5.15pm',
                              'Third'  => '5.15pm- 6.15pm',
                              'Forth'  => '6.15pm- 7.00pm');
        
        $sessionHeaders = array();
        if( empty($this->_params['session_value']) ) {
            //if session filter is empty, display 'Sign Out' header 
            $sessionHeaders['signOut'] = array('title'=> 'Sign Out' );  
        } else {
            foreach( $this->_params['session_value'] as $value ) {
                $sessionHeaders[$value] = array('title'=> $sesionOrder[$value] );
            }
        }

        $sql  = "
SELECT distinct value_extended_care_2_civireport.{$this->_colMapper['sessionName']} as session_name, 
       value_extended_care_2_civireport.{$this->_colMapper['sessionOrder']} as session_order 
FROM   sfschool_extended_care_source value_extended_care_2_civireport
WHERE  is_active = 1
AND    term = %1
";
        $params = array( 1 => array( SFS_Utils_ExtendedCare::getTerm( ), 'String' ) );
        $sname = CRM_Core_DAO::executeQuery( $sql, $params );
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
                       );
            $this->_columnHeaders = array_merge( $this->_columnHeaders, $sessionHeaders );
            $rows[$sname->session_name] = $sessionInfo[$sname->session_name] = array( );
            $sessionInfo[$sname->session_name ] = $sname->session_order;

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
                $rows[$sname->session_name][] = array('contact_civireport_display_name' => '&nbsp;<br/>&nbsp;');
            }
            if ( empty($rows[$sname->session_name]) ) {
                unset($rows[$sname->session_name]);
            }
        }
        $this->formatDisplay( $rows );

        $this->assign_by_ref( 'sessionInfo', $sessionInfo );
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
