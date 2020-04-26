<?php

namespace DAMA\SolariumBundle;

use Solarium\Client;

interface ClientRegistryInterface
{
    public function getDefaultClientName(): ?string;

    /**
     * if no name is passed then will return the default client (if there is one).
     *
     * @throws \InvalidArgumentException if a client for the given name does not exist
     */
    public function getClient(string $name = null): Client;

    /**
     * @return Client[] indexed by name
     */
    public function getClients(): array;

    /**
     * @return string[]
     */
    public function getClientNames(): array;
}
