<?php
add_filter('authenticate', 'langgo_authenticate', 30, 3);

function langgo_authenticate($user, $username, $password) {
    // If previous authentication succeeded, then return the user.
    if (is_a($user, 'WP_User')) { 
        return $user; 
    }

    // If WP user does not exist or authentication failed, check against your API.
    $response = call_langgo_login_api($username, $password);
    
    if ($response->status) {
        $random_email = wp_generate_password(12, false) . "@example.com"; 
        $user_id = wp_create_user($username, $password, $random_email);

        // Optionally update the user's display name with the name from the API.
        wp_update_user([
            'ID' => $user_id,
            'display_name' => $response->user_infor->name,
			'nickname' => $response->user_infor->name
        ]);

        // Return the user for successful login.
        $user = new WP_User($user_id);
        return $user;
    }

    // Handle different API error messages.
    return new WP_Error('invalid_login', __($response->message));
}

function call_langgo_login_api($username, $password) {
    $api_endpoint = "https://api-estudy.langgo.vn/api/v1/writify/login";

    $response = wp_remote_post($api_endpoint, array(
        'body' => array(
            'username' => $username,
            'password' => $password
        )
    ));

    $body = wp_remote_retrieve_body($response);

    return json_decode($body);
}
