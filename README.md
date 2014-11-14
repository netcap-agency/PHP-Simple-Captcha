PHPCaptcha
==========

Simple php captcha class

The class is a simple conversion of simple-php-captcha into a static class :
https://github.com/claviska/simple-php-captcha

_Licensed under the MIT license: http://opensource.org/licenses/MIT_


Usage
=====

To create a captcha simply do :
```php
<?php

require 'captcha.php';
$_SESSION['captcha'] = captcha::run();
```

If you want to retrieve captcha value do :
```php
<?php

$value = $_SESSION['captcha']['code'];
```

To display the captcha image :
```php
<?php
<img src="<?php echo $_SESSION['captcha']['image']; ?>" />
```
_The displayed image is base64 encoded_

If you want to customize settings :
```php
<?php
require 'captcha.php';

$option = array(
	'min_length' => 5,
	'max_length' => 5,
	'backgrounds' => array(image.png', ...),
	'fonts' => array('font.ttf', ...),
	'characters' => 'ABCDEFGHJKLMNPRSTUVWXYZabcdefghjkmnprstuvwxyz23456789',
	'min_font_size' => 28,
	'max_font_size' => 28,
	'color' => '#666',
	'angle_min' => 0,
	'angle_max' => 10,
	'shadow' => true,
	'shadow_color' => '#fff',
	'shadow_offset_x' => -1,
	'shadow_offset_y' => 1
);

$_SESSION['captcha'] = captcha::run($option);

```
