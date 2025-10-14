# Livreurs Multi-Gouvernorats - Documentation

## Date: 14 Janvier 2025

## Vue d'ensemble

Cette fonctionnalité permet aux livreurs d'être assignés à **plusieurs gouvernorats** au lieu d'un seul, offrant plus de flexibilité dans la gestion des livraisons.

### Permissions:
- **Chef Dépôt**: Peut créer des livreurs avec plusieurs gouvernorats, mais **uniquement parmi les gouvernorats qui lui sont assignés**
- **Superviseur**: Peut créer des livreurs avec plusieurs gouvernorats parmi **tous les gouvernorats disponibles** dans le système

## Modifications Effectuées

### 1. Migration Base de Données

**Fichier:** `database/migrations/2025_01_14_000000_add_multiple_gouvernorats_to_deliverers.php`

#### Changements:
- Ajout du champ `deliverer_gouvernorats` (JSON) à la table `users`
- Migration automatique des données existantes: `assigned_delegation` → `deliverer_gouvernorats` (array)
- Conservation du champ `assigned_delegation` pour compatibilité ascendante

```sql
ALTER TABLE users ADD COLUMN deliverer_gouvernorats JSON NULL;

UPDATE users 
SET deliverer_gouvernorats = JSON_ARRAY(assigned_delegation)
WHERE role = 'DELIVERER' 
AND assigned_delegation IS NOT NULL
AND deliverer_type = 'DELEGATION';
```

### 2. Modèle User

**Fichier:** `app/Models/User.php`

#### Nouvelles méthodes:

```php
getDelivererGouvernorats()
```
- Retourne un array des gouvernorats assignés au livreur
- Gère la compatibilité avec l'ancien système (assigned_delegation)
- Retourne un array vide si aucun gouvernorat n'est assigné

```php
canDeliverInGouvernorat($gouvernorat)
```
- Vérifie si le livreur peut livrer dans un gouvernorat spécifique
- Les livreurs JOKER peuvent livrer partout
- Vérifie la présence du gouvernorat dans la liste assignée

```php
getDelivererGouvernoratsTextAttribute()
```
- Retourne une chaîne formatée des gouvernorats
- Gère les cas spéciaux (JOKER, TRANSIT)
- Format: "Tunis, Ariana, Ben Arous"

#### Champs ajoutés:
- `deliverer_gouvernorats` dans `$fillable`
- Cast automatique en array dans `$casts`

### 3. Vue de Création de Livreur

**Fichier:** `resources/views/depot-manager/deliverers/create.blade.php`

#### Améliorations:

**Avant:**
- Sélection simple d'un seul gouvernorat (dropdown)

**Après:**
- ✅ Sélection multiple via checkboxes
- ✅ Liste scrollable avec max-height
- ✅ Boutons "Tout sélectionner" / "Tout désélectionner"
- ✅ Validation visuelle (hover effects)
- ✅ Badges colorés pour chaque gouvernorat
- ✅ Message d'aide contextuel

**Interface:**
```html
☐ Tunis
☐ Ariana
☐ Ben Arous
☐ Manouba
...

[✓ Tout sélectionner] | [✗ Tout désélectionner]
```

### 4. Vue d'Édition de Livreur

**Fichier:** `resources/views/depot-manager/deliverers/edit.blade.php`

#### Améliorations:
- Interface identique à la création
- Pré-sélection des gouvernorats actuels du livreur
- Gestion de `old()` pour conserver les sélections en cas d'erreur
- Utilisation de `getDelivererGouvernorats()` pour charger les données

### 5. Contrôleur Depot Manager

**Fichier:** `app/Http/Controllers/DepotManager/DepotManagerDelivererController.php`

#### Méthode `store()`:

**Validation:**
```php
'deliverer_gouvernorats' => 'required|array|min:1',
'deliverer_gouvernorats.*' => 'required|string|in:' . implode(',', $user->assigned_gouvernorats_array),
```

**Logique:**
- Accepte un array de gouvernorats
- Valide que chaque gouvernorat est dans la liste autorisée du chef dépôt
- Stocke le premier gouvernorat dans `assigned_delegation` (compatibilité)
- Stocke tous les gouvernorats dans `deliverer_gouvernorats`

