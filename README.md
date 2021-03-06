Easy Options v1.6.1
=====================

WordPress plugin options / theme options made easy. 

## Features

* Easy to use
* Unlimited options page
* Support tabbed options page
* Custom icon
* Various field types: `checkbox`, `checkboxgroup`, `text`, `textarea`, `dropdown_pages`, `image`.
* Flexible menu location. Where you want to place the menu for options page is your choice.
* Each option group is saved in one row of wp_options table, no wasting your database.

## Changelog

1.6.1

* Image field loading indicator fix
* Add new option `existing_page`, to embed page that already exists to tab menu (eg. edit taxonomy page)

1.6

* Change `apply_filters` to `do_action` hook before and after form
* Add actions to $defaults property, so that it will be easier to specify where actions should be fired. (eg, on current plugin page only)
* Add field type image (using builtin plupload upload method)
* Prevent menu_position conflicts with another plugin
* Now you can use multiple field name, eg photo[], this will return array on output.

1.5

* code rewritten
* fix lot bugs

1.4
* fix double messages if setting is more than 1 tab.
* add version number to class name
* add init() method

## Getting Started

I have attached plugin example in /example folder

## More documentation
Coming soon

## Screenshot
Coming soon

## Contribute
To contribute: [fork](https://github.com/takien/Easy-Options/fork), make your change and make pull request to this repo.

## Support
Use contact form here http://takien.com/contact

## Donate
To donate this project, please visit http://takien.com/donate
