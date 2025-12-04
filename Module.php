<?php
namespace Oral2Text;

use Omeka\Module\AbstractModule;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\Mvc\Controller\AbstractController;

class Module extends AbstractModule
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function install(ServiceLocatorInterface $services)
    {
        $settings = $services->get('Omeka\Settings');
        // Whisper Settings
        $settings->set('oral2text_whisper_model', 'base');
        $settings->set('oral2text_whisper_language', 'fr');
        // LibreTranslate Settings
        $settings->set('oral2text_translation_target_language', '');
        $settings->set('oral2text_libretranslate_url', 'https://libretranslate.com/translate');
        $settings->set('oral2text_libretranslate_api_key', '');
    }

    public function uninstall(ServiceLocatorInterface $services)
    {
        $settings = $services->get('Omeka\Settings');
        $settings->delete('oral2text_whisper_model');
        $settings->delete('oral2text_whisper_language');
        $settings->delete('oral2text_libretranslate_url');
        $settings->delete('oral2text_libretranslate_api_key');
    }

    public function getConfigForm(PhpRenderer $renderer)
    {
        $services = $this->getServiceLocator();
        $settings = $services->get('Omeka\Settings');

        return $renderer->render('oral2-text/admin/config', [
            'whisper_model' => $settings->get('oral2text_whisper_model', 'base'),
            'whisper_language' => $settings->get('oral2text_whisper_language', 'fr'),
            'libretranslate_url' => $settings->get('oral2text_libretranslate_url'),
            'libretranslate_api_key' => $settings->get('oral2text_libretranslate_api_key'),
            'translation_target_language' => $settings->get('oral2text_translation_target_language', ''),
        ]);
    }

    public function handleConfigForm(AbstractController $controller)
    {
        $services = $this->getServiceLocator();
        $settings = $services->get('Omeka\Settings');
        $params = $controller->params()->fromPost();

        $settings->set('oral2text_whisper_model', $params['whisper_model']);
        $settings->set('oral2text_whisper_language', $params['whisper_language']);
        $settings->set('oral2text_libretranslate_url', $params['libretranslate_url']);
        $settings->set('oral2text_libretranslate_api_key', $params['libretranslate_api_key']);
        $settings->set('oral2text_translation_target_language', $params['translation_target_language'] ?? '');
        return true;
    }

    public function getModuleDependencies()
    {
        return ['Omeka'];
    }
}
