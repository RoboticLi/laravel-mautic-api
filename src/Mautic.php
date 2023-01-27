<?php

namespace Triibo\Mautic;

use Mautic\Auth\OAuthClient;
use Triibo\Mautic\Models\MauticConsumer;
use Triibo\Mautic\Factories\MauticFactory;
use GrahamCampbell\Manager\AbstractManager;
use Illuminate\Contracts\Config\Repository;

class Mautic extends AbstractManager
{

    /**
     * The factory instance.
     *
     * @var \Mautic\Factory
     */
    public $factory;

    /**
     * Create a new Mautic manager instance.
     *
     * @param $config
     * @param $factory
     *
     * @return void
     */
    public function __construct($config, MauticFactory $factory)
    {
        // dd($config);
        parent::__construct($config);

        $this->factory = $factory;
    }

    /**
     * Create the connection instance.
     *
     * @param array $config
     *
     * @return mixed
     */
    protected function createConnection(array $config)
    {
        return $this->factory->make($config);
    }

    /**
     * Get the configuration name.
     *
     * @return string
     */
    protected function getConfigName()
    {
        return "mautic";
    }

    /**
     * Get the factory instance.
     *
     * @return \Mautic\MauticFactory
     */
    public function getFactory()
    {
        return $this->factory;
    }

    public function setConfig($config)
    {
        $this->factory->setDefaultConnection($config);
    }

    /**
     * @param null $method
     * @param null $endpoints
     * @param null $body
     * @return mixed
     */
    public function request($method = null, $endpoints = null, $body = null, $mautic_domain = null)
    {
        if ($mautic_domain == null) {
            $consumer = MauticConsumer::whereNotNull("id")->orderBy("created_at", "desc")->first();
        } else {
            $consumer = MauticConsumer::whereNotNull("id")->where('url', $mautic_domain)->orderBy("created_at", "desc")->first();
        }

        // dump($consumer);
        $expirationStatus = $this->factory->checkExpirationTime($consumer->expires);

        if ($expirationStatus == true){ 
            // dump('expired token');
            $consumer = $this->factory->refreshToken($consumer->refresh_token, $mautic_domain);
        }

        return $this->factory->callMautic($method, $endpoints, $body, $consumer->access_token, $mautic_domain);
    }
}
