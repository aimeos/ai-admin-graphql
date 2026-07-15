<?php

return [
	'name' => 'ai-admin-graphql',
	'config' => [
		'config',
	],
	'depends' => [
		'aimeos-core',
	],
	'include' => [
		'src',
	],
	'template' => [
		'admin/graphql/templates' => [
			'templates/admin/graphql',
		],
	],
];
