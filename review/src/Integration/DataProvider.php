<?php

namespace src\Integration;

class DataProvider
{
    // Не хватает аннотаций, это затрудняет понимание
    private $host;
    private $user;
    private $password;

    // Не хватает аннотаций с типами параметров, это затрудняет понимание
    /**
     * @param $host
     * @param $user
     * @param $password
     */
    public function __construct($host, $user, $password)
    {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * @param array $request
     *
     * @return array
     */
    public function get(array $request)
    {
        // returns a response from external service
    }
}