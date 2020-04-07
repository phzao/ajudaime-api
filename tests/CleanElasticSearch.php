<?php

namespace App\Tests;

use App\Repository\ElasticSearch\ElasticSearchRepository;

/**
 * @package App\Tests
 */
trait CleanElasticSearch
{
    protected $registeredData = [];
    protected $indexes = [
        ["index" => "api_tokens_test"],
        ["index" => "donations_test"],
        ["index" => "needs_test"],
        ["index" => "talks_test"],
        ["index" => "users_test"]
    ];

    public function getTokenAuthenticate()
    {
        return "d75eb00f544d7fb2b2471089e408db66db25b1f9e193b6b49e5c8f70d0cfb8f713721515e5d0137310ec0517ca444f3584f7925b7ac53cfe06ca0b9c0bf74d9a8b7d1128681196a8d2ca1c5a1cd2b93eb102a0fcca68a6554851f97466ce2580d88dde3e8163f58c85e8580b8179f14ec3b280332523467f037c67";
    }

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