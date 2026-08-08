# Pointage ZKTeco - Gestion des pointeuses réseau

## Architecture de l'application

```
/kz-sync-app/
├── config/
│   └── devices.json      -- Configuration: pointeuses (nom, IP, port, département)
├── devices.php            -- Charge config/devices.json → array pour sync.php & status.php
├── sync.php               -- Synchronisation réseau (SDK ZKTeco → data.json)
├── status.php             -- Vérification de l'état des pointeuses (ping + connect)
├── api.php                -- API REST: sert data.json en cache
├── api-devices.php        -- API REST: CRUD sur les pointeuses (Ajouter/Modifier/Supprimer)
├── manage.html            -- Interface web de gestion des pointeuses
├── debug.php              -- Script de diagnostic réseau
├── index.html             -- Interface utilisateur (tableau de bord pointages)
├── style.css              -- Design & styles
├── app.js                 -- Logique JS (Fetch API, filtres, rendu)
│   &nbsp;&nbsp;Filtres : nom, période (date de à), type (Entrée/Sortie)
│   &nbsp;&nbsp;Compteur dynamique : N lignes affichées / Total
├── data.json              -- Cache généré par sync.php
└── zkteco/                -- SDK ZKTeco (CodingLibs/ZktecoPhp)
    ├── autoload.php       -- Autoloader PSR-4 (sans Composer)
    └── src/               -- Classes SDK complètes
```

## Flux de données

```
index.html → Fetch → api.php         (sert data.json en cache)
index.html → Fetch → status.php      (état des pointeuses)
index.html → manage.html            (gestion pointeuses: add/edit/delete)
  manage.html → Fetch → api-devices.php (CRUD sur config/devices.json)
[btn Sync]  → Fetch → sync.php       (SDK UDP → users + attendances → data.json)
```

## Gestion des pointeuses

Accédez à l'interface de gestion via **⚙️ Gérer les pointeuses** sur le tableau de bord, ou directement à `manage.html`.

### Fonctionnalités
- **Ajouter** : nom, adresse IP, port UDP, département
- **Modifier** : cliquez sur ✏️ sur une ligne, modifiez, enregistrez
- **Supprimer** : cliquez sur 🗑️ (confirmation requise)
- **Annuler** : bouton ✕ pour annuler une modification en cours

### Validation côté serveur (`api-devices.php`)
- Nom et IP obligatoires
- IP validée via `filter_var(FILTER_VALIDATE_IP)`
- Port entre 1 et 65535
- Détection de doublons IP/port

### Format de configuration (`config/devices.json`)
```json
{
    "default_port": 4370,
    "devices": [
        {
            "id": 1,
            "name": "Pointeuse Atelier",
            "ip": "192.168.100.140",
            "port": 4370,
            "department": "Atelier"
        }
    ]
}
```

## Protocole ZKTeco (SDK CodingLibs/ZktecoPhp)

Le SDK utilise le **protocole UDP natif** (port 4370) avec checksum dynamique :

| Commande | Code | Description |
|---|---|---|
| CMD_CONNECT | 1000 | Session UDP, échange session_id |
| CMD_USER_TEMP_RRQ | 9 | Récupère les utilisateurs (`chr(FCT_USER)` préfixé) |
| CMD_ATTLOG_RRQ | 13 | Récupère l'historique des pointages |
| CMD_EXIT | 1001 | Déconnexion |
| CMD_PREPARE_DATA | 1500 | Annonce multipaquets |
| CMD_DATA | 1501 | Paquet de données |
| CMD_FREE_DATA | 1502 | Nettoie le buffer |

## Dépannage

```bash
php debug.php   # Ping, connexion, version, users, attendances
```
