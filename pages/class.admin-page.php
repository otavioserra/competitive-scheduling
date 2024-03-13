<?php

if ( ! class_exists( 'Competitive_Scheduling_Admin_Page' ) ) {
	class Competitive_Scheduling_Admin_Page {

		public function __construct() {
			/* add_action(
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
			); */
		}

		public function page() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
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
			Interfaces::finish();

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

				// Get schedule data.
				$schedule_id = $params['schedule_id'];

				// Sanitize all fields
				$schedule_id = sanitize_text_field( $schedule_id );

				// Get user ID
				$user_id = get_current_user_id();

				// Get cells from the data.
				$page = file_get_contents( CS_PATH . 'views/competitive-scheduling_shortecode.php' );

				$cell_name          = 'cell-data';
				$cell[ $cell_name ] = Templates::tag_value( $page, '<!-- ' . $cell_name . ' < -->', '<!-- ' . $cell_name . ' > -->' );
				$page               = Templates::tag_in( $page, '<!-- ' . $cell_name . ' < -->', '<!-- ' . $cell_name . ' > -->', '<!-- ' . $cell_name . ' -->' );
				$cell_name          = 'schedule-data';
				$cell[ $cell_name ] = Templates::tag_value( $page, '<!-- ' . $cell_name . ' < -->', '<!-- ' . $cell_name . ' > -->' );
				$page               = Templates::tag_in( $page, '<!-- ' . $cell_name . ' < -->', '<!-- ' . $cell_name . ' > -->', '<!-- ' . $cell_name . ' -->' );

				$dataSchedules = $cell['schedule-data'];

				// Get the user's full name.
				$first_name = get_user_meta( $user_id, 'first_name', true );
				$last_name  = get_user_meta( $user_id, 'last_name', true );

				if ( ! empty( $first_name ) && ! empty( $last_name ) ) {
					$user_name = $first_name . ' ' . $last_name;
				} else {
					$user_data = get_userdata( $user_id );
					$user_name = $user_data->display_name;
				}

				$dataSchedules = Templates::change_variable( $dataSchedules, '[[header-name]]', __( 'Scheduled People', 'competitive-scheduling' ) );
				$dataSchedules = Templates::change_variable( $dataSchedules, '[[your-name-title]]', __( 'Your name', 'competitive-scheduling' ) );
				$dataSchedules = Templates::change_variable( $dataSchedules, '[[your-name]]', $user_name );

				// Companion details.
				global $wpdb;
				$query                = $wpdb->prepare(
					"SELECT name 
                    FROM {$wpdb->prefix}schedules_companions 
                    WHERE id_schedules = '%s' AND user_id = '%s'",
					array( $schedule_id, $user_id )
				);
				$schedules_companions = $wpdb->get_results( $query );

				// Set up the companions' cell.
				$num = 0;
				if ( $schedules_companions ) {
					foreach ( $schedules_companions as $companion ) {
						++$num;

						$cell_aux = $cell['cell-data'];

						$cell_aux = Templates::change_variable( $cell_aux, '[[companion-title]]', __( 'Companion', 'competitive-scheduling' ) . ' ' . $num );
						$cell_aux = Templates::change_variable( $cell_aux, '[[companion]]', $companion->name );

						$dataSchedules = Templates::variable_in( $dataSchedules, '<!-- cell-data -->', $cell_aux );
					}
				}

				// Response data
				$response = array(
					'status'        => 'OK',
					'dataSchedules' => $dataSchedules,
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
