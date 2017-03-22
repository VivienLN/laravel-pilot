<?php
// Get Pilot singleton
$pilot = app(VivienLN\Pilot\Pilot::class);

// setup patterns
Route::pattern('slug', '[a-z0-9_-]+');


Route::group(['prefix' => config('pilot.prefix'), 'middleware' => ['web']], function() use ($pilot) {
    Route::group(['namespace' => 'VivienLN\Pilot\Controllers'], function() {
        // Pages available to guests
        Route::get('login', 'AuthController@showLoginForm');
        Route::post('login', 'AuthController@login');
        Route::get('logout', 'AuthController@logout');

        // Restricted access pages
        Route::group(['middleware' => [VivienLN\Pilot\Middleware\RedirectGuest::class]], function() {
            // Dashboard
            Route::get('/', 'PilotController@index');

            // Show form for editing or creating a model
            Route::get('{slug}/edit/{id?}', 'PilotController@edit');

            // Update or create a model
            Route::post('{slug}/edit/{id?}', 'PilotController@store');

            // Model list
            Route::get('{slug}/{scope?}', 'PilotController@table');


            // Delete a model
            // Route::post('{modelClass}/delete/{model}', 'PilotModelController@delete');

            // Update an Image
            Route::post('/upload', 'PilotController@upload');
        });
    });
});
