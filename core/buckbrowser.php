<?php

/**
 * Created by PhpStorm.
 * User: langstra
 * Date: 16-12-14
 * Time: 23:05
 */
class BuckBrowser
{
    public static function load_model($name)
    {
        $path = MODELS_PATH . strtolower($name) . '_model.php';

        $options = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING);

        $db = new PDO(DB_TYPE . ':host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS, $options);

        if (file_exists($path)) {
            require_once $path;
            // The "Model" has a capital letter as this is the second part of the models class name,
            // all models have names like "LoginModel"
            $modelName = $name;
            // return the new models object while passing the database connection to the models
            return new $modelName($db);
        } else {
            return "File does not exist";
        }
    }

    public static function cors() {
        // Allow from any origin
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');    // cache for 1 day
        }

        // Access-Control headers are received during OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

            exit(0);
        }
    }

}