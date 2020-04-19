<?php

namespace App\Utils\ElasticSearch;

/**
 * @package App\Utils\ElasticSearch
 */
interface ElasticSearchQueriesInterface
{
    public function getQueryExactBy(string $column, string $value): array;

    public function getQueryToSingleSearch(string $query, string $field): array;

    public function getMatchSearch(array $params): array;

    public function getBodyData(): array;

    public function getBoolMustMatchBy(array $params): array;

    public function getBoolMustNotMatchBy(array $params): array;

    public function getBoolMustOrShouldBy(array $must, array $should): array;

    public function getBoolMustMatchMustNotBy(array $mustMatch, array $mustNot): array;

    public function setIndex(string $index): void;

    public function getMustAndMustNotExist(array $must, string $exist_field, array $should): array;

}