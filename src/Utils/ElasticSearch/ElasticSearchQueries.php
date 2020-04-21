<?php

namespace App\Utils\ElasticSearch;

/**
 * @package App\Utils\ElasticSearch
 */
class ElasticSearchQueries implements ElasticSearchQueriesInterface
{
    private $index;

    public function setIndex(string $index): void
    {
        $this->index = $index;
    }

    public function getQueryExactBy(string $column,
                                    string $value): array
    {
        $body = $this->getBodyData();

        $body["body"] = [
            "query" => [
                "match_phrase_prefix" => [$column => $value]
            ]
        ];

        return $body;
    }

    public function getQueryToSingleSearch(string $query,
                                           string $field): array
    {
        $body = $this->getBodyData();

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

    public function getMatchSearch(array $params): array
    {
        if (count($params) > 1) {
            return $params;
        }

        $key   = array_keys($params);
        $value = array_values($params);

        $body = $this->getBodyData();

        $body["body"] = [
            "query" => [
                "match" => [
                    $key[0] => $value[0]
                ]
            ]
        ];

        return $body;
    }

    public function getBoolMustMatchBy(array $params): array
    {
        $matchData = [];

        foreach ($params as $key => $param) {
            $matchData[] = ["match" => [$key => $param]];
        }

        $body = $this->getBodyData();

        $body["body"] = [
            "query" => [
                "bool" =>
                    ["must" => $matchData]
            ]
        ];

        return $body;
    }

    public function getMustAndOlderDateBy(array $must, string $field_date, string $field_value): array
    {
        $matchData = [];

        foreach ($must as $key => $param) {
            $matchData[] = ["match" => [$key => $param]];
        }

        $body = $this->getBodyData();

        $matchData[] = [
            "range" => [
                "created_at" => ["lte" => $field_value]
            ]
        ];

        $body["body"] = [
            "query" => [
                "bool" =>
                    ["must" => $matchData]
            ]
        ];

        return $body;
    }

    public function getBoolMustNotMatchBy(array $params): array
    {
        $matchData = [];

        foreach ($params as $key => $param) {
            $matchData[] = ["match" => [$key => $param]];
        }

        $body = $this->getBodyData();

        $body["body"] = [
            "query" => [
                "bool" =>
                    ["must_not" => $matchData]
            ]
        ];

        return $body;
    }

    public function getMustAndMustNotExist(array $must, string $exist_field, array $should): array
    {
        $matchData = [];

        foreach ($must as $key => $param) {
            $matchData = ["match" => [$key => $param]];
        }

        $shouldData = [];

        foreach ($should as $key => $param) {
            $shouldData = ["match" => [$key => $param]];
        }

        $body = $this->getBodyData();

        $shouldBody["bool"] = [
                "should" => [
                    [
                        "bool" =>
                            [
                                "must_not" => [
                                    "exists" => ["field" => $exist_field]
                                ]
                            ]
                    ],
                    [
                        "bool" =>[
                            "must" => $shouldData
                        ]
                    ]
                ]
        ];

        $mustBody["bool"]["must"] = [$matchData, $shouldBody];

        $body["body"]["query"] = $mustBody;

        return $body;
    }

    public function getBoolMustOrShouldBy(array $must, array $should): array
    {
        $matchData = [];

        foreach ($must as $key => $param) {
            $matchData[] = ["match" => [$key => $param]];
        }

        $shouldData = [];

        foreach ($should as $key => $param) {
            $shouldData[] = ["match" => [$key => $param]];
        }

        $body = $this->getBodyData();

        $matchData[]  = [
            "bool" =>
                ["should" => $shouldData]
        ];
        $body["body"] = [
            "query" => [
                "bool" =>
                    ["must" => $matchData],
            ]
        ];

        return $body;
    }

    public function getBoolMustMatchMustNotBy(array $mustMatch,
                                              array $mustNot): array
    {
        $matchData    = [];
        $notMatchData = [];

        foreach ($mustMatch as $key => $param) {
            $matchData[] = ["match" => [$key => $param]];
        }

        foreach ($mustNot as $key => $param) {
            $notMatchData[] = ["match" => [$key => $param]];
        }

        $body = $this->getBodyData();

        $body["body"] = [
            "query" => [
                "bool" =>
                    [
                        "must"     => $matchData,
                        "must_not" => $notMatchData
                    ]
            ]
        ];

        return $body;
    }

    public function getBodyData(): array
    {
        return [
            "index" => $this->index,
            "body"  => []
        ];
    }
}