#### Méthode `update()`:
- Même logique que `store()`
- Permet de modifier la liste des gouvernorats
- Validation stricte des gouvernorats autorisés

### 6. Vues d'Affichage

#### Vue Détails (`show.blade.php`):

**Avant:**
```
📍 Tunis
```

**Après:**
```
📍 Gouvernorats:
   [Tunis] [Ariana] [Ben Arous]
```

- Affichage sous forme de badges
- Badges bleus avec texte blanc
- Disposition en flex-wrap pour s'adapter à l'espace

#### Vue Liste (`index.blade.php`):

**Colonne Localisation:**
- Affichage de tous les gouvernorats en badges
- Type de livreur en dessous (Délégation fixe, JOKER, etc.)
- Compact et lisible

### 7. JavaScript Helper Functions

**Fonctions ajoutées:**

```javascript
function selectAllGouvernorats()
```
- Coche toutes les checkboxes des gouvernorats

```javascript
function deselectAllGouvernorats()
```
- Décoche toutes les checkboxes des gouvernorats

## Compatibilité

### Rétrocompatibilité:
✅ **Ancien système conservé:**
- Le champ `assigned_delegation` existe toujours
- Contient le premier gouvernorat de la liste
- Les anciennes requêtes fonctionnent toujours

✅ **Migration automatique:**
- Les livreurs existants sont automatiquement migrés
- Leur `assigned_delegation` est converti en array

✅ **Fallback intelligent:**
- Si `deliverer_gouvernorats` est vide, utilise `assigned_delegation`
- Garantit qu'aucun livreur ne perd son affectation

### Types de livreurs:

**DELEGATION (Délégation fixe):**
- Peut avoir 1 ou plusieurs gouvernorats
- Limité aux gouvernorats du chef dépôt

**JOKER:**
- Peut livrer partout
- `canDeliverInGouvernorat()` retourne toujours `true`

**TRANSIT:**
- Gère uniquement les changements
- Pas de gouvernorats spécifiques

## Flux de Travail

### Création d'un livreur:
1. Chef dépôt accède à "Nouveau livreur"
2. Remplit les informations personnelles
3. **Sélectionne un ou plusieurs gouvernorats** (minimum 1)
4. Peut utiliser "Tout sélectionner" pour tous les gouvernorats
5. Soumet le formulaire
6. Le livreur est créé avec tous les gouvernorats sélectionnés

### Modification d'un livreur:
1. Chef dépôt accède à l'édition
2. Voit les gouvernorats actuellement sélectionnés
3. Peut ajouter ou retirer des gouvernorats
4. Doit avoir au moins 1 gouvernorat sélectionné
5. Sauvegarde les modifications

### Affichage:
1. Liste: badges compacts pour chaque gouvernorat
2. Détails: section dédiée avec tous les gouvernorats
3. Statistiques: prend en compte tous les gouvernorats

## Validation

### Règles de validation:

**Création:**
```php
'deliverer_gouvernorats' => 'required|array|min:1'
'deliverer_gouvernorats.*' => 'required|string|in:Tunis,Ariana,...'
```

**Contraintes:**
- ✅ Au moins 1 gouvernorat requis
- ✅ Chaque gouvernorat doit être dans la liste autorisée du chef dépôt
- ✅ Pas de doublons (géré par les checkboxes)
- ✅ Format array obligatoire

## Tests Recommandés

### À tester:
1. ✅ Créer un livreur avec 1 gouvernorat
2. ✅ Créer un livreur avec plusieurs gouvernorats
3. ✅ Créer un livreur avec tous les gouvernorats (Tout sélectionner)
4. ✅ Modifier les gouvernorats d'un livreur existant
5. ✅ Vérifier l'affichage dans la liste
6. ✅ Vérifier l'affichage dans les détails
7. ✅ Tester la validation (aucun gouvernorat sélectionné)
8. ✅ Vérifier la compatibilité avec les livreurs existants
9. ✅ Tester avec un livreur JOKER
10. ✅ Vérifier que `canDeliverInGouvernorat()` fonctionne correctement

