<?php
namespace Oral2Text\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    protected $speechService;
    protected $translationService;
    
    public function __construct($speechService, $translationService)
    {
        $this->speechService = $speechService;
        $this->translationService = $translationService;
    }
    
    public function indexAction()
    {
        $message = null;
        $transcription = null;
        $translation = null;
        
        if ($this->getRequest()->isPost()) {
            $files = $this->params()->fromFiles('audio_file');
            $targetLang = $this->params()->fromPost('target_language');
            $saveToOmeka = $this->params()->fromPost('save_to_omeka');
            
            if ($files && $files['error'] === UPLOAD_ERR_OK) {
                try {
                    // Transcrire
                    $transcription = $this->speechService->transcribe($files['tmp_name']);
                    
                    // Traduire si demandé
                    if ($targetLang && $targetLang !== 'none') {
                        $translation = $this->translationService->translate(
                            $transcription, 
                            $targetLang
                        );
                    }
                    
                    // Enregistrer dans Omeka S si demandé
                    if ($saveToOmeka) {
                        $audioId = $this->saveToOmeka($files, $transcription, $translation, $targetLang);
                        $message = ['success' => true, 'text' => 'Traitement réussi ! Audio ID: ' . $audioId];
                    } else {
                        $message = ['success' => true, 'text' => 'Traitement réussi !'];
                    }
                } catch (\Exception $e) {
                    $message = ['success' => false, 'text' => $e->getMessage()];
                }
            } else {
                $message = ['success' => false, 'text' => 'Aucun fichier audio uploadé'];
            }
        }
        
        return new ViewModel([
            'message' => $message,
            'transcription' => $transcription,
            'translation' => $translation,
        ]);
    }
    
    protected function saveToOmeka($audioFile, $transcription, $translation, $targetLang)
{
    $api = $this->api();
    $settings = $this->settings();
    
    // 1. Créer l'item Audio AVEC le média directement
    $audioData = [
        'o:resource_class' => ['o:id' => $this->getClassId('o2t:Audio')],
        'o:resource_template' => ['o:id' => $this->getTemplateId('Audio')],
        'dcterms:title' => [[
            '@value' => basename($audioFile['name']),
            'property_id' => $this->getPropertyId('dcterms:title'),
            'type' => 'literal'
        ]],
        'o2t:titre' => [[
            '@value' => basename($audioFile['name']),
            'property_id' => $this->getPropertyId('o2t:titre'),
            'type' => 'literal'
        ]],
        'o2t:dateUpload' => [[
            '@value' => date('Y-m-d'),
            'property_id' => $this->getPropertyId('o2t:dateUpload'),
            'type' => 'literal'
        ]],
        // Ajouter le média à la création
        'o:media' => [[
            'o:ingester' => 'upload',
            'file_index' => '0',
            'dcterms:title' => [[
                '@value' => basename($audioFile['name']),
                'property_id' => $this->getPropertyId('dcterms:title'),
                'type' => 'literal'
            ]]
        ]]
    ];
    
    // Préparer les données du fichier au bon format
    $fileData = [
        'file' => [
            0 => [ // L'index doit correspondre à 'file_index' => '0'
                'name' => $audioFile['name'],
                'type' => $audioFile['type'],
                'tmp_name' => $audioFile['tmp_name'],
                'error' => $audioFile['error'],
                'size' => $audioFile['size']
            ]
        ]
    ];
    
    // Créer l'item avec le média
    $response = $api->create('items', $audioData, $fileData);
    $audioItem = $response->getContent();
    $audioId = $audioItem->id();
    
    // Récupérer l'ID du média créé
    $medias = $audioItem->media();
    $mediaId = null;
    if (count($medias) > 0) {
        $mediaId = $medias[0]->id();
    }
    
    // 2. Mettre à jour l'item Audio avec l'ID et le lien vers le média
    $updateData = [
        'o2t:idAudio' => [[
            '@value' => $audioId,
            'property_id' => $this->getPropertyId('o2t:idAudio'),
            'type' => 'literal'
        ]]
    ];
    
    if ($mediaId) {
        $updateData['o2t:fichier'] = [[
            'value_resource_id' => $mediaId,
            'property_id' => $this->getPropertyId('o2t:fichier'),
            'type' => 'resource'
        ]];
    }
    
    //$api->update('items', $audioId, $updateData, [], ['isPartial' => true]);
    
    // 3. Créer l'item Transcription
    $transcriptionData = [
        'o:resource_class' => ['o:id' => $this->getClassId('o2t:Transcription')],
        'o:resource_template' => ['o:id' => $this->getTemplateId('Transcription')],
        'dcterms:title' => [[
            '@value' => 'Transcription de ' . basename($audioFile['name']),
            'property_id' => $this->getPropertyId('dcterms:title'),
            'type' => 'literal'
        ]],
        'o2t:contenuTranscription' => [[
            '@value' => $transcription,
            'property_id' => $this->getPropertyId('o2t:contenuTranscription'),
            'type' => 'literal'
        ]],
        'o2t:langueTranscription' => [[
            '@value' => $settings->get('oral2text_whisper_language', 'fr'),
            'property_id' => $this->getPropertyId('o2t:langueTranscription'),
            'type' => 'literal'
        ]],
        'o2t:aPourAudio' => [[
            'value_resource_id' => $audioId,
            'property_id' => $this->getPropertyId('o2t:aPourAudio'),
            'type' => 'resource'
        ]]
    ];
    
    $response = $api->create('items', $transcriptionData);
    $transcriptionItem = $response->getContent();
    $transcriptionId = $transcriptionItem->id();
    
    $api->update('items', $transcriptionId, [
        'o2t:idTranscription' => [[
            '@value' => $transcriptionId,
            'property_id' => $this->getPropertyId('o2t:idTranscription'),
            'type' => 'literal'
        ]]
    ], [], ['isPartial' => true]);
    
    // 4. Créer l'item Traduction si nécessaire
    if ($translation && $targetLang !== 'none') {
        $traductionData = [
            'o:resource_class' => ['o:id' => $this->getClassId('o2t:Traduction')],
            'o:resource_template' => ['o:id' => $this->getTemplateId('Traduction')],
            'dcterms:title' => [[
                '@value' => 'Traduction (' . $targetLang . ') de ' . basename($audioFile['name']),
                'property_id' => $this->getPropertyId('dcterms:title'),
                'type' => 'literal'
            ]],
            'o2t:contenuTraduction' => [[
                '@value' => $translation,
                'property_id' => $this->getPropertyId('o2t:contenuTraduction'),
                'type' => 'literal'
            ]],
            'o2t:langueCible' => [[
                '@value' => $targetLang,
                'property_id' => $this->getPropertyId('o2t:langueCible'),
                'type' => 'literal'
            ]],
            'o2t:aPourTranscription' => [[
                'value_resource_id' => $transcriptionId,
                'property_id' => $this->getPropertyId('o2t:aPourTranscription'),
                'type' => 'resource'
            ]]
        ];
        
        $response = $api->create('items', $traductionData);
        $traductionItem = $response->getContent();
        $traductionId = $traductionItem->id();
        
        $api->update('items', $traductionId, [
            'o2t:idTraduction' => [[
                '@value' => $traductionId,
                'property_id' => $this->getPropertyId('o2t:idTraduction'),
                'type' => 'literal'
            ]]
        ], [], ['isPartial' => true]);
    }
    
    return $audioId;
}
    
    protected function getClassId($term)
    {
        $entityManager = $this->getEvent()->getApplication()->getServiceManager()->get('Omeka\EntityManager');
        $vocabPrefix = explode(':', $term)[0];
        $localName = explode(':', $term)[1];
        
        $vocab = $entityManager->getRepository('Omeka\Entity\Vocabulary')
            ->findOneBy(['prefix' => $vocabPrefix]);
        
        if (!$vocab) {
            throw new \Exception("Vocabulaire '$vocabPrefix' introuvable");
        }
        
        $class = $entityManager->getRepository('Omeka\Entity\ResourceClass')
            ->findOneBy(['vocabulary' => $vocab, 'localName' => $localName]);
        
        if (!$class) {
            throw new \Exception("Classe '$term' introuvable");
        }
        
        return $class->getId();
    }
    
    protected function getPropertyId($term)
    {
        $entityManager = $this->getEvent()->getApplication()->getServiceManager()->get('Omeka\EntityManager');
        $vocabPrefix = explode(':', $term)[0];
        $localName = explode(':', $term)[1];
        
        $vocab = $entityManager->getRepository('Omeka\Entity\Vocabulary')
            ->findOneBy(['prefix' => $vocabPrefix]);
        
        if (!$vocab) {
            throw new \Exception("Vocabulaire '$vocabPrefix' introuvable");
        }
        
        $property = $entityManager->getRepository('Omeka\Entity\Property')
            ->findOneBy(['vocabulary' => $vocab, 'localName' => $localName]);
        
        if (!$property) {
            throw new \Exception("Propriété '$term' introuvable");
        }
        
        return $property->getId();
    }
    
    protected function getTemplateId($label)
    {
        $entityManager = $this->getEvent()->getApplication()->getServiceManager()->get('Omeka\EntityManager');
        $template = $entityManager->getRepository('Omeka\Entity\ResourceTemplate')
            ->findOneBy(['label' => $label]);
        
        if (!$template) {
            throw new \Exception("Template '$label' introuvable");
        }
        
        return $template->getId();
    }
}