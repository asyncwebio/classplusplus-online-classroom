<?php
/**
 * Basic template for the app
 *
 * @package Bbbonlineclassroom
 */

// phpcs:disable
$rest_url = get_rest_url();
$delimiter = str_contains( $rest_url, '/wp-json/' ) ? '?' : '&';
$api_url = $rest_url . 'bbb-online-classroom/v1';

echo "
<div id='rest-api' data-rest-endpoint='$api_url' data-delimiter='$delimiter' >
</div>
<div id='bbb'>
    <h2>Loading...</h2>
</div>
";
