<?php
namespace Oral2Text;

return [
    'service_manager' => [
        'factories' => [
            'Oral2Text\SpeechToTextService' => Service\SpeechToText\SpeechToTextServiceFactory::class,
            'Oral2Text\TranslationService' => Service\Translation\TranslationServiceFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => Controller\IndexControllerFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'oral2text' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/admin/oral2text[/:action]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        '__NAMESPACE__' => 'Oral2Text\Controller',
                        '__ADMIN__' => true,
                        'controller' => Controller\IndexController::class,
                        'action' => 'index',
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
    ],
];