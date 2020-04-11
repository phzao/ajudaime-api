<?php

namespace App\Utils\ElasticSearch;

/**
 * @package App\Utils\ElasticSearch
 */
class ElasticSearchQueries implements ElasticSearchQueriesInterface
{
    public function getQueryExactBy(string $index,
                                    string $column,
                                    string $value): array
    {
        $body = $this->getBodyData($index);

        $body["body"] = [
            "query" => [
                "match_phrase_prefix" => [ $column => $value]
            ]
        ];

        return $body;
    }

    public function getQueryToSingleSearch(string $index,
                                           string $query,
                                           string $field): array
    {
        $body = $this->getBodyData($index);

        $body["body"] = [
                "query" => [
                    "simple_query_string" => [
                        "query"  => $query,
                        "fields" => [$field]
                    ]
                ]
        ];

        return $body;
    }

    public function getMatchSearch(string $index, array $params): array
    {
        if (count($params)>1) {
            return $params;
        }

        $key = array_keys($params);
        $value = array_values($params);

        $body = $this->getBodyData($index);

        $body["body"] = [
            "query" => [
                "match" => [
                    $key[0] => $value[0]
                ]
            ]
        ];

        return $body;
    }

    public function getBoolMustMatchBy(string $index, array $params): array
    {
        $matchData = [];

        foreach($params as $key=>$param)
        {
            $matchData[] = ["match" => [$key => $param]];
        }

        $body = $this->getBodyData($index);

        $body["body"] = [
            "query" => [
                "bool" =>
                ["must" => $matchData]
            ]
        ];

        return $body;
    }

    public function getBoolMustNotMatchBy(string $index, array $params): array
    {
        $matchData = [];

        foreach($params as $key=>$param)
        {
            $matchData[] = ["match" => [$key => $param]];
        }

        $body = $this->getBodyData($index);

        $body["body"] = [
            "query" => [
                "bool" =>
                    ["must_not" => $matchData]
            ]
        ];

        return $body;
    }

    public function getBoolMustMatchMustNotBy(string $index,
                                              array $mustMatch,
                                              array $mustNot): array
    {
        $matchData = [];
        $notMatchData = [];

        foreach($mustMatch as $key=>$param)
        {
            $matchData[] = ["match" => [$key => $param]];
        }

        foreach($mustNot as $key=>$param)
        {
            $notMatchData[] = ["match" => [$key => $param]];
        }

        $body = $this->getBodyData($index);

        $body["body"] = [
            "query" => [
                "bool" =>
                    [
                        "must" => $matchData,
                        "must_not" => $notMatchData
                    ]
            ]
        ];

        return $body;
    }

    public function getBodyData(string $index): array
    {
        return [
            "index" => $index,
            "body"  => []
        ];
    }
}