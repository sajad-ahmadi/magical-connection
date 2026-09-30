<?php

declare(strict_types=1);

use MagicalConnection\HttpAPI\API;
use MagicalConnection\HttpAPI\Delete;
use MagicalConnection\HttpAPI\Security;
use MagicalConnection\HttpAPI\Upload;

require __DIR__ . '/src/Response.php';
require __DIR__ . '/src/Security.php';
require __DIR__ . '/src/Upload.php';
require __DIR__ . '/src/Delete.php';
require __DIR__ . '/src/API.php';

$security = new Security(MAGICAL_API_KEY);
$upload = new Upload();
$delete = new Delete();
$api = new API($security, $upload, $delete);