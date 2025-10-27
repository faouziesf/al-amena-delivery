# 🔐 SPÉCIFICATION TECHNIQUE COMPLÈTE - API CLIENT

**Version** : 1.0  
**Date** : 24 Octobre 2025  
**Statut** : Prêt pour Développement

---

## 📚 DOCUMENTATION COMPLÈTE

Cette spécification technique couvre tous les aspects de la nouvelle fonctionnalité API Client pour Al-Amena Delivery.

### **📂 Documents**

| # | Document | Description |
|---|----------|-------------|
| 1 | **[PARTIE 1 : Interface Utilisateur](SPEC_API_CLIENT_PARTIE_1_UI.md)** | Design complet de l'UI pour la gestion du token API |
| 2 | **[PARTIE 2 : Endpoints API](SPEC_API_CLIENT_PARTIE_2_ENDPOINTS.md)** | Définition des routes, requêtes/réponses JSON, exemples |
| 3 | **[PARTIE 3 : Sécurité & Documentation](SPEC_API_CLIENT_PARTIE_3_SECURITE_DOC.md)** | Mesures de sécurité et structure documentation client |

---

## 🎯 VUE D'ENSEMBLE

### **Objectif**

Permettre aux clients d'intégrer automatiquement Al-Amena Delivery avec leur système e-commerce ou ERP via une API REST sécurisée.

### **Fonctionnalités Principales**

✅ **Gestion du Token API**
- Interface utilisateur complète dans l'espace client
- Génération/Régénération sécurisée
- Statistiques d'utilisation en temps réel

✅ **Création de Colis**
- Endpoint POST pour créer 1 à 100 colis par requête
- Validation complète des données
- Retour immédiat des tracking numbers

✅ **Export et Suivi**
- Endpoint GET avec filtres avancés
- Pagination performante
- Historique complet de chaque colis

✅ **Sécurité Renforcée**
- Bearer Token Authentication
- Rate limiting (120 req/min)
- HTTPS obligatoire
- Isolation complète des données

---

## 📋 RÉSUMÉ PAR PARTIE

### **PARTIE 1 : Interface Utilisateur** 📱

**Emplacement** : `/client/settings/api`

**Composants** :
- Section "À propos de l'API" avec lien documentation
- Gestion du token (affichage, copie, régénération)
- Modales de confirmation avec avertissements clairs
- Section sécurité avec consignes importantes
- Statistiques d'utilisation (aujourd'hui, mois, historique)

**Technologies** :
- HTML/Blade templates
- TailwindCSS pour le style
- Alpine.js pour interactions
- JavaScript vanilla pour API calls

**États Gérés** :
1. Aucun token → Bouton "Générer"
2. Token actif masqué → Afficher/Copier/Régénérer
3. Token révélé → Copie facilitée
4. Modale succès → Token affiché une seule fois

---

### **PARTIE 2 : Endpoints API** 🌐

**Base URL** : `https://api.al-amena.tn/v1`

#### **Endpoints Disponibles**

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| `POST` | `/api/v1/client/packages` | Créer des colis (1-100) |
| `GET` | `/api/v1/client/packages` | Lister avec filtres |
| `GET` | `/api/v1/client/packages/{tracking}` | Détail d'un colis |
| `GET` | `/api/v1/client/stats` | Statistiques client |

#### **Authentification**

```http
Authorization: Bearer alamena_live_a3f8d9c7b2e1f4a6d8c9b3e7f1a2d5c8
```

#### **Filtres Disponibles (GET)**

- `status` : Filtrer par statut
- `date_from` / `date_to` : Plage de dates
- `tracking_number` : Recherche exacte
- `gouvernorat` / `delegation` : Filtres géographiques
- `payment_type` : COD ou PREPAID
- `page` / `per_page` : Pagination (10-100)

#### **Création de Colis (POST)**

**Champs Obligatoires** :
- `pickup_address_id`
- `recipient_name`, `recipient_phone`
- `recipient_gouvernorat`, `recipient_delegation`, `recipient_address`
- `package_content`, `package_price`
- `delivery_type` (HOME, STOP_DESK)
- `payment_type` (COD, PREPAID)

