<?php
namespace Oral2Text\Service\Translation;

use GuzzleHttp\Client;
use Omeka\Settings\Settings;


/**
 * Service de traduction avec LibreTranslate
 */
class TranslationService
{
    protected $settings;
    protected $client;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
        $this->client = new Client(['timeout' => 30]);
    }

    /**
     * Traduire un texte
     * @param string $text Texte à traduire
     * @param string $targetLanguage Langue cible (ex: 'en')
     * @param string $sourceLanguage Langue source ('auto' pour détection automatique)
     * @return string Texte traduit
     */
    public function translate($text, $targetLanguage, $sourceLanguage = 'auto')
    {
        if (empty($text)) {
            throw new \Exception("Le texte à traduire est vide");
        }

        $apiUrl = $this->settings->get(
            'oral2text_libretranslate_url',
            'https://libretranslate.com/translate'
        );
        $apiKey = $this->settings->get('oral2text_libretranslate_api_key');

        $payload = [
            'q' => $text,
            'source' => $sourceLanguage,
            'target' => $targetLanguage,
            'format' => 'text',
        ];

        // Ajouter la clé API si disponible
        if (!empty($apiKey)) {
            $payload['api_key'] = $apiKey;
        }

        try {
            $response = $this->client->post($apiUrl, [
                'json' => $payload,
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            if (isset($data['translatedText'])) {
                return $data['translatedText'];
            }

            throw new \Exception("Réponse invalide de LibreTranslate");

        } catch (\Exception $e) {
            throw new \Exception("Erreur de traduction: " . $e->getMessage());
        }
    }

    /**
     * Détecter la langue d'un texte
     */
    public function detectLanguage($text)
    {
        $apiUrl = $this->settings->get(
            'oral2text_libretranslate_url',
            'https://libretranslate.com/translate'
        );
    
        // Construire l'URL /detect à partir de l'URL /translate
        $baseUrl = parse_url($apiUrl, PHP_URL_SCHEME) . '://' . 
                   parse_url($apiUrl, PHP_URL_HOST);
        if (parse_url($apiUrl, PHP_URL_PORT)) {
            $baseUrl .= ':' . parse_url($apiUrl, PHP_URL_PORT);
        }
        $apiUrl = $baseUrl . '/detect';

        try {
            $response = $this->client->post($apiUrl, [
                'json' => ['q' => $text],
            ]);

            $data = json_decode($response->getBody(), true);
            return $data[0]['language'] ?? 'auto';

        } catch (\Exception $e) {
            return 'auto';
        }
    }

    /**
 * Tester la connexion à LibreTranslate
 * @return array ['success' => bool, 'message' => string, 'languages' => int]
 */
    public function testConnection()
    {
        $apiUrl = $this->settings->get(
            'oral2text_libretranslate_url',
            'https://libretranslate.com/translate'
        );
    
        // Construire l'URL /languages
        $baseUrl = parse_url($apiUrl, PHP_URL_SCHEME) . '://' . 
                   parse_url($apiUrl, PHP_URL_HOST);
        if (parse_url($apiUrl, PHP_URL_PORT)) {
            $baseUrl .= ':' . parse_url($apiUrl, PHP_URL_PORT);
        }
        $languagesUrl = $baseUrl . '/languages';
    
        try {
            $response = $this->client->get($languagesUrl, ['timeout' => 5]);
            $data = json_decode($response->getBody(), true);
        
            return [
                'success' => true,
                'message' => 'Connexion réussie !',
                'languages' => count($data)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
                'languages' => 0
            ];
        }
    }

    /**
     * Langues supportées par LibreTranslate
     */
    public function getSupportedLanguages()
    {
        return [
            'auto' => 'Détection automatique',
            'fr' => 'Français',
            'en' => 'English',
            'es' => 'Español',
            'ar' => 'العربية',
            'de' => 'Deutsch',
            'it' => 'Italiano',
            'pt' => 'Português',
            'ru' => 'Русский',
            'zh' => '中文',
            'ja' => '日本語',
            'nl' => 'Nederlands',
            'pl' => 'Polski',
        ];
    }
}