## Migration à Exécuter

```bash
php artisan migrate
```

Cette commande va:
1. Ajouter le champ `deliverer_gouvernorats` à la table `users`
2. Migrer automatiquement les données existantes
3. Conserver le champ `assigned_delegation` pour compatibilité

## Fichiers Modifiés/Créés

### Créés:
- `database/migrations/2025_01_14_000000_add_multiple_gouvernorats_to_deliverers.php`
- `LIVREURS_MULTI_GOUVERNORATS.md` (cette documentation)

### Modifiés:
- `app/Models/User.php`
  - Ajout de `deliverer_gouvernorats` dans `$fillable`
  - Ajout du cast array
  - Nouvelles méthodes: `getDelivererGouvernorats()`, `canDeliverInGouvernorat()`, `getDelivererGouvernoratsTextAttribute()`

- **Chef Dépôt:**
  - `app/Http/Controllers/DepotManager/DepotManagerDelivererController.php`
    - Méthode `store()`: gestion array de gouvernorats (limité aux gouvernorats du chef dépôt)
    - Méthode `update()`: gestion array de gouvernorats (limité aux gouvernorats du chef dépôt)

  - `resources/views/depot-manager/deliverers/create.blade.php`
    - Interface de sélection multiple
    - Boutons helper
    - JavaScript functions

  - `resources/views/depot-manager/deliverers/edit.blade.php`
    - Interface de sélection multiple
    - Pré-sélection des gouvernorats actuels
    - JavaScript functions

  - `resources/views/depot-manager/deliverers/show.blade.php`
    - Affichage multi-badges des gouvernorats

  - `resources/views/depot-manager/deliverers/index.blade.php`
    - Affichage multi-badges dans la liste

- **Superviseur:**
  - `app/Http/Controllers/Supervisor/UserController.php`
    - Méthode `store()`: gestion array de gouvernorats pour livreurs (tous les gouvernorats disponibles)
    - Validation spéciale pour livreurs locaux

  - `resources/views/supervisor/users/create.blade.php`
    - Section dédiée pour sélection multiple de gouvernorats livreur
    - Validation JavaScript
    - Boutons helper (Tout sélectionner/désélectionner)

## Avantages

### Pour les chefs dépôt:
- ✅ Plus de flexibilité dans l'affectation des livreurs
- ✅ Un livreur peut couvrir plusieurs zones
- ✅ Meilleure optimisation des ressources
- ✅ Interface intuitive avec sélection multiple

### Pour les livreurs:
- ✅ Possibilité de travailler dans plusieurs gouvernorats
- ✅ Plus d'opportunités de livraison
- ✅ Flexibilité géographique

### Technique:
- ✅ Rétrocompatibilité totale
- ✅ Migration automatique des données
- ✅ Code propre et maintenable
- ✅ Validation robuste

## Notes Importantes

- Le champ `assigned_delegation` est conservé pour compatibilité
- Il contient toujours le **premier gouvernorat** de la liste
- Les anciennes requêtes utilisant `assigned_delegation` fonctionnent toujours
- La méthode `getDelivererGouvernorats()` gère automatiquement le fallback
- Les livreurs JOKER ne sont pas affectés par ce changement
- Les livreurs TRANSIT non plus

### Restrictions par Rôle:

**Chef Dépôt:**
- ✅ Peut créer des livreurs avec plusieurs gouvernorats
- ⚠️ **Limité aux gouvernorats qui lui sont assignés**
- ❌ Ne peut pas assigner un gouvernorat qu'il ne gère pas
- Validation: `in:` liste des gouvernorats du chef dépôt

**Superviseur:**
- ✅ Peut créer des livreurs avec plusieurs gouvernorats
- ✅ **Accès à tous les gouvernorats du système**
- ✅ Aucune restriction géographique
- Validation: `in:` tous les gouvernorats disponibles

## Support

Pour toute question ou problème:
1. Vérifier que la migration a été exécutée
2. Vérifier que les gouvernorats du chef dépôt sont bien définis
3. Consulter les logs Laravel en cas d'erreur
4. Tester avec un nouveau livreur d'abord
