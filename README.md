[Leia em português](README-pt-BR.md)

# Competitive Scheduling

## Description

**Competitive Scheduling** is a WordPress plugin that allows competitive scheduling of events against other users. It is ideal for places with more people interested in the same time slots than available slots. In cases where there are fewer slots than interested people, the plugin helps schedule events fairly based on random draws.

This plugin supports both shortcode usage (for classic editors) and Gutenberg blocks (for modern editors), offering flexibility in integration with your WordPress site.

## Features

- **Competitive Scheduling**: Users compete for limited time slots.
- **Random Draws**: When there are more interested parties than slots, the system performs fair draws.
- **Pre-Scheduling and Confirmations**: Support for pre-scheduling that needs to be confirmed within a specific period.
- **Priority Coupons**: Coupon system to give priority to certain users.
- **User-Friendly Interface**: Uses Fomantic UI for a modern visual experience.
- **Internationalization**: Full support for translations (included .pot, .po, .mo files).
- **Gutenberg Block**: Native integration with WordPress block editor.
- **Shortcodes**: Compatibility with classic editors via shortcodes.
- **Automatic Emails**: Sending notifications for scheduling, confirmations, and cancellations.
- **Admin Panel**: Administrative interface to manage scheduling, settings, and coupons.
- **REST API**: Endpoints for integration with other applications.

## Requirements

- WordPress 5.6 or higher
- PHP 7.0 or higher
- MySQL 5.6 or higher

## Installation

1. Download the plugin from the repository or upload the ZIP file.
2. In the WordPress dashboard, go to **Plugins > Add New**.
3. Click **Upload Plugin** and select the ZIP file.
4. Activate the plugin after installation.
5. Configure options in **Competitive Scheduling > Options**.

### Manual Installation

1. Unzip the plugin ZIP file.
2. Upload the `competitive-scheduling` folder to the `wp-content/plugins/` directory.
3. Activate the plugin in the WordPress dashboard.

### Dependencies

The plugin includes the following dependencies:
- Fomantic UI (version 2.9.0) for styles and UI components.
- jQuery Mask Plugin for input masks.
- Tailwind CSS for additional styles (used in the Gutenberg block).

## Configuration

After activation, access **Competitive Scheduling > Options** to configure:
- Establishment information.
- Email settings.
- Scheduling limits per cycle.
- Confirmation periods.
- Debug options.

### Priority Coupons

Create priority coupons in **Competitive Scheduling > Priority Coupons** to give advantages to specific users.

## Usage

### Via Shortcode (Classic Editor)

Use the shortcode `[competitive_scheduling]` on any page or post to display the scheduling form.

For the public version (no login required): `[competitive_scheduling_public]`.

Example:
```
[competitive_scheduling id="1,2,3" orderby="date"]
```

Parameters:
- `id`: Scheduling IDs separated by commas.
- `orderby`: Order of scheduling (default: 'date').

### Via Gutenberg Block (Modern Editor)

1. In the Gutenberg editor, add a new block.
2. Search for "Competitive Scheduling".
3. Insert the block into the page.
4. Configure options in the block's side panel.

The block uses the same backend as the shortcode, ensuring consistency.

## Project Structure

- `assets/`: Custom CSS and JS.
- `build/`: Compiled files for the Gutenberg block.
- `includes/`: Main classes (database, authentication, interfaces, etc.).
- `languages/`: Translation files (.pot, .po, .mo).
- `pages/`: Admin and public pages.
- `post-types/`: Custom post types (Priority Coupons).
- `settings/`: Plugin settings.
- `shortcodes/`: Shortcode implementations.
- `src/`: Source code for the Gutenberg block (JS, SCSS).
- `vendor/`: External dependencies (Fomantic UI, etc.).
- `views/`: PHP templates for interfaces.

## Development

### Building the Gutenberg Block

To develop the Gutenberg block:
1. Install dependencies: `npm install`
2. Start development mode: `npm start`
3. For production build: `npm run build`

### Translations

The plugin supports internationalization. To add translations:
1. Use the `languages/competitive-scheduling.pot` file as a base.
2. Create .po and .mo files for specific languages.
3. Use tools like Poedit to edit translations.

## FAQ

**Q: Does the plugin work with custom themes?**
A: Yes, the plugin is theme-independent and uses its own stylesheets.

**Q: How to customize emails?**
A: Email messages can be configured in the plugin options.

**Q: Is it possible to integrate with other login plugins?**
A: Yes, the plugin checks if Ultimate Member is active and redirects to its login page.

**Q: How to handle time slot conflicts?**
A: The draw system ensures fairness when there are more interested parties than slots.

## Support

For support, visit the GitHub repository or contact the author: Otávio Campos de Abreu Serra (otavio.serra@ageone.com.br).

## Contributing

Contributions are welcome! Follow these steps:
1. Fork the repository.
2. Create a branch for your feature (`git checkout -b feature/new-feature`).
3. Commit your changes (`git commit -am 'Add new feature'`).
4. Push to the branch (`git push origin feature/new-feature`).
5. Open a Pull Request.

## License

This plugin is licensed under GPL v2 or later. See the LICENSE file for more details.

## Changelog

### Version 1.1.4
- User interface improvements.
- Bug fixes.
- Enhanced Gutenberg block support.

For the full changelog, see the CHANGELOG.md file.