<?php
return [
	'settings' => [
		'displayErrorDetails' => IS_DEVELOPMENT, // set to false in production
		'addContentLengthHeader' => false, // Allow the web server to send the content-length header
		'determineRouteBeforeAppMiddleware' => true, // required for validation

		// Renderer settings
		'renderer' => [
			'template_path' => __DIR__ . '/../templates/',
		],

		'validation' => [
			'yaml_dir_path' => CH_BASE_DIR . '/generated-api-schema/',
			'loose_mode' => false,
		]
	],
];
