<?php declare(strict_types=1);

namespace App\Repository\ElasticSearch;

use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\Curl\CouldNotConnectToHost;

/**
 * @package App\Repositories\ElasticSearch
 */
class ElasticSearchRepository implements ElasticSearchRepositoryInterface
{
    /**
     * @var \Elasticsearch\Client
     */
    private $clientBuilder;

    private $url;

    public function __construct()
    {
        $this->url = $_ENV["ELASTICSEARCH_HOST"];

        try {
            $this->clientBuilder = ClientBuilder::create()
                ->setHosts([$this->url])
                ->build();

            $this->clientBuilder->info();
            return true;
        } catch (\Exception $exception) {
            throw new CouldNotConnectToHost('ElasticSearch Offline');
        }

    }

    public function index(array $data)
    {
        try {
            $res = $this->clientBuilder->index($data);

            if (isset($res["_id"])) {

                return $res["_id"];
            }
        } catch (\Exception $exception) {

            return false;
        }
    }

    public function update(array $data)
    {
        return $this->clientBuilder->update($data);
    }

    public function delete(array $data)
    {
        return $this->clientBuilder->delete($data);
    }

    public function get(array $params)
    {
        try {

            $data = $this->clientBuilder->get($params);

            return $data['_source'];

        } catch (\Exception $exception) {

            return [];
        }
    }

    public function search($array): array
    {
        $result = $this->clientBuilder->search($array);

        $hits   = $result['hits'];
        $list   = $hits['hits'];
        $data   = [];

        foreach ($list as $item)
        {
            $data[] = $item['_source'];
        }

        return [
            "took"    => $result["took"],
            "total"   => $hits["total"],
            "results" => $data
        ];
    }

    public function getOneBy(array $array): array
    {
        $array["size"] = 1;
        $result = $this->clientBuilder->search($array);
        $hits = $result["hits"];

        if ($hits["total"]===0) {
            return [];
        }

        $res = $hits["hits"][0];
        $source = $res["_source"];
        $source["id"] = $res["_id"];

        return $source;
    }

    public function isIndexExist(array $index)
    {
        return $this->clientBuilder->indices()->exists($index);
    }
}
