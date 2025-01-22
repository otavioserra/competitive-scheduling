/* eslint-disable no-console */ // Disable console.log warning
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

const PreviousSchedules = () => {
	const [previousSchedules, setPreviousSchedules] = useState([]);
	const [currentPage, setCurrentPage] = useState(1);
	const [totalPages, setTotalPages] = useState(1);
	const [isLoading, setIsLoading] = useState(false);

	// Fetch previous schedules from API
	useEffect(() => {
		const fetchPreviousSchedules = async () => {
			setIsLoading(true);
			try {
				const response = await apiFetch({
					path: `/wp-json/competitive-scheduling/v1/previous-schedules?page=${currentPage}`,
				});
				setPreviousSchedules(response.data);
				setTotalPages(response.totalPages);
			} catch (error) {
				console.error('Error fetching previous schedules:', error);
				// Show error message to the user
			} finally {
				setIsLoading(false);
			}
		};

		fetchPreviousSchedules();
	}, [currentPage]); // Executar o efeito quando currentPage mudar

	// Function to handle page change
	const handlePageChange = (newPage) => {
		setCurrentPage(newPage);
	};

	return (
		<div>
			<h3>{__('Previous Schedules', 'competitive-scheduling')}</h3>

			{isLoading ? (
				<p>{__('Loading', 'competitive-scheduling')}...</p>
			) : (
				<>
					<ul>
						{previousSchedules.map((schedule) => (
							<li key={schedule.id}>
								{/* Render schedule data */}
								<p>
									{__('Date', 'competitive-scheduling')}:{' '}
									{schedule.date}
								</p>
								{/* ... other schedule information */}
							</li>
						))}
					</ul>

					{/* Pagination */}
					<div className="pagination">
						{Array.from(
							{ length: totalPages },
							(_, i) => i + 1
						).map((page) => (
							<button
								key={page}
								onClick={() => handlePageChange(page)}
								className={currentPage === page ? 'active' : ''}
							>
								{page}
							</button>
						))}
					</div>
				</>
			)}
		</div>
	);
};

export default PreviousSchedules;
