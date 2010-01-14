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

require_once 'CRM/Core/Page.php';

class SFS_Page_ExtendedCare extends CRM_Core_Page {

    private static $_actionLinks;

    function &actionLinks()
    {
        // check if variable _actionsLinks is populated
        if (!isset(self::$_actionLinks)) {
           
            self::$_actionLinks = array(
                                        CRM_Core_Action::UPDATE  => array(
                                                                          'name'  => ts('Edit'),
                                                                          'url'   => CRM_Utils_System::currentPath( ),
                                                                          'qs'    => 'reset=1&action=update&objectID=%%objectID%%&id=%%id%%&object=%%object%%',
                                                                          'title' => ts('Update') 
                                                                          ),
                                        
                                        CRM_Core_Action::DELETE => array(
                                                                          'name'  => ts('Delete'),
                                                                          'url'   => CRM_Utils_System::currentPath( ),
                                                                          'qs'    => 'reset=1&action=delete&objectID=%%objectID%%&id=%%id%%&object=%%object%%',
                                                                          'title' => ts('Delete'),
                                                                          ),
                                        );
        }
        return self::$_actionLinks;
    }

    function run( ) {
        $id = CRM_Utils_Request::retrieve( 'id',
                                           'Integer',
                                           $this,
                                           true );

        $action = CRM_Utils_Request::retrieve('action', 'String',
                                              $this, false, 'browse' ); 
        $this->assign('action', $action);

        $currentYear  = date( 'Y' );
        $currentMonth = date( 'm' );
        if ( $currentMonth < 9 ) {
            $currentYear--;
        }
        
        // for this year ONLY lets start from december
        $currentMonth = ( $currentYear == 2009 ) ? '12' : '09';
        $startDate = CRM_Utils_Request::retrieve( 'startDate',
                                                  'String',
                                                  $this,
                                                  false,
                                                  "{$currentYear}{$currentMonth}01" );

        $endDate = CRM_Utils_Request::retrieve( 'endDate',
                                                'String',
                                                $this,
                                                false,
                                                date( 'Ymd' ) );


        $this->assign( 'displayName',
                       CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact',
                                                    $id,
                                                    'display_name' ) );

        if ( ( $action && array_key_exists( $action, self::actionLinks( ) ) ) || 
             ( $action & CRM_Core_Action::ADD ) ) {
            
            $addcurrentPath = "reset=1&id={$id}";
            isset( $startDate )? $addcurrentPath .= "&startDate={$startDate}" : null;
            isset( $endDate )? $addcurrentPath .= "&endDate={$endDate}" : null;

            // set breadcrumb
            $breadCrumb = array( array('title' => ts('Browse Activities'),
                                       'url'   => CRM_Utils_System::url( CRM_Utils_System::currentPath( ), $addcurrentPath )) );
            
            CRM_Utils_System::appendBreadCrumb( $breadCrumb );
            CRM_Utils_System::setTitle( ts('Configure Activity block') );
            $session =& CRM_Core_Session::singleton();
            $session->pushUserContext( CRM_Utils_System::url( CRM_Utils_System::currentPath( ), $addcurrentPath ) );
            $controller =& new CRM_Core_Controller_Simple( 'SFS_Form_ExtendedCare' ,'Edit Activity block');
            $controller->process( );
            return $controller->run( );
        } else {
            require_once 'SFS/Utils/ExtendedCare.php';
            $details = SFS_Utils_ExtendedCare::signoutDetails( $startDate,
                                                               $endDate,
                                                               true,
                                                               true,
                                                               false,
                                                               $id );
            
            $signoutDetails = array_pop( $details );

            require_once 'SFS/Utils/ExtendedCareFees.php';
            $details = SFS_Utils_ExtendedCareFees::feeDetails( $startDate,
                                                               $endDate,
                                                               null,
                                                               false,
                                                               $id );
            $feeDetails = array_pop( $details );

            $actionPermission = false;
            
            if( CRM_Core_Permission::check( 'access CiviCRM' ) && CRM_Core_Permission::check( 'administer CiviCRM' ) ) {
                $actionPermission = true;
            }
           
            $this->assign( 'enableActions', $actionPermission );
            
            if ( ! empty( $signoutDetails ) && $actionPermission ) {
                foreach( $signoutDetails['details'] as $key => $value ) {
                    $signoutDetails['details'][$key]['action'] = CRM_Core_Action::formLink( self::actionLinks(),
                                                                                            null, 
                                                                                            array( 'objectID' => $key,
                                                                                                   'id'       => $id ,
                                                                                                   'object'   => 'signout' ) );
                }
            }
            $this->assign_by_ref( 'signoutDetail', $signoutDetails );

            if ( ! empty( $feeDetails ) && $actionPermission ) {
                foreach( $feeDetails['details'] as $key => $value ) {
                    $feeDetails['details'][$key]['action'] = CRM_Core_Action::formLink( self::actionLinks(),
                                                                                        null, 
                                                                                        array( 'objectID' => $key,
                                                                                               'id'       => $id ,
                                                                                               'object'   => 'fee' ) );
                }
            }
            $this->assign_by_ref( 'feeDetail', $feeDetails );

            if( $actionPermission ) {
                $addBlockUrl = CRM_Utils_System::url( CRM_Utils_System::currentPath( ),"reset=1&id={$id}&action=add");
                $this->assign( 'addActivityBlock', $addBlockUrl);
            }
            
        }
        
        parent::run( );
    }


   
}
