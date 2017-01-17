<?php
require_once 'Logger.php';

if (isset($_GET['method'])) {
    $logger = new Logger;
    $methodName = $_GET['method'];
    if (method_exists($logger, $methodName) && is_callable([$logger, $methodName])) {
        $result = call_user_func([$logger, $methodName]);

        if ($result) {
            echo $result;
        }
    }
} else {
    echo 'GET param "method" should be specified.';
}
