<div class="ui container buttonsMargin">
    <div class="ui hidden divider"></div>
    <div class="ui hidden divider"></div>
    <!-- changes < -->
    <div class="confirmPublic hidden scheduleWindow">
        <div class="ui header"><?php echo esc_html__( 'Confirm Schedule', 'competitive-scheduling' ); ?></div>
        <div class="ui icon attached positive message">
            <i class="calendar check icon"></i>
            <div class="content">
                <div class="header">
                    <?php echo esc_html__( 'Instructions', 'competitive-scheduling' ); ?>
                </div>
                <p><?php echo __( 'Are you sure you want to confirm this schedule? If yes, click on the <b>CONFIRM</b> button, otherwise click on the <b>CANCEL</b> button.', 'competitive-scheduling' ); ?></p>
            </div>
        </div>
        <form class="ui form attached fluid segment confirmationPublicForm" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <div class="ui icon buttons">
                <div class="ui icon buttons">
                    <a class="ui button red cancelPublicSchedulingBtn" data-content="<?php echo esc_html__( 'Click to Cancel Schedule', 'competitive-scheduling' ); ?>" data-position="top left" data-variation="inverted">
                        <i class="calendar minus outline icon"></i>
                        <?php echo esc_html__( 'Cancel', 'competitive-scheduling' ); ?>
                    </a>
                    <a class="ui button green confirmPublicSchedulleBtn" data-content="<?php echo esc_html__( 'Click to Confirm Schedule', 'competitive-scheduling' ); ?>" data-position="top left" data-variation="inverted">
                        <i class="calendar check outline icon"></i>
                        <?php echo esc_html__( 'Confirm', 'competitive-scheduling' ); ?>
                    </a>
                </div>
            </div>
            <input type="hidden" name="action" value="schedule_confirmation">
            <input type="hidden" name="action_after_acceptance" value="1">
            <input type="hidden" name="choice" value="confirm">
            <input type="hidden" name="token" value="[[token]]">
            <input type="hidden" name="pubID" value="[[pubID]]">
        </form>
        <div class="ui bottom attached warning message">
            <i class="icon exclamation triangle"></i>
            <?php echo esc_html__( 'Important: it is not possible to cancel the cancellation of this appointment!', 'competitive-scheduling' ); ?>
        </div>
    </div>
    <div class="cancelPublic hidden scheduleWindow">
        <div class="ui header"><?php echo esc_html__( 'Cancel Schedule', 'competitive-scheduling' ); ?></div>
        <div class="ui icon attached warning message">
            <i class="calendar minus icon"></i>
            <div class="content">
                <div class="header">
                    <?php echo esc_html__( 'Instructions', 'competitive-scheduling' ); ?>
                </div>
                <p><?php echo __( 'Are you sure you want to cancel this schedule? If yes, click the <b>CANCEL</b> button.', 'competitive-scheduling' ); ?></p>
            </div>
        </div>
        <form class="ui form attached fluid segment cancellationPublicoForm" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <div class="ui icon buttons">
                <div class="ui icon buttons">
                    <a class="ui button red cancelPublicSchedulingBtn" title="<?php echo esc_html__( 'Click to Cancel Schedule', 'competitive-scheduling' ); ?>">
                        <i class="calendar minus outline icon"></i>
                        <?php echo esc_html__( 'Cancel', 'competitive-scheduling' ); ?>
                    </a>
                </div>
            </div>
            <input type="hidden" name="action" value="schedule_cancellation">
            <input type="hidden" name="action_after_acceptance" value="1">
            <input type="hidden" name="choice" value="cancel">
            <input type="hidden" name="token" value="[[token]]">
            <input type="hidden" name="pubID" value="[[pubID]]">
        </form>
        <div class="ui bottom attached warning message">
            <i class="icon exclamation triangle"></i>
            <?php echo esc_html__( 'Important: it is not possible to cancel the cancellation of this schedule!', 'competitive-scheduling' ); ?>
        </div>
    </div>
    <div class="expiredOrNotFound hidden scheduleWindow">
        <div class="ui header"><?php echo esc_html__( 'Schedule Change', 'competitive-scheduling' ); ?></div>
        <div class="ui icon attached warning message">
            <i class="exclamation triangle icon"></i>
            <div class="content">
                <div class="header">
                    <?php echo esc_html__( 'Validation Code Not Found', 'competitive-scheduling' ); ?>
                </div>
                <p><?php echo esc_html__( 'The validation code for changing your schedule was not found!', 'competitive-scheduling' ); ?></p>
                <p><?php echo esc_html__( 'Possible Reasons', 'competitive-scheduling' ); ?>:</p>
                <ol class="ui list">
                    <li><?php echo esc_html__( 'The validation code has already been used in another attempt to change the schedule.', 'competitive-scheduling' ); ?></li>
                    <li><?php echo esc_html__( 'The deadline for changing the schedule has been reached.', 'competitive-scheduling' ); ?></li>
                    <li><?php echo esc_html__( 'The code sent is invalid.', 'competitive-scheduling' ); ?></li>
                </ol>
                <p><?php echo esc_html__( 'For more information visit our website', 'competitive-scheduling' ); ?> <a href="<?php echo esc_url( site_url() ); ?>"><?php echo esc_html__( 'here', 'competitive-scheduling' ); ?></a>.</p>
            </div>
        </div>
    </div><!-- changes > -->
    <div class="ui hidden divider"></div>
    <div class="ui hidden divider"></div>
</div>