**Champs Optionnels** :
- `recipient_phone_2`
- `cod_amount` (obligatoire si COD)
- `is_fragile`, `is_exchange`
- `comment`

#### **Codes HTTP**

| Code | Signification |
|------|---------------|
| 200 | OK |
| 201 | Created |
| 401 | Unauthorized (token invalide) |
| 403 | Forbidden (accès refusé) |
| 404 | Not Found |
| 422 | Validation Error |
| 429 | Too Many Requests |
| 500 | Server Error |

---

### **PARTIE 3 : Sécurité & Documentation** 🔒

#### **Mesures de Sécurité**

**Stockage Token** :
- ✅ Hachage SHA-256 en base de données
- ✅ Token généré : 64 caractères aléatoires
- ✅ Préfixe : `alamena_live_` ou `alamena_test_`
- ✅ Index sur `token_hash` pour performance

**Protection des Données** :
- ✅ Middleware authentification
- ✅ Rate limiting (120 req/min global, 60/min création)
- ✅ Isolation par `sender_id`
- ✅ Validation stricte des entrées
- ✅ Sanitization automatique

**Monitoring** :
- ✅ Logging toutes requêtes API
- ✅ Alertes activité suspecte (>20 erreurs en 5min)
- ✅ Tracking dernière utilisation
- ✅ Statistiques en temps réel

**HTTPS** :
- ✅ Force HTTPS en production
- ✅ Headers sécurité (HSTS, X-Frame-Options, etc.)
- ✅ Certificat SSL valide

#### **Documentation Client**

**Structure** :
1. Introduction & Quick Start
2. Authentification (obtenir/utiliser token)
3. Endpoints détaillés avec exemples
4. Exemples par langage (PHP, Python, Node.js, cURL)
5. Codes d'erreur et solutions
6. Rate limiting et limites
7. FAQ & Support

**Formats** :
- Markdown pour documentation statique
- Swagger/Redoc pour documentation interactive
- PDF téléchargeable

**Exemples Fournis** :
- ✅ cURL
- ✅ PHP (natif + Laravel HTTP Client)
- ✅ Python (requests)
- ✅ Node.js (axios)
- ✅ WooCommerce Plugin (snippet)

---

## 🚀 PLAN DE DÉVELOPPEMENT

### **Phase 1 : Backend API** (5 jours)

**Tâches** :
- [ ] Créer migration `api_tokens`
- [ ] Créer modèle `ApiToken` avec méthodes
- [ ] Créer middleware `ApiTokenAuth`
- [ ] Créer contrôleur `ApiPackageController`
- [ ] Définir routes API (`routes/api.php`)
- [ ] Implémenter validation et policies
- [ ] Configurer rate limiting
- [ ] Créer tests unitaires et d'intégration

**Fichiers à Créer** :
```
database/migrations/
  └── 2025_10_24_create_api_tokens_table.php

app/Models/
  └── ApiToken.php

app/Http/Middleware/
  └── ApiTokenAuth.php

app/Http/Controllers/Api/
  └── ApiPackageController.php

app/Http/Requests/Api/
  ├── CreatePackageRequest.php
  └── ListPackagesRequest.php

routes/
  └── api.php (ajouter routes)
```

---

### **Phase 2 : Interface Utilisateur** (3 jours)

**Tâches** :
- [ ] Créer contrôleur `ClientApiTokenController`
- [ ] Créer vue `client/settings/api.blade.php`
- [ ] Implémenter JavaScript (toggle, copy, modales)
- [ ] Styler avec TailwindCSS
- [ ] Ajouter route dans `routes/client.php`
- [ ] Créer composant statistiques

**Fichiers à Créer** :
```
app/Http/Controllers/Client/
  └── ClientApiTokenController.php

resources/views/client/settings/
  └── api.blade.php

public/js/
  └── client-api-token.js
```

---

### **Phase 3 : Documentation** (2 jours)

**Tâches** :
- [ ] Installer Laravel Scramble
- [ ] Configurer Swagger annotations
- [ ] Créer page documentation Markdown
- [ ] Ajouter exemples de code
- [ ] Créer PDF téléchargeable
- [ ] Tester avec Postman

**Fichiers à Créer** :
```
resources/views/client/api/
  └── docs.blade.php

public/docs/
  ├── api-guide.md
  └── api-guide.pdf
```

