<?php

namespace App\DAO;

use PDO;

class ProductDAO
{
    private PDO $connection;

    /**
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->connection = $pdo;
    }

    /**
     * Простой запрос на выбор все строк с сортировкой по столбцу created_at (по возрастанию)
     *
     * @return array
     */
    public function getAllProducts(): array
    {
        $query = 'SELECT id, name, ean, price, created_at FROM products ORDER BY created_at ASC;';
        $result = $this->connection->query($query);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Запрос, позволяющий пропустить несколько строк и ограничить количество получаемых строк
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getProductsWithLimitAndOffset(int $limit, int $offset): array
    {
        $query = 'SELECT id, name, ean, price, created_at 
            FROM products ORDER BY created_at ASC LIMIT :limit OFFSET :offset;';

        $statement = $this->connection->prepare($query);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);

        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Запрос, возвращающий продукт, который содержит id, равный присланному значению product_id
     *
     * @param int $productId
     * @return array|null
     */
    public function getSingleProduct(int $productId): ?array
    {
        $query = 'SELECT id, name, ean, price, created_at FROM products WHERE id = ?;';
        $statement = $this->connection->prepare($query);
        $statement->execute([$productId]);
        return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Запрос, который возвращает строки, соответствующие присланным условиям фильтрации
     *
     * @param Filtration $filtration
     * @return array
     */
    public function getProductsWithFiltration(Filtration $filtration): array
    {
        $queryWhereData = transformFiltrationToQueryWhereData($filtration);
        $query = sprintf(
            'SELECT id, name, ean, price, created_at FROM products %s ORDER BY created_at ASC;',
            $queryWhereData['where_query']
        );
        $statement = $this->connection->prepare($query);
        $statement->execute($queryWhereData['where_values']);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Запрос, подсчитывающий количество строк в таблице
     *
     * @return int
     */
    public function getProductsQty(): int
    {
        $query = 'SELECT COUNT(*) FROM products;';
        $result = $this->connection->query($query);
        return (int)$result->fetchColumn();
    }
}
