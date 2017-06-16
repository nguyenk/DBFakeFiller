<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 02/06/17
 * Time: 19:03
 */

require_once "vendor/autoload.php";

$connectionParams = [
    "host" => "localhost",
    "user" => "root",
    "password" => "root",
    "port" => null,
    "dbname" => "taoservices",
    "charset" => "utf8",
    "driverOptions" => array(
        1002 =>"SET NAMES utf8"
    )
];
$conn = new \Doctrine\DBAL\Connection($connectionParams, new Doctrine\DBAL\Driver\PDOMySql\Driver(), null, new \Doctrine\Common\EventManager());
$conn->getSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping("enum", \Doctrine\DBAL\Types\Type::STRING);
$generator = \Faker\Factory::create();

$col = $conn->getSchemaManager()->listTableDetails("clients")->getColumn("company");

$explorer = new \DBFaker\DBFaker($conn, new \DBFaker\Generators\GeneratorFactory(), new \Mouf\Database\SchemaAnalyzer\SchemaAnalyzer($conn->getSchemaManager()));
$explorer->setReferenceTables(["step_project"]);
$explorer->setFakeTableRowNumbers([
    "user" => 10
]);
$explorer->generateFakeData();