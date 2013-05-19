Easy Options v1.3
=====================

WordPress plugin options / theme options made easy. 

## Features

* Easy to use
* Unlimited options page
* Support tabbed options page
* Custom icon
* Various field types: `checkbox`, `checkboxgroup`, `text`, `textarea`, `dropdown_pages`.
* Flexible menu location. Where you want to place the menu for options page is your choice.
* Each option group is saved in one row of wp_options table, no wasting your database.


## Get Started

#### 1. Include the script

* Upload `easy-options.php` to a folder under your theme/plugin directory, eg. under `/inc` folder
* Include in your theme/plugin. If you use it for your theme, include it in `functions.php`

```
<?php
require_once('inc/easy-options.php')
?>
```

#### 2. Create argument array

```
$args = Array(
  'group'             => 'my-settings-group', //required
	'menu_name'         => 'My Settings', //required
	'menu_slug'         => 'my-settings', //required
	'menu_location'     => 'add_menu_page', //menu location, see Menu Location section for more info
	'icon_big'          => '', //URL to big icon image 32x32 px
	'icon_small'        => '', //URL to small icon image 16x16 px
);

```

#### 3. Call EasyOptions

```
$my_options   = new EasyOptions($args);
```

#### 4. Create Fields

```
$my_options->fields = Array(

//sample field for Dropdown page selector
Array(
	'name'         => 'download_page',
	'label'        => 'Download page',
	'type'         => 'dropdown_pages',
	'description'  => 'Choose page for download'
),

//sample for text field
Array(
	'name'         => 'twitter_username',
	'label'        => 'Your Twitter',
	'value'        => 'twitter', // this will be default value if none inserted.
	'type'         => 'text',
	'description'  => 'Twitter username please ( without @)'
),

//sample for textarea field
Array(
	'name'         => 'message',
	'label'        => 'Message',
	'type'         => 'textarea',
	'value'        => '',
	'description'  => 'Say something'
),

//sample field for checkbox type
Array(
	'name'         => 'display_social_button',
	'label'        => 'Display social button?',
	'type'         => 'checkbox',
	'description'  => 'Check it if you want to display social button on your theme'
),
);

// Other field type coming soon :D
```

#### 5. Done

Yes, you're just creating wonderfull Theme/Plugin option page easily.

But wait! How do I retrieve those values in my theme/plugin? OK, keep reading.

#### 6. Retrieving Saved Options

To retrieve value in your theme or plugin, use `<?php echo easy_options('FIELD_NAME','your_option_group');?>`, 

Example to retrieve from your setup above, for twitter field.

```
<?php 
echo easy_options('twitter_username','my-settings-group');
?>
```

## More documentation
Coming soon

## Screenshot
Coming soon

## Contribute
To contribute, fork, edit and make pull request to this repo.

## Support
Use contact form here http://takien.com/contact

## Donate
To donate this project, please visit http://takien.com/donate
