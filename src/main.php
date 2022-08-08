<?php
require dirname(getcwd())
    . DIRECTORY_SEPARATOR . 'vendor'
    . DIRECTORY_SEPARATOR . 'autoload.php';

use Squille\Cave\MySql\MySqlDatabase;

$databaseA = new MySqlDatabase(new PDO("mysql:host=localhost;dbname=banco_a", "bioacesso", "bioacesso@uzer"));
$databaseB = new MySqlDatabase(new PDO("mysql:host=localhost;dbname=banco_b", "bioacesso", "bioacesso@uzer"));

$unconformities = $databaseB->checkIntegrity($databaseA);

foreach ($unconformities as $unconformity) {
    $description = $unconformity->getDescription();
    if ($description) {
        print "== $description ";
    }
    $unconformity->fix();
    if ($description) {
        print "==\n";
    }
}
