/* eslint-disable no-console */ // Disable console.log warning
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

const ActiveScheduling = () => {
	const [activeSchedules, setActiveSchedules] = useState([]);
	const [isLoading, setIsLoading] = useState(false);

	// Fetch active schedules from API
	useEffect(() => {
		const fetchActiveSchedules = async () => {
			setIsLoading(true);
			try {
				const response = await apiFetch({
					path: '/wp-json/competitive-scheduling/v1/active-schedules',
				}); // Adapt API route
				setActiveSchedules(response);
			} catch (error) {
				console.error('Error fetching active schedules:', error);
				// Show error message to the user
			} finally {
				setIsLoading(false);
			}
		};

		fetchActiveSchedules();
	}, []);

	// Function to confirm a schedule
	const handleConfirmSchedule = async (scheduleId) => {
		// Send request to API to confirm the schedule
		try {
			await apiFetch({
				path: `/wp-json/competitive-scheduling/v1/confirm/${scheduleId}`,
				method: 'POST',
			});
			// Update the list of active schedules
			// ...
		} catch (error) {
			console.error('Error confirming schedule:', error);
			// Show error message to the user
		}
	};

	// Function to cancel a schedule
	const handleCancelSchedule = async (scheduleId) => {
		// Send request to API to cancel the schedule
		try {
			await apiFetch({
				path: `/wp-json/competitive-scheduling/v1/cancel/${scheduleId}`,
				method: 'POST',
			});
			// Update the list of active schedules
			// ...
		} catch (error) {
			console.error('Error canceling schedule:', error);
			// Show error message to the user
		}
	};

	return (
		<div>
			<h3>{__('Active Schedules', 'competitive-scheduling')}</h3>
			{isLoading ? (
				<p>{__('Loading', 'competitive-scheduling')}...</p>
			) : (
				<ul>
					{activeSchedules.map((schedule) => (
						<li key={schedule.id}>
							<p>
								{__('Date', 'competitive-scheduling')}:{' '}
								{schedule.date}
							</p>
							<p>
								{__('Status', 'competitive-scheduling')}:{' '}
								{schedule.status}
							</p>
							{/* ... other schedule information */}
							<button
								onClick={() =>
									handleConfirmSchedule(schedule.id)
								}
							>
								{__('Confirm', 'competitive-scheduling')}
							</button>
							<button
								onClick={() =>
									handleCancelSchedule(schedule.id)
								}
							>
								{__('Cancel', 'competitive-scheduling')}
							</button>
						</li>
					))}
				</ul>
			)}
		</div>
	);
};

export default ActiveScheduling;
