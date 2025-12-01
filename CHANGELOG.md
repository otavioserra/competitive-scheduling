# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.4] - 2024-12-01

### Added
- Gutenberg block support for modern WordPress editors.
- TailwindCSS integration for improved styling in the block editor.
- REST API routes for scheduling actions (confirmation, cancellation, etc.).
- ActiveScheduling component logic in the Gutenberg block.
- Priority coupons system for user advantages.
- Automatic email notifications for scheduling events.
- Internationalization support with .pot, .po, .mo files.

### Changed
- Refactored scheduling, confirmation, and cancellation methods for better modularity.
- Updated build files for Gutenberg block (index.js, index.css, etc.).
- Improved user interface with Fomantic UI components.
- Enhanced admin panel for managing schedules and settings.

### Fixed
- Various bug fixes in the scheduling logic.
- Improved compatibility with WordPress 5.6+.

### Technical
- Added Husky for pre-commit hooks.
- Configured ESLint and Stylelint for code quality.
- Updated package.json with new dependencies (TailwindCSS, @wordpress/scripts).

## [1.1.3] - 2024-01-15

### Added
- Initial implementation of Competitive Scheduling plugin.
- Shortcode support for classic editors.
- Basic scheduling functionality with random draws.
- Admin interface for plugin settings.

### Changed
- Improved database handling and authentication.

## [1.1.2] - 2023-12-01

### Fixed
- Minor fixes in form validation and user interfaces.

## [1.1.1] - 2023-11-01

### Added
- Pre-scheduling and confirmation periods.
- Email templates for notifications.

## [1.1.0] - 2023-10-01

### Added
- Core competitive scheduling logic.
- User login integration (supports Ultimate Member).
- Basic UI with Fomantic UI.

### Changed
- Initial release structure.

---

For older versions, see the Git history.