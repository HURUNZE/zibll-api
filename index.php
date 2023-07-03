<?php
/*
Plugin Name: 子比主题接口
Description: 子比用户接口
Version: 2.0
Author: 幻想工作室
*/

add_action('rest_api_init', function () {
    // User info by QQ endpoint
    register_rest_route('user-info/v1', '/get-by-qq', array(
        'methods' => 'GET',
        'callback' => 'get_user_info_by_qq',
    ));

    // Transfer endpoint
    register_rest_route('user-info/v1', '/transfer', array(
        'methods' => 'POST',
        'callback' => 'user_transfer',
    ));
});

// Define user info API callback function
function get_user_info_by_qq($request) {
    // Get QQ number from request
    $qq_number = $request->get_param('qq');
    if (!$qq_number) {
        return new WP_Error('invalid_qq_number', 'QQ number is required', array('status' => 400));
    }

    // Query user by QQ number
    $users = get_users(array(
        'meta_key' => 'qq',
        'meta_value' => $qq_number,
        'number' => 1
    ));

    // Check if user exists
    if (empty($users)) {
        return new WP_Error('user_not_found', 'User not found', array('status' => 404));
    }

    $user = $users[0];

    // Get user ID and other meta data
    $user_id = $user->ID;
    $nickname = get_user_meta($user_id, 'nickname', true);
    $description = get_user_meta($user_id, 'description', true);
    $wp_user_level = get_user_meta($user_id, 'wp_user_level', true);
    $vip_level = get_user_meta($user_id, 'vip_level', true);
    $balance = get_user_meta($user_id, 'balance', true);
    $qq = get_user_meta($user_id, 'qq', true);
    $points = get_user_meta($user_id, 'points', true); // New: Get the points value

    // Prepare response data
    $response_data = array(
        'status' => 200,
        'data' => array(
            'uid' => $user_id,
            'nickname' => $nickname,
            'description' => $description,
            'wp_user_level' => $wp_user_level,
            'vip_level' => $vip_level,
            'balance' => $balance,
            'qq' => $qq,
            'points' => $points, // New: Include points in the response
        )
    );

    // Return response as JSON
    return $response_data;
}

// Define transfer API callback function
function user_transfer($request) {
    // Get API key from request
    $api_key = $request->get_param('key');

    // Check if API key is provided
    if (!$api_key) {
        return new WP_Error('missing_api_key', 'API key is required', array('status' => 400));
    }

    // Verify API key
    $stored_api_key = get_option('user_info_api_key');
    if ($api_key !== $stored_api_key) {
        return new WP_Error('invalid_api_key', 'Invalid API key', array('status' => 401));
   

 }

    // Get transfer details from request
    $to_qq = $request->get_param('to_qq');
    $from_qq = $request->get_param('from_qq');
    $transfer_amount = $request->get_param('transfer');

    // Validate transfer details
    if (!$to_qq || !$from_qq || !$transfer_amount) {
        return new WP_Error('invalid_transfer_details', 'Invalid transfer details', array('status' => 400));
    }

    // Perform transfer operation (assuming your implementation logic)
    // ...

    // Return success response
    $response_data = array(
        'status' => 200,
        'data' => 'Transfer successful'
    );

    // Return response as JSON
    return $response_data;
}

// Add API key setting to admin dashboard
add_action('admin_menu', function () {
    add_options_page('User Info API Settings', 'User Info API', 'manage_options', 'user-info-api-settings', 'user_info_api_settings_page');
});

// Register API key setting
add_action('admin_init', function () {
    register_setting('user-info-api-settings-group', 'user_info_api_key');
});

// API settings page
function user_info_api_settings_page() {
    $api_key = get_option('user_info_api_key');

    // Generate a random API key if not set
    if (empty($api_key)) {
        $api_key = wp_generate_password(32, false);
        update_option('user_info_api_key', $api_key);
    }
    ?>
    <div class="wrap">
        <h1>User Info API Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('user-info-api-settings-group'); ?>
            <?php do_settings_sections('user-info-api-settings-group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">API Key</th>
                    <td>
                        <input type="text" name="user_info_api_key" value="<?php echo esc_attr($api_key); ?>" />
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
