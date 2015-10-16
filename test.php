<pre>
<?php
/**
 * Created by PhpStorm.
 * User: langstra
 * Date: 17-12-14
 * Time: 11:03
 */
include 'vendor/autoload.php';

error_reporting(E_ALL);
ini_set("display_errors", 1);
$target = 'http://buckbrowser/buckbrowser.php';


$requests = array(
    //array('function' => 'User.create', 'params' => array('username' => 'Langstra', 'password' => 'test', 'email' => 'langstra@live.nl', 'language' => 'en'), 'result' => array('create_error' => array('already_exists' => array('username','email')))),
    //array('function' => 'User.authenticate', 'params' => array('username' => 'Langstra', 'password' => 'test'), 'result' => array('token' => 'abc')),
    //array('function' => 'Country.read', 'params' => array('id' => '1'), 'result' => array('name' => 'Nederland', 'locale' => 'nl')),
    //array('function' => 'Country.get_all', 'params' => null, 'result' => array(array('id' => 1, 'name' => 'Nederland', 'locale' => 'nl'), array('id' => 2, 'name' => 'United Kingdom', 'locale' => 'uk')),
    //array('function' => 'User.read', 'params' => array('token' => '219e71cc8dc47b42020c55e4d73ea5d2b19c4444729ffe4310d2016641b7d238d7f941c7416665dd582cc75bfad0bdc9e8c7a23fec59ff2b22cac2ff0ef41604'), 'result' => array('token' => 'abc')),
    //array('function' => 'Company.create', 'params' => array('token' => '5845fa564564b191855843f03dca52f82b600de076afc4606edced9fc75616d2e118ebddbd4f98af7b8a6bb8bf5197604338345818b38cc6e59a502c1e2b038b', 'name' => 'fellow-it', 'email' => 'wybren.kortstra@gmail.com'), 'result' => array('id' => '1'))

    //)
);

$connection = Tivoka\Client::connect($target);
$no_errors = true;

foreach($requests as $test) {

    if(isset($test['params'])) {
        $request = $connection->sendRequest($test['function'], $test['params']);
    } else {
        $request = $connection->sendRequest($test['function']);
    }

    if($request->isError()) var_dump($request->errorMessage);
    if($request->result != $test['result']) {
        $no_errors = false;
        echo "Error: result is not as expected. <br>";
        echo "Result: <br><pre>";
        var_dump($request->result);
        echo "</pre>";
        echo "Expec: <br><pre>";
        var_dump($test['result']);
        echo "</pre>";
    }
}

if($no_errors) {
    echo "All tests were successful, you are AWESOME!";
}
?>
</pre>