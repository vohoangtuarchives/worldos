<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Math;

use InvalidArgumentException;

/**
 * Pure Mathematical Matrix.
 * Represented as an array of rows, where each row is an associative array of column values.
 */
class Matrix
{
    /**
     * @var float[][] $data [row][col] => value
     */
    protected array $data;
    
    protected array $rowDimensions;
    protected array $colDimensions;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rowDimensions = array_keys($data);
        $this->colDimensions = empty($data) ? [] : array_keys(reset($data));
    }

    public static function identity(array $dimensions): self
    {
        $data = [];
        foreach ($dimensions as $i) {
            foreach ($dimensions as $j) {
                $data[$i][$j] = ($i === $j) ? 1.0 : 0.0;
            }
        }
        return new self($data);
    }
    
    public static function zero(array $rowDimensions, array $colDimensions): self
    {
        $data = [];
        foreach ($rowDimensions as $i) {
            foreach ($colDimensions as $j) {
                $data[$i][$j] = 0.0;
            }
        }
        return new self($data);
    }

    public function get(string|int $row, string|int $col): float
    {
        return $this->data[$row][$col] ?? 0.0;
    }

    public function add(Matrix $other): self
    {
        $newData = $this->data;
        foreach ($other->rowDimensions as $i) {
            foreach ($other->colDimensions as $j) {
                if (!isset($newData[$i][$j])) {
                    $newData[$i][$j] = 0.0;
                }
                $newData[$i][$j] += $other->get($i, $j);
            }
        }
        return new self($newData);
    }

    public function scale(float $scalar): self
    {
        $newData = [];
        foreach ($this->rowDimensions as $i) {
            foreach ($this->colDimensions as $j) {
                $newData[$i][$j] = $this->data[$i][$j] * $scalar;
            }
        }
        return new self($newData);
    }

    public function multiplyVector(Vector $vector): Vector
    {
        $result = [];
        foreach ($this->rowDimensions as $i) {
            $sum = 0.0;
            foreach ($this->colDimensions as $j) {
                $sum += ($this->data[$i][$j] ?? 0.0) * $vector->get($j);
            }
            $result[$i] = $sum;
        }
        return new Vector($result);
    }

    public function multiplyMatrix(Matrix $other): self
    {
        $newData = [];
        foreach ($this->rowDimensions as $i) {
            foreach ($other->colDimensions as $j) {
                $sum = 0.0;
                foreach ($this->colDimensions as $k) {
                    $sum += ($this->data[$i][$k] ?? 0.0) * $other->get($k, $j);
                }
                $newData[$i][$j] = $sum;
            }
        }
        return new self($newData);
    }

    public function transpose(): self
    {
        $newData = [];
        foreach ($this->rowDimensions as $i) {
            foreach ($this->colDimensions as $j) {
                $newData[$j][$i] = $this->data[$i][$j];
            }
        }
        return new self($newData);
    }
    
    public function getRowDimensions(): array
    {
        return $this->rowDimensions;
    }
    
    public function getColDimensions(): array
    {
        return $this->colDimensions;
    }
    
    public function toArray(): array
    {
        return $this->data;
    }
}
