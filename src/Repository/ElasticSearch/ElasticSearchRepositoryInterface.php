<?php declare(strict_types=1);

namespace App\Repository\ElasticSearch;

/**
 * @package App\Repository\ElasticSearch
 */
interface ElasticSearchRepositoryInterface
{
    public function index(array $data);

    public function update(array $data);

    public function isIndexExist(array $index);

    public function get(array $params);

    public function search(array $array): array;

    public function getOneBy(array $array): array;
}
