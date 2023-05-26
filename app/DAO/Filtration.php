<?php

namespace App\DAO;

class Filtration
{
    public const OPERATION_EQUAL = 1;
    public const OPERATION_LIKE = 2;
    public const OPERATION_GREATER_EQ = 3;
    public const OPERATION_LESS_EQ = 4;

    private array $filters = [];

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Применение фильтра для строгого сравнения (=)
     *
     * @param string $name
     * @param string|array $value
     * @param int|null $maxSize
     * @return $this
     */
    public function equal(string $name, string|array $value, ?int $maxSize = null): self
    {
        if ($this->validate($value, $maxSize)) {
            $this->filters[] = ['name' => $name, 'value' => $value, 'operation' => self::OPERATION_EQUAL];
        }

        return $this;
    }

    /**
     * Применение фильтра для нестрогого сравнения (LIKE)
     *
     * @param string $name
     * @param string|array $value
     * @param int|null $maxSize
     * @return $this
     */
    public function like(string $name, string|array $value, ?int $maxSize = null): self
    {
        if ($this->validate($value, $maxSize)) {
            $this->filters[] = ['name' => $name, 'value' => $value, 'operation' => self::OPERATION_LIKE];
        }

        return $this;
    }

    /**
     * Применение фильтра для сравнения на больше или равно (>=)
     *
     * @param string $name
     * @param string|array $value
     * @param int|null $maxSize
     * @return $this
     */
    public function greaterEqual(string $name, string|array $value, ?int $maxSize = null): self
    {
        if ($this->validate($value, $maxSize)) {
            $this->filters[] = ['name' => $name, 'value' => $value, 'operation' => self::OPERATION_GREATER_EQ];
        }

        return $this;
    }

    /**
     * Применение фильтра для сравнения на меньше или равно (<=)
     *
     * @param string $name
     * @param string|array $value
     * @param int|null $maxSize
     * @return $this
     */
    public function lessEqual(string $name, string|array $value, ?int $maxSize = null): self
    {
        if ($this->validate($value, $maxSize)) {
            $this->filters[] = ['name' => $name, 'value' => $value, 'operation' => self::OPERATION_LESS_EQ];
        }

        return $this;
    }

    /**
     * Проверка значения для фильтрации (не должно быть пустой строкой/массивом, а длина не должна превышать лимита)
     *
     * @param string|array $value
     * @param int|null $maxSize
     * @return bool
     */
    private function validate(string|array $value, ?int $maxSize = null): bool
    {
        if (is_array($value) || $value === '') {
            return false;
        }

        if ($maxSize !== null && mb_strlen($value) > $maxSize) {
            return false;
        }

        return true;
    }
}
