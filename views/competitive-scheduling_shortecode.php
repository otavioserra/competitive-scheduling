<div class="ui container buttonsMargin">
    <div class="ui hidden divider"></div>
    <div class="ui hidden divider"></div>
    <!-- inactive < --><div class="agendamento-inativo">
        <div class="ui header"><?php echo esc_html__( 'Inactive Schedule', 'competitive-scheduling' ); ?></div>
        [[msg-scheduling-suspended]]
    </div><!-- inactive > -->
    <!-- active < --><div class="active-scheduling buttonsMargin">
        <a class="ui positive button scheduleBtn" data-content="<?php echo esc_attr__( 'Click to create a new schedule', 'competitive-scheduling' ); ?>" data-position="bottom left" data-variation="inverted">
            <i class="calendar plus icon"></i>
            <?php echo esc_html__( 'Schedule Service', 'competitive-scheduling' ); ?>
        </a>
        <a class="ui blue button schedulesBtn" data-content="<?php echo esc_attr__( 'Click to view previous appointments', 'competitive-scheduling' ); ?>" data-position="bottom left" data-variation="inverted">
            <i class="calendar alternate icon"></i>
            <?php echo esc_html__( 'Previous Appointments', 'competitive-scheduling' ); ?>
    	</a>
        <div class="ui hidden divider"></div>
        <div class="schedule hidden scheduleWindow">
            <div class="ui info message">
                <div class="header">
                    <?php echo esc_html__( 'Scheduling Phases', 'competitive-scheduling' ); ?>
                </div>
                <p><?php echo esc_html__( 'The scheduling process is divided into 4 phases:', 'competitive-scheduling' ); ?></p>
            </div>
            <div class="ui four steps">
                <div class="active step">
                    <i class="calendar plus icon"></i>
                    <div class="content">
                        <div class="title"><?php echo esc_html__( 'Registration', 'competitive-scheduling' ); ?></div>
                        <div class="description"><?php echo esc_html__( 'Registration period to participate in a schedule.', 'competitive-scheduling' ); ?></div>
                    </div>
                </div>
                <div class="step">
                    <i class="clock outline icon"></i>
                    <div class="content">
                        <div class="title"><?php echo esc_html__( 'Waiting Draw', 'competitive-scheduling' ); ?></div>
                        <div class="description"><?php echo esc_html__( 'Waiting for scheduling slot draw.', 'competitive-scheduling' ); ?></div>
                    </div>
                </div>
                <div class="step">
                    <i class="calendar check outline icon"></i>
                    <div class="content">
                        <div class="title"><?php echo esc_html__( 'Draw and Confirmation', 'competitive-scheduling' ); ?></div>
                        <div class="description"><?php echo esc_html__( 'A confirmation email will be sent if you are drawn.', 'competitive-scheduling' ); ?></div>
                    </div>
                </div>
                <div class="step">
                    <i class="route icon"></i>
                    <div class="content">
                        <div class="title"><?php echo esc_html__( 'Utilization', 'competitive-scheduling' ); ?></div>
                        <div class="description"><?php echo esc_html__( 'On the day of booking, use your password sent after confirmation and go to the establishment.', 'competitive-scheduling' ); ?></div>
                    </div>
                </div>
            </div>
            <div class="ui attached info message">
                <div class="header">
                    <?php echo esc_html__( 'Schedule Registration Service', 'competitive-scheduling' ); ?>
                </div>
                <p><?php echo esc_html__( 'Choose an available date below. As well as if you have any companion(s), choose the number and fill in the name of each one. Additionally, optionally, if you have a priority coupon, enter it below. Finally, click the SUBMIT button to create a new schedule.', 'competitive-scheduling' ); ?></p>
            </div>
            <form class="ui form attached fluid segment" method="post" id="formSchedules">
                <div class="two fields">
                    <div class="field">
                        <label><?php echo esc_html__( 'Choose the Date', 'competitive-scheduling' ); ?></label>
                        <input type="hidden" name="data" class="scheduleDate">
                        <div class="dateSelected hidden">
                            <div class="ui primary large label">
                                <i class="calendar check icon"></i>
                                <span class="dateSelectedValue"></span>
                            </div>
                        </div>
                        <div class="ui calendar"></div>
                    </div>
                    <div class="field">
                        <label><?php echo esc_html__( 'Companion', 'competitive-scheduling' ); ?></label>
                        <select class="ui dropdown" name="companions">
                            <!-- companions < --><option value="#num#">#num#</option><!-- companions > -->
                        </select>
                        <div class="companionsCont">
                        </div>
                        <div class="companionsTemplateCont hidden">
                            <div class="field">
                                <label>Field</label>
                                <input name="field" type="text">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="two fields">
                    <div class="field">
                        <label><?php echo esc_html__( 'Priority Coupon', 'competitive-scheduling' ); ?></label>
                        <input name="coupon" type="text" class="coupon">
                    </div>
                </div>
                <div class="ui error message"></div>
                <div class="ui center aligned basic segment">
                    <button id="formAgendarBtn" data-tooltip="<?php echo esc_attr__( 'Click this button to SAVE the changes.', 'competitive-scheduling' ); ?>" data-position="top center" data-inverted="" class="positive ui button"><?php echo esc_html__( 'Send', 'competitive-scheduling' ); ?></button>
                </div>
                <input type="hidden" name="schedule" value="1">
                <input type="hidden" name="schedule-nonce" value="<?php echo wp_create_nonce( 'schedule-nonce' ); ?>">
            </form>
        </div>
        <div class="schedules hidden scheduleWindow">
            <div class="ui header"><?php echo esc_html__( 'Pre-appointments', 'competitive-scheduling' ); ?></div>
            <div class="pre-agendamentos">
                <div class="ui top attached tabular menu">
                    <a class="active item" data-tab="lista-1"><?php echo esc_html__( 'Listing', 'competitive-scheduling' ); ?></a>
                    <a class="item" data-tab="informative-1"><?php echo esc_html__( 'Information', 'competitive-scheduling' ); ?></a>
                </div>
                <div class="ui bottom attached active tab segment" data-tab="lista-1">
                    #pre_appointments#
                </div>
                <div class="ui bottom attached tab segment" data-tab="informative-1">
                    <p><?php echo __( '<span class="ui red text">IMPORTANT 1:</span> Pre-appointments ARE NOT confirmed appointments. They will be drawn through the system <span class="ui blue text">#draw_date#</span> days before the day of service. If your pre-booking is drawn, you must confirm your booking via an email that will be sent <span class="ui blue text">#draw_date#</span> days before the day of the appointment. Or by directly accessing our system after this date and choosing the CONFIRM APPOINTMENT option for the day of your appointment. This confirmation must be made between <span class="ui blue text">#date_confirmation_1#</span> to <span class="ui blue text">#date_confirmation_2#</span> days before the day of service. If you do not confirm your appointment within this period, the places guaranteed in your pre-booking draw will no longer be effective and the places will be released to be chosen by other people via the system again.', 'competitive-scheduling' ); ?></p>
                    <p><?php echo __( '<span class="ui red text">IMPORTANT 2:</span> If there are more pre-bookings than there are service spaces, the system will automatically carry out a draw and send a confirmation email to those selected, otherwise it will send a confirmation email to all. Therefore, if you do not receive a confirmation email, it is because you were not selected to participate in the service.', 'competitive-scheduling' ); ?></p>
                    <p><?php echo __( '<span class="ui red text">IMPORTANT 3:</span> After the day <span class="ui blue text">#date_confirmation_2#</span> the scheduling system will release the residual vacancies to be chosen again and If you have not confirmed, or have not been drawn, you can choose the same date for an appointment. At this stage, places are not guaranteed and can be chosen by anyone who accesses the system.', 'competitive-scheduling' ); ?></p>
                </div>
            </div>
            <!-- pre-appointments < -->
            <div class="ui stackable two column grid tabelaPreAgendamentos">
                <!-- cell-pre < --><div class="column">
                <div class="ui segment">
                    <div class="ui top large attached label"><?php echo esc_html__( 'Service Date:', 'competitive-scheduling' ); ?> [[date]]</div>
                    <table class="ui definition unstackable table">
                        <tbody>
                            <tr>
                                <td class="five wide"><?php echo esc_html__( 'Current state', 'competitive-scheduling' ); ?></td>
                                <td>[[status]]</td>
                            </tr>
                            <tr>
                                <td><?php echo esc_html__( 'Next Update', 'competitive-scheduling' ); ?></td>
                                <td>[[update]]</td>
                            </tr>
                            <tr>
                                <td><?php echo esc_html__( 'Modification Date', 'competitive-scheduling' ); ?></td>
                                <td>[[modification_date]]</td>
                            </tr>
                            <tr>
                                <td><?php echo esc_html__( 'Qty. People', 'competitive-scheduling' ); ?></td>
                                <td>[[people]]</td>
                            </tr>
                            <tr>
                                <td><?php echo esc_html__( 'People', 'competitive-scheduling' ); ?></td>
                                <td>
                                	<div class="ui icon buttons">
                                        <a class="ui tiny button basic blue dataScheduleBtn preAgendamento" data-content="<?php echo esc_attr__( 'Click to View Scheduling Data', 'competitive-scheduling' ); ?>" data-id="[[schedule_id]]" data-position="top right" data-variation="inverted">
                                            <i class="file alternate outline icon"></i>
                                            <?php echo esc_html__( 'View', 'competitive-scheduling' ); ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo esc_html__( 'Options', 'competitive-scheduling' ); ?></td>
                                <td>
                                	<div class="ui icon buttons">
                                        <!-- cancel-btn < --><a class="ui tiny button basic red cancelSchedulingBtn preAgendamento" data-content="<?php echo esc_attr__( 'Click to Cancel Schedule', 'competitive-scheduling' ); ?>" data-id="[[schedule_id]]" data-position="top right" data-variation="inverted">
                                            <i class="calendar minus outline icon"></i>
                                            <?php echo esc_html__( 'Cancel', 'competitive-scheduling' ); ?>
                                        </a><!-- cancel-btn > -->
                                        <!-- confirm-btn < --><a class="ui tiny button basic green confirmScheduleBtn preAgendamento" data-content="<?php echo esc_attr__( 'Click to Confirm Appointment', 'competitive-scheduling' ); ?>" data-id="[[schedule_id]]" data-position="top right" data-variation="inverted">
                                            <i class="calendar check outline icon"></i>
                                            <?php echo esc_html__( 'Confirm', 'competitive-scheduling' ); ?>
                                        </a><!-- confirm-btn > -->
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                </div><!-- cell-pre > -->
            </div>
            <!-- load-more-pre < --><div class="ui center aligned basic segment">
                <a class="ui blue button loadMorePre" data-tooltip="<?php echo esc_attr__( 'Click this button to load more records.', 'competitive-scheduling' ); ?>" data-position="top center" data-inverted="" data-num-pages="[[numPages]]">
                    <i class="calendar alternate outline icon"></i>
                    <?php echo esc_html__( 'Load More', 'competitive-scheduling' ); ?>
                </a>
            </div><!-- load-more-pre > -->
            <!-- pre-appointments > -->
            <div class="ui header"><?php echo esc_html__( 'Confirmed Appointments', 'competitive-scheduling' ); ?></div>
            <div class="agendamentos-confirmados">
                <div class="ui top attached tabular menu">
                    <a class="active item" data-tab="lista-2"><?php echo esc_html__( 'Listing', 'competitive-scheduling' ); ?></a>
                    <a class="item" data-tab="informative-2"><?php echo esc_html__( 'Information', 'competitive-scheduling' ); ?></a>
                </div>
                <div class="ui bottom attached active tab segment" data-tab="lista-2">
                    #confirmed_appointments#
                </div>
                <div class="ui bottom attached tab segment" data-tab="informative-2">
                    <p><?php echo __( '<span class="ui red text">IMPORTANT 1:</span> It is mandatory to present the name and password provided below for you and your companions on the day of the appointment.', 'competitive-scheduling' ); ?></p>
                </div>
            </div>
            <!-- appointments < -->
            <div class="ui stackable two column grid tabelaAgendamentos">
                <!-- cell-appointments < --><div class="column">
                <div class="ui segment">
                    <div class="ui top large attached label"><?php echo esc_html__( 'Service Date:', 'competitive-scheduling' ); ?> [[date]]</div>
                    <table class="ui definition unstackable table">
                        <tbody>
                            <tr>
                                <td class="five wide"><?php echo esc_html__( 'Current state', 'competitive-scheduling' ); ?></td>
                                <td>[[status]]</td>
                            </tr>
                            <tr>
                                <td><?php echo esc_html__( 'Password', 'competitive-scheduling' ); ?></td>
                                <td>[[password]]</td>
                            </tr>
                            <tr>
                                <td><?php echo esc_html__( 'Modification Date', 'competitive-scheduling' ); ?></td>
                                <td>[[modification_date]]</td>
                            </tr>
                            <tr>
                                <td><?php echo esc_html__( 'Qty. People', 'competitive-scheduling' ); ?></td>
                                <td>[[people]]</td>
                            </tr>
                            <tr>
                                <td><?php echo esc_html__( 'People', 'competitive-scheduling' ); ?></td>
                                <td>
                                	<div class="ui icon buttons">
                                        <a class="ui tiny button basic blue dataScheduleBtn agendamento" data-content="<?php echo esc_attr__( 'Click to View Scheduling Data', 'competitive-scheduling' ); ?>" data-id="[[schedule_id]]" data-position="top right" data-variation="inverted">
                                            <i class="file alternate outline icon"></i>
                                            <?php echo esc_html__( 'View', 'competitive-scheduling' ); ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo esc_html__( 'Options', 'competitive-scheduling' ); ?></td>
                                <td>
                                	<div class="ui icon buttons">
                                        <!-- cancel-btn < --><a class="ui tiny button basic red cancelSchedulingBtn agendamento" data-content="<?php echo esc_attr__( 'Click to Cancel Schedule', 'competitive-scheduling' ); ?>" data-id="[[schedule_id]]" data-position="top right" data-variation="inverted">
                                            <i class="calendar minus outline icon"></i>
                                            <?php echo esc_html__( 'Cancel', 'competitive-scheduling' ); ?>
                                        </a><!-- cancel-btn > -->
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                </div><!-- cell-appointments > -->
            </div>
            <!-- load-more-schedules < --><div class="ui center aligned basic segment">
                <a class="ui blue button loadMoreAppointments" data-tooltip="<?php echo esc_attr__( 'Click this button to load more records.', 'competitive-scheduling' ); ?>" data-position="top center" data-inverted="" data-num-pages="[[numPages]]">
                    <i class="calendar alternate outline icon"></i>
                    <?php echo esc_html__( 'Load More', 'competitive-scheduling' ); ?>
                </a>
            </div><!-- load-more-schedules > -->
            <!-- appointments > -->
            <div class="ui header"><?php echo esc_html__( 'Old Schedules', 'competitive-scheduling' ); ?></div>
            <p>#old_schedules#</p>
            <!-- old-appointments < -->
            <div class="ui stackable two column grid tabelaAgendamentosAntigos">
                <!-- cell-olds < --><div class="column">
                <div class="ui segment">
                    <div class="ui top large attached label"><?php echo esc_html__( 'Service Date:', 'competitive-scheduling' ); ?> [[date]]</div>
                    <table class="ui definition unstackable table">
                        <tbody>
                            <tr>
                                <td class="five wide"><?php echo esc_html__( 'Current state', 'competitive-scheduling' ); ?></td>
                                <td>[[status]]</td>
                            </tr>
                            <tr>
                                <td><?php echo esc_html__( 'Modification Date', 'competitive-scheduling' ); ?></td>
                                <td>[[modification_date]]</td>
                            </tr>
                            <tr>
                                <td><?php echo esc_html__( 'Qty. People', 'competitive-scheduling' ); ?></td>
                                <td>[[people]]</td>
                            </tr>
                            <tr>
                            <td><?php echo esc_html__( 'People', 'competitive-scheduling' ); ?></td>
                                <td>
                                	<div class="ui icon buttons">
                                        <a class="ui tiny button basic blue dataScheduleBtn agendamentoAntigo" data-content="<?php echo esc_attr__( 'Click to View Scheduling Data', 'competitive-scheduling' ); ?>" data-id="[[schedule_id]]" data-position="top right" data-variation="inverted">
                                            <i class="file alternate outline icon"></i>
                                            <?php echo esc_html__( 'View', 'competitive-scheduling' ); ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                </div><!-- cell-olds > -->
            </div>
            <!-- load-oldest < --><div class="ui center aligned basic segment">
                <a class="ui blue button loadOldest" data-tooltip="<?php echo esc_attr__( 'Click this button to load more records.', 'competitive-scheduling' ); ?>" data-position="top center" data-inverted="" data-num-pages="[[numPages]]">
                    <i class="calendar alternate outline icon"></i>
                    <?php echo esc_html__( 'Load More', 'competitive-scheduling' ); ?>
                </a>
            </div><!-- load-oldest > -->
            <!-- old-appointments > -->
            <!-- schedule-data < --><table class="ui definition table">
                <thead>
                    <tr>
                        <th></th>
                        <th><?php echo esc_html__( 'Scheduled People', 'competitive-scheduling' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo esc_html__( 'Your name', 'competitive-scheduling' ); ?></td>
                        <td>[[your-name]]</td>
                    </tr>
                    <!-- cell-data < --><tr>
                        <td><?php echo esc_html__( 'Companion', 'competitive-scheduling' ); ?> [[num]]</td>
                        <td>[[companion]]</td>
                    </tr><!-- cell-data > -->
                </tbody>
            </table><!-- schedule-data > -->
            <!-- unregistered < --><div class="ui icon info message">
                <i class="calendar times outline icon"></i>
                <div class="content">
                    <div class="header">
                        <?php echo esc_html__( 'No Records', 'competitive-scheduling' ); ?>
                    </div>
                    <p><?php echo esc_html__( 'There are no registered appointments of this type.', 'competitive-scheduling' ); ?></p>
                </div>
            </div><!-- unregistered > -->
        </div>
    </div><!-- active > -->
    <!-- changes < --><div class="confirmar hidden scheduleWindow">
        <div class="ui header"><?php echo esc_html__( 'Confirm Appointment', 'competitive-scheduling' ); ?></div>
        <div class="ui icon attached positive message">
            <i class="calendar check icon"></i>
            <div class="content">
                <div class="header">
                    <?php echo esc_html__( 'Instructions', 'competitive-scheduling' ); ?>
                </div>
                <p><?php echo esc_html__( 'Are you sure you want to confirm this appointment? If yes, click on the <b>CONFIRM</b> button, otherwise click on the <b>CANCEL</b> button.', 'competitive-scheduling' ); ?></p>
            </div>
        </div>
        <form class="ui form attached fluid segment confirmationForm" method="post" action="<?php echo get_permalink(); ?>">
            <div class="ui primary large label">
                <i class="calendar check icon"></i>
                <?php echo esc_html__( 'Schedule Date:', 'competitive-scheduling' ); ?> [[confirmation-date]]
            </div>
            <br>
            <br>
            <div class="ui icon buttons">
                <div class="ui icon buttons">
                    <a class="ui button red cancelSchedulingBtn" data-content="<?php echo esc_attr__( 'Click to Cancel Schedule', 'competitive-scheduling' ); ?>" data-position="top left" data-variation="inverted">
                        <i class="calendar minus outline icon"></i>
                        <?php echo esc_html__( 'Cancel', 'competitive-scheduling' ); ?>
                    </a>
                    <a class="ui button green confirmScheduleBtn" data-content="<?php echo esc_attr__( 'Click to Confirm Appointment', 'competitive-scheduling' ); ?>" data-position="top left" data-variation="inverted">
                        <i class="calendar check outline icon"></i>
                        <?php echo esc_html__( 'Confirm', 'competitive-scheduling' ); ?>
                    </a>
                </div>
            </div>
            <input type="hidden" name="make_confirmation" value="1">
            <input type="hidden" name="choice" value="cancelar">
            <input type="hidden" name="action" value="confirmar">
            <input type="hidden" name="schedule_id" value="[[confirmation-scheduling-id]]">
        </form>
        <div class="ui bottom attached warning message">
            <i class="icon exclamation triangle"></i>
            <?php echo esc_html__( 'Important: it is not possible to cancel the cancellation of this appointment!', 'competitive-scheduling' ); ?>
        </div>
    </div>
    <div class="cancelar hidden scheduleWindow">
        <div class="ui header"><?php echo esc_html__( 'Cancel Appointment', 'competitive-scheduling' ); ?></div>
        <div class="ui icon attached warning message">
            <i class="calendar minus icon"></i>
            <div class="content">
                <div class="header">
                    <?php echo esc_html__( 'Instructions', 'competitive-scheduling' ); ?>
                </div>
                <p><?php echo esc_html__( 'Are you sure you want to cancel this appointment? If yes, click the <b>CANCEL</b> button.', 'competitive-scheduling' ); ?></p>
            </div>
        </div>
        <form class="ui form attached fluid segment cancelForm" method="post" action="<?php echo get_permalink(); ?>">
            <div class="ui primary large label">
                <i class="calendar check icon"></i>
                <?php echo esc_html__( 'Schedule Date:', 'competitive-scheduling' ); ?> [[cancellation-date]]
            </div>
            <br>
            <br>
            <div class="ui icon buttons">
                <div class="ui icon buttons">
                    <a class="ui button red cancelSchedulingBtn" data-content="<?php echo esc_attr__( 'Click to Cancel Schedule', 'competitive-scheduling' ); ?>" data-position="top left" data-variation="inverted">
                        <i class="calendar minus outline icon"></i>
                        <?php echo esc_html__( 'Cancel', 'competitive-scheduling' ); ?>
                    </a>
                </div>
            </div>
            <input type="hidden" name="make_cancel" value="1">
            <input type="hidden" name="action" value="cancelar">
            <input type="hidden" name="schedule_id" value="[[cancellation-scheduling-id]]">
        </form>
        <div class="ui bottom attached warning message">
            <i class="icon exclamation triangle"></i>
            <?php echo esc_html__( 'Important: it is not possible to cancel the cancellation of this appointment!', 'competitive-scheduling' ); ?>
        </div>
    </div>
    <div class="ExpiredOrNotFound hidden scheduleWindow">
        <div class="ui header"><?php echo esc_html__( 'Schedule Change', 'competitive-scheduling' ); ?></div>
        <div class="ui icon attached warning message">
            <i class="exclamation triangle icon"></i>
            <div class="content">
                <div class="header">
                    <?php echo esc_html__( 'Validation Code Not Found', 'competitive-scheduling' ); ?>
                </div>
                <p><?php echo esc_html__( 'The validation code for changing your schedule was not found!', 'competitive-scheduling' ); ?></p>
                <p><?php echo esc_html__( 'Possible reasons:', 'competitive-scheduling' ); ?></p>
                <ol class="ui list">
                    <li><?php echo esc_html__( 'The validation code has already been used in another attempt to change the schedule.', 'competitive-scheduling' ); ?></li>
                    <li><?php echo esc_html__( 'The deadline for changing the schedule has been reached.', 'competitive-scheduling' ); ?></li>
                    <li><?php echo esc_html__( 'The code sent is invalid.', 'competitive-scheduling' ); ?></li>
                </ol>
                <p><?php echo esc_html__( 'Access your appointments by clicking', 'competitive-scheduling' ); ?> <a href="<?php echo get_permalink(); ?>"><?php echo esc_html__( 'here', 'competitive-scheduling' ); ?></a>.</p>
            </div>
        </div>
    </div><!-- changes > -->
    <div class="ui hidden divider"></div>
    <div class="ui hidden divider"></div>
</div>