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

print_r(BuckBrowser::load_model('Company')->get_all_contacts(array(
    'token' => '521be4861056d8f3d8779b3730308b35a28ff3a7acb0f5ce03c0fa40fdd0cec06048322784b572852dc85e85f911ba0283c464ae7889b55ade7cc659a2da2547'
)));
echo '</pre>';