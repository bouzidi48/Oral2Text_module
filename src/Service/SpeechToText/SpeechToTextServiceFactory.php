<?php
namespace Oral2Text\Service\SpeechToText;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class SpeechToTextServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new SpeechToTextService($container->get('Omeka\Settings'));
    }
}
