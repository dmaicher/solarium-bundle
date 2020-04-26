<?php

namespace Tests;

use DAMA\SolariumBundle\Psr11ClientRegistry;
use PHPUnit\Framework\TestCase;
use Solarium\Client;
use Symfony\Component\DependencyInjection\Container;

class Psr11ClientRegistryTest extends TestCase
{
    public function testGetClientNames(): void
    {
        $registry = new Psr11ClientRegistry(new Container(), ['foo', 'bar'], null);
        $this->assertSame(['foo', 'bar'], $registry->getClientNames());
    }

    public function testGetDefaultClientName(): void
    {
        $registry = new Psr11ClientRegistry(new Container(), [], null);
        $this->assertNull($registry->getDefaultClientName());

        $registry = new Psr11ClientRegistry(new Container(), [], 'foo');
        $this->assertSame('foo', $registry->getDefaultClientName());
    }

    public function testGetClients(): void
    {
        $registry = new Psr11ClientRegistry(new Container(), [], null);
        $this->assertSame([], $registry->getClients());

        $container = new Container();
        $container->set('foo', $this->createMock(Client::class));
        $container->set('bar', $this->createMock(Client::class));

        $registry = new Psr11ClientRegistry($container, ['foo', 'bar'], null);
        $this->assertSame([
            'foo' => $container->get('foo'),
            'bar' => $container->get('bar'),
        ], $registry->getClients());
    }

    public function testGetNotExistentDefaultClient(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $registry = new Psr11ClientRegistry(new Container(), [], null);
        $registry->getClient();
    }

    public function testGetNonExistentNamedClient(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $container = new Container();
        $container->set('foo', $this->createMock(Client::class));

        $registry = new Psr11ClientRegistry($container, ['foo'], null);
        $registry->getClient('bar');
    }

    public function testGetClient(): void
    {
        $container = new Container();
        $container->set('foo', $this->createMock(Client::class));

        $registry = new Psr11ClientRegistry($container, ['foo'], 'foo');

        $this->assertSame($container->get('foo'), $registry->getClient());
        $this->assertSame($container->get('foo'), $registry->getClient('foo'));
    }
}
