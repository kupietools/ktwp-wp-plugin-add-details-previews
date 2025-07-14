# KupieTools Add Preview To Details Elements

A WordPress plugin that automatically generates and displays content previews for HTML `<details>` elements when they are in a closed state.

## Features

- Automatically generates preview text from the content inside `<details>` elements
- Shows the preview only when the details element is closed
- Hides the preview when the element is opened
- Customizable CSS styling for previews
- Control which elements to include or exclude through configurable selectors
- Configuration panel in WordPress admin under "KupieTools" menu
- Responsive design - works well on all device sizes

## How It Works

When a user encounters a closed `<details>` element, the plugin:
1. Extracts the first 250 characters of content inside the element
2. Creates a preview of this content with an ellipsis
3. Displays the preview next to the summary
4. Automatically hides the preview when the element is opened

## Configuration Options

In the WordPress admin panel under KupieTools:
- Set which selectors to apply previews to (defaults to `.entry-content > details, .postlistsubdetail`)
- Define which elements to avoid in the preview summaries
- All settings are saved in WordPress options

## Use Cases

- Improve user experience by giving readers a glimpse of hidden content
- Especially useful for FAQ sections or accordion-style content
- Perfect for lengthy reference materials where users need to quickly scan content

## Installation

1. Upload the plugin files to the `/wp-content/plugins/ktwp-add-details-previews` directory
2. Activate the plugin through the WordPress admin interface
3. Configure settings under the KupieTools menu in WordPress admin

## License

This project is licensed under the GNU General Public License v3.0 - see the [LICENSE](LICENSE) file for details.
