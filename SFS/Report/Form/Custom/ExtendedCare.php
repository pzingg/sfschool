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

class CRM_Report_Form_Custom_ExtendedCare extends CRM_Report_Form {
    
    // set custom table name
    protected $_customTable   = 'civicrm_value_sessions_2';  
    
    // set columns to display
    // colunm_name => title from custom table
    protected $displayColumns = array( 'start_date_7' => 'In' , 'end_date_8' => 'Logout' );
    
    // set colunm_name for grouping , 'Day' should be first element in array
    protected $optionFields   = array ('day_11', 'term_12');
    

    function __construct( ) {
        $this->_columns = array ( );
        
        $fields = array();
        $query  = " 
                   SELECT column_name, label , option_group_id 
                   FROM civicrm_custom_field 
                   WHERE is_active = 1 AND custom_group_id = ( SELECT id FROM civicrm_custom_group WHERE table_name='{$this->_customTable}' ) " ;
        
        $dao_column = CRM_Core_DAO::executeQuery( $query );
        
        $modifyOption = array( );
        while ( $dao_column->fetch( ) ) {
            if ( in_array($dao_column->column_name , $this->optionFields ) ) {
                $fields[$dao_column->column_name] = array(
                                                          'title' => $dao_column->label,
                                                          );
                $modifyOption[$dao_column->option_group_id] = $dao_column->column_name;
                }
            
        }
        $this->optionFields = $modifyOption;
        
        $filters = array( );
        foreach( $this->optionFields as $grp => $fieldName ) {
            $options = array( );
            $query = "SELECT label , value FROM civicrm_option_value WHERE option_group_id = $grp AND is_active=1";

            $dao = CRM_Core_DAO::executeQuery( $query );

            while( $dao->fetch() ) {
                $options[$dao->value] = $dao->label; 
            }
            $filters[$fieldName] = array( 'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                          'options'      => $options );
            
        }

        foreach( $fields as $key => $val ) {
            foreach ( $filters[$key ] as $k => $v ) {
                $fields[$key][$k] = $v; 
            }
        }   
        
        $this->_columns[$this->_customTable] = array( 
                                                     'dao'     => 'CRM_Contact_DAO_Contact',
                                                     'filters' => $fields,
                                                      );
        parent::__construct( );
    }

    function preProcess( ) {
        $this->_csvSupported = false;
        parent::preProcess( );
    }
    
    function select( ) {
        
        $select = $this->_columnHeaders =  array( );

        // add contact field
        $this->_columnHeaders['civicrm_contact_display_name'] = array ('title' => 'Contact Name' );
        $this->_columnHeaders['civicrm_contact_id']           = null;
        $this->_aliases['civicrm_contact'] = 'contact_civireport';

        $alias = $this->_aliases[$this->_customTable];
        
        $select[] = " {$this->_aliases['civicrm_contact']}.display_name as civicrm_contact_display_name,
                      {$this->_aliases['civicrm_contact']}.id as  civicrm_contact_id,
                      $alias.id as id ";
        
        foreach( $this->displayColumns as $colName => $title ) {
            $fieldAlias = $this->_customTable."_".$colName;
            $select[] = " $alias.$colName as $fieldAlias ";
            $this->_columnHeaders[$fieldAlias] = array ('title' => $title );
        }
        
        
        $count = 1; 
        foreach( $this->optionFields  as $grpId => $fld ) {
            $optionAlias = $fld."_".$grpId;
 
            if( $count == 1 ) {
                $labelAlias  = 'day_label';
                $valueAlias  = 'day_value';
                $select[]    = "$optionAlias.label as $labelAlias , $optionAlias.value as $valueAlias ";
            } else {
                $labelAlias  = 'term_label';
                $valueAlias  = 'term_value';
                $select[]    = "$optionAlias.label as $labelAlias ,$optionAlias.value as $valueAlias ";
            }
            $count++;
        }
        
        $this->_select = "SELECT " . implode( ",\n", $select ) . " ";
   }


    function from( ) {

        $alias = $this->_aliases[$this->_customTable];
        $this->_from = " 
                         FROM  
                              {$this->_customTable} {$alias}
                              INNER JOIN civicrm_contact {$this->_aliases['civicrm_contact']} 
	                                     ON {$alias}.entity_id = {$this->_aliases['civicrm_contact']}.id ";

        foreach( $this->optionFields  as $grpId => $fld ) {
            $optionAlias    = $fld."_".$grpId;
            $this->_from .= " 
                             LEFT JOIN civicrm_option_value $optionAlias
                                  ON $optionAlias.value = $alias.$fld AND $optionAlias.option_group_id = $grpId ";
        }
        
        
    }
    
    function where( ) {
        $clauses = array( );
        foreach ( $this->_columns as $tableName => $table ) {
            if ( array_key_exists('filters', $table) ) {
                foreach ( $table['filters'] as $fieldName => $field ) {
                    $clause = null;
                    if ( $field['type'] & CRM_Utils_Type::T_DATE ) {
                        $relative = CRM_Utils_Array::value( "{$fieldName}_relative", $this->_params );
                        $from     = CRM_Utils_Array::value( "{$fieldName}_from"    , $this->_params );
                        $to       = CRM_Utils_Array::value( "{$fieldName}_to"      , $this->_params );
                        
                        $clause = $this->dateClause( $field['name'], $relative, $from, $to );
                    } else {
                        $op = CRM_Utils_Array::value( "{$fieldName}_op", $this->_params );
                        if ( $op ) {
                            $clause = 
                                $this->whereClause( $field,
                                                    $op,
                                                    CRM_Utils_Array::value( "{$fieldName}_value", $this->_params ),
                                                    CRM_Utils_Array::value( "{$fieldName}_min", $this->_params ),
                                                    CRM_Utils_Array::value( "{$fieldName}_max", $this->_params ) );
                        }
                    }
                    
                    if ( ! empty( $clause ) ) {
                        $clauses[] = $clause;
                    }
                }
            }
        }

        if ( empty( $clauses ) ) {
            $this->_where = "WHERE ( 1 ) ";
        } else {
            $this->_where = "WHERE " . implode( ' AND ', $clauses );
        }
    }


    function orderBy( ) {
        $this->_orderBy = "";
        $orderBy = array( );
        $alias = $this->_aliases[$this->_customTable];

        foreach( $this->optionFields  as $grpId => $fld ) {
            $orderBy[] = " $alias.$fld ";
        }
        if( !empty($groupBy)) {
            $this->_groupBy = " ORDER BY ".implode(',', $groupBy ) ;
        }
        
    }

    function groupBy( ) {
        $this->_groupBy = "";
        $groupBy = array( ) ;
        $alias = $this->_aliases[$this->_customTable];

        foreach( $this->optionFields  as $grpId => $fld ) {
            $groupBy[] = " $alias.$fld ";
        }
        if( !empty($groupBy)) {
            $this->_groupBy = " GROUP BY ".implode(',', $groupBy ) ." , $alias.id";
        }
       
    }

    function postProcess( ) {
        $dispalyFields = array();
        
        $count = 1; 
        foreach( $this->optionFields  as $grpId => $fld ) {
            
            if( $count == 1 ) {
                $day_label = 'day_label';
                $day_value = 'day_value';
                $dispalyFields[$fld] = $day_label;
            } else {
                $term_label  = 'term_label';
                $term_value  = 'term_value';
                $dispalyFields[$fld] = $term_label;
            }
            $count++;
        }
        
        $this->beginPostProcess( );
        
        $sql  = $this->buildQuery( );
        
        $rows = $termHeaders =  $dayHeaders = array( ); 
        $dao  = CRM_Core_DAO::executeQuery( $sql );

        while( $dao->fetch( ) ) {
            $row = array( );
            foreach ( $this->_columnHeaders as $key => $value ) {
                if ( property_exists( $dao, $key ) ) {
                    $row[$key] = $dao->$key;
                }
            }
            foreach ($dispalyFields as $k => $v ) {
                if ( property_exists( $dao, $v ) ) {
                    $row[$v] = $dao->$v;
                }
            }
            
            $rows[$dao->$day_value][$dao->$term_value][] = $row;
            $dayHeaders[$dao->$day_value] =  $dao->$day_label;
            $termHeaders[$dao->$day_value][$dao->$term_value]=  $dao->$term_label;
        }   

        $this->assign( 'dayHeaders' , $dayHeaders );
        $this->assign( 'termHeaders' , $termHeaders );
        unset( $this->_columnHeaders['civicrm_contact_id'] );
        $this->formatDisplay( $rows );

        $this->doTemplateAssignment( $rows );

        $this->endPostProcess( $rows );

    }

    function countStat( &$statistics, $count ) {
        $count = $this->_rowsFound;
        $statistics['counts']['rowCount'] = array( 'title' => ts('Row(s) Listed'),
                                                   'value' => $count );

        if ( $this->_rowsFound && ($this->_rowsFound > $count) ) {
            $statistics['counts']['rowsFound'] = array( 'title' => ts('Total Row(s)'),
                                                        'value' => $this->_rowsFound );
        }
    }

    function alterDisplay( &$rows ) {
        foreach ( $rows as $dayId =>$day ) {
            foreach( $day as $termId => $term ) {
                foreach ( $term as $rowNum => $row ) {
                    
                    if ( array_key_exists( 'civicrm_contact_id', $row ) &&
                         array_key_exists( 'civicrm_contact_display_name', $row ) ) {
                        
                        $url = CRM_Utils_System::url( "civicrm/contact/view",  
                                              'reset=1&cid=' . $row['civicrm_contact_id'] );                      
                        $rows[$dayId][$termId][$rowNum]['civicrm_contact_display_name_link'] = $url;
                        $rows[$dayId][$termId][$rowNum]['civicrm_contact_display_name_hover'] =
                            ts("View Contact Summary for this Contact");
                    }
                }
                
            }
        }
    }
    
 }
    