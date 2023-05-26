<?php

use App\DAO\Filtration;

if (!function_exists('transformFiltrationToQueryWhereData')) {
    /**
     * Трансформация объекта фильтрации в словарь, содержащий SQL строку (WHERE ... AND ...) и значения для фильтрации
     *
     * @param Filtration $filtration
     * @return array
     */
    function transformFiltrationToQueryWhereData(Filtration $filtration): array
    {
        $queryWhereData = ['where_query' => '', 'where_values' => []];

        $filters = [];
        foreach ($filtration->getFilters() as $filter) {
            if ($filter['operation'] === Filtration::OPERATION_EQUAL) {
                $filters[] = sprintf('%s = ?', $filter['name']);
                $queryWhereData['where_values'][] = $filter['value'];
            } elseif ($filter['operation'] === Filtration::OPERATION_LIKE) {
                $filters[] = sprintf('%s LIKE ?', $filter['name']);
                $queryWhereData['where_values'][] = sprintf('%%%s%%', $filter['value']);
            } elseif ($filter['operation'] === Filtration::OPERATION_GREATER_EQ) {
                $filters[] = sprintf('%s >= ?', $filter['name']);
                $queryWhereData['where_values'][] = $filter['value'];
            } elseif ($filter['operation'] === Filtration::OPERATION_LESS_EQ) {
                $filters[] = sprintf('%s <= ?', $filter['name']);
                $queryWhereData['where_values'][] = $filter['value'];
            }
        }

        if ($filters) {
            $queryWhereData['where_query'] = ' WHERE ' . implode(' AND ', $filters);
        }

        return $queryWhereData;
    }
}
