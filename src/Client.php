<?php

namespace Pioniro\Seranking;

class Client
{
    /**
     * @var string|null
     */
    protected $login;

    /**
     * @var string|null
     */
    protected $pass;

    /**
     * Client constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        if(isset($config['login'])) {
            $this->login = $config['login'];
        }
        if(isset($config['password'])) {
            $this->pass = $config['password'];
        }
    }
}