<?php

if( !class_exists( 'Templates' ) ){
    class Templates {

        /**
         * Change the first occurence of a variable in a template with the value passed.
         *
         * @param string $template The template content.
         * @param string $variable The variable name.
         * @param string $value The value to be change.
         *
         * @return string
         */

        public static function change_variable( $template = '', $variable = '', $value = '' ){
            $startPos = strpos($template, $variable);
            
            $notFound = false;
            if($startPos === false)
                $notFound = true;
            
            if(!$notFound){
                $endPos = $startPos+strlen($variable);
                
                $previousPart = substr($template,0,$startPos);
                $backPart = substr($template,$endPos,(strlen($template)-$endPos));
                
                $template = $previousPart . $value . $backPart;
            }
            
            return $template;
        }

        /**
         * Change the all occurences of a variable in a template with the value passed.
         *
         * @param string $template The template content.
         * @param string $variable The variable name.
         * @param string $value The value to be change.
         *
         * @return string
         */

        public static function change_variable_all( $template = '', $variable = '', $value = '' ){
            return preg_replace('/'.preg_quote($variable).'/i',$value,$template);
        }

        /**
         * Include the value passed after the first occurence of a variable in a template.
         *
         * @param string $template The template content.
         * @param string $variable The variable name.
         * @param string $value The value to be change.
         *
         * @return string
         */

        public static function variable_in( $template = '', $variable = '', $value = '' ){
            $startPos = strpos($template, $variable);
            
            $notFound = false;
            if($startPos === false)
                $notFound = true;
            
            if(!$notFound){
                $endPos = $startPos+strlen($variable);
                
                $previousPart = substr($template,0,$startPos);
                $backPart = substr($template,$endPos,(strlen($template)-$endPos));
                
                $template = $previousPart . $value . $backPart;
                
                $template = $previousPart . $value . $variable . $backPart;
            }
            
            return $template;
        }

        /**
         * Get the value inside the tags in a passed template.
         *
         * @param string $template The template content.
         * @param string $tag_start The opening tag.
         * @param string $tag_end The closing tag.
         *
         * @return string
         */

        public static function tag_value( $template = '', $tag_start = '', $tag_end = '' ){
            $startPos = strpos($template, $tag_start);
            $endPos = strpos($template, $tag_end);
            
            $notFound = false;
            if($startPos === false || $endPos === false)
                $notFound = true;
            
            if(!$notFound){
                $startPos = $startPos+strlen($tag_start);
                $len = $endPos-$startPos;
                
                $value = substr($template,$startPos,$len);
            }
            
            return (isset($value) ? $value : '');
        }

        /**
         * Insert a value inside the tags in a passed template.
         *
         * @param string $template The template content.
         * @param string $tag_start The opening tag.
         * @param string $tag_end The closing tag.
         * @param string $value The value to be inserted.
         *
         * @return string
         */

        public static function tag_in( $template = '', $tag_start = '', $tag_end = '', $value = '' ){
            $startPos = strpos($template, $tag_start);
            $endPos = strpos($template, $tag_end);
            $notFound = false;
            
            if($startPos === false || $endPos === false)
                $notFound = true;
            
            if(!$notFound){
                $endPos = $endPos+strlen($tag_end);
                
                $previousPart = substr($template,0,$startPos);
                $backPart = substr($template,$endPos,(strlen($template)-$endPos));
                
                $template = $previousPart . $value . $backPart;
            }
            
            return $template;
        }

        /**
         * Delete the tags in a passed template with tag's inside content.
         *
         * @param string $template The template content.
         * @param string $tag_start The opening tag.
         * @param string $tag_end The closing tag.
         *
         * @return string
         */

        public static function tag_del( $template = '', $tag_start = '', $tag_end = '' ){
            $startPos = strpos($template, $tag_start);
            $endPos = strpos($template, $tag_end);
            
            $notFound = false;
            if($startPos === false || $endPos === false)
                $notFound = true;
            
            if(!$notFound){
                $endPos = $endPos+strlen($tag_end);
                
                $previousPart = substr($template,0,$startPos);
                $backPart = substr($template,$endPos,(strlen($template)-$endPos));
                
                $template = $previousPart . $backPart;
            }
            
            return $template;
        }

        /**
         * Change a value inside the tags in a passed template.
         *
         * @param string $template The template content.
         * @param string $tag_start The opening tag.
         * @param string $tag_end The closing tag.
         * @param string $value The value to be inserted.
         *
         * @return string
         */

        public static function tag_change_variable( $template = '', $tag_start = '', $tag_end = '', $value = '' ){
            $startPos = strpos($template, $tag_start);
            $endPos = strpos($template, $tag_end);
            
            $notFound = false;
            if($startPos === false || $endPos === false)
                $notFound = true;
            
            if(!$notFound){
                $endPos = $endPos+strlen($tag_end);
                
                $previousPart = substr($template,0,$startPos+strlen($tag_start));
                $backPart = substr($template,($endPos-strlen($tag_end)),(strlen($template)-($endPos-strlen($tag_end))));
                
                $template = $previousPart . $value . $backPart;
            }
            
            return $template;
        }
    }
}