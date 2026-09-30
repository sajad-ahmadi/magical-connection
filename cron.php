<?php
declare(strict_types=1);

$wpLoad = dirname(__DIR__, 3) . '/wp-load.php';

if (!file_exists($wpLoad)) {
    exit(1);
}

require_once $wpLoad;

do_action('magical_connection_cron');