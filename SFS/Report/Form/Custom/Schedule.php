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

class SFS_Report_Form_Custom_Schedule extends CRM_Report_Form {
    
    // set custom table name
    protected $_customTable   = 'civicrm_value_school_information_1';  
    
    // set colunm_name for grouping
    protected $fieldName   = 'subtype_1';
    
    const
        ROW_COUNT_LIMIT = 10;

    function __construct( ) {
        $this->_columns = 
            array( 
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
                                array('title' => ts('Contact Name')
                                      ) ) ),
                  
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
                         'filters' =>
                         array( 'activity_date_time '=>array( 'title'        => ts('Date'),
                                                              'default'      => 'this.month',
                                                              'operatorType' => CRM_Report_Form::OP_DATE,
                                                              'type'         => CRM_Utils_Type::T_DATE )
                                ), ),

                  'civicrm_activity_target' =>
                   array( 'dao'      => 'CRM_Activity_DAO_ActivityTarget',
                          'fields'    =>
                          array( 'target_contact_id' =>
                                 array( 'no_display' => true ,
                                        'required'   => true )
                                 ),
                          'grouping' => 'activity-fields',
                          ),
                   
                   'civicrm_activity_assignment' => 
                   array( 'dao'      => 'CRM_Activity_DAO_ActivityAssignment',
                          'fields'    =>
                          array( 'assignee_contact_id' =>
                                 array( 'no_display'  => true ,
                                        'required'    => true )
                                 ),
                          'grouping' => 'activity-fields',
                          ),
                  
                  'civicrm_contact_target' =>
                  array( 'dao'       => 'CRM_Contact_DAO_Contact',
                         'fields'    => 
                         array( 'display_name' =>
                                array(
                                      'no_display' => true,
                                      'required'   => true,
                                      'title'      => ts('With')
                                      ),
                                ), ),
                  
                  'civicrm_contact_assignment' =>
                  array( 'dao'       => 'CRM_Contact_DAO_Contact',
                         'fields'    => 
                         array( 'display_name' =>
                                array(
                                      'no_display' => true,
                                      'required'   => true,
                                      'title'      => ts('Assigned To')
                                      ),
                                ), ),
                  
                    
                   );
                  
        $fields = array( );
        $query  = " 
                   SELECT column_name, label , option_group_id 
                   FROM civicrm_custom_field 
                   WHERE is_active = 1 AND column_name='{$this->fieldName}' AND custom_group_id = ( SELECT id FROM civicrm_custom_group WHERE table_name='{$this->_customTable}' ) " ;
        $dao = CRM_Core_DAO::executeQuery( $query );
        
        $filters = $option = array( );
        
        while( $dao->fetch() ) {
            $fields[$dao->column_name] = array( 'required'   => true, 
                                                'title'      => $dao->label,
                                                'no_display' => true
                                                      );
            $this->optionGroupId[$dao->column_name] = $dao->option_group_id;
            
        }

        $dao->free( );

        $query  = "SELECT label , value FROM civicrm_option_value WHERE option_group_id =".$this->optionGroupId[$this->fieldName]."  AND is_active=1";
        $dao    = CRM_Core_DAO::executeQuery( $query );
        while( $dao->fetch() ) {
            $options[$dao->value] = $dao->label; 
        }
        
        $filters[$this->fieldName] = array( 'title'        => ts('Sub Type'),
                                            'default'      => 'Staff',
                                            'operatorType' => CRM_Report_Form::OP_SELECT,
                                            'type'         => CRM_Utils_Type::T_STRING, 
                                            'options'      => $options,
                                            );
        
        $this->_columns[$this->_customTable] = array( 'dao'     => 'CRM_Contact_DAO_Contact',
                                                      'fields' => $fields ,
                                                      'filters' =>$filters ,
                                                      );
        parent::__construct( );
    }

    function preProcess( ) {
        $this->_csvSupported = false;
        parent::preProcess( );
    }
    
    function select( $value = null ) {
        $alias = $this->_aliases[$this->_customTable];
        $fieldArray = array( 'civicrm_contact',$this->_customTable );
        $select = $this->_columnHeaders =  array( );

        foreach ( $this->_columns as $tableName => $table ) {
           if( $value ) {
               if( !in_array( $tableName, $fieldArray ) ) {
                    continue;
                }
           }
           else {
               if( in_array( $tableName, $fieldArray )/*$tableName == $this->_customTable*/  ) {
                   continue;
               }
           }
           if ( array_key_exists('fields', $table) ) {
                foreach ( $table['fields'] as $fieldName => $field ) {
                    if ( CRM_Utils_Array::value( 'required', $field ) ||
                         CRM_Utils_Array::value( $fieldName, $this->_params['fields'] ) ) {
                            
                            $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
                            $this->_columnHeaders["{$tableName}_{$fieldName}"]['type']  = CRM_Utils_Array::value( 'type', $field );
                            $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = $field['title'];
                        
                    }
                }
            }
        }
        
        $this->_select = "SELECT " . implode( ",\n", $select ) . " ";
   }


    function from( ) {

        $alias = $this->_aliases[$this->_customTable];
        $this->_from = "FROM
                        civicrm_activity {$this->_aliases['civicrm_activity']}
                        LEFT JOIN civicrm_activity_target {$this->_aliases['civicrm_activity_target']} ON 
                            {$this->_aliases['civicrm_activity']}.id = {$this->_aliases['civicrm_activity_target']}.activity_id 
                        LEFT JOIN civicrm_activity_assignment {$this->_aliases['civicrm_activity_assignment']} ON 
                            {$this->_aliases['civicrm_activity']}.id = {$this->_aliases['civicrm_activity_assignment']}.activity_id 
                        LEFT JOIN civicrm_contact {$this->_aliases['civicrm_contact']}  ON 
                            {$this->_aliases['civicrm_activity']}.source_contact_id = {$this->_aliases['civicrm_contact']}.id 

		                LEFT JOIN civicrm_contact {$this->_aliases['civicrm_contact_target']} ON 
                           {$this->_aliases['civicrm_activity_target']}.target_contact_id = {$this->_aliases['civicrm_contact_target']}.id

                        LEFT JOIN civicrm_contact {$this->_aliases['civicrm_contact_assignment']} ON 
                           {$this->_aliases['civicrm_activity_assignment']}.assignee_contact_id = {$this->_aliases['civicrm_contact_assignment']}.id ";

    }
    
    function where( $value=null) { //CRm_core_error::debug('',$this);
        $fieldArray = array('civicrm_contact', $this->_customTable );
        $clauses    = array( );
        foreach ( $this->_columns as $tableName => $table ) {
            if( $value   ) {
                if(!in_array( $tableName, $fieldArray ) ) {
                    continue;
                }
            }
            else {
                if( in_array( $tableName, $fieldArray ) ) {
                    continue;
                }
            }    
            if ( array_key_exists('filters', $table) ) {
                foreach ( $table['filters'] as $fieldName => $field ) {
                    $clause = null;

                    //  if ( CRM_Utils_Array::value( 'type', $field ) & CRM_Utils_Type::T_DATE ) {
                     if ( $field['operatorType'] & CRM_Report_Form::OP_DATE ) { 
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
            $this->_where = "WHERE ( 1 ) ";
        } else {
            $this->_where = "WHERE " . implode( ' AND ', $clauses );
        }
        
        if( !$value ) {
            $selectedContacts = implode(',',$this->contactSelected );
            $this->_where .= " AND ( {$this->_aliases['civicrm_activity']}.source_contact_id IN ($selectedContacts) OR 
                                      target_contact_id IN ($selectedContacts) OR 
                                      assignee_contact_id IN ($selectedContacts) OR 
                                     {$this->_aliases['civicrm_activity']}.is_test = 0 )";
        }

    }
    
    function groupBy( ) {
        $this->_groupBy = " GROUP BY {$this->_aliases['civicrm_activity']}.id";
    }

    function postProcess( ) {
        $this->_params = $this->controller->exportValues( $this->_name );
        $this->beginPostProcess( ); 
        $this->selectContacts( );
        if( !empty($this->contactSelected) ) {
        $this->select ( false );
        $this->from   ( );
        $this->where  ( false );
        $this->groupBy( );
        $sql  = "{$this->_select} {$this->_from} {$this->_where} {$this->_groupBy}";
        
        $dao  = CRM_Core_DAO::executeQuery( $sql );
        if( !empty($this->contactSelected) ) {
            $rows = array();
            while( $dao->fetch( ) ) {
                $row = array( );
                foreach ( $this->_columnHeaders as $key => $value ) {
                    if ( property_exists( $dao, $key ) ) {
                        $row[$key] = $dao->$key;
                    }
                }
                if ( isset( $dao->civicrm_activity_source_contact_id ) ) {
                    $rows[ $dao->civicrm_activity_source_contact_id][] = $row ;
                }
                if ( isset( $dao->civicrm_activity_target_target_contact_id ) ) {
                    $rows[$dao->civicrm_activity_target_target_contact_id][] = $row;    
                }
                if ( isset( $dao->civicrm_activity_assignment_assignee_contact_id ) ) {
                    $rows[$dao->civicrm_activity_assignment_assignee_contact_id][] = $row;
                }
            }   
            

            $unsetHeaders = array('civicrm_activity_target_target_contact_id', 'civicrm_activity_assignment_assignee_contact_id');
            foreach( $unsetHeaders as $fld ) {
                unset($this->_columnHeaders[$fld]);
            }
        
            $this->assign( 'activityHeaders' , $this->_columnHeaders );
            $this->assign( 'activityDetails' , $rows );
        }
        }
        $this->formatDisplay( $this->contactSelected ,false );
        
        $this->doTemplateAssignment($this->contactSelected  );
        
        $this->endPostProcess($this->contactSelected  );

    }
    
    function limit( $rowCount = self::ROW_COUNT_LIMIT ) {
        parent::limit( $rowCount );
    }

    function setPager( $rowCount = self::ROW_COUNT_LIMIT ) {
        parent::setPager( $rowCount );
    }

    function selectContacts( ) {
        $this->contactSelected = array( );
        
        $alias = $this->_aliases[$this->_customTable];

        $this->select('contacts'); 
        $this->_from   = "FROM {$this->_customTable} $alias
                               INNER JOIN civicrm_contact {$this->_aliases['civicrm_contact']}
                                   ON {$this->_aliases['civicrm_contact']}.id = {$alias}.entity_id";
        $this->where( 'contacts' );
        $this->limit();
        
        $query = "{$this->_select}{$this->_from} {$this->_where}{$this->_limit}";

        $dao   = CRM_Core_DAO::executeQuery( $query );

        while ( $dao->fetch( ) ) {
            $row = array( );
            foreach ( $this->_columnHeaders as $key => $value ) {

                if ( property_exists( $dao, $key ) ) {


                    $row[$key] = $dao->$key;
                }
            }
            $rows[$dao->civicrm_contact_id] = $row;
            $this->contactSelected[]= $dao->civicrm_contact_id;
        }
        unset($this->_columnHeaders['civicrm_contact_id']);
        $this->assign( 'contactDetails', $rows );
        $this->assign( 'contactHeaders', $this->_columnHeaders );
        $this->setPager( );
        
    }

    
}
