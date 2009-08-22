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

class SFS_Report_Form_ParentTeacherConference extends CRM_Report_Form {
    
    // set custom table name
    protected $_customTable = 'civicrm_value_school_information_1';  
    
    protected $_typeField   = array( 'column_name'  => 'subtype',
                                     'value'        => 'Student' );
    protected $_gradeField  = array( 'column_name'  => 'grade' );

    const
        ROW_COUNT_LIMIT =10;

    function __construct( ) {
        $this->_columns = array( 
                                'civicrm_contact' =>
                                array( 'dao'       => 'CRM_Contact_DAO_Contact',
                                       'fields'    => 
                                       array( 'display_name' =>
                                              array(
                                                    'no_display' => true,
                                                    'required'   => true,
                                                    'title'      => ts('Student')
                                                    ),
                                              'id' =>
                                              array(
                                                    'no_display' => true,
                                                    'required'   => true,
                                                    ),
                                              ),
                                       'filters'   =>
                                       array( 'sort_name' =>
                                              array('title' => ts('Student')
                                                    ) ) ),
                                
                                'civicrm_contact_parent' =>
                                array( 'dao'       => 'CRM_Contact_DAO_Contact',
                                       'fields'   =>
                                       array( 'display_name' =>
                                              array(
                                                    'no_display' => true,
                                                    'required'   => true,
                                                    'title'      => ts('Parent')
                                                    ),
                                              'id' =>
                                              array(
                                                    'no_display' => true,
                                                    'required'   => true,
                                                    ),
                                              ) ),

                                'civicrm_activity'      =>
                                array( 'dao'     => 'CRM_Activity_DAO_Activity',
                                       'fields'  =>
                                       array(
                                             'subject' => array( 'title'      => ts('Activity'),
                                                                 'required'   => true,
                                                                 'no_display' => true),
                                             'activity_date_time' => array( 'title'      => ts('Date'),
                                                                            'no_display' => true, 
                                                                            'required'   => true ),
                                             ),
                                       ),
                                       
                                 'civicrm_contact_teacher' =>
                                array( 'dao'       => 'CRM_Contact_DAO_Contact',
                                       'fields'   =>
                                       array( 'display_name' =>
                                              array(
                                                    'no_display' => true,
                                                    'required'   => true,
                                                    'title'      => ts('Staff')
                                                    ),
                                              'id' =>
                                              array(
                                                    'no_display' => true,
                                                    'required'   => true,
                                                    ),
                                              ) ),
                                       );
        
        $fields = array( );
        $query  = " 
                   SELECT column_name, label , option_group_id 
                   FROM civicrm_custom_field 
                   WHERE is_active = 1 AND column_name='".$this->_gradeField['column_name']."' AND custom_group_id = ( SELECT id FROM civicrm_custom_group WHERE table_name='{$this->_customTable}' ) " ;
        $dao_column = CRM_Core_DAO::executeQuery( $query );
        
        $this->_optionFields = $this->_textFields = array( );

        while ( $dao_column->fetch( ) ) {
            $fields[$dao_column->column_name] = array('required' => true, 
                                                      'title' => $dao_column->label,
                                                      'no_display' =>true
                                                        );
                $this->_gradeField['op_group_id'] = $dao_column->option_group_id;
        }
        
        $filters = array( );
        // filter for Grade
        $options = array( );
        $query   = "SELECT label , value FROM civicrm_option_value WHERE option_group_id =".$this->_gradeField['op_group_id']."  AND is_active=1";
        $dao     = CRM_Core_DAO::executeQuery( $query );
        
        while( $dao->fetch( ) ) {
            $options[$dao->value] = $dao->label; 
        }
        $filters[$this->_gradeField['column_name']] = array(
                                                            'title'        => ts('Grade'),
                                                            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                                            'options'      => $options,
                                                            );


        $this->_columns[$this->_customTable] = array( 'dao'     => 'CRM_Contact_DAO_Contact',
                                                      'fields'  => $fields ,
                                                      'filters' => $filters,
                                                      );
        $this->_columnHeaders = array( );
        parent::__construct( );
    }

    function preProcess( ) {
        $this->_csvSupported = false;
        parent::preProcess( );
    }
  
