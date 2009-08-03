<?php

$fdRead  = fopen( 'SFS_0910.csv', 'r' );
$fdWrite = fopen( 'SFS_0910_FIX.csv', 'w' );

if ( ! $fdRead ) {
    echo "Could not read file\n";
    exit( );
 }

// read first line
$fields = fgetcsv( $fdRead );

array_splice( $fields,  4, 0, array( 'Contact SubType' ) );
array_splice( $fields,  7, 0, array( 'Contact SubType' ) );
array_splice( $fields, 10, 0, array( 'Contact SubType' ) );

fputcsv( $fdWrite, $fields );

$fixFields = array( 4, 5, 6, 7 );

$validSIDs = array( 100030, 100033, 201820, 201513, 100019, 201610, 201421, 201121, 201811, 201722 );

while ( $fields = fgetcsv( $fdRead ) ) {
    // print_r( $fields );

    if ( $fields[2] == 0 ) {
        $fields[2] = 'K N';
    } else if ( $fields[2] == -1 ) {
        $fields[2] = 'PK4 N';
    } else if ( $fields[2] == -2 ) {
        $fields[2] = 'PK3 N';
    }

    foreach ( $fixFields as $fix ) {
        if ( empty( $fields[$fix] ) ) {
            continue;
        }
        $name = explode( ',', $fields[$fix] );

        if ( $fix == 4 || $fix == 6 ) {
            $fields[$fix] = trim( $name[1] );
        } else {
            $fields[$fix] = trim( $name[0] );
        }
    }

    // also add Student and Parent sub type fields
    array_splice( $fields, 4, 0, array( 'Student' ) );
    if ( ! empty( $fields[5] ) ) {
        array_splice( $fields, 7, 0, array( 'Parent' ) );
    } else {
        array_splice( $fields, 7, 0, array( '' ) );
    }

    if ( ! empty( $fields[8] ) ) {
        array_splice( $fields, 10, 0, array( 'Parent' ) );
    } else {
        array_splice( $fields, 10, 0, array( '' ) );
    }

    // scramble the fields
    if ( in_array( $fields[3], $validSIDs ) === false ) {
        scrambleFields( $fields );
    }

    fputcsv( $fdWrite, $fields );
    // print_r( $fields );
    // exit( );
}


fclose( $fdRead  );
fclose( $fdWrite );

function scrambleFields( &$fields ) {
    static $skipFields = array( 2, 3, 4, 7, 10 );
    for ( $i = 0; $i <= 13; $i++ ) {
        if ( in_array( $i, $skipFields ) !== false || empty( $fields[$i] ) ) {
            continue;
        } else if ( $i < 12 ) {
            $fields[$i] = substr( md5( $fields[$i] ), 0, 16 );
        } else {
            // email address
            $fields[$i] = substr( md5( $fields[$i] ), 0, 16 ) . '@example.com';
        }
    }
}
