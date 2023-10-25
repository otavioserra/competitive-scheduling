<?php

if( !class_exists( 'Database' ) ){
    class Database {
        public static function create_tables() {
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
                )
            );

            // Require templates class to manipulate page.
            require_once( CS_PATH . 'includes/class.templates.php' );

            // Check if tables already exist or install the tables.
            global $wpdb;
            $tables = $dataBase['tables'];

            foreach ( $tables as $table_name => $table_sql ) {
                $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}{$table_name}'");

                if ( !$table_exists ) {
                    // Create table
                    $table_sql = Templates::change_variable($table_sql, '#prefix#', $wpdb->prefix);
                    $wpdb->query($table_sql);
                }
            }
        }
    }
}