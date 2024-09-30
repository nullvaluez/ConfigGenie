<?php
/*
Plugin Name: ConfigGenie
Description: Modify default PHP settings via wp-config.php from the admin dashboard.
Version: 1.0
Author: Byron Fecho
*/

// Add menu item in admin dashboard
add_action('admin_menu', 'wpsettings_modifier_menu');

function wpsettings_modifier_menu() {
    add_menu_page(
        'PHP Settings Modifier',
        'PHP Settings',
        'manage_options',
        'php-settings-modifier',
        'wpsettings_modifier_options_page',
        'dashicons-admin-generic',
        81
    );
}

// Display options page
function wpsettings_modifier_options_page() {
    // Check if the user is allowed to update options
    if (!current_user_can('manage_options')) {
        return;
    }

    // Handle form submission
    if (isset($_POST['wpsettings_modifier_submit'])) {
        // Verify nonce
        if (!isset($_POST['wpsettings_modifier_nonce']) || !wp_verify_nonce($_POST['wpsettings_modifier_nonce'], 'wpsettings_modifier_update')) {
            echo '<div class="error"><p>Security check failed.</p></div>';
            return;
        }

        // Sanitize and process form data
        $post_max_size = sanitize_text_field($_POST['post_max_size']);
        $max_execution_time = intval($_POST['max_execution_time']);
        $max_input_time = intval($_POST['max_input_time']);
        $max_input_vars = intval($_POST['max_input_vars']);

        // Update wp-config.php
        $result = wpsettings_modifier_update_wp_config($post_max_size, $max_execution_time, $max_input_time, $max_input_vars);

        if ($result === true) {
            echo '<div class="updated"><p>Settings updated successfully.</p></div>';
        } else {
            echo '<div class="error"><p>Error updating settings: ' . esc_html($result) . '</p></div>';
        }
    }

    // Get current settings
    $current_settings = wpsettings_modifier_get_current_settings();

    $post_max_size = isset($current_settings['post_max_size']) ? $current_settings['post_max_size'] : ini_get('post_max_size');
    $max_execution_time = isset($current_settings['max_execution_time']) ? $current_settings['max_execution_time'] : ini_get('max_execution_time');
    $max_input_time = isset($current_settings['max_input_time']) ? $current_settings['max_input_time'] : ini_get('max_input_time');
    $max_input_vars = isset($current_settings['max_input_vars']) ? $current_settings['max_input_vars'] : ini_get('max_input_vars');

    // Display form
    ?>
    <div class="wrap">
        <h1>PHP Settings Modifier</h1>
        <form method="post" action="">
            <?php wp_nonce_field('wpsettings_modifier_update', 'wpsettings_modifier_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="post_max_size">post_max_size</label></th>
                    <td><input name="post_max_size" type="text" id="post_max_size" value="<?php echo esc_attr($post_max_size); ?>" class="regular-text">
                    <p class="description">e.g., 2G, 512M</p></td>
                </tr>
                <tr>
                    <th scope="row"><label for="max_execution_time">max_execution_time</label></th>
                    <td><input name="max_execution_time" type="number" id="max_execution_time" value="<?php echo esc_attr($max_execution_time); ?>" class="regular-text">
                    <p class="description">In seconds</p></td>
                </tr>
                <tr>
                    <th scope="row"><label for="max_input_time">max_input_time</label></th>
                    <td><input name="max_input_time" type="number" id="max_input_time" value="<?php echo esc_attr($max_input_time); ?>" class="regular-text">
                    <p class="description">In seconds</p></td>
                </tr>
                <tr>
                    <th scope="row"><label for="max_input_vars">max_input_vars</label></th>
                    <td><input name="max_input_vars" type="number" id="max_input_vars" value="<?php echo esc_attr($max_input_vars); ?>" class="regular-text"></td>
                </tr>
            </table>
            <?php submit_button('Save Settings', 'primary', 'wpsettings_modifier_submit'); ?>
        </form>
    </div>
    <?php
}

// Function to update wp-config.php
function wpsettings_modifier_update_wp_config($post_max_size, $max_execution_time, $max_input_time, $max_input_vars) {
    $wp_config_file = ABSPATH . 'wp-config.php';

    if (!is_writable($wp_config_file)) {
        return 'The wp-config.php file is not writable.';
    }

    // Backup the wp-config.php file
    $backup_file = $wp_config_file . '.bak.' . time();
    if (!copy($wp_config_file, $backup_file)) {
        return 'Failed to create a backup of wp-config.php.';
    }

    // Read the contents of wp-config.php
    $config_contents = file_get_contents($wp_config_file);
    if ($config_contents === false) {
        return 'Failed to read wp-config.php.';
    }

    $start_marker = "/* WP PHP Settings Modifier Start */";
    $end_marker = "/* WP PHP Settings Modifier End */";

    // Prepare new ini_set() calls
    $new_settings = '';
    if (!empty($post_max_size)) {
        $new_settings .= "@ini_set('post_max_size', '" . addslashes($post_max_size) . "');\n";
    }
    if (!empty($max_execution_time)) {
        $new_settings .= "@ini_set('max_execution_time', '" . intval($max_execution_time) . "');\n";
    }
    if (!empty($max_input_time)) {
        $new_settings .= "@ini_set('max_input_time', '" . intval($max_input_time) . "');\n";
    }
    if (!empty($max_input_vars)) {
        $new_settings .= "@ini_set('max_input_vars', '" . intval($max_input_vars) . "');\n";
    }

    // Wrap in markers
    $new_code = "\n$start_marker\n" . $new_settings . "$end_marker\n";

    // Remove existing settings between the markers
    $pattern = "/\/\* WP PHP Settings Modifier Start \*\/.*?\/\* WP PHP Settings Modifier End \*\//s";
    if (preg_match($pattern, $config_contents)) {
        $config_contents = preg_replace($pattern, $new_code, $config_contents);
    } else {
        // Insert new code before the "That's all, stop editing" line
        $insert_pattern = '/(\/\*.*stop editing.*\*\/)/i';
        if (preg_match($insert_pattern, $config_contents, $matches)) {
            $config_contents = preg_replace($insert_pattern, $new_code . "\n" . $matches[0], $config_contents);
        } else {
            // If the stop editing line is not found, append to the end
            $config_contents .= $new_code;
        }
    }

    // Write back to wp-config.php
    $result = file_put_contents($wp_config_file, $config_contents);
    if ($result === false) {
        return 'Failed to write to wp-config.php.';
    }

    return true;
}

// Function to get current settings
function wpsettings_modifier_get_current_settings() {
    $wp_config_file = ABSPATH . 'wp-config.php';

    if (!file_exists($wp_config_file)) {
        return false;
    }

    $config_contents = file_get_contents($wp_config_file);
    if ($config_contents === false) {
        return false;
    }

    $start_marker = "/* WP PHP Settings Modifier Start */";
    $end_marker = "/* WP PHP Settings Modifier End */";

    $pattern = "/\/\* WP PHP Settings Modifier Start \*\/(.*?)\/\* WP PHP Settings Modifier End \*\//s";
    if (preg_match($pattern, $config_contents, $matches)) {
        $ini_code = $matches[1];

        $settings = array();
        $pattern = "/@ini_set\('(\w+)',\s*'(.+?)'\);/";
        if (preg_match_all($pattern, $ini_code, $ini_matches, PREG_SET_ORDER)) {
            foreach ($ini_matches as $ini_match) {
                $settings[$ini_match[1]] = $ini_match[2];
            }
        }

        return $settings;
    } else {
        return array();
    }
}
