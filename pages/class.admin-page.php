<?php

if ( ! class_exists( 'Competitive_Scheduling_Admin_Page' ) ) {
	class Competitive_Scheduling_Admin_Page {

		public function __construct() {
			add_action(
				'rest_api_init',
				function () {
					register_rest_route(
						'competitive-scheduling/v1',
						'/admin-page/',
						array(
							'methods'  => WP_REST_Server::READABLE,
							'callback' => array( $this, 'ajax_schedules' ),
						)
					);
				}
			);
		}

		public function page() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			if( ! empty( $_GET['create-schedules'] ) ){
				if( CS_DEBUG ){
					require_once CS_PATH . 'includes/class.cron.php';
					Cron::tests();
					exit;
				}
			}

			wp_enqueue_style( 'fomantic-ui', CS_URL . 'vendor/fomantic-UI@2.9.0/dist/semantic.min.css', array(), CS_VERSION );
			wp_enqueue_script( 'fomantic-ui', CS_URL . 'vendor/fomantic-UI@2.9.0/dist/semantic.min.js', array( 'jquery' ), CS_VERSION );

			wp_enqueue_style( 'competitive-scheduling-admin', CS_URL . 'assets/css/admin.css', array(), ( CS_DEBUG ? filemtime( CS_PATH . 'assets/css/admin.css' ) : CS_VERSION ) );
			wp_enqueue_script( 'competitive-scheduling-admin', CS_URL . 'assets/js/admin.js', array( 'jquery' ), ( CS_DEBUG ? filemtime( CS_PATH . 'assets/js/admin.js' ) : CS_VERSION ) );

			// Require interface class to alert user and get modal template.
			require_once CS_PATH . 'includes/class.interfaces.php';

			// Finalize interface.
			Interfaces::components_include(
				array(
					'component' => array(
						'modal-loading',
						'modal-alert',
						'modal-info',
					),
				)
			);
			Interfaces::finish( CS_JS_MANAGER_VAR );
			$components_html = Interfaces::components_html( true );

			$calendar = $this->schedules_calendar();

			require CS_PATH . 'views/competitive-scheduling-admin-page.php';
		}

		public function ajax_schedules( $request ) {
			// Get all sent parameters
			$params = $request->get_params();

			if ( is_user_logged_in() ) {
				// Verify nonce
				$nonce = $params['nonce'];
				if ( ! wp_verify_nonce( $nonce, 'schedules-nonce' ) ) {
					return new WP_Error( 'rest_api_nonce_invalid', esc_html__( 'The system did not validate the nonce sent. Please try again or seek help from support.', 'competitive-scheduling' ), array( 'status' => 403 ) );
				}

				// Require templates class to manipulate page.
				require_once CS_PATH . 'includes/class.templates.php';

				// Require database class to manipulate data.
				require_once( CS_PATH . 'includes/class.database.php' );

				// Require user class to get user's data.
				require_once( CS_PATH . 'includes/class.user.php' );

				// Require formats class to manipulate data.
				require_once( CS_PATH . 'includes/class.formats.php' );

				// Initial default variables.
				$total = 0;
				$print = false;
				
				// Get all parmas sent.
				$date = $params['date'];
				$status = $params['status'];

				// Sanitize all fields
				$date = sanitize_text_field( $date );
				$status = sanitize_text_field( $status );

				// Get table cells.
				$page = Templates::render_view( CS_PATH . 'views/competitive-scheduling-admin-page.php' );

				// Get the tables from the page.
				$cell_name = 'people-table'; $table = Templates::tag_value( $page, '<!-- '.$cell_name.' < -->','<!-- '.$cell_name.' > -->' );
				$cell_name = 'print-table'; $printLayout = Templates::tag_value( $page, '<!-- '.$cell_name.' < -->','<!-- '.$cell_name.' > -->' );
				$cell_name = 'print-header'; $printHeader = Templates::tag_value( $page, '<!-- '.$cell_name.' < -->','<!-- '.$cell_name.' > -->' );

				// Get all cells from people table.
				$cell_name = 'th-password'; $cell[$cell_name] = Templates::tag_value( $table, '<!-- '.$cell_name.' < -->','<!-- '.$cell_name.' > -->' ); $table = Templates::tag_in( $table,'<!-- '.$cell_name.' < -->', '<!-- '.$cell_name.' > -->', '<!-- '.$cell_name.' -->' );
				$cell_name = 'th-email'; $cell[$cell_name] = Templates::tag_value( $table, '<!-- '.$cell_name.' < -->','<!-- '.$cell_name.' > -->' ); $table = Templates::tag_in( $table,'<!-- '.$cell_name.' < -->', '<!-- '.$cell_name.' > -->', '<!-- '.$cell_name.' -->' );
				
				$cell_name = 'cell-companion'; $cell[$cell_name] = Templates::tag_value( $table, '<!-- '.$cell_name.' < -->','<!-- '.$cell_name.' > -->' ); $table = Templates::tag_in( $table,'<!-- '.$cell_name.' < -->', '<!-- '.$cell_name.' > -->', '<!-- '.$cell_name.' -->' );
				$cell_name = 'td-companions'; $cell[$cell_name] = Templates::tag_value( $table, '<!-- '.$cell_name.' < -->','<!-- '.$cell_name.' > -->' ); $table = Templates::tag_in( $table,'<!-- '.$cell_name.' < -->', '<!-- '.$cell_name.' > -->', '<!-- '.$cell_name.' -->' );
				$cell_name = 'td-password'; $cell[$cell_name] = Templates::tag_value( $table, '<!-- '.$cell_name.' < -->','<!-- '.$cell_name.' > -->' ); $table = Templates::tag_in( $table,'<!-- '.$cell_name.' < -->', '<!-- '.$cell_name.' > -->', '<!-- '.$cell_name.' -->' );
				
				$cell_name = 'sent'; $cell[$cell_name] = Templates::tag_value( $table, '<!-- '.$cell_name.' < -->','<!-- '.$cell_name.' > -->' ); $table = Templates::tag_in( $table,'<!-- '.$cell_name.' < -->', '<!-- '.$cell_name.' > -->', '<!-- '.$cell_name.' -->' );
				$cell_name = 'not-sent'; $cell[$cell_name] = Templates::tag_value( $table, '<!-- '.$cell_name.' < -->','<!-- '.$cell_name.' > -->' ); $table = Templates::tag_in( $table,'<!-- '.$cell_name.' < -->', '<!-- '.$cell_name.' > -->', '<!-- '.$cell_name.' -->' );
				$cell_name = 'td-email'; $cell[$cell_name] = Templates::tag_value( $table, '<!-- '.$cell_name.' < -->','<!-- '.$cell_name.' > -->' ); $table = Templates::tag_in( $table,'<!-- '.$cell_name.' < -->', '<!-- '.$cell_name.' > -->', '<!-- '.$cell_name.' -->' );
				
				$cell_name = 'cell-schedule'; $cell[$cell_name] = Templates::tag_value( $table, '<!-- '.$cell_name.' < -->','<!-- '.$cell_name.' > -->' ); $table = Templates::tag_in( $table,'<!-- '.$cell_name.' < -->', '<!-- '.$cell_name.' > -->', '<!-- '.$cell_name.' -->' );
				
				// Get all cells from print table.
				$cell_name = 'th-companions'; $cell2[$cell_name] = Templates::tag_value( $printLayout, '<!-- '.$cell_name.' < -->','<!-- '.$cell_name.' > -->' ); $printLayout = Templates::tag_in( $printLayout,'<!-- '.$cell_name.' < -->', '<!-- '.$cell_name.' > -->', '<!-- '.$cell_name.' -->' );
				$cell_name = 'td-companions'; $cell2[$cell_name] = Templates::tag_value( $printLayout, '<!-- '.$cell_name.' < -->','<!-- '.$cell_name.' > -->' ); $printLayout = Templates::tag_in( $printLayout,'<!-- '.$cell_name.' < -->', '<!-- '.$cell_name.' > -->', '<!-- '.$cell_name.' -->' );
				$cell_name = 'cell-people'; $cell2[$cell_name] = Templates::tag_value( $printLayout, '<!-- '.$cell_name.' < -->','<!-- '.$cell_name.' > -->' ); $printLayout = Templates::tag_in( $printLayout,'<!-- '.$cell_name.' < -->', '<!-- '.$cell_name.' > -->', '<!-- '.$cell_name.' -->' );

				echo $printLayout;exit;
				// Treat each status sent.
				switch( $status ){
					case 'pre':
						// Get the data from the bank.
						global $wpdb;
						$query = $wpdb->prepare(
							"SELECT id_schedules, companions, user_id 
							FROM {$wpdb->prefix}schedules 
							WHERE date = '%s' AND status='new'",
							array( $date )
						);
						$schedules = $wpdb->get_results( $query, ARRAY_A );

						// Scan all schedules.
						if( $schedules )
						foreach( $schedules as $schedule ){
							// Get scheduling data.
							$id_schedules = $schedule['id_schedules'];
							$user_id = $schedule['user_id'];
							$companions = (int)$schedule['companions'];
							
							// Get user data from the schedule.
							$name = User::get_name( $user_id );

							$schedulesAux = Array(
								'name' => $name,
								'companions' => $companions,
							);
							
							// Get the companions’ details.
							global $wpdb;
							$query = $wpdb->prepare(
								"SELECT name 
								FROM {$wpdb->prefix}schedules_companions 
								WHERE id_schedules = '%s' AND user_id = '%s'",
								array( $id_schedules, $user_id )
							);
							$schedules_companions = $wpdb->get_results( $query, ARRAY_A );

							$schedulesAux['companionsData'] = $schedules_companions;
							
							// Update the total number of people scheduled.
							$total += 1 + $companions;
							
							// Include the schedule data in the schedules array.
							$schedulesProc[] = $schedulesAux;
						}

						// Set up table.
						if( ! empty( $schedulesProc ) ){
							$cel_name = 'cell-schedule';
							
							// Sort the data by name to assemble the table.
							usort( $schedulesProc, function( $a, $b ){
								return $a['name'] <=> $b['name'];
							} );
							
							foreach( $schedulesProc as $schedule ){
								$cel_aux = $cell[$cel_name];
								
								// Include the name.
								$cel_aux = Templates::change_variable( $cel_aux, '[[name]]', $schedule['name'] );
								
								// Popular escorts.
								$companionNum = 0;
								if( ! empty( $schedule['companionsData'] ) ){
									$cel_aux = Templates::change_variable( $cel_aux, '<!-- td-companions -->', $cell['td-companions'] );
									
									foreach( $schedule['companionsData'] as $companionsData ){
										$companionNum++;

										$cel_comp = 'cell-companion'; $cel_aux_2 = $cell[$cel_comp];

										$cel_aux_2 = Templates::change_variable( $cel_aux_2, '[[num]]', $companionNum );
										$cel_aux_2 = Templates::change_variable( $cel_aux_2, '[[companion]]', $companionsData['name'] );
										
										$cel_aux = Templates::variable_in( $cel_aux, '<!-- '.$cel_comp.' -->', $cel_aux_2 );
									}
								}
								
								$table = Templates::variable_in( $table, '<!-- '.$cel_name.' -->', $cel_aux );
							}
							
							$table = Templates::change_variable( $table, '<!-- '.$cel_name.' -->', '' );
						} else {
							$table = '';
						}
					break;
					case 'waiting':
						// Get the data from the bank.
						global $wpdb;
						$query = $wpdb->prepare(
							"SELECT id_schedules, companions, user_id, status 
							FROM {$wpdb->prefix}schedules 
							WHERE date = '%s' AND (status='email-sent' OR status='email-not-sent')",
							array( $date )
						);
						$schedules = $wpdb->get_results( $query, ARRAY_A );

						// Scan all schedules.
						
						if( $schedules )
						foreach( $schedules as $schedule ){
							// Get scheduling data.
							$id_schedules = $schedule['id_schedules'];
							$user_id = $schedule['user_id'];
							$companions = (int)$schedule['companions'];
							
							// Get user data from the schedule.
							$name = User::get_name( $user_id );

							$schedulesAux = Array(
								'name' => $name,
								'companions' => $companions,
								'status' => $schedule['status'],
							);

							// Get the companions’ details.
							global $wpdb;
							$query = $wpdb->prepare(
								"SELECT name 
								FROM {$wpdb->prefix}schedules_companions 
								WHERE id_schedules = '%s' AND user_id = '%s'",
								array( $id_schedules, $user_id )
							);
							$schedules_companions = $wpdb->get_results( $query, ARRAY_A );

							$schedulesAux['companionsData'] = $schedules_companions;
							
							// Update the total number of people scheduled.
							$total += 1 + $companions;
							
							// Include the schedule data in the schedules array.
							$schedulesProc[] = $schedulesAux;
						}
						
						// Set up table.
						if( ! empty( $schedulesProc ) ){
							$cel_name = 'cell-schedule';
							
							// Sort the data by name to assemble the table.
							usort( $schedulesProc, function( $a, $b ){
								return $a['name'] <=> $b['name'];
							} );
							
							$cel_name = 'th-email'; $table = Templates::change_variable( $table, '<!-- '.$cel_name.' -->', $cell[$cel_name] );
							
							$cel_name = 'cell-schedule';
							
							foreach( $schedulesProc as $schedule ){
								$cel_aux = $cell[$cel_name];
								
								// Include the status of sent or not sent.
								$cel_aux = Templates::change_variable( $cel_aux, '<!-- td-email -->', $cell['td-email'] );
								
								if( $schedule['status'] == 'email-sent' ){
									$cel_aux = Templates::change_variable( $cel_aux, '<!-- sent -->', $cell['sent'] );
								} else {
									$cel_aux = Templates::change_variable( $cel_aux, '<!-- not-sent -->', $cell['not-sent'] );
								}
								
								// Include the name.
								$cel_aux = Templates::change_variable( $cel_aux, '[[name]]', $schedule['name'] );
								
								// Popular escorts.
								$companionNum = 0;
								if( ! empty( $schedule['companionsData'] ) ){
									$cel_aux = Templates::change_variable( $cel_aux, '<!-- td-companions -->', $cell['td-companions'] );
									
									foreach( $schedule['companionsData'] as $companionsData ){
										$companionNum++;

										$cel_comp = 'cell-companion'; $cel_aux_2 = $cell[$cel_comp];

										$cel_aux_2 = Templates::change_variable( $cel_aux_2, '[[num]]', $companionNum );
										$cel_aux_2 = Templates::change_variable( $cel_aux_2, '[[companion]]', $companionsData['name'] );
										
										$cel_aux = Templates::variable_in( $cel_aux, '<!-- '.$cel_comp.' -->', $cel_aux_2 );
									}
								}
								
								$table = Templates::variable_in( $table, '<!-- '.$cel_name.' -->', $cel_aux );
							}
							
							$table = Templates::change_variable( $table, '<!-- '.$cel_name.' -->', '' );
						} else {
							$table = '';
						}
					break;
					case 'confirmed':
						// Get the data from the bank.
						global $wpdb;
						$query = $wpdb->prepare(
							"SELECT id_schedules, companions, user_id, password 
							FROM {$wpdb->prefix}schedules 
							WHERE date = '%s' AND status='confirmed'",
							array( $date )
						);
						$schedules = $wpdb->get_results( $query, ARRAY_A );

						// Scan all schedules.
						if( $schedules )
						foreach( $schedules as $schedule ){
							// Get scheduling data.
							$id_schedules = $schedule['id_schedules'];
							$user_id = $schedule['user_id'];
							$companions = (int)$schedule['companions'];
							$password = $schedule['password'];
							
							// Get user data from the schedule.
							$name = User::get_name( $user_id );

							$schedulesAux = Array(
								'name' => $name,
								'companions' => $companions,
								'password' => $password,
							);
							
							// Get the companions’ details.
							global $wpdb;
							$query = $wpdb->prepare(
								"SELECT name 
								FROM {$wpdb->prefix}schedules_companions 
								WHERE id_schedules = '%s' AND user_id = '%s'",
								array( $id_schedules, $user_id )
							);
							$schedules_companions = $wpdb->get_results( $query, ARRAY_A );

							$schedulesAux['companionsData'] = $schedules_companions;
							
							// Update the total number of people scheduled.
							$total += 1 + $companions;
							
							// Include the schedule data in the schedules array.
							$schedulesProc[] = $schedulesAux;
						}
						
						// Set up table.
						
						$maxCompanion = 0;

						if( ! empty( $schedulesProc ) ){
							$cel_name = 'cell-schedule';
							
							// Sort the data by name to assemble the table.
							usort( $schedulesProc, function( $a, $b ){
								return $a['name'] <=> $b['name'];
							} );
							
							$cel_name = 'th-password'; $table = Templates::change_variable( $table, '<!-- '.$cel_name.' -->', $cell[$cel_name] );
							
							$cel_name = 'cell-schedule';
							
							foreach( $schedulesProc as $schedule ){
								$cel_aux = $cell[$cel_name];
								
								// Include name and password.
								$cel_aux = Templates::change_variable( $cel_aux, '<!-- td-password -->', $cell['td-password'] );
								
								$cel_aux = Templates::change_variable( $cel_aux, '[[name]]', $schedule['name'] );
								$cel_aux = Templates::change_variable( $cel_aux, '[[password]]', $schedule['password'] );
								
								// Popular escorts.
								$companionNum = 0;
								if( ! empty( $schedule['companionsData'] ) ){
									$cel_aux = Templates::change_variable( $cel_aux, '<!-- td-companions -->', $cell['td-companions'] );
									
									foreach( $schedule['companionsData'] as $companionsData ){
										$companionNum++;

										$cel_comp = 'cell-companion'; $cel_aux_2 = $cell[$cel_comp];

										$cel_aux_2 = Templates::change_variable( $cel_aux_2, '[[num]]', $companionNum );
										$cel_aux_2 = Templates::change_variable( $cel_aux_2, '[[companion]]', $companionsData['name'] );
										
										$cel_aux = Templates::variable_in( $cel_aux, '<!-- '.$cel_comp.' -->', $cel_aux_2 );
									}
								}

								if($companionNum > $maxCompanion){
									$maxCompanion = $companionNum;
								}
								
								$table = Templates::variable_in( $table, '<!-- '.$cel_name.' -->', $cel_aux );
							}
							
							$table = Templates::change_variable( $table, '<!-- '.$cel_name.' -->', '' );
						} else {
							$table = '';
						}
						
						// Print options.
						if( $total > 0 ){
							// Set up print table.
							if( ! empty( $schedulesProc ) ){
								$tablePrint = $printLayout;
								
								for( $i=1; $i <= $maxCompanion; $i++ ){
									$cel_aux = $cell2['th-companions'];
									
									$cel_aux = Templates::change_variable( $cel_aux, '#th-companions#', esc_html__( 'Companion', 'competitive-scheduling' ) . ' ' . $i );

									$tablePrint = Templates::variable_in( $tablePrint, '<!-- th-companions -->', $cel_aux );
								}

								$tablePrint = Templates::change_variable( $tablePrint, '<!-- th-companions -->', '' );
								
								$cel_name = 'cell-people';
								
								foreach( $schedulesProc as $schedule ){
									$cel_aux = $cell2[$cel_name];
									
									// Include password and name.
									$cel_aux = Templates::change_variable( $cel_aux, '#name#', $schedule['name'] );
									$cel_aux = Templates::change_variable( $cel_aux, '#password#', $schedule['password'] );
									
									// Popular escorts.
									$companionNum = 0;
									if( ! empty( $schedule['companionsData'] ) ){
										$cel_aux = Templates::change_variable( $cel_aux, '<!-- td-companions -->', $cell['td-companions'] );
										
										foreach( $schedule['companionsData'] as $companionsData ){
											$companionNum++;

											$cel_comp = 'td-companions'; $cel_aux_2 = $cell2[$cel_comp];

											$cel_aux_2 = Templates::change_variable( $cel_aux_2, '#td-companions#', $companionsData['name'] );
											
											$cel_aux = Templates::variable_in( $cel_aux, '<!-- '.$cel_comp.' -->', $cel_aux_2 );
										}
									}
									
									for( $i=( $companionNum + 1 ); $i <= $maxCompanion; $i++ ){
										$cel_aux_2 = $cell2['td-companions'];

										$cel_aux_2 = Templates::change_variable( $cel_aux_2, '#td-companions#', '' );
										
										$cel_aux = Templates::variable_in( $cel_aux, '<!-- td-companions -->', $cel_aux_2 );
									}
									
									$cel_aux = Templates::change_variable( $cel_aux, '<!-- td-companions -->', '' );
									
									$tablePrint = Templates::variable_in( $tablePrint, '<!-- '.$cel_name.' -->', $cel_aux );
								}
								
								$tablePrint = Templates::change_variable( $tablePrint, '<!-- '.$cel_name.' -->', '' );
							} else {
								$tablePrint = '';
							}
							
							// Variable printing patterns.
							$print = true;
							
							// Format date.
							$dateStr = Formats::data_format_to( 'date-to-text', $date );
							
							// Change print header fields.
							$printHeader = Templates::change_variable( $printHeader, '#date#', $dateStr );
							$printHeader = Templates::change_variable( $printHeader, '#total#', $total );
							
							$tablePrint = $printHeader . $tablePrint;
						}
					break;
					case 'finalized':
						// Get the data from the bank.
						global $wpdb;
						$query = $wpdb->prepare(
							"SELECT id_schedules, companions, user_id 
							FROM {$wpdb->prefix}schedules 
							WHERE date = '%s' AND status='finalized'",
							array( $date )
						);
						$schedules = $wpdb->get_results( $query, ARRAY_A );

						// Scan all schedules.
						if( $schedules )
						foreach( $schedules as $schedule ){
							// Get scheduling data.
							$id_schedules = $schedule['id_schedules'];
							$user_id = $schedule['user_id'];
							$companions = (int)$schedule['companions'];
							
							// Get user data from the schedule.
							$name = User::get_name( $user_id );

							$schedulesAux = Array(
								'name' => $name,
								'companions' => $companions,
							);
							
							// Get the companions’ details.
							global $wpdb;
							$query = $wpdb->prepare(
								"SELECT name 
								FROM {$wpdb->prefix}schedules_companions 
								WHERE id_schedules = '%s' AND user_id = '%s'",
								array( $id_schedules, $user_id )
							);
							$schedules_companions = $wpdb->get_results( $query, ARRAY_A );

							$schedulesAux['companionsData'] = $schedules_companions;

							// Update the total number of people scheduled.
							$total += 1 + $companions;
							
							// Include the schedule data in the schedules array.
							$schedulesProc[] = $schedulesAux;
						}
						
						// Set up table.
						if( ! empty( $schedulesProc ) ){
							$cel_name = 'cell-schedule';
							
							// Sort the data by name to assemble the table.
							usort( $schedulesProc, function( $a, $b ){
								return $a['name'] <=> $b['name'];
							} );

							$cel_name = 'cell-schedule';
							
							foreach( $schedulesProc as $schedule ){
								$cel_aux = $cell[$cel_name];
								
								// Include the name.
								$cel_aux = Templates::change_variable( $cel_aux, '[[name]]', $schedule['name'] );
								
								// Popular escorts.
								$companionNum = 0;
								if( ! empty( $schedule['companionsData'] ) ){
									$cel_aux = Templates::change_variable( $cel_aux, '<!-- td-companions -->', $cell['td-companions'] );
									
									foreach( $schedule['companionsData'] as $companionsData ){
										$companionNum++;

										$cel_comp = 'cell-companion'; $cel_aux_2 = $cell[$cel_comp];

										$cel_aux_2 = Templates::change_variable( $cel_aux_2, '[[num]]', $companionNum );
										$cel_aux_2 = Templates::change_variable( $cel_aux_2, '[[companion]]', $companionsData['name'] );
										
										$cel_aux = Templates::variable_in( $cel_aux, '<!-- '.$cel_comp.' -->', $cel_aux_2 );
									}
								}
								
								$table = Templates::variable_in( $table, '<!-- '.$cel_name.' -->', $cel_aux );
							}
							
							$table = Templates::change_variable( $table, '<!-- '.$cel_name.' -->', '' );
						} else {
							$table = '';
						}
					break;
					default:
						$table = '';
				}
				
				// Response data
				$response = array(
					'status'        => 'OK',
					'total' 		=> $total,
					'table' 		=> $table,
					'print' 		=> $print,
					'tablePrint' 	=> ( ! empty( $tablePrint ) ? $tablePrint : '' ),
					'nonce'         => wp_create_nonce( 'schedules-nonce' ),
				);
			} else {
				// Response data
				$response = array(
					'status' => 'ERROR',
					'alert'  => __( 'User is not logged in', 'competitive-scheduling' ),
				);
			}

			return rest_ensure_response( $response );
		}

		private function schedules_calendar() {
			// Require formats class to prepare data.
			require_once CS_PATH . 'includes/class.formats.php';

			// Force date to today for debuging or set today's date
			if ( CS_FORCE_DATE_TODAY ) {
				$today = CS_DATE_TODAY_FORCED_VALUE;
			} else {
				$today = date( 'Y-m-d' ); }

			// Get the configuration data.
			$options = get_option( 'competitive_scheduling_options' );

			$days_week = ( isset( $options['days-week'] ) ? explode( ',', $options['days-week'] ) : array() );
			$years     = ( isset( $options['calendar-years'] ) ? (int) $options['calendar-years'] : 2 );
			if ( isset( $options['unavailable-dates'] ) ) {
				$unavailable_dates = ( isset( $options['unavailable-dates-values'] ) ? explode( '|', $options['unavailable-dates-values'] ) : array() );
			}
			$calendar_holidays_start = ( isset( $options['calendar-holidays-start'] ) ? trim( $options['calendar-holidays-start'] ) : '15 December' );
			$calendar_holidays_end   = ( isset( $options['calendar-holidays-end'] ) ? trim( $options['calendar-holidays-end'] ) : '20 January' );

			$start_year = date( 'Y' );
			$year_end   = (int) $start_year + $years;

			for ( $i = -1; $i < $years + 1; $i++ ) {
				$period_holidays[] = array(
					'start' => strtotime( $calendar_holidays_start . ' ' . ( $start_year + $i ) ),
					'end'   => strtotime( $calendar_holidays_end . ' ' . ( $start_year + $i + 1 ) ),
				);
			}

			$first_day = strtotime( date( 'Y-m-d', time() ) . ' + 1 day' );
			$last_day  = strtotime( date( 'Y-m-d', time() ) . ' + ' . $years . ' year' );

			$day = $first_day;
			do {
				$dateFormatted = date( 'd/m/Y', $day );
				$flag          = false;

				if ( isset( $period_holidays ) ) {
					foreach ( $period_holidays as $period ) {
						if (
							$day > $period['start'] &&
							$day < $period['end']
						) {
							$flag = true;
							break;
						}
					}
				}

				if ( isset( $unavailable_dates ) ) {
					foreach ( $unavailable_dates as $ud ) {
						if ( $dateFormatted == $ud ) {
							$flag = true;
							break;
						}
					}
				}

				if ( ! $flag ) {
					$flag2 = false;

					if ( isset( $days_week ) ) {
						foreach ( $days_week as $day_week ) {
							if ( $day_week == strtolower( date( 'D', $day ) ) ) {
								$flag2 = true;
								break;
							}
						}
					}

					if ( $flag2 ) {
						$date           = date( 'Y-m-d', $day );
						$dates[ $date ] = 1;
					}
				}

				$day += 86400;
			} while ( $day < $last_day );

			return array(
				'available_dates' => ( isset( $dates ) ? $dates : array() ),
				'start_year'      => $start_year,
				'year_end'        => $year_end,
			);
		}
	}
}
