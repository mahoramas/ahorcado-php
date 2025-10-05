<?php

require_once __DIR__ . '/backend/Storage.php';

use Ahorcado\Storage;

$storage = new Storage();
$storage->reset();

header('Location: index.php');
exit;