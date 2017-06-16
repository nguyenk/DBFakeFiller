<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 12/06/17
 * Time: 10:34
 */

namespace DBFaker;


use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Type;

class ColumnInspector
{

    /**
     * @var Column
     */
    private $column;

    private static $handledNumberTypes = [
        Type::BIGINT,
        Type::DECIMAL,
        Type::INTEGER,
        Type::SMALLINT,
        Type::FLOAT
    ];

    /**
     * ColumnInspector constructor.
     * @param Column $column
     */
    public function __construct(Column $column)
    {
        $this->column = $column;
    }

    /**
     * return mixed
     */
    public function getMinNumericValue(){
        if (array_search($this->column->getType()->getName(), self::$handledNumberTypes) === false){
            throw new \Exception("unsupported type for min value : " . $this->column->getType()->getName());
        }
        $precisionValue = $this->getAbsValueByLengthPrecision($this->column);
        switch ($this->column->getType()->getName()){
            case Type::BIGINT:
                return $this->column->getUnsigned() ? 0 : -gmp_pow(2, 63);
                break;
            case Type::INTEGER:
                return $this->column->getUnsigned() ? 0 : max(-$precisionValue, -gmp_pow(2, 31));
                break;
            case Type::SMALLINT:
                return $this->column->getUnsigned() ? 0 : -gmp_pow(2, 15);
                break;
            case Type::DECIMAL:
                return $this->column->getUnsigned() ? 0 : -$precisionValue;
                break;
            case Type::FLOAT:
                return $this->column->getUnsigned() ? 0 : -1.79 * gmp_pow(10, 308);
                break;
        }
    }

    /**
     * return mixed
     */
    public function getMaxNumericValue(){
        if (array_search($this->column->getType()->getName(), self::$handledNumberTypes) === false){
            throw new \Exception("unsupported type for min value : " . $this->column->getType()->getName());
        }
        $precisionValue = $this->getAbsValueByLengthPrecision($this->column);
        switch ($this->column->getType()->getName()){
            case Type::BIGINT:
                return $this->column->getUnsigned() ? gmp_pow(2, 64) : gmp_pow(2, 63) - 1;
                break;
            case Type::INTEGER:
                return $this->column->getUnsigned() ? gmp_pow(2, 32) : min($precisionValue, gmp_pow(2, 31) - 1);
                break;
            case Type::SMALLINT:
                return $this->column->getUnsigned() ? gmp_pow(2, 16) : gmp_pow(2, 15) - 1;
                break;
            case Type::DECIMAL:
                return $this->column->getUnsigned() ? 0 : $precisionValue;
                break;
            case Type::FLOAT:
                return 1.79 * gmp_pow(10, 308);
                break;
        }
    }

    private function getAbsValueByLengthPrecision(Column $column)
    {
        switch ($column->getType()->getName()){
            case Type::DECIMAL:
                $str = str_repeat(9, $column->getScale());
                return (double) substr_replace($str, ".", $column->getScale() - $column->getPrecision(), 0);
                break;
            case Type::INTEGER:
                $str = str_repeat(9, $column->getPrecision() - 1);
                return (int) $str;
                break;
        }
    }

}