<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 09/06/17
 * Time: 23:25
 */

namespace DBFaker\Generators;


use Doctrine\DBAL\Schema\Column;
use Faker\Generator;

class GeneratorFactory
{

    /**
     * @var Generator
     */
    private $faker;

    /**
     * GeneratorFactory constructor.
     * @param Generator $faker
     */
    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
    }

    /**
     * @param Column $column
     * @return FakeDataGeneratorInterface
     */
    public function create(Column $column)
    {
        switch ($column->getType()->getName()){
            case Type::TARRAY :
                $generator = new ComplexObjectGenerator($this->faker);
            case Type::SIMPLE_ARRAY :
                return $this->getFakeSimpleArray();
            default :
                return null;
        }

        return $generator;
        
        $mapping = [
            Type::TARRAY => "array",
            Type::SIMPLE_ARRAY => 'simple_array',
            Type::JSON_ARRAY => 'json_array',
            Type::BIGINT => 'bigint',
            Type::BOOLEAN => 'boolean',
            Type::DATETIME => 'datetime',
            Type::DATETIMETZ => 'datetimetz',
            Type::DATE => 'date',
            Type::TIME => 'time',
            Type::DECIMAL => 'decimal',
            Type::INTEGER => 'integer',
            Type::OBJECT => 'object',
            Type::SMALLINT => 'smallint',
            Type::STRING => 'string',
            Type::TEXT => 'text',
            Type::BINARY => 'binary',
            Type::BLOB => 'blob',
            Type::FLOAT => 'float',
            Type::GUID => 'guid',
        ];
    }


}