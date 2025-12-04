<?php
namespace Oral2Text\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class IndexControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $speechService = $container->get('Oral2Text\SpeechToTextService');
        $translationService = $container->get('Oral2Text\TranslationService');
        
        return new IndexController($speechService, $translationService);
    }
}