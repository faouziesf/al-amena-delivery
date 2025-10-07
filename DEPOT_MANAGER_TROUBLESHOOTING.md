# Guide de Dépannage - Création Chef Dépôt

## Problème
La création de compte Chef Dépôt par le superviseur ne fonctionne pas.

## Corrections Effectuées

### 1. Affichage des Erreurs ✅
- Ajout d'un bloc d'affichage des erreurs de validation en haut du formulaire
- Messages d'erreur personnalisés en français pour les gouvernorats

### 2. Interface Améliorée ✅
- Indication claire qu'il faut sélectionner au moins 1 gouvernorat
- Checkboxes avec hover et style amélioré
- Message d'aide expliquant le rôle des gouvernorats

### 3. Validation Côté Client ✅
- Vérification JavaScript avant soumission
- Alert si aucun gouvernorat n'est coché
- Empêche la soumission du formulaire si invalide

### 4. Logs de Débogage ✅
- Ajout de logs dans `UserController@store` pour le rôle DEPOT_MANAGER
- Permet de voir exactement ce qui est reçu par le serveur

## Tests à Effectuer

### Test 1 : Vérifier l'affichage du formulaire

1. Connectez-vous en tant que SUPERVISEUR
2. Allez sur `/supervisor/users/create`
3. Sélectionnez "Chef Dépôt" à l'étape 1
4. Remplissez les informations à l'étape 2 :
   - Nom complet
   - Email
   - Téléphone
   - Mot de passe (min 8 caractères)
5. Cliquez sur "Continuer"
6. À l'étape 3, vérifiez que la section "Gouvernorats assignés" s'affiche

### Test 2 : Tester la validation

1. À l'étape 3, NE COCHEZ AUCUN gouvernorat
2. Cliquez sur "Créer l'Utilisateur"
3. ✅ Vous devriez voir une alerte JavaScript : "⚠️ Veuillez sélectionner au moins un gouvernorat"

### Test 3 : Créer un chef dépôt

1. À l'étape 3, cochez AU MOINS UN gouvernorat (ex: Tunis)
2. Vérifiez le statut (ACTIF ou EN ATTENTE)
3. Cliquez sur "Créer l'Utilisateur"
4. ✅ Vous devriez être redirigé vers la liste des utilisateurs avec un message de succès

### Test 4 : Vérifier les logs

Si la création échoue, vérifiez les logs Laravel :

```bash
# Ouvrir le terminal dans le dossier du projet
cd c:\Users\DELL\Documents\GitHub\al-amena-delivery

# Voir les logs en temps réel
php artisan tail

# Ou consulter le fichier directement
type storage\logs\laravel.log
```

Recherchez la ligne : `Création Chef Dépôt - Données reçues:`

### Test 5 : Créer directement via Tinker

Pour tester si le problème vient du formulaire ou du modèle :

```bash
php artisan tinker
```

Puis exécutez :

```php
$user = \App\Models\User::create([
    'name' => 'Chef Depot Test',
    'email' => 'depot.test@example.com',
    'phone' => '+21698765432',
    'password' => \Hash::make('password123'),
    'role' => 'DEPOT_MANAGER',
    'account_status' => 'ACTIVE',
    'assigned_gouvernorats' => json_encode(['Tunis', 'Ariana', 'Ben Arous']),
    'is_depot_manager' => true,
    'verified_at' => now(),
    'email_verified_at' => now(),
]);

echo "Utilisateur créé : " . $user->id . "\n";
```

Si cela fonctionne, le problème vient du formulaire. Sinon, c'est la base de données ou le modèle.

## Problèmes Possibles et Solutions

### Problème A : Erreur "assigned_gouvernorats is required"

**Cause** : Les checkboxes ne sont pas cochées ou ne sont pas soumises

**Solution** :
1. Vérifiez que vous avez bien coché au moins un gouvernorat
2. Vérifiez dans les outils de développement du navigateur (F12) → Onglet "Network" → Regardez la requête POST et ses données
3. Les données doivent contenir : `assigned_gouvernorats[0]=Tunis` (par exemple)

### Problème B : Erreur SQL ou colonne manquante

**Cause** : La colonne `assigned_gouvernorats` ou `is_depot_manager` n'existe pas

**Solution** :
```bash
php artisan migrate:fresh --seed
```

⚠️ **ATTENTION** : Cela supprimera toutes les données ! Utilisez en développement uniquement.

Ou vérifiez la structure de la table :

```bash
php artisan tinker
```

```php
\DB::select('DESCRIBE users');
```

### Problème C : Les gouvernorats ne s'affichent pas

**Cause** : La variable `$gouvernorats` n'est pas passée à la vue

**Solution** : Vérifiez dans `UserController@create` :

```php
public function create()
{
    $delegations = User::getAvailableDelegations();
    $delivererTypes = User::getDelivererTypes();
    $gouvernorats = User::getAvailableDelegations(); // DOIT être présent
    return view('supervisor.users.create', compact('delegations', 'delivererTypes', 'gouvernorats'));
}
```

### Problème D : Erreur "unique:users" sur email/phone

**Cause** : Un utilisateur avec cet email ou téléphone existe déjà

**Solution** : Utilisez un autre email/téléphone ou supprimez l'ancien utilisateur :

```bash
php artisan tinker
```

```php
\App\Models\User::where('email', 'depot.test@example.com')->delete();
```

## Vérification Finale

Après la création réussie d'un chef dépôt :

1. Allez sur `/supervisor/users`
2. Recherchez l'utilisateur créé
3. Cliquez sur son nom pour voir les détails
4. Vérifiez que :
   - ✅ Rôle = DEPOT_MANAGER
   - ✅ Les gouvernorats assignés sont bien affichés

## Contact

Si le problème persiste après tous ces tests :

1. Copiez les logs d'erreur complets
2. Faites une capture d'écran du formulaire à l'étape 3
3. Notez exactement à quelle étape l'erreur se produit
4. Partagez ces informations pour un diagnostic plus approfondi

---

**Date de création** : 2025-10-06  
**Dernière mise à jour** : 2025-10-06
