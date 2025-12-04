# Module Oral2Text pour Omeka S

Module de transcription audio (Whisper) et traduction (LibreTranslate) 100%% gratuit.

## Prérequis

### 1. Installer Whisper
```bash
# Option 1: Via pip
pip install openai-whisper

# Option 2: Via conda
conda install -c conda-forge openai-whisper
```

### 2. Installer FFmpeg (requis par Whisper)
```bash
# Ubuntu/Debian
sudo apt install ffmpeg

# Windows
# Télécharger: https://ffmpeg.org/download.html

# macOS
brew install ffmpeg
```

## Installation du module

1. Copier ce dossier dans `/modules/Oral2Text`
2. Installer les dépendances PHP:
   ```bash
   cd modules/Oral2Text
   composer install
   ```
3. Dans Omeka S Admin: Modules -> Oral2Text -> Installer
4. Configurer: Modules -> Oral2Text -> Configurer

## Utilisation

### Dans votre code PHP:
```php
// Récupérer les services
$speechService = $serviceLocator->get('Oral2Text\SpeechToTextService');
$translationService = $serviceLocator->get('Oral2Text\TranslationService');

// 1. Transcrire un audio
$audioPath = '/path/to/audio.mp3';
$transcription = $speechService->transcribe($audioPath, 'fr');

// 2. Traduire le texte
$traduction = $translationService->translate($transcription, 'en', 'fr');
```

## Configuration

### Modèles Whisper
- **tiny**: 39M - Très rapide, précision moyenne
- **base**: 74M - Rapide, bonne précision (recommandé)
- **small**: 244M - Équilibré
- **medium**: 769M - Précis, plus lent
- **large**: 1550M - Maximum de précision

### LibreTranslate
- API publique: https://libretranslate.com/translate (gratuit, 5 req/min)
- Installation locale: https://github.com/LibreTranslate/LibreTranslate

## Auteur

Mohammed Bouzidi Idirissi
