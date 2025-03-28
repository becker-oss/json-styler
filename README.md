# Laravel Json Styler
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

## Requirements
- PHP 8.3+
- Laravel 12
- TailwindCSS

## Installation

```sh
composer require beckeross/laravel-json-styler
```

## Using the Component
To use the component you have to pass the json as string to:  
```
<x-json-styler :data="$json" />
```

If no theme is given , it will use the default theme of the config:

```
'default' => [
        'braces' => 'darkorange',
        'brackets' => 'darkgreen',
        'keys' => 'purple',
        'values' => 'darkgreen',
        'background'=> 'bg-white',
        'custom_keywords' => [
            'ERROR' => 'red',
            'issues' => 'red',
        ],
    ],
```

## Customizing

Standard options are 
- dark
- default (white)


To further customize themes and adding custom keywords:
```sh
php artisan vendor:publish --tag=laravelJsonStyler-config
```

Edit the Config file to your needs:
```
 'default' => [
        'braces' => 'darkorange',
        'brackets' => 'darkgreen',
        'keys' => 'purple',
        'values' => 'darkgreen',
        'background'=> 'bg-white',
        'custom_keywords' => [
            'ERROR' => 'red',
            'issues' => 'red',
        ],
    ],

    'adminTool' => [
        'braces' => '#ff9800',
        'brackets' => '#4caf50',
        'keys' => '#9c27b0',
        'values' => '#8bc34a',
        'background'=> 'bg-gray',
        'custom_keywords' => [
            'ERROR' => '#f44336',
            'issues' => '#e91e63',
        ],
    ],
```

You can now pass your theme to the component like this : 

```
<x-json-styler :data="$json" theme="adminTool" />
```
or use a variable for changing themes like this : 

```
<x-json-styler :data="$json" :theme="$theme" />
```
## Finally
You can now customize the coloroutput of the Json Styler component and integrate it into your code.
r too small can slow down your app - try to start with 4