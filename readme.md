# Pilot: Laravel Administration panel

## Table of contents

* [Requirements](#requirements)
* [Quickstart](#quickstart)
    * [Setup](#setup)
    * [Basic configuration](#basic-configuration)
    * [Users and roles](#users-and-roles)
        * [Grant permissions to a user](#grant-permissions-to-a-user)
        * [Deny permissions to a user](#deny-permissions-to-a-user)
    * [Authorization Policies](#authorization-policies)
	    * [Supported policy methods](#supported-policy-methods)
	    * [Policies examples](#policy-examples)
* [Documentation](#documentation)
    * [Routes Prefix](#routes-prefix)
    * [Title](#title)
    * [Upload](#upload)
    * [Models](#models)
        * [columns](#columns)
        * [icon](#icon)
        * [per_page](#per_page)
        * [scopes](#scopes)
    * [Customize views and controllers](#customize-views-and-controllers)    
        * [Views used by Pilot](#views-used-by-pilot)    
        * [Variables passed to the views](#variables-passed-to-the-views)    
        * [Custom routes](#custom-routes)    
* [Configuration example](#configuration-example)

## Requirements

* Laravel v5.3+
* PHP v7.0.0+

## Quickstart

### Setup

1. Install package
2. Add this to `composer.json` in the `autoload.psr-4` hash (just below `"App\\": "app/"`): `"VivienLN\\Pilot\\": "vendor/vivienln/pilot"` (required until the package is on packagist)
2. In `config/app.php`, add this in the `providers` array: `VivienLN\Pilot\PilotServiceProvider::class,`
2. In a shell, execute `composer dump-autoload`
3. In a shell, execute `php artisan pilot:install`. This will copy configuration files and assets to your main directory, run migrations and create empty policies if you need so.
4. Configure pilot in `config/pilot.php` and policies (see below)


### Basic configuration

All configuration is done within the `config/pilot.php` file.

The most basic setup is done by providing model class names such as follow:

```php
return [
    'models' => [
        ['model' => 'App\Post'],
        ['model' => 'App\Category'],
    ],
];
```

This will, by default, show all columns of this models and use models and columns names for display. However, you still need to define policies to setup user permissions (see below).

> **Note:** If you use config cache, do not forget to refresh it: `php artisan config:cache`

> **Note:** `model` is the only required key to make Pilot aware of your models, but you can customize many more things in each array. See [Documentation](#documentation) for more information.


### Users and roles

Each user can have one or more _roles_ that define what permissions they have. By defaut, a `super-admin` role is created.

> **Note:** When you install pilot, 2 tables are created to handle roles: `pilot_roles`and `pilot_role_user`.

#### Grant permissions to a user

To associate a user with a role, use the `pilot:grant` command:
```
php artisan pilot:grant super-admin 1
```

The parameters are:
1. The slug of the role to give the user(s)
2. The user id

#### Deny permissions to a user

The `pilot:deny` command let you do the exact opposite, removing a role from a user. The parameters are the same.

### Authorization Policies

Pilot uses the Laravel Policies to determine whether or not a user can do an action. If you are not familiar with policies, read the [official documentation about policies](https://laravel.com/docs/5.3/authorization).

For each model you want to use with Pilot, you must create and declare a Policy. When installing pilot, you can automatically generate policies.

If you do not want to use this feature, or juste want to add policies later, you can still use artisan: `php artisan make:policy MyModelPolicy --model=MyModel`
 
In any case, you must register your policies in `app/Providers/AuthServiceProvider.php`, in the `$providers` array.


> **Note:** You need to declare models in the Pilot config file (see below) __and__ setup policies to browse them in pilot. Others are simply ignored.


#### Supported policy methods

The following methods are recognized by Pilot and will be automatically used:
* `view(User $user, Model $model)` Return whether the user can view the model. Models user cannot view are excluded from the _table_ view.
* `create(User $user)` Return whether the user can create a new model.
* `update(User $user, Model $model)` Return whether the user can edit the model. If the user can view the model but not update it, the form will be shown but disabled.
* `delete(User $user, Model $model)` Return whether the user can delete the model. //TODO: used??
* `list(User $user)` Return whether the user can access the _table_ view listing the instances of the model. Excludes the ones he/she cannot view.

> **Note:** The `view`, `create`, `update`, and `delete` methods are automatically added when you use Artisan to generate a policy class. Only the `list` method must be manually added.

> **Note:** If a method is not defined, **no user** will have the right to perform the action.

Do not forget you can use the `before` method.


#### Policies examples

Let the user update only the posts they created. They still can view other posts but not update them (form fields are greyed-out).
```php
public function update(User $user, Post $post)
{
	return $user->id === $post->user_id;
}
```

Let the user view only the posts they created. Others post will not be listed in the _table_ view.
```php
public function view(User $user, Post $post)
{
	return $user->id === $post->user_id;
}
```

Allow the user to view and edit all their posts, and hide unpublished posts from other users.
```php
public function view(User $user, Post $post)
{
	return $user->id === $post->user_id;
}

public function view(User $user, Post $post)
{
	return $user->id === $post->user_id || $post->is_published;
}
```

> **Note:** If a user have the _update_ permission, but not the _view_ permission, they will still be unable to create or update an item, because they won't have access to the form.

## Documentation

All configuration is done within the `config/pilot.php` file, in the form of an array.

To better understand what follows, you must know what pages are used in Pilot. Here is the list:
* index: The dashboard of the admin panel.
* table: For one model, the list of all the instances in database.
* edit: The edit/create form of a model.

### Routes Prefix

You can set a custom prefix for all the administration routes, with the `prefix` key:

```php
    'prefix' => 'admin',
```

The default value is `admin`, meaning you'll access the panel with `/admin/`.

### Title

The `title` key lets you provide a custom HTML `<title>` for the admin panel pages.

```php
    'title' => 'My awesome panel',
```

### Upload

[TODO]

### Models

The `models` array is the most important part of the configuration.
 
Again, the simplest `pilot.php` configuration file is as follow:
```php
return [
    'models' => [
        ['model' => 'App\Post'],
        ['model' => 'App\Category'],
    ],
];
```

Each sub arrays of `models` can contain many variables to customize further how Pilot interacts with your models.
 
See below for more information. Each of the following properties can be added to the model arrays.

#### `columns`
_`Array` (default: all columns)_

For each model, you can provide a `columns` array, specifying which properties/columns will be shown and editable. You can simply specifies columns in the array:

```php
'models' => [
    [
        'model' => 'App\Post',
        'columns' => ['id', 'title', 'category'],
    ],
    ['model' => 'App\Category'],
],
```

Or ou can pass more parameters for each column, by defining an array with the column name as key, ie.:

```php
'models' => [
    [
        'model' => 'App\Post',
        'columns' => [
            'id'=> ['display' => '#'],
            'title',
            'category',
        ],
    ],
    ['model' => 'App\Category'],
],
```

Continue reading for all the properties of `columns`.

##### `display`
_`String` (default: _property name_)_
How the column/property will be displayed in admin panel.

##### `editable`
_`Boolean` (default: `true`)_
If false, the input will be disabled, but it will be displayed. This is especially usefull for _id_ fields.

##### `filters`
_`Array` or `String` (default: `[]`)_

In a `filters` array, you can pass pre-defined filters as string, that will be applied when displaying data in the _table_ view.
Some filters accept a parameter, in which case they are passed with the syntax `filter:parameter`.

* `limit:<limit>`: The maximum number of characters to display; adds an ellipsis ("...") if needed.
* `asset:<width>`: Displays an image with `asset(<value>)` as `src` attribute. You can provide a width to resize it.
* `link`: Displays property as a link to the _edit_ view.

Example:
```php
'models' => [
    [
        'model' => 'App\Post',
        'columns' => [
            'title'=> [
                'display' => 'Title',
                'filters' => ['limit:10', 'link'],
            ],
            'category',
            'id',
        ],
    ],
]
```

> **Note:** You can obviously set multiple filters, but note that they are applied **in the order of the array**, so for example `['link', 'limit:10']` will put a link around the text and _then_ cut it at 10 characters. In this case you should write `['limit:10', 'link']`

> **Note:** You can pass `filters` as a String if there is only one filter.

##### `filters_before` and `filters_after`
_`Callable` (default: `null`)_

If you need further customization, you can use on or more filter hooks:
* `filters_before`
* `filters_after`

Use them as keys in the model array, and a custom callback as value, ie:
```php
'models' => [
    [
        'model' => 'App\Post',
        'columns' => [
            'title'=> [
                'display' => 'Title',
                'filters' => 'limit:10',
                'filters_before' => function($item, $value, $reflector) { 
                    // your code 
                }
            ],
            'category',
            'id',
        ],
    ],
]
```

As the names imply, `filter_before` and `filter_after` will be called respectively before and after the filters defined by `filters`.

Both functions accept the following parameters (in this order):
* `$item` (Model): The current item whose value is filtered
* `$value` (String): The value being filtered. May have been altered by previous filters in the case of `filter_after`
* `$reflector` An instance of `VivienLN\Pilot\ModelReflector` used for the current model

Both functions must return the filtered value as a string.

> **Note:** value is initially filtered through PHP `strip_tags()` (before all filters and hooks), but the _final_ output will be shown as HTML.

##### `related_attribute`
_`String` (default: `null`)_

When the "column" is actually a related model, use this property to define which attribute of the related model must be displayed.

##### `with`
_`Array` (default: `[]`)_

Used for eager loading. Specify the relationships to load along the models, for the _table_ view.

```php
'models' => [
    [
        'model' => 'App\Post',
        'with' => ['author', 'categories'],
    ],
]
```

##### `show_in_table`
_`Boolean` (default: `true`)_
Whether the column will be shown in table view. This lets you hide from this view some property with lengthy content (for example a post text).

##### Edit/Create form customization

For each column, you can specify with the `form` key which form element will be used to edit the value. 
These are the available values:
* `text` (default)
* `textarea`
* `checkbox`
* `select:option1,option2,...`
* `select_multiple:option1,option2,...`

> **Note:** If your column corresponds to a related model, you do not need to define the `form` property.

##### [TODO] Edit/Create form validation

##### [TODO] Edit/Create form messages

#### Relationships and custom attributes

In the `columns` array, you can use relationships, or even custom attributes.

In the end, the attribute simply should be callable, eg:
```php
'columns' => [
    'foo'=> [
        ...
    ],
],
```
Will automatically call `$yourModel->foo()`, no matter how this method is defined.
However, there are some things to know to use relations.

##### Relationships

If you have defined a relationship, when displaying its value, by default Laravel will show the whole related model as a JSON string.

Use the `related_attribute` property on the column array to specify which attribute will be used. 

> **Note:** Currently, in filters and hooks, the `$item` is still the current model (not the related model). This means that, for example, the `link` filter will _NOT_ link to the related model.
> **Note:** Many To Many relationships models will be displayed separated by commas.
> **Note:** You do not need to define the `form` attribute for related models. Pilot will automatically use a select / multiple select, using the `related_attribute` for values.

##### Custom attributes

Just define a dynamic attribute getter on your model, eg:
```php
public function getFooAttribute()
{
    return 'bar';
}
```
You can then use it in the `columns` array of your model.

#### `icon`
_`String` (default: `"list"`)_

For each model, you can customize which icon will be used. 

You can use:
* One of the default icons by using its name (see list below)
* A custom image url (use the `asset()` helper)

> **Note:** Any SVG icon will be inserted as inline svg, to allow control from CSS.


The available default icons are:

* <img src="http://vivienleneez.fr/static/pilot/img/icons/box.svg" alt="" height="18"/> `box`
* <img src="http://vivienleneez.fr/static/pilot/img/icons/box2.svg" alt="" height="18"/> `box2`
* <img src="http://vivienleneez.fr/static/pilot/img/icons/calendar.svg" alt="" height="18"/> `calendar`
* <img src="http://vivienleneez.fr/static/pilot/img/icons/chart.svg" alt="" height="18"/> `chart`
* <img src="http://vivienleneez.fr/static/pilot/img/icons/checkmark.svg" alt="" height="18"/> `checkmark`
* <img src="http://vivienleneez.fr/static/pilot/img/icons/comment.svg" alt="" height="18"/> `comment`
* <img src="http://vivienleneez.fr/static/pilot/img/icons/dashboard.svg" alt="" height="18"/> `dashboard`
* <img src="http://vivienleneez.fr/static/pilot/img/icons/down.svg" alt="" height="18"/> `down`
* <img src="http://vivienleneez.fr/static/pilot/img/icons/edit.svg" alt="" height="18"/> `edit`
* <img src="http://vivienleneez.fr/static/pilot/img/icons/eye.svg" alt="" height="18"/> `eye`
* <img src="http://vivienleneez.fr/static/pilot/img/icons/file.svg" alt="" height="18"/> `file`
* <img src="http://vivienleneez.fr/static/pilot/img/icons/folder.svg" alt="" height="18"/> `folder`
* <img src="http://vivienleneez.fr/static/pilot/img/icons/gear.svg" alt="" height="18"/> `gear`
* <img src="http://vivienleneez.fr/static/pilot/img/icons/heart.svg" alt="" height="18"/> `heart`
* <img src="http://vivienleneez.fr/static/pilot/img/icons/left.svg" alt="" height="18"/> `left`
* <img src="http://vivienleneez.fr/static/pilot/img/icons/link.svg" alt="" height="18"/> `link`
* <img src="http://vivienleneez.fr/static/pilot/img/icons/list.svg" alt="" height="18"/> `list`
* <img src="http://vivienleneez.fr/static/pilot/img/icons/lock.svg" alt="" height="18"/> `lock`
* <img src="http://vivienleneez.fr/static/pilot/img/icons/mail.svg" alt="" height="18"/> `mail`
* <img src="http://vivienleneez.fr/static/pilot/img/icons/mail2.svg" alt="" height="18"/> `mail2`
* <img src="http://vivienleneez.fr/static/pilot/img/icons/off.svg" alt="" height="18"/> `off`
* <img src="http://vivienleneez.fr/static/pilot/img/icons/gamepad.svg" alt="" height="18"/> `gamepad`
* <img src="http://vivienleneez.fr/static/pilot/img/icons/pin.svg" alt="" height="18"/> `pin`
* <img src="http://vivienleneez.fr/static/pilot/img/icons/remove.svg" alt="" height="18"/> `remove`
* <img src="http://vivienleneez.fr/static/pilot/img/icons/right.svg" alt="" height="18"/> `right`
* <img src="http://vivienleneez.fr/static/pilot/img/icons/rocket.svg" alt="" height="18"/> `rocket`
* <img src="http://vivienleneez.fr/static/pilot/img/icons/search.svg" alt="" height="18"/> `search`
* <img src="http://vivienleneez.fr/static/pilot/img/icons/star.svg" alt="" height="18"/> `star`
* <img src="http://vivienleneez.fr/static/pilot/img/icons/tag.svg" alt="" height="18"/> `tag`
* <img src="http://vivienleneez.fr/static/pilot/img/icons/trash.svg" alt="" height="18"/> `trash`
* <img src="http://vivienleneez.fr/static/pilot/img/icons/up.svg" alt="" height="18"/> `up`
* <img src="http://vivienleneez.fr/static/pilot/img/icons/user.svg" alt="" height="18"/> `user`

Usage example:
```php
[
    'model' => 'App\Post',
    'icon' => 'rocket',
    ...
]
```

#### `per_page`
_`Integer` (default: `25`)_

Pilot uses pagination in table view, with `LengthAwarePaginator`. With `per_page` you can customize the page size for each model.

#### `scopes`
_`Array` (default: `null`)_

From [Laravel documentation](https://laravel.com/docs/5.4/eloquent#local-scopes):
> Local scopes allow you to define common sets of constraints that you may easily re-use throughout your application. For example, you may need to frequently retrieve all users that are considered "popular".

You can configure Pilot to add tabs on top of the table view, each tab corresponding to a given scope. 

To do so, add in your model array a `scopes` array which will contain scope names as keys, and tab texts as values.

```php
[
    'model' => 'App\Post',
    'scopes' => [
        'published' => 'Published'
    ],
    ...
]
```

This will add a "Published" tab on top on the _table_ view. This tab will call `$model->published()`, that will look for a `scopePublished()` method on your model. It is up to you to implement it such as described in Laravel documentation. 

> When you setup at least one scope, a _"All"_ tab will automatically be added at the beginning and showing by default.

### Customize views and controllers

#### Views used by Pilot

When you install Pilot, all the needed views are published to your app `resources/views/vendor/pilot` folder. 

You can edit these views as much as you want:

* `layout`: The layout of the Pilot administration panel
* `index`: The admin Dashboard 
* `table`: The table listing all entries of one model
* `edit`: The edit/create form for one model
* `partials/edit/`: Views used in the edit/creation form, one for each input type
* `partials/table/`: Views used in the _table_ view
 
> **Note:** When overriding default views, be careful of Auth checks (`@can` and `@cannot` directives). Even if the controller will block unauthorized actions, you may not want to leave links and buttons leading to 403 errors.

#### Variables passed to the views

Here is a list of all the variables used in the views.

* To all views:
    * `$pilot` (`VivienLN\Pilot\Pilot`): The `Pilot` object
    * `$user` (`App\User`): The current user
    * `$title` (`String`): The title of the page, dynamically generated by controller
* `layout`
    * _No additional variables_
* `index`
    * _No additional variables_
* `table`
    * `$items` (`Illuminate\Database\Eloquent\Collection`): A collection of all the models displayed in table
    * `$slug` (`String`): The slug of the current `Model` class, used by reflectors and routes
    * `$reflector` (`VivienLN\Pilot\ModelReflector`): A reflector for the current `Model` class
    * `$scopes` (`Array`): All the scopes available for the current `Model` class
    * `$scope` (`String`): Current scope (can be null; see [Models > scopes](#scopes)
* `edit`
    * `$model` (`Illuminate\Database\Eloquent\Model`): The current `Model` being edited
    * `$slug` (`String`): The slug of the current `Model`, used by reflectors and routes
    * `$reflector` (`VivienLN\Pilot\ModelReflector`): A reflector for the current `Model` class
    * `$canSave` (`Boolean`): Flag that defines if a user can save (update model) or not.
    
#### Custom routes

You can set custom admin routes in your main routes file. Just remember to use the `RedirectGuest` middleware provided by Pilot to redirect non-authenticated users:
```php
['middleware' => [VivienLN\Pilot\Middleware\RedirectGuest::class]
```

You can of course create a controller extending `PilotController` to keep and add features.


## Configuration example

This is a sample `pilot.php` file, combining all the options see above.

```php
return [
    'prefix' => 'admin123456',
    'models' => [
        ['model' => 'App\Category'],
        ['model' => 'App\Tag'],
        [
            'model' => 'App\Post',
            'display' => 'Blog posts',
            'slug' => 'blog_post',
            'with' => ['category', 'tags'],
            'columns' => [
                'id' => [
                    'display' => '#'
                ],
                'picture' => [
                    'display' => 'Picture',
                    'filters' => 'asset:120',
                ],
                'title' => [
                    'display' => 'Titre',
                    'filters' => ['limit:10', 'link'],
                ],
                'is_published' => [
                    'show_in_table' => false,
                ],
            ],
        ],
    ],
];
```
