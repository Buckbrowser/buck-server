<?php
/**
 * Created by PhpStorm.
 * User: langstra
 * Date: 17-12-14
 * Time: 23:31
 */

require 'vendor/autoload.php';
require 'core/buckbrowser.php';
require 'core/Model.php';
require 'config/application.php';
require 'config/db.php';
require 'config/mail.php';

ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

echo '<pre>';

//print_r(BuckBrowser::load_model('User')->auth(array(
//    'username' => 'rienheuver',
//    'password' => 'test',
//)));

print_r(BuckBrowser::load_model('Template')->read(array(
    'token' => 'e38bb53faaaf90c338b408fdd47bdba01c6479291b97077b831e46a38cf9bd76945e5d7c0e15a32c147db6e97bef4e5ad3f26cf2495e3198a5f37f1a3ef43954',
    'id' => 1
)));
echo '</pre>';