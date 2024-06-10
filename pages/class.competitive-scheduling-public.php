<?php 

if( ! class_exists( 'Competitive_Scheduling_Public' ) ){
    class Competitive_Scheduling_Public {
        public $statusSchedulingIDs = Array(
            'status-confirmed',
            'status-finished',
            'status-unqualified',
            'status-new',
            'status-qualified',
            'status-no-residual-vacancy',
            'status-residual-vacancies',
        );

        public function __construct(){
            add_action( 'admin_post_schedule_cancellation', array( $this, 'cancellation' ) );
            add_action( 'admin_post_schedule_confirmation', array( $this, 'confirmation_page' ) );
        }

        public function cancellation(){
            // Prepare JSs and CSSs
            wp_enqueue_style( 'fomantic-ui', CS_URL . 'vendor/fomantic-UI@2.9.0/dist/semantic.min.css', array(  ), CS_VERSION );
            wp_enqueue_script( 'fomantic-ui', CS_URL . 'vendor/fomantic-UI@2.9.0/dist/semantic.min.js', array( 'jquery' ), CS_VERSION );
            
            wp_enqueue_style( 'competitive-scheduling-public', CS_URL . 'assets/css/public.css', array(  ), ( CS_DEBUG ? filemtime( CS_PATH . 'assets/css/public.css' ) : CS_VERSION ) );
            wp_enqueue_script( 'competitive-scheduling-public', CS_URL . 'assets/js/public.js', array( 'jquery' ), ( CS_DEBUG ? filemtime( CS_PATH . 'assets/js/public.js' ) : CS_VERSION ) );

            // Get page view and return processed page
            ob_start();
            require( CS_PATH . 'views/competitive-scheduling-public.php' );

            echo $this->cancellation_page(ob_get_clean());
        }

        public function confirmation_page(){
            global $_MANAGER;
        }

        private function confirmation( $page ){
            global $_MANAGER;

            // Require formats class to manipulate data.
            require_once( CS_PATH . 'includes/class.formats.php' );

            // Require templates class to manipulate page.
            require_once( CS_PATH . 'includes/class.templates.php' );

            // Require interfaces class to manipulate page.
            require_once( CS_PATH . 'includes/class.interfaces.php' );

            // Get current user id.
            $user_id = get_current_user_id();
            
            // Get the configuration data.
            $options = get_option( 'competitive_scheduling_options' );
            $msg_options = get_option( 'competitive_scheduling_msg_options' );
            
            // Validate the sent schedule_id.
            $id_schedules = ( isset( $_REQUEST['schedule_id'] ) ? sanitize_text_field( $_REQUEST['schedule_id'] ) : '' );
            
            global $wpdb;
            $query = $wpdb->prepare(
                "SELECT date, status 
                FROM {$wpdb->prefix}schedules 
                WHERE id_schedules = '%s' 
                AND user_id = '%s'",
                array( $id_schedules, $user_id )
            );
            $schedules = $wpdb->get_results( $query );

            if( ! $schedules ){
                // Activation of expiredOrNotFound.
                $_MANAGER['javascript-vars']['expiredOrNotFound'] = true;
            } else {
                // Force date to today for debuging or set today's date
                if( CS_FORCE_DATE_TODAY ){ $today = CS_DATE_TODAY_FORCED_VALUE; } else { $today = date('Y-m-d'); }

                // Scheduling data.
                $date = $schedules->date;
                $status = $schedules->status;
                
                // Get the configuration data.
                $draw_phase = ( isset( $options['draw-phase'] ) ? explode(',',$options['draw-phase'] ) : Array(7,5) );
                $residual_phase = ( isset( $options['residual-phase'] ) ? (int)$options['residual-phase'] : 5 );
           
                // Check whether the current status of the schedule allows confirmation.
                if(
                    $status == 'confirmed' ||
                    $status == 'qualified' ||
                    $status == 'email-sent' ||
                    $status == 'email-not-sent'
                ){
                    // Check if you are in the confirmation phase.
                    if(
                        strtotime( $date ) >= strtotime( $today.' + '.($draw_phase[1]+1).' day' ) &&
                        strtotime( $date ) < strtotime( $today.' + '.($draw_phase[0]+1).' day' )
                    ){
                        
                    } else {
                        // Confirmation period dates.
                        $date_confirmation_1 = Formats::data_format_to( 'date-to-text', date( 'Y-m-d', strtotime( $date.' - '.($draw_phase[0]).' day' ) ) );
                        $date_confirmation_2 = Formats::data_format_to( 'date-to-text', date( 'Y-m-d', strtotime( $date.' - '.($draw_phase[1]).' day' ) - 1 ) );
                    
                        // Return the expired schedule message.
                        $msgScheduleExpired = ( ! empty( $msg_options['msg-schedule-expired'] ) ? $msg_options['msg-schedule-expired'] : '' );
                        
                        $msgScheduleExpired = Templates::change_variable( $msgScheduleExpired, '#date_confirmation_1#', $date_confirmation_1 );
                        $msgScheduleExpired = Templates::change_variable( $msgScheduleExpired, '#date_confirmation_2#', $date_confirmation_2 );
                        
                        Interfaces::alert( array(
                            'redirect' => true,
                            'msg' => $msgScheduleExpired
                        ));

                        // Redirects the page to previous schedules.
                        wp_redirect( get_permalink(), 301, array( 'window' => 'previous-schedules' ) );
                    }
                } else {
                    if(
                        strtotime( $today ) >= strtotime( $date.' - '.$residual_phase.' day' ) &&
                        strtotime( $today ) <= strtotime( $date.' - 1 day' )
                    ){
                        
                    } else {
                        Interfaces::alert( array(
                            'redirect' => true,
                            'msg' => 'SCHEDULING_STATUS_NOT_ALLOWED_CONFIRMATION'
                        ));

                        // Redirects the page to previous schedules.
                        wp_redirect( get_permalink(), 301, array( 'window' => 'previous-schedules' ) );
                    }
                }
                
                // Schedule confirmation request.
                if( isset( $_REQUEST['make_confirmation'] ) ){
                    // Pick up the change choice.
                    $choice = ( $_REQUEST['choice'] == 'confirm' ? 'confirm' : 'cancel' );
                    
                    // Make confirmation.
                    $return = $this->change( array(
                        'opcao' => 'confirm',
                        'choice' => $choice,
                        'id_schedules' => $id_schedules,
                        'user_id' => $user_id,
                        'date' => $date,
                    ) );
                    
                    if( ! $return['completed'] ){
                        switch( $return['status'] ){
                            case 'SCHEDULE_NOT_FOUND':
                            case 'SCHEDULE_CONFIRMATION_EXPIRED':
                            case 'SCHEDULE_WITHOUT_VACANCIES':
                                $msgAlert = ( ! empty( $return['error-msg'] ) ? $return['error-msg'] : $return['status'] );
                        break;
                        default:
                            $msgAlert = ( ! empty( $msg_options['msg-alert'] ) ? $msg_options['msg-alert'] : '' );
                            
                            $msgAlert = Templates::change_variable( $msgAlert, '#error-msg#', ( ! empty( $return['error-msg'] ) ? $return['error-msg'] : $return['status'] ) );
                        }
                        
                        // Alert the user if a problem occurs with the problem description message.
                        Interfaces::alert( array(
                            'redirect' => true,
                            'msg' => $msgAlert
                        ));
                    } else {
                        // Returned data.
                        $data = Array();
                        if( isset( $return['data'] ) ){
                            $data = $return['data'];
                        }
                        
                        // Alert the user of change success.
                        Interfaces::alert( array(
                            'redirect' => true,
                            'msg' => $data['alert']
                        ));
                    }
                    
                    // Redirects the page to previous schedules.
                    wp_redirect( get_permalink(), 301, array( 'window' => 'previous-schedules' ) );
                }
                
                // Activation of confirmation.
                $_MANAGER['javascript-vars']['confirm'] = true;
            }

            // Remove the active cell and changes.
            $cell_name = 'active'; $cell[$cell_name] = Formats::tag_value( $page, '<!-- '.$cell_name.' < -->','<!-- '.$cell_name.' > -->' ); $page = Formats::tag_in( $page,'<!-- '.$cell_name.' < -->', '<!-- '.$cell_name.' > -->', '<!-- '.$cell_name.' -->' );
            $cell_name = 'changes'; $cell[$cell_name] = Formats::tag_value( $page, '<!-- '.$cell_name.' < -->','<!-- '.$cell_name.' > -->' ); $page = Formats::tag_in( $page,'<!-- '.$cell_name.' < -->', '<!-- '.$cell_name.' > -->', '<!-- '.$cell_name.' -->' );
            
            // Incluir o token no formulário.
            $page = Templates::change_variable( $page, '[[confirmation-date]]', ( $schedules ? Formats::data_format_to( 'date-to-text', $schedules->date ) : '' ) );
            $page = Templates::change_variable( $page, '[[confirmation-scheduling-id]]', $id_schedules );

            // Finalize interface.
            Interfaces::components_include( array(
                'component' => Array(
                    'modal-loading',
                    'modal-alert',
                )
            ) );
            
            $page = Interfaces::finish( $page );

            return $page;
        }

        private function cancellation_page( $page ){
            global $_MANAGER;

            // Generate the validation token.
            require_once( CS_PATH . 'includes/class.authentication.php' );
            
            $pubID = Authentication::validate_token_validation( array( 'token' => ( ! empty( $_REQUEST['token'] ) ? $_REQUEST['token'] : '' ) ) );

            $pubIDSent = ( ! empty( $_REQUEST['pubID'] ) ? $_REQUEST['pubID'] : '' );

            echo 'pubID: ' . $pubID . ' == ' . $pubIDSent;

            return $page;
            
            // Require formats class to manipulate data.
            require_once( CS_PATH . 'includes/class.formats.php' );

            // Require templates class to manipulate page.
            require_once( CS_PATH . 'includes/class.templates.php' );

            // Require interfaces class to manipulate page.
            require_once( CS_PATH . 'includes/class.interfaces.php' );

            // Get current user id.
            $user_id = get_current_user_id();
            
            // Get the configuration data.
            $options = get_option( 'competitive_scheduling_options' );
            $msg_options = get_option( 'competitive_scheduling_msg_options' );
            
            // Validate the sent schedule_id.
            $id_schedules = ( isset( $_REQUEST['schedule_id'] ) ? sanitize_text_field( $_REQUEST['schedule_id'] ) : '' );

            global $wpdb;
            $query = $wpdb->prepare(
                "SELECT date, status 
                FROM {$wpdb->prefix}schedules 
                WHERE id_schedules = '%s' 
                AND user_id = '%s'",
                array( $id_schedules, $user_id )
            );
            $schedules = $wpdb->get_results( $query );

            if( ! $schedules ){
                // Activation of expiredOrNotFound.
                $_MANAGER['javascript-vars']['expiredOrNotFound'] = true;
            } else {
                // Request for confirmation of cancellation.
                if( isset( $_REQUEST['make_cancel'] ) ){
                    // Make confirmation.
                    $return = $this->change( array(
                        'opcao' => 'cancel',
                        'id_schedules' => $id_schedules,
                        'user_id' => $user_id,
                    ) );
                    
                    if( ! $return['completed'] ){
                        switch( $return['status'] ){
                            case 'SCHEDULE_NOT_FOUND':
                            case 'SCHEDULE_CONFIRMATION_EXPIRED':
                            case 'SCHEDULE_WITHOUT_VACANCIES':
                                $msgAlert = ( ! empty( $return['error-msg'] ) ? $return['error-msg'] : $return['status'] );
                        break;
                        default:
                            $msgAlert = ( ! empty( $msg_options['msg-alert'] ) ? $msg_options['msg-alert'] : '' );
                            
                            $msgAlert = Templates::change_variable( $msgAlert, '#error-msg#', ( ! empty( $return['error-msg'] ) ? $return['error-msg'] : $return['status'] ) );
                        }
                        
                        // Alert the user if a problem occurs with the problem description message.
                        Interfaces::alert( array(
                            'redirect' => true,
                            'msg' => $msgAlert
                        ));
                    } else {
                        // Returned data.
                        $data = Array();
                        if( isset( $return['data'] ) ){
                            $data = $return['data'];
                        }
                        
                        // Alert the user of change success.
                        Interfaces::alert( array(
                            'redirect' => true,
                            'msg' => $data['alert']
                        ));
                    }
                    
                    // Redirects the page to previous schedules.
                    wp_redirect( get_permalink(), 301, array( 'window' => 'previous-schedules' ) );
                }
                
                // Cancellation activation.
                $_MANAGER['javascript-vars']['cancel'] = true;
            }

            // Remove the active cell and changes.
            $cell_name = 'active'; $cell[$cell_name] = Formats::tag_value( $page, '<!-- '.$cell_name.' < -->','<!-- '.$cell_name.' > -->' ); $page = Formats::tag_in( $page,'<!-- '.$cell_name.' < -->', '<!-- '.$cell_name.' > -->', '<!-- '.$cell_name.' -->' );
            $cell_name = 'changes'; $cell[$cell_name] = Formats::tag_value( $page, '<!-- '.$cell_name.' < -->','<!-- '.$cell_name.' > -->' ); $page = Formats::tag_in( $page,'<!-- '.$cell_name.' < -->', '<!-- '.$cell_name.' > -->', '<!-- '.$cell_name.' -->' );
            
            // Incluir o token no formulário.
            $page = Templates::change_variable( $page, '[[cancellation-date]]', ( $schedules ? Formats::data_format_to( 'date-to-text', $schedules->date ) : '' ) );
            $page = Templates::change_variable( $page, '[[cancellation-scheduling-id]]', $id_schedules );

            // Finalize interface.
            Interfaces::components_include( array(
                'component' => Array(
                    'modal-loading',
                    'modal-alert',
                )
            ) );
            
            $page = Interfaces::finish( $page );

            return $page;
        }
        
        private function change( $params = false ){
            if( $params ) foreach( $params as $var => $val ) $$var = $val;

            global $_MANAGER;

            // Get the configuration data.
            $options = get_option( 'competitive_scheduling_options' );
            $msg_options = get_option( 'competitive_scheduling_msg_options' );
            
            switch($opcao){
                case 'confirmPublic':
                    // Decodificar os dados em formato Array
                    
                    $dados = Array();
                    if(isset($_REQUEST['dados'])){
                        $dados = json_decode($_REQUEST['dados'],true);
                    }
                    
                    // Verificar se os campos obrigatórios foram enviados: pubId.
                    
                    if(isset($dados['pubId'])){
                        // Pegar os dados de configuração.
                        
                        gestor_incluir_biblioteca('configuracao');
                        
                        $config = configuracao_hosts_variaveis(Array('modulo' => 'configuracoes-agendamentos'));
                        
                        $msgAgendamentoNaoEncontrado = (existe($config['msg-agendamento-nao-encontrado']) ? $config['msg-agendamento-nao-encontrado'] : '');
                        
                        // Tratar os dados enviados.
                        
                        $pubId = banco_escape_field($dados['pubId']);
                        $choice = $dados['escolha'];
                        
                        // Pegar o agendamento no banco de dados.
                        
                        $hosts_agendamentos = banco_select(Array(
                            'unico' => true,
                            'tabela' => 'hosts_agendamentos',
                            'campos' => Array(
                                'id_hosts_agendamentos',
                                'id_hosts_usuarios',
                                'status',
                                'data',
                            ),
                            'extra' => 
                                "WHERE pubId='".$pubId."'"
                                ." AND id_hosts='".$id_hosts."'"
                        ));
                        
                        // Caso não exista, retorar erro.
                        
                        if(!$hosts_agendamentos){
                            return Array(
                                'status' => 'SCHEDULE_NOT_FOUND',
                                'error-msg' => $msgAgendamentoNaoEncontrado,
                            );
                        }
                        
                        $id_schedules = $schedules->id_hosts_agendamentos;
                        $user_id = $schedules->id_hosts_usuarios;
                        $date = $schedules->date;
                        
                        // Tratar cada escolha: 'confirmar' ou 'cancelar'.
                        
                        switch($choice){
                            case 'confirm':
                                // Dados do agendamento.
                                
                                if($modulo['forcarDataHoje']){ $today = $modulo['dataHojeForcada']; } else { $today = date('Y-m-d'); }
                                
                                // Configuração de fase de sorteio.
                            
                                $draw_phase = (existe($config['fase-sorteio']) ? explode(',',$config['fase-sorteio']) : Array(7,5));
                                
                                // Verificar se o status atual do agendamento permite confirmação.
                                
                                if(
                                    $schedules->status == 'confirmado' ||
                                    $schedules->status == 'qualificado' ||
                                    $schedules->status == 'email-enviado' ||
                                    $schedules->status == 'email-nao-enviado'
                                ){
                                    // Verificar se está na fase de confirmação.
                                    
                                    if(
                                        strtotime($date) >= strtotime($today.' + '.($draw_phase[1]+1).' day') &&
                                        strtotime($date) < strtotime($today.' + '.($draw_phase[0]+1).' day')
                                    ){
                                        // Caso não tenha sido confirmado anteriormente, confirmar o agendamento.
                                        
                                        $return = $this->schedule_confirm( array(
                                            'id_hosts' => $id_hosts,
                                            'id_hosts_agendamentos' => $id_schedules,
                                            'id_hosts_usuarios' => $user_id,
                                            'data' => $date,
                                        ) );
                                        
                                        // Verificar se a confirmação ocorreu corretamente.
                                        
                                        if(!$return['confirmado']){
                                            return Array(
                                                'status' => $return['status'],
                                                'error-msg' => $return['alert'],
                                            );
                                        } else {
                                            // Alerta de confirmação do agendamento.
                                            
                                            $returnData['alert'] = $return['alert'];
                                        }
                                    } else {
                                        // Datas do período de confirmação.
                                        
                                        gestor_incluir_biblioteca('formato');
                                        
                                        $data_confirmacao_1 = formato_dado_para('data',date('Y-m-d',strtotime($schedules->date.' - '.($draw_phase[0]).' day')));
                                        $data_confirmacao_2 = formato_dado_para('data',date('Y-m-d',strtotime($schedules->date.' - '.($draw_phase[1]).' day') - 1));
                                        
                                        // Retornar a mensagem de agendamento expirado.
                                        
                                        $msgAgendamentoExpirado = (existe($config['msg-agendamento-expirado']) ? $config['msg-agendamento-expirado'] : '');
                                        
                                        $msgAgendamentoExpirado = modelo_var_troca_tudo($msgAgendamentoExpirado,"#data_confirmacao_1#",$data_confirmacao_1);
                                        $msgAgendamentoExpirado = modelo_var_troca_tudo($msgAgendamentoExpirado,"#data_confirmacao_2#",$data_confirmacao_2);
                                        
                                        return Array(
                                            'status' => 'SCHEDULE_CONFIRMATION_EXPIRED',
                                            'error-msg' => $msgAgendamentoExpirado,
                                        );
                                    }
                                } else {
                                    return Array(
                                        'status' => 'AGENDAMENTO_STATUS_NAO_PERMITIDO_CONFIRMACAO',
                                    );
                                }
                            break;
                            default:
                                // Efetuar o cancelamento.
                                
                                $return = $this->schedule_cancel( array(
                                    'id_hosts' => $id_hosts,
                                    'id_hosts_agendamentos' => $id_schedules,
                                    'id_hosts_usuarios' => $user_id,
                                    'data' => $date,
                                ) );
                                
                                // Verificar se o cancelamento ocorreu corretamente.
                                
                                if(!$return['cancelado']){
                                    return Array(
                                        'status' => $return['status'],
                                        'error-msg' => $return['alert'],
                                    );
                                } else {
                                    // Alerta do cancelamento do agendamento.
                                    
                                    $returnData['alert'] = $return['alert'];
                                }
                        }
                        
                        // Formatar dados de retorno.
                        
                        $hosts_agendamentos_datas = banco_select(Array(
                            'unico' => true,
                            'tabela' => 'hosts_agendamentos_datas',
                            'campos' => '*',
                            'extra' => 
                                "WHERE id_hosts='".$id_hosts."'"
                                ." AND data='".$date."'"
                        ));
                        
                        if($hosts_agendamentos_datas){
                            unset($hosts_agendamentos_datas['id_hosts']);
                            
                            $returnData['agendamentos_datas'] = $hosts_agendamentos_datas;
                        }
                        
                        $hosts_agendamentos = banco_select(Array(
                            'unico' => true,
                            'tabela' => 'hosts_agendamentos',
                            'campos' => '*',
                            'extra' => 
                                "WHERE id_hosts='".$id_hosts."'"
                                ." AND id_hosts_agendamentos='".$id_schedules."'"
                                ." AND id_hosts_usuarios='".$user_id."'"
                        ));
                        
                        unset($schedules->id_hosts);
                        
                        $returnData['agendamentos'] = $hosts_agendamentos;
                        
                        // Retornar dados.
                        
                        return Array(
                            'status' => 'OK',
                            'data' => $returnData,
                        );
                    } else {
                        return Array(
                            'status' => 'MANDATORY_FIELDS_NOT_INFORMED',
                        );
                    }
                break;
                case 'cancelPublic':
                    // Decodificar os dados em formato Array
                    
                    $dados = Array();
                    if(isset($_REQUEST['dados'])){
                        $dados = json_decode($_REQUEST['dados'],true);
                    }
                    
                    // Verificar se os campos obrigatórios foram enviados: pubId.
                    
                    if(isset($dados['pubId'])){
                        // Pegar os dados de configuração.
                        
                        gestor_incluir_biblioteca('configuracao');
                        
                        $config = configuracao_hosts_variaveis(Array('modulo' => 'configuracoes-agendamentos'));
                        
                        $msgAgendamentoNaoEncontrado = (existe($config['msg-agendamento-nao-encontrado']) ? $config['msg-agendamento-nao-encontrado'] : '');
                        
                        // Tratar os dados enviados.
                        
                        $pubId = banco_escape_field($dados['pubId']);
                        
                        // Pegar o agendamento no banco de dados.
                        
                        $hosts_agendamentos = banco_select(Array(
                            'unico' => true,
                            'tabela' => 'hosts_agendamentos',
                            'campos' => Array(
                                'id_hosts_agendamentos',
                                'id_hosts_usuarios',
                                'status',
                                'data',
                            ),
                            'extra' => 
                                "WHERE pubId='".$pubId."'"
                                ." AND id_hosts='".$id_hosts."'"
                        ));
                        
                        // Caso não exista, retorar erro.
                        
                        if(!$hosts_agendamentos){
                            return Array(
                                'status' => 'SCHEDULE_NOT_FOUND',
                                'error-msg' => $msgAgendamentoNaoEncontrado,
                            );
                        }
                        
                        $id_schedules = $schedules->id_hosts_agendamentos;
                        $user_id = $schedules->id_hosts_usuarios;
                        $date = $schedules->date;
                        
                        // Efetuar o cancelamento.
                        
                        $return = $this->schedule_cancel(Array(
                            'id_hosts' => $id_hosts,
                            'id_hosts_agendamentos' => $id_schedules,
                            'id_hosts_usuarios' => $user_id,
                            'data' => $date,
                        ));
                        
                        // Verificar se o cancelamento ocorreu corretamente.
                        
                        if(!$return['cancelado']){
                            return Array(
                                'status' => $return['status'],
                                'error-msg' => $return['alert'],
                            );
                        } else {
                            // Alerta do cancelamento do agendamento.
                            
                            $returnData['alert'] = $return['alert'];
                        }
                        
                        // Formatar dados de retorno.
                        
                        $hosts_agendamentos_datas = banco_select(Array(
                            'unico' => true,
                            'tabela' => 'hosts_agendamentos_datas',
                            'campos' => '*',
                            'extra' => 
                                "WHERE id_hosts='".$id_hosts."'"
                                ." AND data='".$date."'"
                        ));
                        
                        if($hosts_agendamentos_datas){
                            unset($hosts_agendamentos_datas['id_hosts']);
                            
                            $returnData['agendamentos_datas'] = $hosts_agendamentos_datas;
                        }
                        
                        $hosts_agendamentos = banco_select(Array(
                            'unico' => true,
                            'tabela' => 'hosts_agendamentos',
                            'campos' => '*',
                            'extra' => 
                                "WHERE id_hosts='".$id_hosts."'"
                                ." AND id_hosts_agendamentos='".$id_schedules."'"
                                ." AND id_hosts_usuarios='".$user_id."'"
                        ));
                        
                        unset($schedules->id_hosts);
                        
                        $returnData['agendamentos'] = $hosts_agendamentos;
                        
                        // Retornar dados.
                        
                        return Array(
                            'status' => 'OK',
                            'data' => $returnData,
                        );
                    } else {
                        return Array(
                            'status' => 'MANDATORY_FIELDS_NOT_INFORMED',
                        );
                    }
                break;
                case 'confirm':
                    // Check that the required fields were sent: id_schedules, user_id and date.
                    if( isset( $id_schedules ) && isset( $user_id ) && isset( $date ) ){
                        // Treat each choice: 'confirm' or 'cancel'.
                        switch( $choice ){
                            case 'confirm':
                                // If it has not been confirmed previously, confirm the schedule.
                                $return = $this->schedule_confirm( array(
                                    'id_hosts' => $id_hosts,
                                    'id_hosts_agendamentos' => $id_schedules,
                                    'id_hosts_usuarios' => $user_id,
                                    'data' => $date,
                                ) );
                                
                                // Verificar se a confirmação ocorreu corretamente.
                                
                                if(!$return['confirmado']){
                                    return Array(
                                        'status' => $return['status'],
                                        'error-msg' => $return['alert'],
                                    );
                                } else {
                                    // Alerta de confirmação do agendamento.
                                    
                                    $returnData['alert'] = $return['alert'];
                                }
                            break;
                            default:
                                // Efetuar o cancelamento.
                                
                                $return = $this->schedule_cancel( array(
                                    'id_hosts' => $id_hosts,
                                    'id_hosts_agendamentos' => $id_schedules,
                                    'id_hosts_usuarios' => $user_id,
                                    'data' => $date,
                                ) );
                                
                                // Verificar se o cancelamento ocorreu corretamente.
                                
                                if(!$return['cancelado']){
                                    return Array(
                                        'status' => $return['status'],
                                        'error-msg' => $return['alert'],
                                    );
                                } else {
                                    // Alerta do cancelamento do agendamento.
                                    
                                    $returnData['alert'] = $return['alert'];
                                }
                        }
                        
                        // Return data.
                        return Array(
                            'status' => 'OK',
                            'data' => $returnData,
                        );
                    } else {
                        return Array(
                            'status' => 'MANDATORY_FIELDS_NOT_INFORMED',
                        );
                    }
                break;
                case 'cancel':
                    // Verificar se os campos obrigatórios foram enviados: id_hosts_agendamentos e id_hosts_usuarios.
                    if(isset($id_schedules) && isset($user_id) && isset( $date )){
                        // Efetuar o cancelamento.
                        
                        $return = $this->schedule_cancel( array(
                            'id_hosts' => $id_hosts,
                            'id_hosts_agendamentos' => $id_schedules,
                            'id_hosts_usuarios' => $user_id,
                            'data' => $date,
                        ) );
                        
                        // Verificar se o cancelamento ocorreu corretamente.
                        
                        if(!$return['cancelado']){
                            return Array(
                                'status' => $return['status'],
                                'error-msg' => $return['alert'],
                            );
                        } else {
                            // Alerta do cancelamento do agendamento.
                            
                            $returnData['alert'] = $return['alert'];
                        }
                        
                        // Return data.
                        return Array(
                            'status' => 'OK',
                            'data' => $returnData,
                        );
                    } else {
                        return Array(
                            'status' => 'MANDATORY_FIELDS_NOT_INFORMED',
                        );
                    }
                break;
                default:
                    return Array(
                        'status' => 'OPTION_NOT_DEFINED',
                    );
            }
        }
                
        private function schedule_confirm($params = false){
            global $_GESTOR;
            
            if($params)foreach($params as $var => $val)$$var = $val;
            
            // ===== Parâmetros
            
            // id_hosts - Int - Obrigatório - Identificador do host.
            // id_hosts_agendamentos - Int - Obrigatório - Identificador do agendamento.
            // id_hosts_usuarios - Int - Obrigatório - Identificador do usuário.
            // data - String - Obrigatório - Data do agendamento.
            
            // ===== 
            
            // ===== Pegar os dados de configuração.
            
            gestor_incluir_biblioteca('configuracao');
            
            $config = configuracao_hosts_variaveis(Array('modulo' => 'configuracoes-agendamentos'));
            
            // ===== Pegar dados do agendamento.
            
            $hosts_agendamentos = banco_select(Array(
                'unico' => true,
                'tabela' => 'hosts_agendamentos',
                'campos' => Array(
                    'acompanhantes',
                    'pubID',
                    'status',
                    'senha',
                ),
                'extra' => 
                    "WHERE id_hosts_agendamentos='".$id_hosts_agendamentos."'"
                    ." AND id_hosts='".$id_hosts."'"
            ));
            
            $acompanhantes = (int)$hosts_agendamentos['acompanhantes'];
            $status = $hosts_agendamentos['status'];
            $senha = $hosts_agendamentos['senha'];
            
            // ===== Pegar os dados dos acompanhantes.
            
            $hosts_agendamentos_acompanhantes = banco_select(Array(
                'tabela' => 'hosts_agendamentos_acompanhantes',
                'campos' => Array(
                    'nome',
                ),
                'extra' => 
                    "WHERE id_hosts_usuarios='".$id_hosts_usuarios."'"
                    ." AND id_hosts='".$id_hosts."'"
                    ." AND id_hosts_agendamentos='".$id_hosts_agendamentos."'"
                    ." ORDER BY nome ASC"
            ));
            
            if($hosts_agendamentos_acompanhantes)
            foreach($hosts_agendamentos_acompanhantes as $acompanhante){
                $acompanhantesNomes[] = $acompanhante['nome'];
            }
            
            // ===== Gerar o token de validação.
            
            gestor_incluir_biblioteca('autenticacao');
            
            $validacao = autenticacao_cliente_gerar_token_validacao(Array(
                'id_hosts' => $id_hosts,
                'pubID' => ($hosts_agendamentos['pubID'] ? $hosts_agendamentos['pubID'] : null),
            ));
            
            $token = $validacao['token'];
            
            // ===== Verificar se já foi confirmado. Caso tenha sido confirmado, só alertar e enviar email ao usuário. Senão, fazer o procedimento de confirmação.
            
            if($status != 'confirmado'){
                // ===== Pegar a quantidade de vagas máxima.
                
                $dias_semana = (existe($config['dias-semana']) ? explode(',',$config['dias-semana']) : Array());
                $dias_semana_maximo_vagas_arr = (existe($config['dias-semana-maximo-vagas']) ? explode(',',$config['dias-semana-maximo-vagas']) : Array());
                
                $count_dias = 0;
                if($dias_semana)
                foreach($dias_semana as $dia_semana){
                    if($dia_semana == strtolower(date('D',strtotime($data)))){
                        break;
                    }
                    $count_dias++;
                }
                
                if(count($dias_semana_maximo_vagas_arr) > 1){
                    $dias_semana_maximo_vagas = $dias_semana_maximo_vagas_arr[$count_dias];
                } else {
                    $dias_semana_maximo_vagas = $dias_semana_maximo_vagas_arr[0];
                }
                
                // ===== Verificar se há vagas suficientes para a data requerida. Caso não tenha, retornar mensagem de erro.
                
                $hosts_agendamentos_datas = banco_select(Array(
                    'unico' => true,
                    'tabela' => 'hosts_agendamentos_datas',
                    'campos' => Array(
                        'id_hosts_agendamentos_datas',
                        'total',
                    ),
                    'extra' => 
                        "WHERE id_hosts='".$id_hosts."'"
                        ." AND data='".$data."'"
                        ." AND total + ".($acompanhantes+1)." <= ".$dias_semana_maximo_vagas
                ));
                
                if(!$hosts_agendamentos_datas){
                    $msgAgendamentoSemVagas = (existe($config['msg-agendamento-sem-vagas']) ? $config['msg-agendamento-sem-vagas'] : '');
                    
                    return Array(
                        'confirmado' => false,
                        'status' => 'AGENDAMENTO_SEM_VAGAS',
                        'alerta' => $msgAgendamentoSemVagas,
                    );
                }
                
                // ===== Atualizar a quantidade total de vagas utilizadas em agendamentos para a data em questão.
                
                banco_update_campo('total','total+'.($acompanhantes+1),true);
                
                banco_update_executar('hosts_agendamentos_datas',"WHERE id_hosts_agendamentos_datas='".$hosts_agendamentos_datas['id_hosts_agendamentos_datas']."'");
                
                // ===== Gerar senha do agendamento.
                
                gestor_incluir_biblioteca('formato');
                
                $senha = formato_colocar_char_meio_numero(formato_zero_a_esquerda(rand(1,99999),6));
                
                // ===== Atualizar agendamento.
                
                banco_update_campo('senha',$senha);
                banco_update_campo('status','confirmado');
                banco_update_campo('versao','versao+1',true);
                banco_update_campo('data_modificacao','NOW()',true);
                
                banco_update_executar('hosts_agendamentos',"WHERE id_hosts='".$id_hosts."' AND id_hosts_agendamentos='".$id_hosts_agendamentos."' AND id_hosts_usuarios='".$id_hosts_usuarios."'");
            }
            
            // ===== Pegar dados do usuário.
            
            $hosts_usuarios = banco_select(Array(
                'unico' => true,
                'tabela' => 'hosts_usuarios',
                'campos' => Array(
                    'nome',
                    'email',
                ),
                'extra' => 
                    "WHERE id_hosts_usuarios='".$id_hosts_usuarios."'"
                    ." AND id_hosts='".$id_hosts."'"
            ));
            
            // ===== Formatar dados do email.
            
            $agendamentoAssunto = (existe($config['agendamento-assunto']) ? $config['agendamento-assunto'] : '');
            $agendamentoMensagem = (existe($config['agendamento-mensagem']) ? $config['agendamento-mensagem'] : '');
            $msgConclusaoAgendamento = (existe($config['msg-conclusao-agendamento']) ? $config['msg-conclusao-agendamento'] : '');
            
            $tituloEstabelecimento = (existe($config['titulo-estabelecimento']) ? $config['titulo-estabelecimento'] : '');
            
            $email = $hosts_usuarios['email'];
            $nome = $hosts_usuarios['nome'];
            
            gestor_incluir_biblioteca('formato');
            
            $codigo = date('dmY').formato_zero_a_esquerda($id_hosts_agendamentos,6);
            
            // ===== Formatar mensagem do email.
            
            gestor_incluir_biblioteca('host');
            
            $agendamentoAssunto = modelo_var_troca_tudo($agendamentoAssunto,"#codigo#",$codigo);
            
            $agendamentoMensagem = modelo_var_troca_tudo($agendamentoMensagem,"#codigo#",$codigo);
            $agendamentoMensagem = modelo_var_troca_tudo($agendamentoMensagem,"#titulo#",$tituloEstabelecimento);
            $agendamentoMensagem = modelo_var_troca_tudo($agendamentoMensagem,"#data#",formato_dado_para('data',$data));
            $agendamentoMensagem = modelo_var_troca_tudo($agendamentoMensagem,"#senha#",$senha);
            $agendamentoMensagem = modelo_var_troca_tudo($agendamentoMensagem,"#url-cancelamento#",'<a target="agendamento" href="'.host_url(Array('opcao'=>'full')).'agendamentos-publico/?acao=cancelar&token='.$token.'" style="overflow-wrap: break-word;">'.host_url(Array('opcao'=>'full')).'agendamentos-publico/?acao=cancelar&token='.$token.'</a>');
            
            $cel_nome = 'cel'; $cel[$cel_nome] = modelo_tag_val($agendamentoMensagem,'<!-- '.$cel_nome.' < -->','<!-- '.$cel_nome.' > -->'); $agendamentoMensagem = modelo_tag_in($agendamentoMensagem,'<!-- '.$cel_nome.' < -->','<!-- '.$cel_nome.' > -->','<!-- '.$cel_nome.' -->');
            
            $agendamentoMensagem = modelo_var_troca($agendamentoMensagem,"#seu-nome#",$nome);
            
            for($i=0;$i<(int)$acompanhantes;$i++){
                $cel_aux = $cel[$cel_nome];
                
                $cel_aux = modelo_var_troca($cel_aux,"#num#",($i+1));
                $cel_aux = modelo_var_troca($cel_aux,"#acompanhante#",$acompanhantesNomes[$i]);
                
                $agendamentoMensagem = modelo_var_in($agendamentoMensagem,'<!-- '.$cel_nome.' -->',$cel_aux);
            }
            $agendamentoMensagem = modelo_var_troca($agendamentoMensagem,'<!-- '.$cel_nome.' -->','');
            
            // ===== Formatar mensagem do alerta.
            
            $msgConclusaoAgendamento = modelo_var_troca_tudo($msgConclusaoAgendamento,"#data#",formato_dado_para('data',$data));
            $msgConclusaoAgendamento = modelo_var_troca_tudo($msgConclusaoAgendamento,"#senha#",$senha);
            
            $cel_nome = 'cel'; $cel[$cel_nome] = modelo_tag_val($msgConclusaoAgendamento,'<!-- '.$cel_nome.' < -->','<!-- '.$cel_nome.' > -->'); $msgConclusaoAgendamento = modelo_tag_in($msgConclusaoAgendamento,'<!-- '.$cel_nome.' < -->','<!-- '.$cel_nome.' > -->','<!-- '.$cel_nome.' -->');
            
            $msgConclusaoAgendamento = modelo_var_troca($msgConclusaoAgendamento,"#seu-nome#",$nome);
            
            for($i=0;$i<(int)$acompanhantes;$i++){
                $cel_aux = $cel[$cel_nome];
                
                $cel_aux = modelo_var_troca($cel_aux,"#num#",($i+1));
                $cel_aux = modelo_var_troca($cel_aux,"#acompanhante#",$acompanhantesNomes[$i]);
                
                $msgConclusaoAgendamento = modelo_var_in($msgConclusaoAgendamento,'<!-- '.$cel_nome.' -->',$cel_aux);
            }
            $msgConclusaoAgendamento = modelo_var_troca($msgConclusaoAgendamento,'<!-- '.$cel_nome.' -->','');
            
            $msgAlerta = $msgConclusaoAgendamento;
            
            // ===== Enviar email com informações do agendamento.
            
            gestor_incluir_biblioteca(Array('comunicacao','host'));
            
            if(comunicacao_email(Array(
                'hostPersonalizacao' => true,
                'destinatarios' => Array(
                    Array(
                        'email' => $email,
                        'nome' => $nome,
                    ),
                ),
                'mensagem' => Array(
                    'assunto' => $agendamentoAssunto,
                    'html' => $agendamentoMensagem,
                    'htmlAssinaturaAutomatica' => true,
                    'htmlVariaveis' => Array(
                        Array(
                            'variavel' => '[[url]]',
                            'valor' => host_url(Array('opcao'=>'full')),
                        ),
                    ),
                ),
            ))){
                
            }
            
            return Array(
                'confirmado' => true,
                'alerta' => $msgAlerta,
            );
        }

        private function schedule_cancel($params = false){
            global $_GESTOR;
            
            if($params)foreach($params as $var => $val)$$var = $val;
            
            // ===== Parâmetros
            
            // id_hosts - Int - Obrigatório - Identificador do host.
            // id_hosts_agendamentos - Int - Obrigatório - Identificador do agendamento.
            // id_hosts_usuarios - Int - Obrigatório - Identificador do usuário.
            // data - String - Obrigatório - Data do agendamento.
            
            // ===== 
            
            // ===== Pegar os dados de configuração.
            
            gestor_incluir_biblioteca('configuracao');
            
            $config = configuracao_hosts_variaveis(Array('modulo' => 'configuracoes-agendamentos'));
            
            // ===== Pegar dados do agendamento.
            
            $hosts_agendamentos = banco_select(Array(
                'unico' => true,
                'tabela' => 'hosts_agendamentos',
                'campos' => Array(
                    'acompanhantes',
                    'status',
                ),
                'extra' => 
                    "WHERE id_hosts_agendamentos='".$id_hosts_agendamentos."'"
                    ." AND id_hosts='".$id_hosts."'"
            ));
            
            $acompanhantes = (int)$hosts_agendamentos['acompanhantes'];
            $status = $hosts_agendamentos['status'];
            
            // ===== Verificar se já foi confirmado. Caso tenha sido confirmado, atualizar a quantidade total de vagas.
            
            if($status == 'confirmado'){
                // ===== Pegar o identificador do 'hosts_agendamentos_datas'.
                
                $hosts_agendamentos_datas = banco_select(Array(
                    'unico' => true,
                    'tabela' => 'hosts_agendamentos_datas',
                    'campos' => Array(
                        'id_hosts_agendamentos_datas',
                    ),
                    'extra' => 
                        "WHERE id_hosts='".$id_hosts."'"
                        ." AND data='".$data."'"
                ));
                
                // ===== Atualizar a quantidade total de vagas utilizadas em agendamentos para a data em questão.
                
                if($hosts_agendamentos_datas){
                    
                    banco_update_campo('total','total-'.($acompanhantes+1),true);
                    
                    banco_update_executar('hosts_agendamentos_datas',"WHERE id_hosts_agendamentos_datas='".$hosts_agendamentos_datas['id_hosts_agendamentos_datas']."'");
                }
            }
            
            // ===== Atualizar agendamento.
            
            banco_update_campo('status','finalizado');
            banco_update_campo('versao','versao+1',true);
            banco_update_campo('data_modificacao','NOW()',true);
            
            banco_update_executar('hosts_agendamentos',"WHERE id_hosts='".$id_hosts."' AND id_hosts_agendamentos='".$id_hosts_agendamentos."' AND id_hosts_usuarios='".$id_hosts_usuarios."'");
            
            // ===== Pegar dados do usuário.
            
            $hosts_usuarios = banco_select(Array(
                'unico' => true,
                'tabela' => 'hosts_usuarios',
                'campos' => Array(
                    'nome',
                    'email',
                ),
                'extra' => 
                    "WHERE id_hosts_usuarios='".$id_hosts_usuarios."'"
                    ." AND id_hosts='".$id_hosts."'"
            ));
            
            // ===== Formatar dados do email.
            
            $desagendamentoAssunto = (existe($config['desagendamento-assunto']) ? $config['desagendamento-assunto'] : '');
            $desagendamentoMensagem = (existe($config['desagendamento-mensagem']) ? $config['desagendamento-mensagem'] : '');
            $msgAgendamentoCancelado = (existe($config['msg-agendamento-cancelado']) ? $config['msg-agendamento-cancelado'] : '');
            
            $tituloEstabelecimento = (existe($config['titulo-estabelecimento']) ? $config['titulo-estabelecimento'] : '');
            
            $email = $hosts_usuarios['email'];
            $nome = $hosts_usuarios['nome'];
            
            gestor_incluir_biblioteca('formato');
            
            $codigo = date('dmY').formato_zero_a_esquerda($id_hosts_agendamentos,6);
            
            // ===== Formatar mensagem do email.
            
            gestor_incluir_biblioteca('host');
            
            $desagendamentoAssunto = modelo_var_troca_tudo($desagendamentoAssunto,"#codigo#",$codigo);
            
            $desagendamentoMensagem = modelo_var_troca_tudo($desagendamentoMensagem,"#codigo#",$codigo);
            $desagendamentoMensagem = modelo_var_troca_tudo($desagendamentoMensagem,"#titulo#",$tituloEstabelecimento);
            $desagendamentoMensagem = modelo_var_troca_tudo($desagendamentoMensagem,"#data#",formato_dado_para('data',$data));
            
            // ===== Formatar mensagem do alerta.
            
            $msgAlerta = $msgAgendamentoCancelado;
            
            // ===== Enviar email com informações do agendamento.
            
            gestor_incluir_biblioteca(Array('comunicacao','host'));
            
            if(comunicacao_email(Array(
                'hostPersonalizacao' => true,
                'destinatarios' => Array(
                    Array(
                        'email' => $email,
                        'nome' => $nome,
                    ),
                ),
                'mensagem' => Array(
                    'assunto' => $desagendamentoAssunto,
                    'html' => $desagendamentoMensagem,
                    'htmlAssinaturaAutomatica' => true,
                    'htmlVariaveis' => Array(
                        Array(
                            'variavel' => '[[url]]',
                            'valor' => host_url(Array('opcao'=>'full')),
                        ),
                    ),
                ),
            ))){
                
            }
            
            return Array(
                'cancelado' => true,
                'alerta' => $msgAlerta,
            );
        }

        private function calendar( $params = false ){
            global $_MANAGER;

            if( $params ) foreach( $params as $var => $val ) $$var = $val;
            
            // Force date to today for debuging or set today's date
            if( CS_FORCE_DATE_TODAY ){ $today = CS_DATE_TODAY_FORCED_VALUE; } else { $today = date('Y-m-d'); }
            
            // Get the configuration data.
            $options = get_option( 'competitive_scheduling_options' );
            
            $days_week = ( isset( $options['days-week'] ) ? explode(',',$options['days-week'] ) : Array() );
            $years = ( isset( $options['calendar-years'] ) ? (int)$options['calendar-years'] : 2 );
            $days_week_maximum_vacancies = ( isset( $options['days-week-maximum-vacancies'] ) ? explode(',',$options['days-week-maximum-vacancies'] ) : Array() );
            if( isset( $options['unavailable-dates'] )) $unavailable_dates = ( isset( $options['unavailable-dates-values'] ) ? explode('|',$options['unavailable-dates-values'] ) : Array() );
            $calendar_limit_month_ahead = ( isset( $options['calendar-limit-month-ahead'] ) ? (int)$options['calendar-limit-month-ahead'] : false );
            $draw_phase = ( isset( $options['draw-phase'] ) ? explode(',',$options['draw-phase'] ) : Array(7,5) );
            $residual_phase = ( isset( $options['residual-phase'] ) ? (int)$options['residual-phase'] : 5 );
            $calendar_holidays_start = ( isset( $options['calendar-holidays-start'] ) ? trim( $options['calendar-holidays-start'] ) : '15 December' );
            $calendar_holidays_end = ( isset( $options['calendar-holidays-end'] ) ? trim( $options['calendar-holidays-end'] ) : '20 January' );
            
            $start_year = date('Y');
            $year_end = (int)$start_year + $years;

            global $wpdb;
            $query = $wpdb->prepare(
                "SELECT date,total 
                FROM {$wpdb->prefix}schedules_dates 
                WHERE date >= '%s'",
                $today
            );
            $schedules_dates = $wpdb->get_results( $query );

            for( $i=-1; $i<$years+1; $i++ ){
                $period_holidays[] = Array(
                    'start' => strtotime( $calendar_holidays_start." ".( $start_year+$i ) ),
                    'end' => strtotime( $calendar_holidays_end." ".( $start_year+$i+1 ) ),
                );
            }
            
            $first_day = strtotime( date( "Y-m-d", time() ) . " + 1 day" );
            $last_day = strtotime( date( "Y-m-d", time() ) . " + ".$years." year" );
            
            if( $calendar_limit_month_ahead ){
                $limit_calendar = strtotime( date( "Y-m", strtotime( $today . " + ".$calendar_limit_month_ahead." month") ).'-01' );
            }

            $day = $first_day;
            do {
                if( isset( $limit_calendar ) ){
                    if( $day >= $limit_calendar ){
                        break;
                    }
                }
                
                $dateFormatted = date( 'd/m/Y', $day );
                $flag = false;
                
                if( isset( $period_holidays ) ){
                    foreach( $period_holidays as $period ){
                        if(
                            $day > $period['start'] &&
                            $day < $period['end']
                        ){
                            $flag = true;
                            break;
                        }
                    }
                }
                
                if( isset( $unavailable_dates ) ){
                    foreach( $unavailable_dates as $ud){
                        if( $dateFormatted == $ud ){
                            $flag = true;
                            break;
                        }
                    }
                }
                
                if( isset( $draw_phase ) ){
                    if(
                        $day >= strtotime( $today.' + '.( $draw_phase[1]+1).' day') &&
                        $day < strtotime( $today.' + '.( $draw_phase[0]+1).' day')
                    ){
                        $flag = true;
                    }
                }
                
                if( ! $flag ){
                    $flag2 = false;
                    $count_days = 0;

                    if( isset( $days_week ) )
                    foreach( $days_week as $day_week ){
                        if( $day_week == strtolower( date( 'D', $day ) ) ){
                            $flag2 = true;
                            break;
                        }
                        $count_days++;
                    }

                    if( $flag2 ){
                        $date = date('Y-m-d', $day);
                        $flag3 = false;
                        
                        if( $day < strtotime($today.' + '.$residual_phase.' day' ) ){
                            if( $schedules_dates ){
                                foreach( $schedules_dates as $schedule_date ){
                                    if( $date == $schedule_date->date ){
                                        if( count( $days_week_maximum_vacancies ) > 1 ){
                                            $days_semana_maximo_vacancies = $days_week_maximum_vacancies[$count_days];
                                        } else {
                                            $days_semana_maximo_vacancies = $days_week_maximum_vacancies[0];
                                        }
                                        
                                        if( (int)$days_semana_maximo_vacancies <= (int)$schedule_date->total ){
                                            $flag3 = true;
                                        }
                                        
                                        break;
                                    }
                                }
                            }
                        }
                        
                        if( ! $flag3 ){
                            $dates[$date] = 1;
                        }
                    }
                }
                
                $day += 86400;
            } while ( $day < $last_day );
            
            $JScalendar['available_dates'] = $dates;
            $JScalendar['start_year'] = $start_year;
            $JScalendar['year_end'] = $year_end;
            
            // JS variables.
            $_MANAGER['javascript-vars']['calendar'] = $JScalendar;
        }

        private function allowed_date( $date ){
            // Require formats class to prepare data.
            require_once( CS_PATH . 'includes/class.formats.php' );
            
            // Force date to today for debuging or set today's date
            if( CS_FORCE_DATE_TODAY ){ $today = CS_DATE_TODAY_FORCED_VALUE; } else { $today = date('Y-m-d'); }
            
            // Get the configuration data.
            $options = get_option( 'competitive_scheduling_options' );
            
            $days_week = ( isset( $options['days-week'] ) ? explode(',',$options['days-week'] ) : Array());
            $years = ( isset( $options['calendar-years'] ) ? (int)$options['calendar-years'] : 2);
            if( isset( $options['unavailable-dates'] )) $unavailable_dates = ( isset( $options['unavailable-dates-values'] ) ? explode('|',$options['unavailable-dates-values'] ) : Array());
            $calendar_limit_month_ahead = ( isset( $options['calendar-limit-month-ahead'] ) ? (int)$options['calendar-limit-month-ahead'] : false);
            $draw_phase = ( isset( $options['draw-phase'] ) ? explode(',',$options['draw-phase'] ) : Array(7,5));
            $calendar_holidays_start = ( isset( $options['calendar-holidays-start'] ) ? trim( $options['calendar-holidays-start'] ) : '15 December');
            $calendar_holidays_end = ( isset( $options['calendar-holidays-end'] ) ? trim( $options['calendar-holidays-end'] ) : '20 January');
            
            $start_year = date('Y');
            $year_end = (int)$start_year + $years;
            
            if( $days_week )
            foreach( $days_week as $day_week ){
                if(!$flag){
                    $first_day_week = $day_week;
                    $flag = true;
                }
            }
            
            for( $i=-1; $i<$years+1; $i++ ){
                $period_holidays[] = Array(
                    'start' => strtotime( $calendar_holidays_start." ".( $start_year+$i ) ),
                    'end' => strtotime( $calendar_holidays_end." ".( $start_year+$i+1 ) ),
                );
            }
            
            $first_day = strtotime( date( "Y-m-d", time() ) . " + 1 day" );
            $last_day = strtotime( date( "Y-m-d", time() ) . " + ".$years." year" );
            
            if( $calendar_limit_month_ahead ){
                $limit_calendar = strtotime( date( "Y-m", strtotime( $today . " + ".$calendar_limit_month_ahead." month") ).'-01' );
            }
            
            $day = $first_day;
            do {
                if( isset( $limit_calendar ) ){
                    if( $day >= $limit_calendar ){
                        break;
                    }
                }
                
                $flag = false;
                
                if( isset( $period_holidays ) ){
                    foreach( $period_holidays as $period ){
                        if(
                            $day > $period['start'] &&
                            $day < $period['end']
                        ){
                            $flag = true;
                            break;
                        }
                    }
                }
                
                if( isset( $unavailable_dates ) ){
                    foreach( $unavailable_dates as $ud){
                        if(
                            $day > strtotime( Formats::data_format_to( 'text-to-date', $ud ).' 00:00:00' ) &&
                            $day < strtotime( Formats::data_format_to( 'text-to-date', $ud ).' 23:59:59' )
                        ){
                            $flag = true;
                            break;
                        }
                    }
                }
                
                if( isset( $draw_phase ) ){
                    if(
                        $day >= strtotime( $today.' + '.( $draw_phase[1]+1).' day') &&
                        $day < strtotime( $today.' + '.( $draw_phase[0]+1).' day')
                    ){
                        $flag = true;
                    }
                }
                
                if( ! $flag ){
                    $flag2 = false;
                    
                    if( isset( $days_week ) )
                    foreach( $days_week as $day_week ){
                        if( $day_week == strtolower( date( 'D', $day ) ) ){
                            $flag2 = true;
                            break;
                        }
                    }
                    
                    if( $flag2 ){
                        if( $date == date('Y-m-d', $day)){
                            return true;
                        }
                    }
                }
                
                $day += 86400;
            } while ( $day < $last_day);
            
            return false;
        }

        private function status_text( $status = '' ){
            $statusSchedulingTexts = Array(
                'status-confirmed' => __( '<span class="ui green label">Confirmed</span>', 'competitive-scheduling' ),
                'status-finished' => __( '<span class="ui grey label">Finished</span>', 'competitive-scheduling' ),
                'status-unqualified' => __( '<span class="ui brown label">Not Drawn - Waiting for Residual Vacancies</span>', 'competitive-scheduling' ),
                'status-new' => __( '<span class="ui grey label">Waiting For Draw</span>', 'competitive-scheduling' ),
                'status-qualified' => __( '<span class="ui yellow label">Drawn - Awaiting Confirmation</span>', 'competitive-scheduling' ),
                'status-no-residual-vacancy' => __( '<span class="ui brown label">No Residual Vacancies</span>', 'competitive-scheduling' ),
                'status-residual-vacancies' => __( '<span class="ui teal label">Available Residual Vacancies</span>', 'competitive-scheduling' ),
            );

            return ( ! empty( $statusSchedulingTexts[$status] ) ? $statusSchedulingTexts[$status] : __( '<span class="ui grey label">Undefined Status</span>', 'competitive-scheduling' ) );
        }

        private function nonce_verify( $nonce ){
            // Verifiying nonce
            if( isset( $_POST[$nonce] ) ){
                if( ! wp_verify_nonce( $_POST[$nonce], $nonce ) ){
                    $noNonce = true;
                }
            } else {
                $noNonce = true;
            }
            
            // If nonce is invalid, redirect to home
            if( isset( $noNonce ) ){
                wp_redirect( home_url( '/' ) );
            }
        }

    }
}