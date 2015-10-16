<?php
/**
 * Created by PhpStorm.
 * User: Langstra
 * Date: 27-12-2014
 * Time: 16:54
 */

/**
 * Configuration for: Error reporting
 * Useful to show every little problem during development, but only show hard errors in production
 */
error_reporting(E_ALL);
ini_set("display_errors", 1);

// DIRECTORY_SEPARATOR adds a slash to the end of the path
define('ROOT', dirname(__DIR__) . DIRECTORY_SEPARATOR);

define('MODELS_PATH', ROOT . 'models/');
define('LIBS_PATH', ROOT . 'libs/');
define('TEMPLATE_PATH', ROOT . 'templates/');