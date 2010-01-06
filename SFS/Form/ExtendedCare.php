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
require_once 'CRM/Core/OptionGroup.php';
require_once 'CRM/Core/SelectValues.php';


class SFS_Form_ExtendedCare extends CRM_Core_Form
{

    public  function preProcess( ) 
    {
        if( !CRM_Core_Permission::check( 'access CiviCRM' ) || !CRM_Core_Permission::check( 'administer CiviCRM' ) ) {
            CRM_Utils_System::permissionDenied( );
            exit();
        }
        $this->_signoutId  = CRM_Utils_Request::retrieve( 'signoutid',
                                                          'Integer',
                                                          $this, true );

        $this->_action = CRM_Utils_Request::retrieve( 'action','String',$this, false );

        $this->_customFields = array( 'entity_id', 'pickup_person_name', 'signin_time' , 'signout_time' , 'class', 'is_morning'	,'at_school_meeting');
           	
        parent::preProcess();
        
        
    }
    
     public  function setDefaultValues( $freez =1 ) {
         
         $defaults = array();
         $sql = "SELECT * FROM civicrm_value_extended_care_signout WHERE id={$this->_signoutId}";
         $dao = CRM_Core_DAO::executeQuery( $sql );
         
         if ( $this->_action & CRM_Core_Action::DELETE ) { 
             while( $dao->fetch() ) {
                 $this->assign( 'class' , $dao->class );
             }
             return $defaults;
         }
         
         if( $this->_signoutId ) {
             
             while( $dao->fetch() ) {
                 foreach( $this->_customFields as $field ) {
                     if( property_exists( $dao, $field ) ) { 
                         if ( in_array($field, array('signin_time', 'signout_time')) ) {
                             list( $defaults[$field], 
                                   $defaults[$field . '_time'] ) = 
                                 CRM_Utils_Date::setDateDefaults($dao->$field);
                         } else {
                             $defaults[$field] = $dao->$field;
                         }
                     }
                 }
             }
         }
         return $defaults;
     } 
     
     public function buildQuickForm( ) 
    {
            if( $this->_action & CRM_Core_Action::DELETE ) {
                $buttonLabel = ts('Delete');
            } else {
                require_once 'SFS/Utils/Query.php';

                $buttonLabel = ts('Save');
                $students =  SFS_Utils_Query::getStudentsByGrade( true, false, true , '' );
                $classes  =  SFS_Utils_Query::getClasses();
                    
                $this->add( 'select', 'entity_id', ts('Student'), array(''=>'--select--') + $students, true );
                $this->add( 'text', 'pickup_person_name', ts('Pickup Person:') ); 
                $this->addDateTime('signin_time',  ts('Signin'), CRM_Core_SelectValues::date( 'custom', 10, 2 ) );
                $this->addDateTime('signout_time',  ts('Signout'), CRM_Core_SelectValues::date( 'custom', 10, 2 ) );
                $this->add( 'select', 'class', ts('Class'), array(''=>'--select--') + $classes, true);
                $this->add('checkbox', 'is_morning', ts('Is morning?'));
                $this->add('checkbox', 'at_school_meeting', ts('At School Meeting?'));
            }
            
            $this->addButtons(array( 
                                    array ( 'type'      => 'next', 
                                            'name'      => $buttonLabel, 
                                            'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 
                                            'isDefault' => true   ), 
                                    array ( 'type'      => 'cancel', 
                                            'name'      => ts('Cancel') ), 
                                     ) 
                              );
            
    }
     
     
     public  function postProcess() 
     { 
         if( $this->_action & CRM_Core_Action::DELETE ) {
             $query  = "DELETE FROM  civicrm_value_extended_care_signout WHERE id =%1";
             $statusMsg = ts("Activity Block has been deleted successfuly");
         } else {
             $params = $this->controller->exportValues( $this->_name );
             $updateData = array( );
             foreach( $this->_customFields as $field ) {
                 
                 if( in_array( $field , array( 'is_morning', 'at_school_meeting' ) ) ) { 
                     $value =  CRM_Utils_Array::value( $field , $params)? 1:0;
                     $updateData[] = $field .'='.$value;
                     continue;
                 }
                 
                 $value = CRM_Utils_Array::value( $field , $params);
                 
                 if( in_array( $field , array( 'signin_time', 'signout_time' ) ) ) {
                     $value =  CRM_Utils_Array::value( $field , $params)? CRM_Utils_Date::processDate( $params[$field], $params[$field . '_time'] ): null;
                 }
                 
                 if( $value ) {
                     $updateData[] = $field ."="."'{$value}'";
                 } else {
                     $updateData[] = $field ."= null";
                 }            
             }
             
             $query  = "UPDATE civicrm_value_extended_care_signout SET " . implode( ' , ', $updateData ) ."  WHERE id =%1";
             $statusMsg = ts("Activity Block has been updated successfuly");
         }

         $params = array( 1 => array( $this->_signoutId ,'Integer') );
         CRM_Core_DAO::executeQuery( $query, $params );
         
         CRM_Core_Session::setStatus( $statusMsg );

     }
}   
