<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 12/06/17
 * Time: 07:21
 */

namespace DBFaker\Generators;


use Doctrine\DBAL\Schema\Column;

class SimpleGenerator extends AbstractGenerator
{

    /**
     * @var callable
     */
    private $generator;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function getdata(Column $column)
    {
        return call_user_func($this->callback, $column);
    }


}