---

### **Phase 4 : Sécurité & Tests** (2 jours)

**Tâches** :
- [ ] Audit sécurité complet
- [ ] Tests de pénétration
- [ ] Vérifier rate limiting
- [ ] Tests end-to-end
- [ ] Documentation sécurité
- [ ] Plan de réponse incidents

**Tests à Créer** :
```
tests/Feature/Api/
  ├── ApiAuthenticationTest.php
  ├── CreatePackageTest.php
  ├── ListPackagesTest.php
  └── RateLimitingTest.php

tests/Unit/
  └── ApiTokenTest.php
```

---

## 📊 MÉTRIQUES DE SUCCÈS

### **Performance**

| Métrique | Cible |
|----------|-------|
| Temps réponse moyen | < 200ms |
| Temps création colis | < 500ms |
| Disponibilité API | > 99.9% |
| Rate limit OK | 120 req/min |

### **Adoption**

| Indicateur | Objectif Mois 1 | Objectif Mois 3 |
|-----------|-----------------|-----------------|
| Clients avec token | 50 | 200 |
| Colis via API | 1,000 | 10,000 |
| Requêtes/jour | 5,000 | 50,000 |

### **Qualité**

- Taux d'erreur < 1%
- Temps résolution bugs < 24h
- Support temps réponse < 4h
- Documentation à jour 100%

---

## 🔧 CONFIGURATION REQUISE

### **Serveur**

```env
# .env
API_RATE_LIMIT=120
API_RATE_LIMIT_CREATE=60
API_TOKEN_EXPIRES_DAYS=null
API_MAX_PACKAGES_PER_REQUEST=100
```

### **Base de Données**

```sql
-- Migration api_tokens
CREATE TABLE api_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) DEFAULT 'API Token',
    token VARCHAR(64) UNIQUE NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token_hash (token_hash),
    INDEX idx_user_id (user_id)
);

-- Table logs API (optionnel)
CREATE TABLE api_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED,
    endpoint VARCHAR(255),
    method VARCHAR(10),
    ip_address VARCHAR(45),
    response_status INT,
    response_time FLOAT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
);
```

---

## 📞 SUPPORT & CONTACT

**Pour le développement** :
- Lead Dev : [Nom]
- Architecte : [Nom]
- QA : [Nom]

**Pour les clients** :
- Email : api-support@al-amena.tn
- Téléphone : +216 XX XXX XXX
- Docs : https://al-amena.tn/docs/api

---

## ✅ CHECKLIST FINALE

### **Avant le Lancement**

- [ ] Backend API testé et fonctionnel
- [ ] UI testée sur desktop et mobile
- [ ] Documentation complète et accessible
- [ ] Exemples de code vérifiés
- [ ] Tests de charge effectués (1000 req/min)
- [ ] Tests de sécurité validés
- [ ] Rate limiting configuré
- [ ] Monitoring et alertes actifs
- [ ] Backup automatique en place
- [ ] Plan de rollback préparé
- [ ] Support formé sur la nouvelle fonctionnalité
- [ ] Communication clients préparée

### **Après le Lancement**

- [ ] Monitoring actif 24/7
- [ ] Support disponible
- [ ] Feedback clients collecté
- [ ] Métriques suivies quotidiennement
- [ ] Bugs critiques résolus en < 4h
- [ ] Documentation mise à jour selon feedback
- [ ] Plan amélioration continue

---

## 🎉 CONCLUSION

Cette spécification technique fournit tous les éléments nécessaires pour développer et déployer la fonctionnalité API Client avec succès.

**Documents de référence** :
1. [PARTIE 1 : Interface Utilisateur](SPEC_API_CLIENT_PARTIE_1_UI.md)
2. [PARTIE 2 : Endpoints API](SPEC_API_CLIENT_PARTIE_2_ENDPOINTS.md)
3. [PARTIE 3 : Sécurité & Documentation](SPEC_API_CLIENT_PARTIE_3_SECURITE_DOC.md)

**Durée estimée** : 12 jours de développement  
**Complexité** : Moyenne  
**Impact** : Fort (augmentation adoption client)

---

**Prêt pour le développement !** 🚀
