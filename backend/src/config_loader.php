<?php
if (defined('CH_TEST') && file_exists(__DIR__ . '/../db/generated-conf/config-test.php')) {
	require_once __DIR__ . '/../db/generated-conf/config-test.php';
} else if (file_exists(__DIR__ . '/../db/generated-conf/config.php')) {
	require_once __DIR__ . '/../db/generated-conf/config.php';
}

require_once __DIR__ . '/../config/env.php';
