<?php

if( ! class_exists( 'Formats' ) ){
    class Formats {

        /**
         * Change the format of the value based on type.
         *
         * @param string $value value of the date to be formatted.
         * @param string $type formatting type.
         *
         * @return string
         */

        public static function data_format_to( $type, $value ){
            if( isset( $value ) && isset( $type ) ){
                $value = self::data_format( array(
                    'value' => $value,
                    'type' => $type,
                ) );
                
                return $value;
            }
            
            return '';
        }

        /**
         * Change the format of the value based on type.
         *
         * @param string $value value of the date to be formatted.
         * @param string $type formatting type.
         *
         * @return string
         */
        
        public static function data_format( $params = false ){
            if( $params ) foreach( $params as $var => $val ) $$var = $val;
            
            if( isset( $value ) && isset( $type ) ){
                switch( $type ){
                    case 'float-to-text': $value = self::float_format_to_text($value); break;
                    case 'text-to-float': $value = self::text_format_to_float($value); break;
                    case 'int-to-text': $value = self::int_format_to_text($value); break;
                    case 'text-to-int': $value = self::format_text_to_int($value); break;
                    case 'date-to-text': $value = self::date_format_from_datetime_to_text($value); break;
                    case 'datetime-to-text': $value = self::format_date_time_from_datetime_to_text($value); break;
                    case 'text-to-datetime': $value = self::format_date_time_default_datetime($value); break;
                    case 'text-to-date': $value = self::format_date_time_default_datetime($value,true); break;
                }
                
                return $value;
            }
            
            return '';
        }

        public static function format_date_time_array($date_time_default_datetime_or_default_date){
            $date_time = explode(" ",$date_time_default_datetime_or_default_date);
            
            if(count($date_time) > 1){
                $date_aux = explode("-",$date_time[0]);
                $hour_aux = explode(":",$date_time[1]);
                
                $date_time_array = Array(
                    'day' => $date_aux[2],
                    'mon' => $date_aux[1],
                    'year' => $date_aux[0],
                    'hour' => $hour_aux[0],
                    'min' => $hour_aux[1],
                    'sec' => $hour_aux[2],
                );
            } else {
                $date_aux = explode("-",$date_time[0]);
                
                $date_time_array = Array(
                    'day' => $date_aux[2],
                    'mon' => $date_aux[1],
                    'year' => $date_aux[0],
                );
            }
            
            return $date_time_array;
        }
        
        public static function format_date_time_default_datetime($datetimeText, $withoutHour = false){
            $datetimeTextArray = explode(" ",$datetimeText);
            $dateArray = explode("/",$datetimeTextArray[0]);
            $datetime = $dateArray[2]."-".$dateArray[1]."-".$dateArray[0].($withoutHour ? '' : " ".$datetimeTextArray[1].":00");
            
            return $datetime;
        }
        
        public static function format_date_time_from_datetime_to_text($date_time, $format = false){
            $defaultFormat = 'D/ME/A HhMI';
            
            if($date_time){
                $date_time = explode(" ",$date_time);
                $date_aux = explode("-",$date_time[0]);
                
                if($format){
                    $hour_aux = explode(":",$date_time[1]);
                    $format = preg_replace('/D/', $date_aux[2], $format);
                    $format = preg_replace('/ME/', $date_aux[1], $format);
                    $format = preg_replace('/A/', $date_aux[0], $format);
                    $format = preg_replace('/H/', $hour_aux[0], $format);
                    $format = preg_replace('/MI/', $hour_aux[1], $format);
                    $format = preg_replace('/S/', $hour_aux[2], $format);
                    
                    return $format;
                } else if($defaultFormat){
                    $format = $defaultFormat;
                    $hour_aux = explode(":",$date_time[1]);
                    $format = preg_replace('/D/', $date_aux[2], $format);
                    $format = preg_replace('/ME/', $date_aux[1], $format);
                    $format = preg_replace('/A/', $date_aux[0], $format);
                    $format = preg_replace('/H/', $hour_aux[0], $format);
                    $format = preg_replace('/MI/', $hour_aux[1], $format);
                    $format = preg_replace('/S/', $hour_aux[2], $format);
                    
                    return $format;
                } else {
                    $date = $date_aux[2] . "/" . $date_aux[1] . "/" .$date_aux[0];
                    $hour = $date_time[1];
                    
                    return $date . " " . $hour;
                }
            } else {
                return "";
            }
        }
        
        public static function date_format_from_datetime_to_text($date_time){
            $date_time = explode(" ",$date_time);
            $date_aux = explode("-",$date_time[0]);
            $date = $date_aux[2] . "/" . $date_aux[1] . "/" .$date_aux[0];
            
            return $date;
        }
        
        public static function float_format_to_text($float,$noDecimal = false){
            // Format 00.000,00
            
            return number_format((float)$float, 2, ',', '.');
        }
        
        public static function text_format_to_float($text){
            // Format 00.000,00
            
            $num_1_2 = explode(",",$text);
            
            if($num_1_2){
                $num_aux = explode(".",$num_1_2[0]);
                $num_1 = '';
                
                if($num_aux){
                    for($i=0;$i<count($num_aux);$i++){
                        $num_1 .= $num_aux[$i];
                    }
                } else
                    $num_1 = $num_1_2[0];
                
                $num_2 = $num_1_2[1];
                
                return ($num_1 . "." . $num_2);
            } else
                return $text;
        }
        
        public static function int_format_to_text($int){
            // Format 00.000.000
            
            return number_format((float)$int, 0, '', '.');
        }
        
        public static function format_text_to_int($text){
            // Format 00.000.000
            
            return str_replace(".", "", $text);
        }
        
        public static function format_zero_to_the_left($num,$dig){
            $len = strlen((string)$num);
            
            if($len < $dig){
                $num2 = $num;
                
                for($i=0;$i<$dig - $len;$i++){
                    $num2 = '0'.$num2;
                }
                
                return $num2;
            } else {
                return $num;
            }
        }
        
        public static function format_put_char_half_number($num,$char = '-'){
            $len = strlen((string)$num);
            
            $numArr = str_split($num, floor($len/2));
            $numFinal = '';
            
            foreach($numArr as $n){
                $numFinal .= $n . (!isset($charPlaced) ? $char : '');
                $charPlaced = true;
            }
            
            return $numFinal;
        }

    }
}