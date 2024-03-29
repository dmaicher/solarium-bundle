# !! ABANDONED !! Use `nelmio/solarium-bundle` instead

## About

The DAMASolariumBundle provides integration with the [Solarium](http://www.solarium-project.org)
solr client library.

This Bundle is partially based on [NelmioSolariumBundle](https://github.com/nelmio/NelmioSolariumBundle) and is fully compatible with Symfony 5 and Solarium 5.2.

## Installation

Require the `dama/solarium-bundle` package in your composer.json and update your dependencies.

```sh
$ composer require dama/solarium-bundle
```

Add the DAMASolariumBundle to your AppKernel.php

```php
public function registerBundles()
{
    $bundles = array(
        ...
        new \DAMA\SolariumBundle\DAMASolariumBundle(),
        ...
    );
    ...
}
```

## Basic configuration

Quick-start configuration:

```yaml
dama_solarium: ~
```

Gives you a Solarium Client service with default options (`http://localhost:8983/solr`)

```php
    $client = $this->get('solarium.client');
```

Configure your endpoints in config.yml:

```yaml
dama_solarium:
    endpoints:
        default:
            scheme: http
            host: localhost
            port: 8983
            path: /solr
            core: active
    clients:
        default:
            endpoints: [default]
```

If you only have one endpoint, the ```client``` section is not necessary

## Usage

```php
$client = $this->get('solarium.client');
$select = $client->createSelect();
$select->setQuery('foo');
$results = $client->select($select);
```

For more information see the [Solarium documentation](http://solarium.readthedocs.io/en/stable/).

## Multiple clients and endpoints

```yaml
dama_solarium:
    endpoints:
        default:
            host: 192.168.1.2
        another:
            host: 192.168.1.3
    clients:
        default:
            endpoints: [default]
        another:
            endpoints: [another]
```

```php
    $defaultClient = $this->get('solarium.client');
    $anotherClient = $this->get('solarium.client.another');
```

You may also change `default` name with your own, but don't forget change `default_client` option if you want to get access to
`solarium.client` service

```yaml
dama_solarium:
    default_client: firstOne
    endpoints:
        firstOne:
            host: 192.168.1.2
        anotherOne:
            host: 192.168.1.3
    clients:
        firstOne:
            endpoints: [firstOne]
        anotherOne:
            endpoints: [anotherOne]
```

```php
    $firstOneClient = $this->get('solarium.client');
    //or
    $firstOneClient = $this->get('solarium.client.firstOne');

    $anotherOneClient = $this->get('solarium.client.anotherOne');
```

You can also have multiple endpoints within the same client

```yaml
dama_solarium:
    endpoints:
        default:
            host: 192.168.1.2
        another:
            host: 192.168.1.3
    # if you are using all the endpoints, the clients section is not necessary
    clients:
        default:
            endpoints: [default, another]
```

You can also set which is the default endpoint

```yaml
dama_solarium:
    endpoints:
        default:
            host: 192.168.1.2
        another:
            host: 192.168.1.3
    clients:
        default:
            endpoints: [default, another]
            default_endpoint: another
```

You can combine both multiple client and endpoints too

```yaml
dama_solarium:
    endpoints:
        one:
            host: 192.168.1.2
        two:
            host: 192.168.1.3
        three:
            host: 192.168.1.4
    clients:
        firstOne:
            endpoints: [one, two]
            default_endpoint: two
        secondOne:
            endpoints: [two, three]
            default_endpoint: three
```

## Client registry

You can also use the service ```solarium.client_registry``` to access the clients you have configured using the names you have used in the configuration (with the example above).

```php
/** @var DAMA\SolariumBundle\ClientRegistryInterface $registry */
$registry = $this->get('solarium.client_registry');
$firstOne = $registry->getClient('firstOne');
$secondOne = $registry->getClient('secondOne');
```

or if you have configured a default client

```php
$registry = $this->get('solarium.client_registry');
$default = $registry->getClient();
```
## Plugins

Solarium works with plugins. If you want to use your own plugins, you can register a plugin in the bundle configuration either with a service id or the plugin class:

```yaml
dama_solarium:
    clients:
        default:
            plugins:
                test_plugin_service:
                    plugin_service: plugin _service_id
                test_plugin_classname:
                    plugin_class: Some\Plugin\TestPlugin
```

## Overriding the Client class

To change the client class, you can set the client_class option:

```yaml
dama_solarium:
    clients:
        default:
            client_class: Solarium\Core\Client
```

## Customizing the HTTP Adapter used by the Client

If you need to customize the Adapter that is used by the Client to perform HTTP requests to Solr then you can use the `adapter_service` option to specify the ID of a symfony service to be used as an adapter:

```yaml
dama_solarium:
    clients:
        default:
            adapter_service: 'my.custom.adapter.service'
```

## HTTP Request timeout

If you are using the default adapter (`Curl`) and did not customize the `adapter_service` then you can use the `adapter_timeout` option to customize the timeout.
Solarium uses a timeout of 5 seconds by default.

```yaml
dama_solarium:
    clients:
        default:
            adapter_timeout: 10
```

## License

Released under the MIT License, see LICENSE.
