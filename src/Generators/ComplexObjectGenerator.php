<?php
namespace DBFaker\Generators;

use Faker\Generator;

class ComplexObjectGenerator implements FakeDataGeneratorInterface
{

    /**
     * @var Generator
     */
    private $generator;

    /**
     * ComplexObjectGenerator constructor.
     * @param Generator $generator
     */
    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }


    public function getValue()
    {
        $depth = random_int(0, 5);
        return $this->generateRandomObject($depth);
    }

    private function generateRandomObject($depth)
    {
        $obj = new \stdClass();
        $nbProps = random_int(5, 20);
        for ($i = 0; $i < $nbProps; $i++){
            $propName = $this->randomPropName();
            $value = $depth == 0 ? $this->randomValue() : $this->generateRandomObject($depth - 1);
            $obj->$propName = $value;
        }
        return $obj;
    }

    private function randomValue()
    {
        $generators = [
            $this->generator->biasedNumberBetween(),
            $this->generator->boolean,
            $this->generator->century,
            $this->generator->city,
            $this->generator->creditCardExpirationDate,
            $this->generator->dateTime,
            $this->generator->longitude
        ];
        return array_rand($generators);
    }

    private function randomPropName()
    {
        return $this->generator->userName;
    }


}