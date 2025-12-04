<?php
namespace Oral2Text\Service\SpeechToText;

use Omeka\Settings\Settings;

/**
 * Service de transcription audio avec Whisper Local
 */
class SpeechToTextService
{
    protected $settings;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Transcrire un fichier audio en texte
     * @param string $audioFilePath Chemin vers le fichier audio
     * @param string $language Langue (ex: 'fr', 'en', 'ar')
     * @return string Texte transcrit
     */
    public function transcribe($audioFilePath)
{
    if (!file_exists($audioFilePath)) {
        throw new \Exception("Le fichier audio n'existe pas : $audioFilePath");
    }

    // Récupérer le modèle depuis les settings
    $model = $this->settings->get('oral2text_whisper_model', 'base');
    
    // Vérifier que le modèle est valide
    $validModels = ['tiny', 'base', 'small', 'medium', 'large'];
    if (!in_array($model, $validModels)) {
        $model = 'base';
    }

    // Créer un dossier temporaire pour les résultats
    $outputDir = sys_get_temp_dir() . '/whisper_' . uniqid();
    if (!mkdir($outputDir, 0777, true)) {
        throw new \Exception("Impossible de créer le dossier de sortie");
    }

    // ⭐ AJOUTER FFMPEG AU PATH POUR CETTE SESSION
    $currentPath = getenv('PATH');
    $ffmpegPaths = [
        'C:\ProgramData\chocolatey\bin',  // Path Chocolatey
        'C:\ProgramData\chocolatey\lib\ffmpeg\tools\ffmpeg\bin',  // Path FFmpeg via Choco
        'C:\ffmpeg\bin',  // Path manuel
    ];
    
    foreach ($ffmpegPaths as $path) {
        if (is_dir($path)) {
            putenv("PATH=$currentPath;$path");
            break;
        }
    }

    // Construire la commande Whisper
    $command = sprintf(
        'whisper "%s" --model %s --output_dir "%s" --output_format txt --language fr',
        $audioFilePath,
        $model,
        $outputDir
    );

    // Exécuter la commande
    exec($command . ' 2>&1', $output, $returnCode);

    // DEBUG : Afficher la commande et la sortie
    if ($returnCode !== 0) {
        $errorMsg = "Erreur Whisper (code: $returnCode)\n";
        $errorMsg .= "Commande: $command\n";
        $errorMsg .= "PATH utilisé: " . getenv('PATH') . "\n";
        $errorMsg .= "Sortie: " . implode("\n", $output);
        throw new \Exception($errorMsg);
    }

    // Chercher le fichier de transcription
    $possibleFiles = [
        $outputDir . '/' . pathinfo($audioFilePath, PATHINFO_FILENAME) . '.txt',
        $outputDir . '/' . basename($audioFilePath, '.' . pathinfo($audioFilePath, PATHINFO_EXTENSION)) . '.txt'
    ];

    // Lister tous les fichiers générés pour debug
    $generatedFiles = glob("$outputDir/*");
    
    $txtFile = null;
    foreach ($possibleFiles as $file) {
        if (file_exists($file)) {
            $txtFile = $file;
            break;
        }
    }

    // Si aucun fichier trouvé, chercher n'importe quel .txt
    if (!$txtFile) {
        $txtFiles = glob("$outputDir/*.txt");
        if (!empty($txtFiles)) {
            $txtFile = $txtFiles[0];
        }
    }

    if (!$txtFile || !file_exists($txtFile)) {
        $errorMsg = "Fichier de transcription non généré\n";
        $errorMsg .= "Dossier de sortie: $outputDir\n";
        $errorMsg .= "Fichiers générés: " . implode(', ', $generatedFiles) . "\n";
        $errorMsg .= "Commande exécutée: $command\n";
        $errorMsg .= "PATH: " . getenv('PATH') . "\n";
        $errorMsg .= "Sortie Whisper: " . implode("\n", $output);
        
        // Nettoyer avant de lever l'exception
        if (!empty($generatedFiles)) {
            array_map('unlink', $generatedFiles);
        }
        rmdir($outputDir);
        
        throw new \Exception($errorMsg);
    }

    $transcription = file_get_contents($txtFile);

    // Nettoyer le dossier temporaire
    array_map('unlink', glob("$outputDir/*"));
    rmdir($outputDir);

    return trim($transcription);
}

    /**
     * Langues supportées par Whisper
     */
    public function getSupportedLanguages()
    {
        return [
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
        ];
    }

    /**
     * Modèles Whisper disponibles
     */
    public function getAvailableModels()
    {
        return [
            'tiny' => 'Tiny (39M - Très rapide)',
            'base' => 'Base (74M - Rapide)',
            'small' => 'Small (244M - Équilibré)',
            'medium' => 'Medium (769M - Précis)',
            'large' => 'Large (1550M - Très précis)',
        ];
    }
}
