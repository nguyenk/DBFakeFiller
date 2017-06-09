<?php
namespace DBFaker;


use DBFaker\Generators\ComplexObjectGenerator;
use DBFaker\Generators\GeneratorFactory;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use Faker\Generator;
use Mouf\Database\SchemaAnalyzer\SchemaAnalyzer;

class DBFaker
{

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var GeneratorFactory
     */
    private $generatorFactory;

    /**
     * @var SchemaAnalyzer
     */
    private $schemaAnalyzer;

    /**
     * @var array
     */
    private $referenceTables = [];

    /**
     * @var array
     */
    private $fakeTableRowNumbers = [];

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var PrimaryKeyRegistry[]
     */
    private $primaryKeyRegistries = [];

    /**
     * @var array
     */
    private $fkTargets = [];

    /**
     * DBExplorer constructor.
     * @param Connection $connection
     * @param Generator $faker
     */
    public function __construct(Connection $connection, GeneratorFactory $generatorFactory, SchemaAnalyzer $schemaAnalyzer)
    {
        $this->connection = $connection;
        $this->generatorFactory = $generatorFactory;
        $this->schemaAnalyzer = $schemaAnalyzer;
    }


    public function generateFakeData()
    {
        $this->buildPrimaryKeys();

        $tables = $this->connection->getSchemaManager()->listTables();
        foreach ($tables as $table) {
            if (array_key_exists($table->getName(), $this->referenceTableData)) {
                //Skip, table is a reference table
                continue;
            }
            $this->data[$table->getName()] = $this->getFakeDataForTable($table);
        }
    }

    private function buildPrimaryKeys()
    {
        $tables = $this->connection->getSchemaManager()->listTables();
        foreach ($tables as $table) {
            $primaryKeys = $table->getPrimaryKey()->getColumns();
            /** @var  $primaryKey  Column */
            foreach ($primaryKeys as $primaryKey) {
                $registry = $this->getPkRegistry($table, $primaryKey);
                if (array_search($table->getName(), $this->referenceTables)) {
                    $registry->loadValuesFromTable();
                } else if (array_key_exists($table->getName(), $this->fakeTableRowNumbers)) {
                    $registry->generateValues($this->fakeTableRowNumbers[$table->getName()]);
                } else {
                    //TODO : Throw or pass ? (some tables are may not be filled)
                }
            }
        }
    }

    /**
     * @param Table $table
     * @param string $primaryKey
     * @return PrimaryKeyRegistry
     */
    private function getPkRegistry(Table $table, string $primaryKey)
    {
        if (!isset($this->primaryKeyRegistries[$table->getName()])) {
            $this->primaryKeyRegistries[$table->getName()] = [];
            if (!isset($this->primaryKeyRegistries[$table->getName()][$primaryKey])) {
                $this->primaryKeyRegistries[$table->getName()][$primaryKey] = new PrimaryKeyRegistry($this->connection, $table, $primaryKey);
            }
        }
        return $this->primaryKeyRegistries[$table->getName()][$primaryKey];
    }

    private function getFakeDataForTable(Table $table) : array
    {
        for ($i = 0; $i < $this->fakeTableRowNumbers[$table->getName()]; $i++) {
            $row = [];
            foreach ($table->getColumns() as $column) {
                $row[$column->getName()] = $this->getFakeDataForColumn($column, $table);
            }
            $this->data[$table->getName()][] = $row;
        }
    }

    private function getFakeDataForColumn(Column $column, Table $table)
    {
        //Priority 1 : ForeignKeys are not generated, but randomly picked inside the PK's Pool
        if ($foriegnKey = $this->getForeignKeyDetails($column, $table)) {
            $value = $this->getRandomForeignKeyValue($foriegnKey, $column);
        } //Priority 2 : Primary Keys, that are not FKs are picked inside PK's Pool values
        else if (array_search($column->getName(), $table->getPrimaryKeyColumns()) !== false) {
            $value = $this->getPkRegistry($table, $column->getName())->getNextValue();
        } //P3 : other data will be Faked depending of cokumn's type and attributes
        else {
            $value = $this->getFakeValueForColumn($column);
        }
    }

    private function getForeignKeyDetails(Column $column, Table $table)
    {
        $tableForeignKeys = $table->getForeignKeys();
        foreach ($tableForeignKeys as $foreignKey) {
            foreach ($foreignKey->getLocalColumns() as $index => $colName) {
                if ($colName == $column->getName()) {
                    return $foreignKey;
                }
            }
        }
        return null;
    }

    private function getRandomForeignKeyValue(ForeignKeyConstraint $foreignKey, Column $localColumn)
    {
        if (isset($this->fkTargets[$foreignKey->getName()])) {
            $foreignColumn = $this->fkTargets[$foreignKey->getName()];
        } else {
            $foreignColumnIndex = null;
            foreach ($foreignKey->getLocalColumns() as $index => $colName) {
                if ($colName == $localColumn->getName()) {
                    $foreignColumnIndex = $index;
                }
            }
            if (!$foreignColumnIndex) {
                throw new \Exception("Could not find foreign column matching ForeignKey '"
                    . $foreignKey->getLocalTableName() . "." . $foreignKey->getName() . "' with local column : "
                    . $localColumn->getName());
            }
            $foreignColumn = $foreignKey->getForeignColumns()[$foreignColumnIndex];
            $this->fkTargets[$foreignKey->getName()] = $foreignColumn;
        }
        $foreignTable = $this->connection->getSchemaManager()->listTableDetails($foreignKey->getForeignTableName());

        return $this->getPkRegistry($foreignTable, $foreignColumn)->getRandomValue();
    }

    private function getFakeValueForColumn(Column $column)
    {
        $generator = $this->generatorFactory->create($this->faker, $column);
        return $generator->getValue();
    }

    /**
     * @param array $referenceTables
     */
    public function setReferenceTables(array $referenceTables)
    {
        $this->referenceTables = $referenceTables;
    }

    public function setFakeTableRowNumbers(array $fakeTableRowNumbers)
    {
        $this->fakeTableRowNumbers = $fakeTableRowNumbers;
    }
}