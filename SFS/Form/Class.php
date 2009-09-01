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


class SFS_Form_Class extends CRM_Core_Form
{

    public  function preProcess( ) 
    {
        if( !CRM_Core_Permission::check( 'Administer Extended Care Information' ) ) {
            CRM_Utils_System::permissionDenied( );
            exit();
        }
        $this->_indexID  = CRM_Utils_Request::retrieve( 'id',
                                                        'Integer',
                                                        $this, false );

        $this->_action = CRM_Utils_Request::retrieve( 'action','String',$this, false );

        if ( ( $this->_action & CRM_Core_Action::DISABLE ) ||
             ( $this->_action & CRM_Core_Action::ENABLE ) ) {
            return;
        } 
  
        $this->_customFields =  array('term','session','name','day_of_week','min_grade','max_grade','start_date','end_date','instructor','fee_block','max_participants','location','url');
        
        parent::preProcess();
        
        
    }
    
     public  function setDefaultValues( $freez =1 ) {
         
         $defaults = array();
         
         if ( ( $this->_action & CRM_Core_Action::DISABLE ) ||
              ( $this->_action & CRM_Core_Action::ENABLE ) ) {
             return $defaults;
         }
         
         if( $this->_indexID ) {
             $sql = "SELECT * FROM sfschool_extended_care_source WHERE id={$this->_indexID}";
             
             $dao = CRM_Core_DAO::executeQuery( $sql );
             while( $dao->fetch() ) {
                 foreach($this->_customFields as $field) {
                     
                     
                     if( property_exists( $dao, $field ) ) {
                         $defaults[$field] = $dao->$field; 
                     }
                 }
             }
         }
         return $defaults;
     } 
     
     public function buildQuickForm( ) 
    {
        if ( ( $this->_action & CRM_Core_Action::DISABLE ) ||
              ( $this->_action & CRM_Core_Action::ENABLE ) ) {
            
            if( $this->_action & CRM_Core_Action::DISABLE ) {
                $buttonLabel = ts('Disable');
            } else {
                $buttonLabel = ts('Enable');
            }
            $this->addButtons(array( 
                                    array ( 'type'      => 'submit', 
                                            'name'      => $buttonLabel, 
                                            'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 
                                            'isDefault' => true   ), 
                                    array ( 'type'      => 'cancel', 
                                            'name'      => ts('Cancel') ), 
                                     ) 
                              );
            return;
        }

        $options = array();
        
        $sql = "SELECT column_name,option_group_id FROM civicrm_custom_field WHERE column_name IN('term', 'day_of_week', 'session' , 'grade')";
        
        $dao = CRM_Core_DAO::executeQuery( $sql );
        while( $dao->fetch( ) ) {
            $options[$dao->column_name] = CRM_Core_OptionGroup::valuesByID($dao->option_group_id);
            
        }
        
        
        $this->add('select', 'term',  ts( 'Term' ), array(''=>'-select')+$options['term'] , true );
        $this->add('select', 'day_of_week', ts( 'Day Of Week:' ), array(''=>'-select')+$options['day_of_week'], true );
        
        $this->add('select', 'session', ts( 'Session:' ), array(''=>'-select')+ $options['session'], true );
        
        $this->add('select', 'max_grade', ts( 'Max Grade:' ), array(''=>'-select')+ $options['grade'],true );
        $this->add('select', 'min_grade', ts( 'Min Grade:' ), array(''=>'-select')+$options['grade'],true );
        
        $this->add('text', 'name', ts( 'Class Name:' ),null, true);
        
        $this->add('date', 'start_date',  ts('Start Date'), CRM_Core_SelectValues::date( 'custom', 10, 2 ) );
        $this->add('date', 'end_date',  ts('End Date'), CRM_Core_SelectValues::date( 'custom', 10, 2 ) );
        
        $this->add('text', 'instructor', ts( 'Instructor:' ) );
        $this->add('text', 'fee_block', ts( 'Fee Block:' ) );
        $this->add('text', 'max_participants', ts( 'Max Participant:' ) );
        $this->add('text', 'location', ts( 'Location:' ) );
        $this->add('text', 'url', ts( 'Url:' ));
        
        $this->addButtons(array(
                                array ( 'type'      => 'submit',
                                        'name'      => ts('Save'),
                                        'isDefault' => true   ),
                                array ( 'type'       => 'cancel',
                                        'name'       => ts('Cancel') ),
                                )
                          );    
        
        $this->assign( 'elements', $this->_customFields );
        
        
    }
     
     
     public  function postProcess() 
     { 
         if($this->_action & CRM_Core_Action::DISABLE) {

             if( $this->_indexID )  { 
                 $sql = "UPDATE sfschool_extended_care_source SET is_active=0 WHERE id=".$this->_indexID;
                  CRM_Core_DAO::executeQuery( $sql);
                
                 CRM_Core_Session::setStatus( ts('Class has been has been Disabled.') );
                 CRM_Utils_System::redirect( CRM_Utils_System::url('civicrm/sfschool/class', "reset=1") );
             } 

         } elseif( $this->_action & CRM_Core_Action::ENABLE ) {
             if( $this->_indexID )  {
                 $sql = "UPDATE sfschool_extended_care_source SET is_active=1 WHERE id=".$this->_indexID;
                  CRM_Core_DAO::executeQuery( $sql);
                
                 CRM_Core_Session::setStatus( ts('Class has been has been Enabled.') );
                 CRM_Utils_System::redirect( CRM_Utils_System::url('civicrm/sfschool/class', "reset=1") );
             }

         }else {
             $params = $this->controller->exportValues( $this->_name );
             $updateValues = array();
             foreach( $this->_customFields as $field ) {
                 $value = CRM_Utils_Array::value( $field , $params );
                 if( $value) {
                     if( $field == 'start_date' || $field == 'end_date' ) {
                         $updateValues[] = $field."='".CRM_Utils_date::format($params[$field])."' ";
                         continue;
                     } 
                     $updateValues[] =  $field."='".$value."' ";
                 }
             }
             if( $this->_indexID ) {
                 $sql = "UPDATE sfschool_extended_care_source SET " . implode(',', $updateValues ). "WHERE id=".$this->_indexID;
                 CRM_Core_DAO::executeQuery( $sql);
                 
                 $statusMsg = ts("Class Has been edited Successfully");
                 CRM_Core_Session::setStatus( $statusMsg );
             }
             
         }
         
     }
}   
     