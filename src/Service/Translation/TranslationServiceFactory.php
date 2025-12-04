<?php
namespace Oral2Text\Service\Translation;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class TranslationServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new TranslationService($container->get('Omeka\Settings'));
    }
}
