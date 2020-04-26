<?php

namespace DAMA\SolariumBundle;

use Psr\Container\ContainerInterface;
use Solarium\Client;

final class Psr11ClientRegistry implements ClientRegistryInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string[]
     */
    private $clientNames;

    /**
     * @var string|null
     */
    private $defaultClientName;

    public function __construct(ContainerInterface $container, array $clientNames, ?string $defaultClientName)
    {
        $this->container = $container;
        $this->clientNames = $clientNames;
        $this->defaultClientName = $defaultClientName;
    }

    public function getDefaultClientName(): ?string
    {
        return $this->defaultClientName;
    }

    public function getClient(string $name = null): Client
    {
        if ($name === null) {
            $name = $this->defaultClientName;
        }

        if ($name !== null && $this->container->has($name)) {
            return $this->container->get($name);
        }

        throw new \InvalidArgumentException(sprintf('Client with name "%s" does not exist. Valid names are [%s].', $name, implode(',', $this->clientNames)));
    }

    public function getClients(): array
    {
        $clients = [];

        foreach ($this->clientNames as $name) {
            $clients[$name] = $this->container->get($name);
        }

        return $clients;
    }

    public function getClientNames(): array
    {
        return $this->clientNames;
    }
}
