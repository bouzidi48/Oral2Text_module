# Guide d'installation Whisper + LibreTranslate

## 1. Installer Whisper

### Ubuntu/Debian
```bash
# Installer Python et pip
sudo apt update
sudo apt install python3 python3-pip ffmpeg

# Installer Whisper
pip3 install openai-whisper

# Vérifier l'installation
whisper --help
```

### Windows
```cmd
# 1. Installer Python 3.8+ depuis python.org
# 2. Installer FFmpeg depuis ffmpeg.org
# 3. Ouvrir CMD et installer Whisper:
pip install openai-whisper

# Vérifier
whisper --help
```

### Tester Whisper
```bash
# Télécharger un audio de test
wget https://github.com/openai/whisper/raw/main/tests/jfk.flac

# Transcrire
whisper jfk.flac --model base --language en
```

## 2. Configurer LibreTranslate

### Option A: Utiliser l'API publique (gratuit)
- URL: https://libretranslate.com/translate
- Limite: 5 requêtes/minute
- Aucune configuration requise

### Option B: Installation locale (illimité)
```bash
# Installer avec pip
pip install libretranslate

# Lancer le serveur
libretranslate --host 0.0.0.0 --port 5000

# URL locale: http://localhost:5000/translate
```

## 3. Configuration dans Omeka S

1. Aller dans: Admin -> Modules -> Oral2Text -> Configurer
2. Choisir le modèle Whisper (recommandé: base)
3. Définir la langue par défaut
4. Configurer l'URL LibreTranslate

## Résolution de problèmes

### Whisper ne fonctionne pas
```bash
# Vérifier que Whisper est installé
python3 -c "import whisper; print(whisper.__version__)"

# Vérifier FFmpeg
ffmpeg -version
```

### LibreTranslate timeout
- Augmenter le timeout dans TranslationService.php
- Utiliser une instance locale pour de meilleures performances

## Support

- Whisper: https://github.com/openai/whisper
- LibreTranslate: https://github.com/LibreTranslate/LibreTranslate
