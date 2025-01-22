import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
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
			<div className="container mx-auto p-4">
				{' '}
				{/* Tailwind classes for container and padding */}
				<InactiveSchedule />
				<ActiveScheduling />
				<div className="active-scheduling mt-4">
					{' '}
					{/* Tailwind class for margin top */}
					<a
						className="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mr-2"
						href="foo"
					>
						{' '}
						{/* Tailwind classes for button styling and margin right */}
						<i className="calendar plus icon"></i>{' '}
						{/* Fomantic UI icon (to be replaced later) */}
						{__('Schedule Service', 'competitive-scheduling')}
					</a>
					<a
						className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
						href="foo"
					>
						{' '}
						{/* Tailwind classes for button styling */}
						<i className="calendar alternate icon"></i>{' '}
						{/* Fomantic UI icon (to be replaced later) */}
						{__('Previous Schedules', 'competitive-scheduling')}
					</a>
					<div className="schedule hidden scheduleWindow mt-4">
						{' '}
						{/* Tailwind class for margin top */}
						<ScheduleForm />
					</div>
					<div className="schedules hidden scheduleWindow">
						<PreviousSchedules />
					</div>
					{/* ... other sections */}
				</div>
			</div>
		</div>
	);
};

export default Edit;
