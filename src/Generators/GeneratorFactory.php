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
use Faker\Factory;
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

    /**
     * @var Generator
     */
    private $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    /**
     * @param Table $table
     * @param Column $column
     * @param Generator $faker
     * @return FakeDataGeneratorInterface
     * @throws \Exception
     */
    public function getGenerator(Table $table, Column $column)
    {
        $identifier = $table->getName() . "." . $column->getName();
        if (!isset($this->generators[$identifier])){
            $defaultGenerator = $this->getDefaultGenerator($column->getType());
            if ($defaultGenerator === null){
                throw new \Exception("No default generator found for column '".$identifier."' of type '".$column->getType()->getName()."', you must provide it !");
            }
            $this->generators[$identifier] = $defaultGenerator;
        }
        return $this->generators[$identifier];
    }

    /**
     * @param Column $column
     * @return FakeDataGeneratorInterface
     */
    private function getDefaultGenerator(Type $type)
    {
        $faker = $this->faker;
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
                    $generator = new NumericGenerator($faker);
                    break;
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
                        $maxLength = $column->getLength() > 5 ? max($column->getLength(), 300) : $column->getLength();
                        return $column->getLength() > 5 ? $faker->text($maxLength) : substr($faker->text(5), 0, $column->getLength() - 1);
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
    }

    /**
     * @param  callable|FakeDataGeneratorInterface $generator
     * @param string $table
     * @param string $column
     */
    public function setGeneratorForColumn($generator, string $table, string $column)
    {
        if (is_callable($generator)){
            $generator = new SimpleGenerator($generator);
        }
        if (!$generator instanceof FakeDataGeneratorInterface){
            throw new \Exception("function 'setGeneratorForColumn' only takes callable or a FakeDataGeneratorInterface");
        }
        $this->generators[$table . "." . $column] = $generator;
    }

    /**
     * @param array $generators
     */
    public function setDefaultGenerator(Type $type, FakeDataGeneratorInterface $generator)
    {
        $this->defaultGenerators[$type] = $generator;
    }

}