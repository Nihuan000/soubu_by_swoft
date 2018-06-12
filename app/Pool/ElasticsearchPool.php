<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-11
 * Time: 下午2:04
 */

namespace App\Pool;


use Elasticsearch\ConnectionPool\AbstractConnectionPool;
use Elasticsearch\ConnectionPool\ConnectionPoolInterface;

class ElasticsearchPool extends AbstractConnectionPool implements ConnectionPoolInterface
{

    /**
     * @param bool $force
     *
     * @return \Elasticsearch\Connections\ConnectionInterface
     */
    public function nextConnection($force = false)
    {
        if (isset($this->connections)) {
            return $this->selector->select($this->connections);
        }
    }

    public function scheduleCheck()
    {
        // TODO: Implement scheduleCheck() method.
    }
}