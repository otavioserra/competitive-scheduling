import { __ } from '@wordpress/i18n';

const InactiveSchedule = () => (
	<div className="schedule-inactive">
		<div className="ui header">
			{__('Inactive Schedule', 'competitive-scheduling')}
		</div>
		<p>[[msg-scheduling-suspended]]</p>
	</div>
);

export default InactiveSchedule;
