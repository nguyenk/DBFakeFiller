<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 09/06/17
 * Time: 23:25
 */

namespace DBFaker\Generators;


use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use Faker\Generator;

class GeneratorFactory
{
    /**
     * @var array
     */
    private $generators;

    /**
     * @var array
     */
    private $defaultGenerators;

    public function getGenerator(Table $table, Column $column, Generator $faker)
    {
        $identifier = $table->getFullQualifiedName() . "." . $column->getFullQualifiedName();
        if (!isset($this->generators[$identifier])){
            $defaultGenerator = $this->defaultGenerator($column->getType(), $faker);
            if ($defaultGenerator === null){
                throw new \Exception("No default generator found for column '".$identifier."' of type '".$column->getType()->getName()."', you must provide it !")
            }
            $this->generators[$identifier] = $defaultGenerator;
        }
        return $this->generators[$identifier];
    }

    /**
     * @param Column $column
     * @return FakeDataGeneratorInterface
     */
    private function defaultGenerator(Type $type, Generator $faker)
    {
        $type = $type->getName();
        if (!isset($this->defaultGenerators[$type])){
            switch ($type){
                case Type::TARRAY :
                    $generator = new ComplexObjectGenerator($faker);
                    break;
                case Type::SIMPLE_ARRAY :
                    $generator = new ComplexObjectGenerator($faker, 0);
                    break;
                case Type::JSON_ARRAY :
                    $generator = new ComplexObjectGenerator($faker, 1);
                    break;
                case Type::BOOLEAN :
                    $generator = new SimpleGenerator(function(Column $column) use ($faker) {
                        return $faker->boolean;
                    });
                    break;
                case Type::DATETIME :
                case Type::DATETIMETZ :
                case Type::DATE :
                case Type::TIME :
                    $generator = new SimpleGenerator(function(Column $column) use ($faker) {
                        return $faker->dateTime;
                    });
                    break;
                case Type::BIGINT :
                case Type::INTEGER :
                case Type::SMALLINT :
                case Type::FLOAT :
                    $generator = new NumericGenerator($faker);
                    break;
                case Type::OBJECT :
                    $generator = new ComplexObjectGenerator($faker);
                    break;
                case Type::GUID :
                    $generator = new SimpleGenerator(function(Column $column) use ($faker) {
                        $chars = "0123456789abcdef";
                        $groups = [8 ,4, 4, 4, 12];
                        $guid = [];
                        foreach ($groups as $length){
                            $sub = "";
                            for ($i = 0; $i < $length; $i++){
                                $sub .= $chars[random_int(0, count($chars) - 1)];
                            }
                            $guid[] = $sub;
                        }
                        return implode("-", $guid);
                    });
                    break;
                case Type::STRING :
                case Type::TEXT :
                    $generator = new SimpleGenerator(function(Column $column) use ($faker) {
                        return $faker->text($column->getLength());
                    });
                    break;
                case Type::BINARY :
                case Type::BLOB :
                    $generator = null;
                    break;
                default :
                    throw new \Exception("Unsupported data type : " . $type);
            }
            $this->defaultGenerators[$type] = $generator;
        }



        return $this->defaultGenerators[$type];
        
        $mapping = [
            Type::BINARY => 'binary',
            Type::BLOB => 'blob',
            Type::GUID => 'guid',
        ];
    }

    /**
     * @param array $generators
     */
    public function setGeneratorForColumn(FakeDataGeneratorInterface $generator, Table $table, Column $column)
    {
        $this->generators[$table->getFullQualifiedName() . "." . $column->getFullQualifiedName()] = $generator;
    }

    /**
     * @param array $generators
     */
    public function setDefaultGenerator(Type $type, FakeDataGeneratorInterface $generator)
    {
        $this->defaultGenerators[$type] = $generator;
    }

}