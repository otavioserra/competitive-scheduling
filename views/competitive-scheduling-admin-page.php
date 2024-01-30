<?php

    $data = '
        var manager = {};

        manager.calendar = '.json_encode( $calendar ).';
    ';

    wp_add_inline_script( 'competitive-scheduling-admin', $data, $position = 'after' );
?>

<div class="wrap limit-max-width">
    <h1 class="wp-heading-inline"><?php echo esc_html__( 'Competitive Scheduling', 'competitive-scheduling' ); ?></h1>
    <div class="ui attached icon message">
        <i class="calendar alternate icon"></i>
        <div class="content">
            <div class="header">
                <?php echo esc_html__( 'Instructions', 'competitive-scheduling' ); ?>
            </div>
            <p><?php echo esc_html__( 'Select a date from those available to view the list of services and modify the options below as appropriate.', 'competitive-scheduling' ); ?></p>
        </div>
    </div>
    <form class="ui form attached fluid segment" method="post" id="formSchedules">
        <div class="two fields">
            <div class="field">
                <label><?php echo esc_html__( 'Schedule Date', 'competitive-scheduling' ); ?></label>
                <input type="hidden" name="date" class="scheduleDate">
                <div class="ui calendar"></div>
            </div>
            <div class="field">
                <label><?php echo esc_html__( 'Schedule Status', 'competitive-scheduling' ); ?></label>
                <div class="ui vertical labeled icon buttons schedule-states">
                    <div class="ui button brown" data-value="pre">
                        <i class="square outline icon"></i>
                        <?php echo esc_html__( 'Pre-Schedule', 'competitive-scheduling' ); ?>
                    </div>
                    <div class="ui button grey" data-value="waiting">
                        <i class="square outline icon"></i>
                        <?php echo esc_html__( 'Waiting Confirmation', 'competitive-scheduling' ); ?>
                    </div>
                    <div class="ui button teal" data-value="confirmed">
                        <i class="square outline icon"></i>
                        <?php echo esc_html__( 'Confirmed', 'competitive-scheduling' ); ?>
                    </div>
                    <div class="ui button blue" data-value="finalized">
                        <i class="square outline icon"></i>
                        <?php echo esc_html__( 'Finalized', 'competitive-scheduling' ); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="hidden resultados">
            <div class="ui basic fitted segment">
                <span class="dateSelected">
                    <div class="ui primary large label">
                        <i class="calendar check icon"></i>
                        <span class="dateSelectedValue"></span>
                    </div>
                </span>
                &nbsp;
                <span class="totalPeople hidden">
                    <div class="ui green large label">
                        <i class="users icon"></i>
                        <?php echo esc_html__( 'Total', 'competitive-scheduling' ); ?>: 
                        <span class="totalValue">0</span>
                    </div>
                </span>
                &nbsp;
                <a class="ui orange large label printBtn hidden">
                    <i class="print icon"></i>
                    <?php echo esc_html__( 'Print', 'competitive-scheduling' ); ?>
                </a>
            </div>
            <div class="tablePeople hidden">
                <!-- people-table < --><table class="ui unstackable celled very compact table">
                    <thead>
                        <tr>
                            <th><?php echo esc_html__( 'Name', 'competitive-scheduling' ); ?></th>
                            <!-- th-password < --><th><?php echo esc_html__( 'Password', 'competitive-scheduling' ); ?></th><!-- th-password > -->
                            <th><?php echo esc_html__( 'Companions', 'competitive-scheduling' ); ?></th>
                            <!-- th-email < --><th><?php echo esc_html__( 'Email', 'competitive-scheduling' ); ?></th><!-- th-email > -->
                        </tr>
                    </thead>
                    <tbody>
                        <!-- cell-schedule < --><tr>
                        <td>[[name]]</td>
                        <!-- td-password < --><td>[[password]]</td><!-- td-password > -->
                        <td>
                            <!-- td-companions < --><table class="ui definition very compact table">
                                <tbody>
                                    <!-- cell-companion < --><tr>
                                        <td class="nowrap" style="width: 80px;"><?php echo esc_html__( 'Companion', 'competitive-scheduling' ); ?> [[num]]</td>
                                        <td>[[companion]]</td>
                                    </tr><!-- cell-companion > -->
                                </tbody>
                            </table><!-- td-companions > -->
                        </td>
                        <!-- td-email < --><td>
                            <!-- sent < --><div class="ui green large label">
                                <i class="paper plane icon"></i>
                                <?php echo esc_html__( 'Sent', 'competitive-scheduling' ); ?>
                            </div><!-- sent > -->
                            <!-- not-sent < --><div class="ui yellow large label nowrap">
                                <i class="exclamation triangle icon"></i>
                                <?php echo esc_html__( 'Not Sent', 'competitive-scheduling' ); ?>
                            </div><!-- not-sent > -->
                        </td><!-- td-email > -->
                        </tr><!-- cell-schedule > -->
                    </tbody>
                </table><!-- people-table > -->
            </div>
        </div>
        <input type="hidden" name="schedules-nonce" value="<?php echo wp_create_nonce( 'schedules-nonce' ); ?>">
    </form>
</div>
