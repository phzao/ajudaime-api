<?php

namespace App\Tests;

use App\Repository\ElasticSearch\ElasticSearchRepository;

/**
 * @package App\Tests
 */
trait CleanElasticSearch
{
    protected $indexes = [
        ["index" => "api_tokens_test"],
        ["index" => "donations_test"],
        ["index" => "needs_test"],
        ["index" => "talks_test"],
        ["index" => "users_test"]
    ];

    public function clearIndexes()
    {
        $this->elasticRepository = new ElasticSearchRepository();

        foreach($this->indexes as $index)
        {
            if ($this->elasticRepository->isIndexExist($index)) {
                $this->elasticRepository->deleteIndex(["index" => $index]);
            }
        }
    }
}