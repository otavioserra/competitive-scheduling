import { useBlockProps } from '@wordpress/block-editor';
import './style.scss';
import './editor.scss';
import InactiveSchedule from './components/InactiveSchedule';
import ActiveScheduling from './components/ActiveScheduling';
import PreviousSchedules from './components/PreviousSchedules';
import ScheduleForm from './components/ScheduleForm';

// Main block component
const Edit = () => {
	const blockProps = useBlockProps();

	return (
		<div {...blockProps}>
			<div className="ui container buttonsMargin">
				<div className="ui hidden divider"></div>
				<div className="ui hidden divider"></div>

				<InactiveSchedule />
				<ActiveScheduling />

				<div className="active-scheduling buttonsMargin">
					<a className="ui positive button scheduleBtn" href="foo">
						<i className="calendar plus icon"></i>
						Schedule Service
					</a>
					<a className="ui blue button schedulesBtn" href="foo">
						<i className="calendar alternate icon"></i>
						Previous Schedules
					</a>
					<div className="ui hidden divider"></div>

					<div className="schedule hidden scheduleWindow">
						{/* Content of the Schedule section */}
						<ScheduleForm />
					</div>

					<div className="schedules hidden scheduleWindow">
						{/* Content of the Previous Schedules section */}
						<PreviousSchedules />
					</div>

					{/* ... other sections */}
				</div>

				<div className="ui hidden divider"></div>
				<div className="ui hidden divider"></div>
			</div>
		</div>
	);
};

export default Edit;
