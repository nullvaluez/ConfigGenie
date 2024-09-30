# ConfigGenie: WP PHP Settings Modifier

**A WordPress plugin that allows you to modify key PHP settings directly from the admin dashboard by updating the `wp-config.php` file.**

---

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Usage](#usage)
- [Examples](#examples)
- [Important Considerations](#important-considerations)
- [Contributing](#contributing)
- [License](#license)

---

## Features

- Modify PHP settings such as `post_max_size`, `max_execution_time`, `max_input_time`, and `max_input_vars`.
- User-friendly interface integrated into the WordPress admin dashboard.
- Automatic backup of `wp-config.php` before making changes.
- Secure operations with permission checks and nonce verification.
- No need to manually edit configuration files.

---

## Installation

1. **Download the Plugin:**
   - Download the latest version of the plugin from the [GitHub repository](#).

2. **Upload to WordPress:**
   - Log in to your WordPress admin dashboard.
   - Navigate to **Plugins** > **Add New** > **Upload Plugin**.
   - Click **Choose File** and select the downloaded plugin ZIP file.
   - Click **Install Now**.

3. **Activate the Plugin:**
   - After installation, click **Activate Plugin**.

---

## Usage

1. **Access the Settings Page:**
   - In the WordPress admin dashboard, navigate to **ConfigGenie** in the left-hand menu.

2. **Modify PHP Settings:**
   - **post_max_size:** Enter values like `2G`, `512M`, etc.
   - **max_execution_time:** Enter an integer value in seconds.
   - **max_input_time:** Enter an integer value in seconds.
   - **max_input_vars:** Enter an integer value.

3. **Save Changes:**
   - Click **Save Settings**.
   - A success message will appear if the settings are updated successfully.

---

## Examples

### Increasing `post_max_size` to 1GB

1. Go to **ConfigGenie** in the admin dashboard.
2. Set **post_max_size** to `1G`.
3. Leave other settings as default or adjust as needed.
4. Click **Save Settings**.

### Extending `max_execution_time` to 300 Seconds

1. Navigate to **ConfigGenie**.
2. Set **max_execution_time** to `300`.
3. Click **Save Settings**.

---

## Important Considerations

- **Permissions:** Ensure that the `wp-config.php` file is writable by the web server.
- **Backups:** The plugin creates a backup of `wp-config.php` before making changes (e.g., `wp-config.php.bak.1617181920`).
- **Security:** The plugin includes nonce verification and permission checks to enhance security.
- **Hosting Restrictions:** Some hosting providers may not allow scripts to modify `wp-config.php`. Check with your host if you're unsure.
- **Risk of Errors:** Incorrect settings can break your site. Use this plugin at your own risk.

---

## Contributing

Contributions are welcome! Please open an issue or submit a pull request on the [GitHub repository](#).

---

## License

This plugin is open-source software licensed under the [MIT License](LICENSE).

---

*By using ConfigGenie, you can conveniently modify essential PHP settings directly from your WordPress dashboard without manually editing configuration files.*
