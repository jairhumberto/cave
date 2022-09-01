<?php
require dirname(getcwd())
    . DIRECTORY_SEPARATOR . 'vendor'
    . DIRECTORY_SEPARATOR . 'autoload.php';

use Squille\Cave\MySql\MySqlDatabase;
use Squille\Cave\Xml\XmlDatabase;

//$databaseA = new MySqlDatabase(new PDO("mysql:host=localhost;dbname=banco_a", "bioacesso", "bioacesso@uzer"));
$databaseA = new MySqlDatabase(new PDO("mysql:host=localhost;dbname=banco_a", "bioacesso", "bioacesso@uzer"));

$xml = new DOMDocument("1.0", "utf-8");
$xml->load("../banco_b.xml");

$databaseB = new XmlDatabase($xml);

$unconformities = $databaseB->checkIntegrity($databaseA);

foreach ($unconformities as $unconformity) {
    $description = $unconformity->getDescription();
    if ($description) {
        print " == $description...";
    }
    $unconformity->fix();
    if ($description) {
        print str_repeat(chr(8),3)." ==\n";
    }
}

$databaseB->save("../banco_b.xml");