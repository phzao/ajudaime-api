<?php

namespace App\Utils\ElasticSearch;

/**
 * @package App\Utils\ElasticSearch
 */
interface ElasticSearchQueriesInterface
{
    public function getQueryExactBy(string $index, string $column, string $value): array;

    public function getQueryToSingleSearch(string $index, string $query, string $field): array;

    public function getMatchSearch(string $index, array $params): array;

    public function getBodyData(string $index): array;

    public function getBoolMustMatchBy(string $index, array $params): array;

    public function getBoolMustMatchMustNotBy(string $index, array $mustMatch, array $mustNot): array;
}