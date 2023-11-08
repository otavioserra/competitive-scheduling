<?php

if( ! class_exists( 'Form' ) ){
    class Form {

        /**
         * Define validation rules for a specific form.
         *
         * @param string $formId form identifier.
         * @param array|null $validation set of all validations with the field and rule required for application.
         *     @param string $rule identifier of the rule that will be implemented when validating the form.
         *     @param string $field name of the form field where the rule will be applied.
         *     @param string $label label of the form field where the rule will be applied.
         *     @param string|null $identifier field identifier if it is necessary to reference a different field name.
         *     @param array|null $removeRule set of all the rules you want to remove from the default rules.
         * 
         * if rule = 'email-comparison'
         * @param array $comparison set of all comparison data.
         *     @param string $id comparison target identifier.
         *     @param string $field-1 field 1 label to show the error if any.
         *     @param string $field-2 field 2 label to show the error if any.
         * 
         * @param array|null $rulesExtra set of all extra rules in addition to the standard ones.
         *     @param string $rule identifier of the rule that will be implemented when validating the form.
         * 
         *     if rule = 'regexPermited'
         *     @param string $regex regex that will be used by the form validator.
         *     @param string $regexPermitedChars allowed characters that will be shown along with the error message.
         * 
         *     if rule = 'regexNecessary'
         *     @param string $regex regex that will be used by the form validator.
         *     @param string $regexNecessaryChars required characters that will be shown along with the error message.
         * 
         *     if rule = 'manual'
         *     @param array $rulesManuals set manually defined rules.
         * 
         * @return void
         */

        public static function validation( $params = false ){
            if( $params ) foreach( $params as $var => $val ) $$var = $val;
            
            // Require templates class to prepare data.
			require_once( CS_PATH . 'includes/class.templates.php' );

            global $_MANAGER;

            if( isset( $validation ) && isset( $formId ) ){
                foreach( $validation as $rule ){
                    switch( $rule['rule'] ){
                        case 'manual':
                            $validation_rules[$rule['field']] = Array(
                                'rules' => $rule['rulesManuals'],
                            );
                        break;
                        case 'required-text':
                            $prompt[1] = Templates::change_variable( self::message( 'validation-empty' ), '#label#', $rule['label'] );
                            $prompt[2] = Templates::change_variable( self::message( 'validation-min-length' ), '#label#', $rule['label'] );
                            $prompt[3] = Templates::change_variable( self::message( 'validation-max-length' ), '#label#', $rule['label'] );

                            $validation_rules[$rule['field']] = Array(
                                'rules' => Array(
                                    Array(
                                        'type' => 'empty',
                                        'prompt' => $prompt[1],
                                    ),
                                    Array(
                                        'type' => 'minLength[3]',
                                        'prompt' => $prompt[2],
                                    ),
                                    Array(
                                        'type' => 'maxLength[100]',
                                        'prompt' => $prompt[3],
                                    ),
                                )
                            );
                        break;
                        case 'required-text-check-field':
                            $prompt[1] = Templates::change_variable( self::message( 'validation-empty' ), '#label#', $rule['label'] );
                            $prompt[2] = Templates::change_variable( self::message( 'validation-min-length' ), '#label#', $rule['label'] );
                            $prompt[3] = Templates::change_variable( self::message( 'validation-max-length' ), '#label#', $rule['label'] );
                            $prompt[4] = Templates::change_variable( self::message( 'validation-verify-field' ), '#label#', $rule['label'] );

                            $validation_rules[$rule['field']] = Array(
                                'rules' => Array(
                                    Array(
                                        'type' => 'empty',
                                        'prompt' => $prompt[1],
                                    ),
                                    Array(
                                        'type' => 'minLength[3]',
                                        'prompt' => $prompt[2],
                                    ),
                                    Array(
                                        'type' => 'maxLength[100]',
                                        'prompt' => $prompt[3],
                                    ),
                                )
                            );
                            
                            if(isset($rule['identifier'])){
                                $validateFields[$rule['identifier']] = Array(
                                    'prompt' => $prompt[4],
                                    'field' => $rule['field'],
                                );
                            } else {
                                $validateFields[$rule['field']] = Array(
                                    'prompt' => $prompt[4],
                                );
                            }
                        break;
                        case 'mandatory-selection':
                            $prompt[1] = Templates::change_variable( self::message( 'validation-select' ), '#label#', $rule['label'] );
                            
                            $validation_rules[$rule['field']] = Array(
                                'rules' => Array(
                                    Array(
                                        'type' => 'empty',
                                        'prompt' => $prompt[1],
                                    ),
                                )
                            );
                        break;
                        case 'non-empty':
                            $prompt[1] = Templates::change_variable( self::message( 'validation-empty' ), '#label#', $rule['label'] );
                            
                            $validation_rules[$rule['field']] = Array(
                                'rules' => Array(
                                    Array(
                                        'type' => 'empty',
                                        'prompt' => $prompt[1],
                                    ),
                                )
                            );
                        break;
                        case 'email':
                            $prompt[1] = Templates::change_variable( self::message( 'validation-empty' ), '#label#', $rule['label'] );
                            $prompt[2] = Templates::change_variable( self::message( 'validation-email' ), '#label#', $rule['label'] );
                            
                            $validation_rules[$rule['field']] = Array(
                                'rules' => Array(
                                    Array(
                                        'type' => 'empty',
                                        'prompt' => $prompt[1],
                                    ),
                                    Array(
                                        'type' => 'email',
                                        'prompt' => $prompt[2],
                                    ),
                                )
                            );
                        break;
                        case 'password':
                            $prompt[1] = Templates::change_variable( self::message( 'validation-empty' ), '#label#', $rule['label'] );
                            $prompt[2] = Templates::change_variable( self::message( 'validation-min-length-password' ), '#label#', $rule['label'] );
                            $prompt[3] = Templates::change_variable( self::message( 'validation-max-length' ), '#label#', $rule['label'] );
                            $prompt[4] = Templates::change_variable( self::message( 'validation-password-chars' ), '#label#', $rule['label'] );
                            
                            $validation_rules[$rule['field']] = Array(
                                'rules' => Array(
                                    Array(
                                        'type' => 'empty',
                                        'prompt' => $prompt[1],
                                    ),
                                    Array(
                                        'type' => 'minLength[12]',
                                        'prompt' => $prompt[2],
                                    ),
                                    Array(
                                        'type' => 'maxLength[100]',
                                        'prompt' => $prompt[3],
                                    ),
                                    Array(
                                        'type' => 'regExp[/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])/]',
                                        'prompt' => $prompt[4],
                                    ),
                                )
                            );
                        break;
                        case 'email-comparison':
                            if( isset( $rule['comparison'] ) ){
                                if( isset( $rule['comparison']['id'] ) && isset( $rule['comparison']['field-1'] ) && isset( $rule['comparison']['field-2'] ) ){
                                    $prompt[1] = Templates::change_variable( self::message( 'validation-empty' ), '#label#', $rule['label'] );
                                    $prompt[2] = Templates::change_variable( self::message( 'validation-email' ), '#label#', $rule['label'] );
                                    $prompt[3] = Templates::change_variable( self::message( 'validation-email-compare' ), '#field-1#', $rule['comparison']['field-1'] );
                                    $prompt[3] = Templates::change_variable( $prompt[3], '#field-2#', $rule['comparison']['field-2'] );

                                    $validation_rules[$rule['field']] = Array(
                                        'rules' => Array(
                                            Array(
                                                'type' => 'empty',
                                                'prompt' => $prompt[1],
                                            ),
                                            Array(
                                                'type' => 'email',
                                                'prompt' => $prompt[2],
                                            ),
                                            Array(
                                                'type' => 'match['.$rule['comparison']['id'].']',
                                                'prompt' => $prompt[3],
                                            ),
                                        )
                                    );
                                }
                            }
                        break;
                        case 'password-comparison':
                            if( isset( $rule['comparison'] ) ){
                                if( isset( $rule['comparison']['id'] ) && isset( $rule['comparison']['field-1'] ) && isset( $rule['comparison']['field-2'] ) ){
                                    $prompt[1] = Templates::change_variable( self::message( 'validation-empty' ), '#label#', $rule['label'] );
                                    $prompt[2] = Templates::change_variable( self::message( 'validation-min-length-password' ), '#label#', $rule['label'] );
                                    $prompt[3] = Templates::change_variable( self::message( 'validation-max-length' ), '#label#', $rule['label'] );
                                    $prompt[4] = Templates::change_variable( self::message( 'validation-email-compare' ), '#field-1#', $rule['comparison']['field-1'] );
                                    $prompt[4] = Templates::change_variable( $prompt[4], '#field-2#', $rule['comparison']['field-2'] );
                                    $prompt[5] = Templates::change_variable( self::message( 'validation-password-chars' ), '#label#', $rule['label'] );

                                    $validation_rules[$rule['field']] = Array(
                                        'rules' => Array(
                                            Array(
                                                'type' => 'empty',
                                                'prompt' => $prompt[1],
                                            ),
                                            Array(
                                                'type' => 'minLength[12]',
                                                'prompt' => $prompt[2],
                                            ),
                                            Array(
                                                'type' => 'maxLength[100]',
                                                'prompt' => $prompt[3],
                                            ),
                                            Array(
                                                'type' => 'match['.$rule['comparison']['id'].']',
                                                'prompt' => $prompt[4],
                                            ),
                                            Array(
                                                'type' => 'regExp[/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])/]',
                                                'prompt' => $prompt[5],
                                            ),
                                        )
                                    );
                                }
                            }
                        break;
                        case 'email-comparison-verify-field':
                            if( isset( $rule['comparison'] ) ){
                                if( isset( $rule['comparison']['id'] ) && isset( $rule['comparison']['field-1'] ) && isset( $rule['comparison']['field-2'] ) ){
                                    $prompt[1] = Templates::change_variable( self::message( 'validation-empty' ), '#label#', $rule['label'] );
                                    $prompt[2] = Templates::change_variable( self::message( 'validation-email' ), '#label#', $rule['label'] );
                                    $prompt[3] = Templates::change_variable( self::message( 'validation-email-compare' ), '#field-1#', $rule['comparison']['field-1'] );
                                    $prompt[3] = Templates::change_variable( $prompt[3], '#field-2#', $rule['comparison']['field-2'] );
                                    $prompt[4] = Templates::change_variable( self::message( 'validation-verify-field' ), '#label#', $rule['label'] );

                                    $validation_rules[$rule['field']] = Array(
                                        'rules' => Array(
                                            Array(
                                                'type' => 'empty',
                                                'prompt' => $prompt[1],
                                            ),
                                            Array(
                                                'type' => 'email',
                                                'prompt' => $prompt[2],
                                            ),
                                            Array(
                                                'type' => 'match['.$rule['comparison']['id'].']',
                                                'prompt' => $prompt[3],
                                            ),
                                        )
                                    );
                                    
                                    if( isset( $rule['identifier'] ) ){
                                        $validateFields[$rule['identifier']] = Array(
                                            'prompt' => $prompt[4],
                                            'field' => $rule['field'],
                                        );
                                    } else {
                                        $validateFields[$rule['field']] = Array(
                                            'prompt' => $prompt[4],
                                        );
                                    }
                                }
                            }
                        break;
                    }
                    
                    if( isset( $rule['rulesExtra'] ) ){
                        $rulesExtra = $rule['rulesExtra'];
                        foreach( $rulesExtra as $ruleExtra ){
                            switch( $ruleExtra['rule'] ){
                                case 'regexPermited':
                                    $prompt[1] = Templates::change_variable( self::message( 'validation-regex-permited-chars' ), '#label#', $rule['label'] );
                                    $prompt[1] = Templates::change_variable( $prompt[1], '#permited-chars#', $ruleExtra['regexPermitedChars'] );
                                    
                                    $validation_rules[$rule['field']]['rules'][] = Array(
                                        'type' => 'regExp['.$ruleExtra['regex'].']',
                                        'prompt' => $prompt[1],
                                    );
                                break;
                                case 'regexNecessary':
                                    $prompt[1] = Templates::change_variable( self::message( 'validation-regex-permited-chars' ), '#label#', $rule['label'] );
                                    $prompt[1] = Templates::change_variable( $prompt[1], '#necessary-chars#', $ruleExtra['regexNecessaryChars'] );

                                    $validation_rules[$rule['field']]['rules'][] = Array(
                                        'type' => 'regExp['.$ruleExtra['regex'].']',
                                        'prompt' => $prompt[1],
                                    );
                                break;
                            }
                        }
                    }
                    
                    if( isset( $rule['removeRule'] ) ){
                        $rules = $validation_rules[$rule['field']]['rules'];
                        unset( $rulesAux );
                        
                        foreach( $rules as $rule ){
                            foreach( $rule['removeRule'] as $removeRule ){
                                if( $rule['type'] == $removeRule ){
                                    $removedRule = true;
                                    break;
                                }
                            }
                            
                            if( ! $removedRule ){
                                $rulesAux[] = $rule;
                            }
                        }
                        
                        if( isset( $rulesAux ) ){
                            $validation_rules[$rule['field']]['rules'] = $rulesAux;
                        }
                    }
                    
                    if( isset( $rule['identifier'] ) ){
                        $validation_rules[$rule['field']]['identifier'] = $rule['identifier'];
                    }
                }
                
                // Include validation rules in javascript.
                if( isset( $validation_rules ) ){
                    if( ! isset( $_MANAGER['javascript-vars']['form'] ) ){
                        $_MANAGER['javascript-vars']['form'] = Array();
                    }
                
                    $_MANAGER['javascript-vars']['form'][$formId]['validationRules'] = $validation_rules;
                }
                
                if( isset( $validateFields ) ){
                    if( ! isset( $_MANAGER['javascript-vars']['form'] ) ){
                        $_MANAGER['javascript-vars']['form'] = Array();
                    }
                
                    $_MANAGER['javascript-vars']['form']['validateFields'] = $validateFields;
                }
                
                // Include module JS.
                self::include_js();
            }
        }

        /**
         * Return standard problem information messages with the form
         *
         * @param string $id message identifier.
         * 
         * @return string
         */

        public static function message( $id ){
            $messages = [
                'validation-empty' => __( 'It is mandatory to fill in the field <b>#label#</b>.', 'competitive-scheduling' ),
                'validation-min-length' => __( 'The <b>#label#</b> field must have at least 3 characters.', 'competitive-scheduling' ),
                'validation-max-length' => __( 'The <b>#label#</b> field must have a maximum of 100 characters.', 'competitive-scheduling' ),
                'validation-verify-field' => __( 'There is already a registration for the field <b>#label#</b> with the same value filled in. Please choose another <b>#label#</b> and try again.', 'competitive-scheduling' ),
                'validation-select' => __( 'It is mandatory to select at least one option from the <b>#label#</b> field.', 'competitive-scheduling' ),
                'validation-email' => __( 'It is mandatory to define a valid email in the <b>#label#</b> field.', 'competitive-scheduling' ),
                'validation-min-length-password' => __( 'The <b>#label#</b> field must have at least 12 characters.', 'competitive-scheduling' ),
                'validation-password-chars' => __( 'The <b>#label#</b> field must have at least <b>one lowercase character</b>, at least <b>one uppercase character</b>, at least <b>one number</b > and at least one special character equal to <b>!@#$%^&*</b>.', 'competitive-scheduling' ),
                'validation-email-compare' => __( 'The field <b>#field-1#</b> is different from the field <b>#field-2#</b>. It is mandatory that both have the same value.', 'competitive-scheduling' ),
                'validation-min-length-password' => __( 'The <b>#label#</b> field must have at least 12 characters.', 'competitive-scheduling' ),
                'validation-regex-permited-chars' => __( 'Only the following characters are allowed in the <b>#label#</b> field: <b>#permited-chars#</b>. Please remove invalid characters and try again.', 'competitive-scheduling' ),
                'validation-regex-necessary-chars' => __( 'It is necessary to define the following characters in the <b>#label#</b> field: <b>#necessary-chars#</b>. Please add the characters and try again.', 'competitive-scheduling' ),
            ];

            return ( ! empty( $messages[$id] ) ? $messages[$id] : '' );
        }
        
        /**
         * Includes the js code that controls form validations.
         *
         * @return void
         */

        public static function include_js(){
            
        }
        
    }
}