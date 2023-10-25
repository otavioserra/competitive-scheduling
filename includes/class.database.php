<?php

if( !class_exists( 'Database' ) ){
    class Database {
        public static function update_database() {
            /*
                template database scheme
                    
                    'tableName' => 
                        'SQL_CODE'
                    ,
            */

            // Database scheme.
            $dataBase = Array(
                'tables' => Array(
                    'schedules_dates' => 
                        'CREATE TABLE IF NOT EXISTS `#prefix#schedules_dates` (
                            `id_schedules_dates` INT NOT NULL AUTO_INCREMENT,
                            `date` DATE NULL,
                            `total` INT NULL,
                            `status` VARCHAR(255) NULL,
                            PRIMARY KEY (`id_schedules_dates`))
                        ENGINE = InnoDB'
                    ,
                    'schedules' => 
                        'CREATE TABLE IF NOT EXISTS `#prefix#schedules` (
                            `id_schedules` INT NOT NULL AUTO_INCREMENT,
                            `user_id` INT NULL,
                            `date` DATE NULL,
                            `companions` INT NULL,
                            `password` VARCHAR(100) NULL,
                            `status` VARCHAR(100) NULL,
                            `pubID` VARCHAR(255) NULL,
                            `token` VARCHAR(255) NULL,
                            `version` INT NULL,
                            `date_creation` DATETIME NULL,
                            `modification_date` DATETIME NULL,
                            PRIMARY KEY (`id_schedules`))
                        ENGINE = InnoDB'
                    ,
                    'schedules_companions' => 
                    'CREATE TABLE IF NOT EXISTS `#prefix#schedules_companions` (
                            `id_schedules_companions` INT NOT NULL AUTO_INCREMENT,
                            `id_schedules` INT NULL,
                            `user_id` INT NULL,
                            `name` VARCHAR(255) NULL,
                            PRIMARY KEY (`id_schedules_companions`))
                        ENGINE = InnoDB'
                    ,
                    'coupons_priority' => 
                    'CREATE TABLE IF NOT EXISTS `#prefix#coupons_priority` (
                            `id_coupons_priority` INT NOT NULL AUTO_INCREMENT,
                            `post_id` INT NULL,
                            `id_schedules` INT NULL,
                            `coupon` VARCHAR(255) NULL,
                            PRIMARY KEY (`id_coupons_priority`))
                        ENGINE = InnoDB'
                    ,
                    'schedules_weights' => 
                    'CREATE TABLE IF NOT EXISTS `#prefix#schedules_weights` (
                            `id_schedules_weights` INT NOT NULL AUTO_INCREMENT,
                            `user_id` INT NULL,
                            `weight` INT NULL,
                            PRIMARY KEY (`id_schedules_weights`))
                        ENGINE = InnoDB'
                    ,
                )
            );

            // Require templates class to manipulate page.
            require_once( CS_PATH . 'includes/class.templates.php' );

            global $wpdb;
            
            // Scan all tables.
            if( isset( $dataBase ) )
            foreach( $dataBase['tables'] as $table => $sql){
                // Create table if it does not exist, otherwise update fields.
                $table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}{$table}'" );

                if( ! $table_exists ){
                    // Create table
                    $sql = Templates::change_variable( $sql, '#prefix#', $wpdb->prefix );
                    $wpdb->query( $sql );
                } else {
                    // Get all fields from the table.
                    $fields = $wpdb->get_col( "SHOW COLUMNS FROM '{$wpdb->prefix}{$table}'" );

                    // Scan all SQL rows.
                    $alterTableAfter = '';
                    foreach( preg_split( "/((\r?\n)|(\r\n?))/", $sql ) as $lineSQL ){
                        $lineSQL = trim( $lineSQL );
                        $line_arr = explode( ' ', $lineSQL );
                        
                        if( $line_arr[0] ){
                            switch( $line_arr[0] ){
                                case 'CREATE':
                                case 'ENGINE':
                                case 'PRIMARY':
                                    // Ignore lineSQL
                                break;
                                default:
                                    // If you find the pattern `fieldName`. Checks if all fields exist.
                                    preg_match( '/`.*?`/', $lineSQL, $matches );
                                    
                                    if( $matches[0] ){
                                        $field = ltrim( rtrim( $matches[0], "`" ), "`" );
                                        
                                        $foundField = false;
                                        if( isset( $fields ) )
                                        foreach( $fields as $fieldDB ){
                                            if( $field == $fieldDB ){
                                                $foundField = true;
                                                break;
                                            }
                                        }
                                        
                                        // If it doesn't find a field, it changes the table and includes the line.
                                        if( ! $foundField ){
                                            $fieldData = rtrim( $lineSQL, "," );
                                            $wpdb->query( 'ALTER TABLE `'.$wpdb->prefix.$table.'` ADD '.$fieldData . $alterTableAfter );
                                        }
                                        
                                        // After to put it in the correct sequence that comes from SQL.
                                        $alterTableAfter = ' AFTER `'.$field.'`';
                                    }
                                    
                            }
                        }
                    }
                }
            }
        }
    }
}