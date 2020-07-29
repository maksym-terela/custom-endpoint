<?php
/*
 * Plugin Name:       Custom Endpoint
 * Description:       Custom Endpoint description
 * Version:           0.1
 * Author:            Maksym Terela
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       custom-endpoint
*/

function custom_plugin_response( $data ) {
    $post = get_post($data['id'], ARRAY_A);
    if (is_array($post) && isset($post['post_title'])) {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://jsonplaceholder.typicode.com/todos/1");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $output = curl_exec($ch);

        // Check HTTP status code
        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200:  # OK
                    $response = json_decode($output);
                    $post['post_title'] = 'Updated '.$response->title. ' ' . date('Y-M-D H:m:s') ;
                    wp_update_post($post);
                    return array(
                        'status' => 'ok',
                        'message' => 'Post up to date'
                    );
                    break;
                default:
                    echo 'Unexpected HTTP code: ', $http_code, "\n";
            }
        }

        curl_close($ch);


    } else {
        return array(
            'status' => 'error',
            'message' => 'Post not found'
        );
    }

}

add_action( 'rest_api_init', function () {
    register_rest_route( 'customendpoint/v1', '/postid/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'custom_plugin_response',
    ) );
} );