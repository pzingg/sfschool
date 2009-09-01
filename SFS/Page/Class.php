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

class SFS_Page_Class extends CRM_Core_Page {
    private static $_actionLinks;

function &actionLinks()
    {
        // check if variable _actionsLinks is populated
        if (!isset(self::$_actionLinks)) {
           
            self::$_actionLinks = array(
                                        CRM_Core_Action::UPDATE  => array(
                                                                          'name'  => ts('Edit'),
                                                                          'url'   => CRM_Utils_System::currentPath( ),
                                                                          'qs'    => 'reset=1&action=update&id=%%id%%',
                                                                          'title' => ts('Configure') 
                                                                          ),
                                        
                                        CRM_Core_Action::DISABLE => array(
                                                                          'name'  => ts('Disable'),
                                                                          'url'   => CRM_Utils_System::currentPath( ),
                                                                          'qs'    => 'reset=1&action=disable&id=%%id%%',
                                                                          'title' => ts('Disable'),
                                                                          ),
                                        CRM_Core_Action::ENABLE  => array(
                                                                          'name'  => ts('Enable'),
                                                                          'url'   => CRM_Utils_System::currentPath( ),
                                                                          'qs'    => 'reset=1&action=enable&id=%%id%%',
                                                                          'title' => ts('Enable'),
                                                                          ),
                                        );
        }
        return self::$_actionLinks;
    }

    function run( ) {

        $action = CRM_Utils_Request::retrieve('action', 'String',
                                              $this, false, 0 ); // default to 'browse'
        // assign vars to templates
        $this->assign('action', $action);
        $id = CRM_Utils_Request::retrieve('id', 'Positive',
                                          $this, false, 0);
        // set breadcrumb
        $breadCrumb = array( array('title' => ts('Class Information'),
                                   'url'   => CRM_Utils_System::url( CRM_Utils_System::currentPath( ), 
                                                                     'reset=1' )) );
        if ( $action ) {
            $validAction = false;
            CRM_Utils_System::appendBreadCrumb( $breadCrumb );
            $session =& CRM_Core_Session::singleton();
            if ($action & CRM_Core_Action::UPDATE ) {
                $session->pushUserContext( CRM_Utils_System::url( CRM_Utils_System::currentPath( ), 'reset=1&action=update' ) );  
                CRM_Utils_System::setTitle( ts('Edit Class') );
                $validAction = true;
            } else if ($action & CRM_Core_Action::DISABLE ) { 
                $session->pushUserContext( CRM_Utils_System::url( CRM_Utils_System::currentPath( ), 'reset=1&action=disable' ) );
                CRM_Utils_System::setTitle( ts('Disable Class') );
                $validAction = true;
            } else if ($action & CRM_Core_Action::ENABLE ) { 
                $session->pushUserContext( CRM_Utils_System::url( CRM_Utils_System::currentPath( ), 'reset=1&action=enable' ) );
                CRM_Utils_System::setTitle( ts('Enable Class') );
                $validAction = true;
            }
            
            if ( $validAction ) {
                $controller =& new CRM_Core_Controller_Simple( 'SFS_Form_Class' ,'Configure Class');
                $controller->set('id', $id);
                $controller->process( );
                return $controller->run( );
            }
        } else {
            $this->browse();
            CRM_Utils_System::setTitle( ts('Class') );
        }
        parent::run( );
    }

    function browse($action=null)
    { 
        $this->assign( 'editClass', false );
        $permission = false;
        if( CRM_Core_Permission::check( 'Administer Extended Care Information' ) ) {
            $this->assign( 'editClass', true );
            $permission = true;
        }
        
        require_once 'SFS/Utils/ExtendedCare.php';
        $activities =  array( );
        $activities =& SFS_Utils_ExtendedCare::getActivities( null,
                                                              CRM_Core_DAO::$_nullObject );
        $actionEnable  -= ( CRM_Core_Action::ENABLE + 1 );
        $values  = array( );
        foreach ( $activities as $day => &$dayValues ) {
            $values[$day] = array( );
            foreach ( $dayValues as $session => &$sessionValues ) {
                foreach ( $sessionValues['details'] as $id => &$idValues ) {
                    if( $permission ) {
                        $idValues['action' ] = CRM_Core_Action::formLink(self::actionLinks(),$actionEnable, 
                                                                         array('id' =>$idValues['index'] ));
                    }
                    $idValues['session'] = $idValues['session'] == 'First' ? '3:30 pm - 4:30 pm' : "4:30 pm - 5:30 pm";
                    $values[$day][] =& $idValues;
                }
            }
        }
        
        $this->assign( 'schedule', $values );
        
        if( $permission ) {
            $disableActivities = array( );
            $disableActivities =& SFS_Utils_ExtendedCare::getActivities( null,
                                                                         CRM_Core_DAO::$_nullObject ,
                                                                         false );
            $actionDisable  -= ( CRM_Core_Action::DISABLE + 1 );
            $disable = array( );
            foreach ( $disableActivities as $day => $valueDay ) {
                $values[$day] = array( );
                foreach ( $valueDay as $session => $valueSession ) {
                    foreach ( $valueSession['details'] as $id => $valueId ) {
                        $valueId['action' ] = CRM_Core_Action::formLink(self::actionLinks(),$actionDisable, 
                                                                        array('id' =>$valueId['index'] ));
                        $valueId['session'] = $valueId['session'] == 'First' ? '3:30 pm - 4:30 pm' : "4:30 pm - 5:30 pm";
                        $disable[$day][] = $valueId;
                    }
                }
            }
            if( !empty( $disable ) ) {
                $this->assign('disableActivities', $disable );
            }
        }

    }
}
