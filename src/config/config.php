<?php

return array(

	's3' => [
		'key'    => 'AMAZON_API_KEY',
		'secret' => 'AMAZON_API_SECRET',
		'bucket' => 'your-bucket-name',
		'prefix' => '/path/to/backups',
	],

	// Gzip the file before uploading?
	'gzip' => true,

);