    function select( $value ) {
        $parentInfo = array( 'civicrm_contact', 'civicrm_contact_parent' , $this->_customTable );
        $activityInfo = array( 'civicrm_contact', 'civicrm_contact_teacher', 'civicrm_activity' );
        $fieldArray = array();
        if( $value == 'parentInfo' ){
            $fieldArray = $parentInfo;
        } else {
            $fieldArray = $activityInfo;
        }
        
        $select = array( );
        $this->_columnHeaders = array( );
        foreach ( $this->_columns as $tableName => $table ) {
            if ( array_key_exists('fields', $table) ) {
                foreach ( $table['fields'] as $fieldName => $field ) {
                    if ( CRM_Utils_Array::value( 'required', $field ) ||
                         CRM_Utils_Array::value( $fieldName, $this->_params['fields'] ) ) {
                        if ( in_array( $tableName, $fieldArray  ) ) {
                            
                            $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
                            $this->_columnHeaders["{$tableName}_{$fieldName}"]['type']  = CRM_Utils_Array::value( 'type', $field );
                            $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = $field['title'];
                        }
                    }
                }
                }
        }
        
        $this->_select = "SELECT " . implode( ', ', $select ) . " ";
    }
    

    function from( $value ) {

        $alias = $this->_aliases[$this->_customTable];
        
         $this->_from = " 
                         FROM
                            {$this->_customTable} {$alias} 
                            INNER JOIN civicrm_contact {$this->_aliases['civicrm_contact']} 
                                       ON  {$this->_aliases['civicrm_contact']}.id = {$alias}.entity_id";
         
         if( $value == 'parentInfo' ) {
             
             $this->_from .= " 
                              LEFT JOIN civicrm_relationship relationship
                                        ON ( relationship.contact_id_a = {$alias}.entity_id AND
                                             relationship.relationship_type_id = 1)
                              INNER JOIN civicrm_contact {$this->_aliases['civicrm_contact_parent']}
                                        ON ( {$this->_aliases['civicrm_contact_parent']}.id = relationship.contact_id_b )";
         } else {
             $this->_from .= "
                              LEFT JOIN civicrm_activity_target target
                                        ON target.target_contact_id = {$alias}.entity_id
                              LEFT JOIN civicrm_activity_assignment assignment
                                        ON assignment.activity_id =  target.activity_id 
                              LEFT JOIN civicrm_activity {$this->_aliases['civicrm_activity']}
                                        ON {$this->_aliases['civicrm_activity']}.id = target.activity_id
                              INNER JOIN civicrm_contact {$this->_aliases['civicrm_contact_teacher']}
                                        ON {$this->_aliases['civicrm_contact_teacher']}.id = assignment.assignee_contact_id ";
                               
         }
    }
    
