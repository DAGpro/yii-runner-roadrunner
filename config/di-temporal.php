<?php

declare(strict_types=1);

use Temporal\Client\GRPC\ServiceClient;
use Temporal\Client\GRPC\ServiceClientInterface;
use Temporal\Client\WorkflowClient;
use Temporal\Client\WorkflowClientInterface;
use Temporal\DataConverter\DataConverter;
use Temporal\DataConverter\DataConverterInterface;
use Temporal\Worker\Transport\Goridge;
use Temporal\Worker\Transport\HostConnectionInterface;
use Temporal\Worker\Transport\RoadRunner;
use Temporal\Worker\Transport\RPCConnectionInterface;
use Temporal\Worker\WorkerFactoryInterface;
use Temporal\WorkerFactory;
use Yiisoft\Definitions\Reference;
use Yiisoft\Yii\Runner\RoadRunner\Temporal\TemporalDeclarationProvider;

/**
 * @var $params array
 */

$temporalParams = $params['yiisoft/yii-runner-roadrunner']['temporal'];
if (!($temporalParams['enabled'] ?? false)) {
    return [];
}

return [
    DataConverterInterface::class => DataConverter::class,
    DataConverter::class => fn () => DataConverter::createDefault(),

    RPCConnectionInterface::class => Goridge::class,
    Goridge::class => fn () => Goridge::create(),

    WorkerFactoryInterface::class => WorkerFactory::class,
    WorkerFactory::class => fn () => WorkerFactory::create(),

    HostConnectionInterface::class => RoadRunner::class,
    RoadRunner::class => fn () => RoadRunner::create(),

    WorkflowClientInterface::class => WorkflowClient::class,
    WorkflowClient::class => [
        'class' => WorkflowClient::class,
        '__construct()' => [
            Reference::to(ServiceClientInterface::class),
        ],
    ],

    ServiceClientInterface::class => ServiceClient::class,
    ServiceClient::class => fn () => ServiceClient::create($temporalParams['host']),

    TemporalDeclarationProvider::class => fn () => new TemporalDeclarationProvider(
        $temporalParams['workflows'] ?? [],
        $temporalParams['activities'] ?? [],
    ),
];
