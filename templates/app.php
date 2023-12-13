<?php
/**
 * Basic template for the app
 *
 * @package HELOnlineClassroom
 */

// phpcs:disable
$rest_url = get_rest_url();
$delimiter = str_contains( $rest_url, '/wp-json/' ) ? '?' : '&';
$api_url = $rest_url . 'hel-online-classroom/v1';

echo '
<div id="rest-api" data-rest-endpoint="' . esc_url($api_url) . '" data-delimiter="' . esc_attr($delimiter) . '">
</div>
<div id="bbb">
    <h2>Loading...</h2>
</div>
';
