/* eslint-disable no-console */ // Disable console.log warning
import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

const ScheduleForm = () => {
	const [formData, setFormData] = useState({
		date: '',
		companions: 0,
		coupon: '',
	});
	const [isLoading, setIsLoading] = useState(false);
	const [formErrors, setFormErrors] = useState({});

	// Function to handle form input changes
	const handleInputChange = (event) => {
		const { name, value } = event.target;
		setFormData({
			...formData,
			[name]: value,
		});
	};

	// Function to handle form submission
	const handleSubmit = async (event) => {
		event.preventDefault();
		setFormErrors({});
		setIsLoading(true);

		try {
			const response = await apiFetch({
				path: '/wp-json/competitive-scheduling/v1/schedule',
				method: 'POST',
				data: formData,
			});

			// Show success message to the user (adapt based on your API response)
			console.log('Schedule created successfully:', response);
			// ... update schedules list or redirect user
		} catch (error) {
			console.error('Error creating schedule:', error);
			if (error.data && error.data.errors) {
				setFormErrors(error.data.errors);
			} else {
				// Show generic error message to the user
			}
		} finally {
			setIsLoading(false);
		}
	};

	return (
		<form
			onSubmit={handleSubmit}
			className="ui form attached fluid segment"
		>
			<div className="two fields">
				<div className="field">
					<label htmlFor="date">
						{__('Choose the Date', 'competitive-scheduling')}
					</label>
					<input
						type="date"
						name="date"
						id="date"
						value={formData.date}
						onChange={handleInputChange}
					/>
					{formErrors.date && (
						<div className="error message">{formErrors.date}</div>
					)}
				</div>
				<div className="field">
					<label htmlFor="companions">
						{__('Companions', 'competitive-scheduling')}
					</label>
					<input
						type="number"
						name="companions"
						id="companions"
						min="0"
						value={formData.companions}
						onChange={handleInputChange}
					/>
					{formErrors.companions && (
						<div className="error message">
							{formErrors.companions}
						</div>
					)}
				</div>
			</div>
			<div className="field">
				<label htmlFor="coupon">
					{__('Priority Coupon', 'competitive-scheduling')}
				</label>
				<input
					type="text"
					name="coupon"
					id="coupon"
					value={formData.coupon}
					onChange={handleInputChange}
				/>
				{formErrors.coupon && (
					<div className="error message">{formErrors.coupon}</div>
				)}
			</div>
			{/* ... other form fields */}
			<button
				type="submit"
				className="ui positive button"
				disabled={isLoading}
			>
				{isLoading ? 'Submitting...' : 'Send'}
			</button>
		</form>
	);
};

export default ScheduleForm;
