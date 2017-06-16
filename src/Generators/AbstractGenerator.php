<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 12/06/17
 * Time: 08:47
 */

namespace DBFaker\Generators;


use Doctrine\DBAL\Schema\Column;

abstract class AbstractGenerator implements FakeDataGeneratorInterface
{

    /**
     * @param Column $column
     * @return mixed
     */
    public function getValue(Column $column)
    {
        $rand = random_int(1, 10);
        if (!$column->getNotnull() && $rand % 3 == 0){
            return null;
        }else{
            return $this->getData($column);
        }
    }

    /**
     * @param Column $column
     * @return mixed
     */
    public abstract function getData(Column $column);


}