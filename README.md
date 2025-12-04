# Module Oral2Text pour Omeka S

Module de transcription audio (Whisper) et traduction (LibreTranslate) 100%% gratuit.

# Installation


Guide d'installation simplifié pour le module de transcription et traduction audio.

---

## Ce qu'il faut installer

### 1. Python 3.10
### 2. Whisper (transcription audio)
### 3. LibreTranslate (traduction)
### 4. FFmpeg (traitement audio)
### 5. Guzzle (client HTTP PHP)

---

## Installation étape par étape

### Étape 1 : Installer Python 3.10

1. **Téléchargez Python 3.10.9** :
   ```
   https://www.python.org/ftp/python/3.10.9/python-3.10.9-amd64.exe
   ```

2. **Lancez l'installateur** et **IMPORTANT** :
   - Cochez **"Add Python 3.10 to PATH"**
   - Cliquez sur **"Install Now"**

3. **Vérifiez l'installation** :
   ```powershell
   python --version
   ```
    Devrait afficher : `Python 3.10.9`

---

### Étape 2 : Installer Whisper

```powershell
pip install openai-whisper
```

**Vérification :**
```powershell
whisper --help
```
 Devrait afficher l'aide de Whisper

---

### Étape 3 : Installer LibreTranslate

```powershell
pip install libretranslate
```

**Vérification :**
```powershell
libretranslate --help
```

---

### Étape 4 : Installer FFmpeg

```powershell
# Installer Chocolatey (gestionnaire de paquets Windows)
Set-ExecutionPolicy Bypass -Scope Process -Force
[System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072
iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))

# Installer FFmpeg
choco install ffmpeg
```

**Vérification :**
```powershell
ffmpeg -version
```
 Devrait afficher la version de FFmpeg

** Important :** Redémarrez votre ordinateur après l'installation de FFmpeg.

---

### Étape 5 : Télécharger et installer le module

1. **Téléchargez la dernière version** depuis GitHub :
   ```
   https://github.com/bouzidi48/Oral2Text_module/releases/download/v1.0.0/Oral2Text.zip
   ```

2. **Extrayez l'archive** dans le dossier modules d'Omeka :
   ```
   C:\xampp\htdocs\omk_thyp_25-26_clone\modules\Oral2Text
   ```

3. **Installez Guzzle** à la racine d'Omeka :
   ```powershell
   cd C:\xampp\htdocs\omk_thyp_25-26_clone
   composer require guzzlehttp/guzzle
   composer dump-autoload -o
   ```

---

### Étape 6 : Activer le module dans Omeka-S

1. Redémarrez Apache dans XAMPP
2. Allez sur `http://localhost/omk_thyp_25-26_clone/admin`
3. Cliquez sur **Modules**
4. Trouvez **Oral2Text** et cliquez sur **Installer**
5. Puis cliquez sur **Activer**

---

## Lancer LibreTranslate

### Créer un fichier de lancement automatique

1. **Créez un fichier** `start-libretranslate.bat` sur votre bureau
2. **Copiez ce contenu** :

```bat
@echo off
echo Lancement de LibreTranslate...
C:\Users\oo\AppData\Local\Programs\Python\Python310\Scripts\libretranslate.exe --host 127.0.0.1 --port 5000 --load-only fr,en,ar,es
pause
```

3. **Double-cliquez** sur le fichier pour lancer LibreTranslate
4. **Laissez la fenêtre ouverte** pendant l'utilisation du module

 **Note :** Ajustez le chemin si votre Python est installé ailleurs. Vous pouvez le trouver avec :
```powershell
where python
```

---

## Configuration du module

1. Dans Omeka-S, allez dans **Paramètres** (en haut à droite)
2. Configurez les paramètres Oral2Text :
   - **URL LibreTranslate** : `http://127.0.0.1:5000/translate`
   - **Clé API** : (laisser vide pour l'instance locale)
   - **Modèle Whisper** : `base` (recommandé)

---

## Vérifications

### Test 1 : Python
```powershell
python --version
```
 Doit afficher : `Python 3.10.9`

### Test 2 : Whisper
```powershell
whisper --help
```
 Doit afficher l'aide

### Test 3 : FFmpeg
```powershell
ffmpeg -version
```
 Doit afficher la version

### Test 4 : LibreTranslate
1. Lancez `start-libretranslate.bat`
2. Attendez le message : `Running on http://127.0.0.1:5000`
3. Ouvrez dans un navigateur : `http://127.0.0.1:5000`
 La page LibreTranslate doit s'afficher

### Test 5 : Module Oral2Text
1. Allez sur `http://localhost/omk_thyp_25-26_clone/admin/oral2text`
2. Enregistrez un audio
3. Cliquez sur "Transcrire et Sauvegarder"
 La transcription doit s'afficher


---

**Version :** 1.0.0  
**Compatibilité :** Omeka-S 3.x+  
**Python requis :** 3.10.9  
**PHP requis :** 7.4 ou 8.0+

## Auteur

Mohammed Bouzidi Idirissi
