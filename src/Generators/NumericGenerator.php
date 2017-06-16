<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 12/06/17
 * Time: 07:21
 */

namespace DBFaker\Generators;


use DBFaker\ColumnInspector;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\DecimalType;
use Doctrine\DBAL\Types\Type;
use Faker\Generator;

class NumericGenerator extends AbstractGenerator
{

    /**
     * @var Generator
     */
    private $faker;

    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
    }

    public function getdata(Column $column)
    {
        $inspector = new ColumnInspector($column);
        return $this->faker->randomFloat(10, $inspector->getMinNumericValue(), $inspector->getMaxNumericValue());
    }




}