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
    "dbname" => "timemachine_test",
    "charset" => "utf8",
    "driverOptions" => array(
        1002 =>"SET NAMES utf8"
    )
];
$conn = new \Doctrine\DBAL\Connection($connectionParams, new Doctrine\DBAL\Driver\PDOMySql\Driver(), null, new \Doctrine\Common\EventManager());
$generator = \Faker\Factory::create();
$generatorFactory = new \DBFaker\Generators\GeneratorFactory();
$generatorFactory->setGeneratorForColumn(function() use ($generator) {
    return $generator->email;
}, "user", "email");
$explorer = new \DBFaker\DBFaker($conn, $generatorFactory, new \Mouf\Database\SchemaAnalyzer\SchemaAnalyzer($conn->getSchemaManager()));
$explorer->setFakeTableRowNumbers([
    "a_user_project" => 300,
    "bill" => 200,
    "client" => 10,
    "community_spending" => 100,
    "cron_flag" => 10,
    "project" => 50,
    "project_note" => 200 ,
    "project_steps" => 10,
    "project_tasks" => 35,
    "request_hollidays" => 20,
    "request_hollidays_log" => 200,
    "request_hollidays_projects" => 240,
    "time" => 30,
    "user" => 40,
    "user_right" => 160,
    "validate_week" => 0
]);
$explorer->fakeDB();
