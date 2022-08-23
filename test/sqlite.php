<?php

$kiw_test = new SQLite3("test.sqlite");

//$kiw_test->exec("CREATE TABLE foo (bar CHAR(64))");
$kiw_test->exec("INSERT INTO foo (bar) VALUES ('This is a test')");

// $result = $kiw_test->querySingle("SELECT bar FROM foo");

var_dump($kiw_test->lastInsertRowID());
