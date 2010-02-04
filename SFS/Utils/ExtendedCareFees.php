<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
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

class SFS_Utils_ExtendedCareFees {
    
    static function &feeDetails( $startDate,
                                 $endDate,
                                 $feeType           = null,
                                 $onlyIndexedTution = false,
                                 $includeDetails    = true,
                                 $studentID         = null,
                                 $catagory          = null,
                                 $limit             = null ) {

        $clauses = array( );
        $params  = array( );
        $count   = 1;
        if ( $studentID ) {
            $clauses[] = "c.id = %{$count}";
            $params[$count++] = array( $studentID, 'Integer' );
        }

        if ( $feeType ) {
            $clauses[] = "f.fee_type = %{$count}";
            $params[$count++] = array( $feeType, 'String' );
        }
        
        if( $catagory ) {
            $clauses[] = "f.category = %{$count}";
            $params[$count++] = array( $catagory, 'String' );
            
        }
        
        if ( $onlyIndexedTution ) {
            $clauses[] = "( f.eligible_for_indexed_tuition == 1 )";
        }
        
        $clause = null;
        if ( $clauses ) {
            $clause = ' AND ' . implode( ' AND ', $clauses );
        }

        $countPlusOne = $count + 1;
        $sql = "
SELECT     c.display_name, f.*
FROM       civicrm_value_extended_care_fee_tracker f
INNER JOIN civicrm_contact c ON c.id = f.entity_id
WHERE      DATE( f.fee_date ) >= %{$count}
AND        DATE( f.fee_date ) <= %{$countPlusOne}
           $clause
ORDER BY   f.fee_date, f.fee_type
";
        
        if( $limit ) {
            $sql .= " LIMIT 0, {$limit}";
        }
        $params[$count]        = array( $startDate, 'Date' );
        $params[$countPlusOne] = array( $endDate  , 'Date' );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );

        $summary = array( );
        while ( $dao->fetch( ) ) {
            $studentID = $dao->entity_id;
            if ( ! array_key_exists( $studentID, $summary ) ) {
                $summary[$studentID] = array( 'id'             => $studentID,
                                              'name'           => $dao->display_name,
                                              'payments' => 0,
                                              'charges'  => 0,
                                              'refunds'  => 0 );
                if ( $includeDetails ) {
                    $summary[$studentID]['details'] = array( );
                }
            }

            if ( $includeDetails ) {
                $summary[$studentID]['details'][$dao->id] = array( 'fee_type'     => $dao->fee_type,
                                                                   'description'  => $dao->description,
                                                                   'category'     => $dao->category,
                                                                   'fee_date'     => strftime( "%a, %b %d",
                                                                                               CRM_Utils_Date::unixTime( $dao->fee_date ) ),
                                                                   'total_blocks' => $dao->total_blocks,
                                                                   'eligible_it'  => $dao->eligible_for_indexed_tuition );
            }

            switch ( $dao->fee_type ) {
            case 'Payment':
                $summary[$studentID]['payments'] += $dao->total_blocks;
                break;
            case 'Charge':
                $summary[$studentID]['charges']  += $dao->total_blocks;
                break;
            case 'Charge Back':
                $summary[$studentID]['refunds']  += $dao->total_blocks;
                break;
            }
        }
        return $summary;
    }

}