    function where( ) {
        $alias = $this->_aliases[$this->_customTable];
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
                            
                            // hack for values type string
                            if ( $op == 'in' ) {
                                $value  = CRM_Utils_Array::value( "{$fieldName}_value", $this->_params );
                                if ( $value !== null && count( $value ) > 0 ) {
                                    $clause = "( {$field['dbAlias']} IN ('" . implode( '\',\'', $value ) . "' ) )";
                                }
                            } else {
                                $clause = 
                                    $this->whereClause( $field,
                                                        $op,
                                                        CRM_Utils_Array::value( "{$fieldName}_value", $this->_params ),
                                                        CRM_Utils_Array::value( "{$fieldName}_min", $this->_params ),
                                                        CRM_Utils_Array::value( "{$fieldName}_max", $this->_params ) );
                            }
                        }
                    }
                    
                    if ( ! empty( $clause ) ) {
                        $clauses[] = $clause;
                    }
                }
            }
        }
        
        if ( empty( $clauses ) ) {
            $this->_where = "WHERE {$alias}.{$this->_typeField['column_name']}= '".$this->_typeField['value']."'";
        } else {
            $this->_where = "WHERE {$alias}.{$this->_typeField['column_name']}= '".$this->_typeField['value']."' AND " . implode( ' AND ', $clauses );
        }
    }
    
    function groupBy( $value ) {
        
        $alias = $this->_aliases[$this->_customTable];
        $this->_groupBy = "";
        
        $groupBy = array( );
        
        $groupBy[] = " $alias.entity_id ";
        
        if( $value == 'parentInfo' ){
            $groupBy[] = "{$this->_aliases['civicrm_contact_parent']}.id ";
            
        } else {
            $groupBy[] = "target.activity_id ";
        }
        if( !empty($groupBy) ) {
            $this->_groupBy = " GROUP BY ".implode(',', $groupBy );
        }
        
    }
    
    function limit( $rowCount = self::ROW_COUNT_LIMIT ) {
        parent::limit( $rowCount );
    }
    function setPager( $rowCount = self::ROW_COUNT_LIMIT ) {
        parent::setPager( $rowCount );
    }
    
    function postProcess( ) {
        $this->beginPostProcess( );
        
        $this->selectContacts( );
        
        $gradeAlias  = $this->_customTable.'_'.$this->_gradeField['column_name'];
        $getData     = array( 'parentInfo', 'activityInfo' );
        $columnUnset = array( 'civicrm_contact_display_name', 'civicrm_contact_id', $gradeAlias,'civicrm_contact_parent_id' ,'civicrm_contact_teacher_id');
        
        if ( !empty($this->studentsIds) ) {
            $studentsIds = implode( ',', $this->studentsIds );
            foreach( $getData as $value) {
                $sql  = $this->buildQuery( $value );
                
                $rows = array( ); 
                $dao  = CRM_Core_DAO::executeQuery( $sql );
                
                if( $value == 'parentInfo' ) {
                    $contactSelected = array( );
                    $headersParentInfo =  $this->_columnHeaders;     
                    foreach( $headersParentInfo as $headKey => $header) {
                        if( in_array( $headKey,  $columnUnset ) ) {
                            unset( $headersParentInfo[$headKey] );
                        }
                    }
                    $parentInfo = array( );
                    
                    while( $dao->fetch( ) ) {
                        $contactSelected[$dao->civicrm_contact_id] = array ( 'display_name' =>  $dao->civicrm_contact_display_name,
                                                                             'grade'=> $dao->$gradeAlias );
                        $row = array( );
                        foreach ( $headersParentInfo as $key => $val ) {
                            if ( property_exists( $dao, $key ) ) {
                                $row[$key] = $dao->$key;
                            }
                        }
                        $rows[$dao->civicrm_contact_id][] = $row;
                    }
                    
                    $this->assign( 'headersParentInfo', $headersParentInfo );
                    $this->assign( 'contactSelected', $contactSelected );
                    $this->assign( 'paraentInfo' , $rows );
                    $this->_columnHeaders= array();
                    $dao->free();
                } else { 
                    $headersActivityInfo =  $this->_columnHeaders;
                    foreach( $headersActivityInfo as $headKey => $header) {
                        if( in_array( $headKey,  $columnUnset ) ) {
                            unset( $headersActivityInfo[$headKey] );
                        }
                    }
                    
                    while( $dao->fetch( ) ) {
                        $row = array( );
                        foreach ( $headersActivityInfo as $key => $val ) {
                            if ( property_exists( $dao, $key ) ) {
                                $row[$key] = $dao->$key;
                            }
                        }
                        
                        $rows[$dao->civicrm_contact_id][] = $row;
                    }
                    
                    $this->assign( 'headersActivityInfo', $headersActivityInfo );
                    $this->assign( 'activityInfo', $rows );
                    $dao->free();
                }
            }
        }

        $this->formatDisplay( $this->studentsIds , false );
        
        $this->doTemplateAssignment( $this->studentsIds );
        
        $this->endPostProcess( $this->studentsIds );
        
    }
    
    function buildQuery( $value ) {
        $studentsIds = implode( ',',$this->studentsIds );
        $alias = $this->_aliases[$this->_customTable];
        $this->select ($value );
        $this->from   ($value );
        $this->_where = "WHERE $alias.entity_id IN ($studentsIds) ";
        $this->groupBy($value );
        $this->orderBy($value );
        
        $sql = "{$this->_select} {$this->_from} {$this->_where} {$this->_groupBy} {$this->_having} {$this->_orderBy} ";
        
        return $sql;
    }
    
    function selectContacts( ) {
        $this->studentsIds = array();
        $alias = $this->_aliases[$this->_customTable];
        $this->_select = "SELECT DISTINCT({$alias}.entity_id)";
        $this->_from   = "FROM {$this->_customTable} $alias
                               INNER JOIN civicrm_contact {$this->_aliases['civicrm_contact']}
                                   ON {$this->_aliases['civicrm_contact']}.id = {$alias}.entity_id";
        $this->where() ;
        $this->limit();
        
        $query = "{$this->_select}{$this->_from} {$this->_where}{$this->_limit}";
        $dao   = CRM_Core_DAO::executeQuery( $query );
        while( $dao->fetch( ) ) {
            $this->studentsIds[] = $dao->entity_id;
        }
        
        $this->setPager( );   
    }

    
}
