# Améliorations du Système de Contrôle d'Intégrité

## Vue d'ensemble

Le système `AppIntegrityService` a été amélioré pour détecter les **fichiers rajoutés** au CRM principal, en comparant les fichiers actuels avec ceux listés dans `signatures.json`.

## Comportement Clé

### Pour le CRM Principal
- ✅ Détecte les fichiers **manquants**
- ✅ Détecte les fichiers **modifiés** (hash mismatch)
- ✅ Détecte les fichiers **rajoutés** (fichiers non signés)

### Pour les Plugins
- ✅ Chaque plugin gère sa propre `signatures.json`
- ✅ Chaque plugin vérifie ses fichiers indépendamment
- ❌ **N'applique PAS** la détection de fichiers rajoutés aux plugins

## Répertoires Ignorés Complètement

La recherche de fichiers rajoutés **n'entre jamais** dans ces répertoires:

| Répertoire | Raison |
|-----------|--------|
| `/Images/` | Galerie photos utilisateur (généré) |
| `/Plugins/` | Chaque plugin gère sa propre signature |
| `/private/` | Répertoire privé (généré/modifié) |
| `/data/` | Données personnalisées (généré) |
| `node_modules/` | Dépendances npm |
| `.git/` | Données Git |
| `vendor/` | Dépendances Composer |
| `target/` | Fichiers build |
| `__pycache__/` | Cache Python |

## Modifications Apportées

### 1. Méthode `verifyApplicationIntegrity()` - Mise à jour

La méthode construit une liste de fichiers connus et cherche les fichiers rajoutés dans le répertoire racine du CRM (à l'exception des répertoires ignorés).

```php
// Détecte les fichiers rajoutés uniquement dans le CRM principal
$extraFiles = AppIntegrityService::findAddedFiles(SystemURLs::getDocumentRoot(), $knownFiles);
foreach ($extraFiles as $filename) {
    $signatureFailures['CRM'][] = ['filename' => $filename, 'status' => 'File Added'];
}
```

### 2. Méthode `verifyPluginsIntegrity()` - Mise à jour

**Chaque plugin gère sa propre signature.** La détection de fichiers rajoutés n'est **pas appliquée** aux plugins:

```php
// Les plugins vérifient seulement les fichiers listés dans leur signature.json
// Pas d'appel à findAddedFiles() pour les plugins
foreach ($signatureData->files as $file) {
    $currentFile = SystemURLs::getDocumentRoot() . '/' . $file->filename;
    // ... vérification du hash ou absence du fichier ...
}
```

### 3. 🆕 Méthode `findAddedFiles()` - Nouvelle Méthode Privée

Scanne le répertoire et identifie les fichiers rajoutés non présents dans la liste signée.

### 4. 🆕 Méthode `recursiveFileSearch()` - Nouvelle Méthode Privée

- Utilise `RecursiveDirectoryIterator` pour scanner les fichiers
- Appelle `shouldSkipDirectory()` pour ignorer complètement certains répertoires
- Appelle `shouldIgnoreFile()` pour ignorer les fichiers spécifiques
- Compare chaque fichier avec la liste `$knownFiles`

### 5. 🆕 Méthode `shouldSkipDirectory()` - Nouvelle Méthode Privée

Ignore complètement les répertoires entiers pendant le scan:
- `node_modules/`, `.git/`, `vendor/`, `target/`, `__pycache__/`
- `/Images/` - **Ne sera jamais scannée**
- `/private/`, `/data/`, `/Plugins/`

### 6. Méthode `shouldIgnoreFile()` - Mise à jour

Traite maintenant seulement les fichiers individuels (pas les répertoires):
- `.gitignore`, `.htaccess`, `signatures.json`
- `.DS_Store`, `Thumbs.db`, `/Include/Config.php`

## Résultat de Vérification

### Statuts Possibles

```json
{
  "status": "failure",
  "message": "One or more files failed signature validation",
  "files": {
    "CRM": [
      {
        "filename": "api/newfeature.php",
        "status": "File Added"
      },
      {
        "filename": "api/index.php",
        "status": "Hash Mismatch",
        "expectedhash": "abc123",
        "actualhash": "xyz999"
      },
      {
        "filename": "config/system.php",
        "status": "File Missing"
      }
    ],
    "PluginName": [
      {
        "filename": "Plugins/PluginName/core.php",
        "status": "Hash Mismatch",
        "expectedhash": "def456",
        "actualhash": "uuu888"
      }
    ]
  }
}
```

## Avantages

1. **Sécurité Améliorée:** Détecte les fichiers suspects rajoutés (malware, modifications non autorisées)
2. **Architecture Propre:** Chaque plugin gère sa propre signature
3. **Pas de Faux Positifs:** Les répertoires générés ne sont jamais scannés
4. **Performance Optimisée:** Évite complètement les répertoires inutiles
5. **Flexibilité:** Facile à modifier les patterns d'exclusion

## Utilisation

Le système fonctionne de manière transparente:

```php
$result = AppIntegrityService::verifyApplicationIntegrity();

if ($result['status'] === 'failure') {
    foreach ($result['files']['CRM'] as $file) {
        if ($file['status'] === 'File Added') {
            echo "Fichier inattendu détecté: " . $file['filename'];
        }
    }
}
```

## Notes de Compatibilité

- ✅ Rétro-compatible: Le code existant continue de fonctionner
- ✅ Pas de changement dans la signature des méthodes publiques
- ✅ Pas de dépendances supplémentaires
- ✅ Supporte les chemins Windows et Unix
- ✅ Les plugins gardent leur indépendance (chacun gère sa signature)
