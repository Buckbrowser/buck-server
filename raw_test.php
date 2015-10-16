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

print_r(BuckBrowser::load_model('Company')->delete(array(
    'token' => 'dcdc285474afa3d099f5db6c5bb06f6b9abbbf0495b6a03ef855b12583e7d62ce67bf41a50510a95983c7c5299692124f18cdf644f96293d80ed144224c48073',
    'verification_code' => '043c6824150ad62a596106d2c99c917367f3b3a7189ae0d092c0a4ae7b1992c9'
)));
echo '</pre>';