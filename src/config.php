<?php

return [
	'prefix' => 'admin',
	'title' => [
		'Pilot Admin Panel'
	],
	'models' => [
        [
            'model' => 'App\Post',
            'icon' => 'rocket',
            'scopes' => [
                'published' => 'publiés'
            ]
        ],
        [
            'model' => 'App\Category'
        ],
        [
            'model' => 'App\User',
            'icon' => 'user'
        ],
        [
            'model' => 'App\Tag',
            'icon' => 'tag'
        ],
	]
];