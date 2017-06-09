<?php
namespace DBFaker;


use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;

class PrimaryKeyRegistry
{

    /**
     * @var Table
     */
    private $table;

    /**
     * @var Column
     */
    private $column;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var array
     */
    private $values;

    /**
     * @var int
     */
    private $cursor = 0;

    /**
     * PrimaryKeyRegistry constructor.
     * @param Table $table
     * @param $column
     */
    public function __construct(Connection $connection, Table $table, string $columnName)
    {
        $this->connection = $connection;
        $this->table = $table;
        $this->column = $table->getColumn($columnName);
    }

    public function loadValuesFromTable()
    {
        $values = $this->connection->executeQuery("SELECT ".$this->column->getName()." FROM " . $this->table->getName())->fetchAll();
        $values = array_map(function($row){
            return $row[$this->column->getName()];
        }, $values);
        sort($values);
        $this->values = $values;
    }

    public function generateValues($nbOfValues)
    {
        if ($this->column->getType()->getName() !== Type::INTEGER){
            throw new \Exception("DB Faker doesn't handle PK types other than " . $this->column->getType()->getName());
        }
        for ($i = 0; $i < $nbOfValues; $i++){
            $this->values[] = $i;
        }
    }

    public function getRandomValue(){
        return $this->values[random_int(0, count($this->values) -1)];
    }

    public function getNextValue(){
        return $this->values[++$this->cursor];

    }

    public function getValues(){
        return $this->values;
    }

}