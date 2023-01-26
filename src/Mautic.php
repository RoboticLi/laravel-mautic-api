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
    protected $factory;

    /**
     * Create a new Mautic manager instance.
     *
     * @param $config
     * @param $factory
     *
     * @return void
     */
    public function __construct( Repository $config, MauticFactory $factory, $configarray )
    {
        dump($config);
        dd($configarray);
        parent::__construct( $config );

        $this->factory = $factory;
    }

    /**
     * Create the connection instance.
     *
     * @param array $config
     *
     * @return mixed
     */
    protected function createConnection( array $config )
    {
        return $this->factory->make( $config );
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

    /**
     * @param null $method
     * @param null $endpoints
     * @param null $body
     * @return mixed
     */
    public function request( $method = null, $endpoints = null, $body = null )
    {
        $consumer         = MauticConsumer::whereNotNull( "id" )->orderBy( "created_at", "desc" )->first();

        $expirationStatus = $this->factory->checkExpirationTime( $consumer->expires );

        if ( $expirationStatus == true )
            $consumer = $this->factory->refreshToken( $consumer->refresh_token );

        return $this->factory->callMautic( $method, $endpoints, $body, $consumer->access_token );
    }

}
