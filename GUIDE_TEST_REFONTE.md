# 🧪 Guide de Test - Refonte Layout Client & Index

## 🚀 Démarrage Rapide

### 1. Lancer le serveur
```bash
cd C:\Users\DELL\OneDrive\Documents\GitHub\al-amena-delivery
php artisan serve
```

### 2. Accéder à l'application
```
http://localhost:8000
```

### 3. Se connecter comme client
- Email: [votre email client]
- Mot de passe: [votre mot de passe]

---

## ✅ Checklist de Test Mobile

### Header Mobile (< 1024px)

- [ ] **Header fixe en haut**
  - Logo "Al-Amena" visible
  - Nom d'utilisateur affiché
  - Bouton menu (☰) à gauche
  - Solde wallet à droite
  - Hauteur: 56px

- [ ] **Sidebar Mobile**
  - [ ] Cliquer sur le bouton menu (☰)
  - [ ] Sidebar glisse depuis la gauche
  - [ ] Overlay semi-transparent visible
  - [ ] Avatar avec initiale
  - [ ] Nom et email affichés
  - [ ] Menu items cliquables
  - [ ] Bouton fermer (X) fonctionne
  - [ ] Cliquer sur overlay ferme le sidebar
  - [ ] Bouton déconnexion en bas

- [ ] **Bottom Navigation**
  - [ ] 5 icônes visibles
  - [ ] Accueil, Colis, Nouveau (FAB), Collectes, Wallet
  - [ ] FAB central surélevé
  - [ ] Icône active en bleu
  - [ ] Touch feedback sur tap
  - [ ] Navigation fonctionne

### Page Index Colis Mobile

- [ ] **Header Actions**
  - [ ] Titre "📦 Mes Colis"
  - [ ] Bouton "Filtres" à droite
  - [ ] Boutons "Nouveau" et "Rapide"
  - [ ] Boutons pleine largeur

- [ ] **Filtres**
  - [ ] Cliquer sur "Filtres" les affiche
  - [ ] 4 champs: Statut, Délégation, Recherche, Bouton
  - [ ] Sélecteurs fonctionnels
  - [ ] Checkbox "Tout sélectionner"
  - [ ] Compteur de sélection
  - [ ] Boutons "Imprimer" et "Exporter"

- [ ] **Liste des Colis (Cartes)**
  - [ ] Cartes empilées verticalement
  - [ ] Checkbox à gauche
  - [ ] Code colis cliquable
  - [ ] Badge de statut coloré
  - [ ] Nom destinataire
  - [ ] Délégation
  - [ ] Date et montant COD
  - [ ] Menu actions (⋮) à droite
  - [ ] Touch feedback sur tap

- [ ] **Menu Actions (⋮)**
  - [ ] Voir détails
  - [ ] Suivre colis
  - [ ] Imprimer
  - [ ] Modifier (si possible)
  - [ ] Supprimer (si possible)
  - [ ] Créer réclamation

- [ ] **Sélection Multiple**
  - [ ] Cocher plusieurs colis
  - [ ] Compteur s'incrémente
  - [ ] Bouton "Imprimer" activé
  - [ ] Bouton "Exporter" activé
  - [ ] Tout sélectionner fonctionne
  - [ ] Tout désélectionner fonctionne

---

## ✅ Checklist de Test Desktop (≥ 1024px)

### Layout Desktop

- [ ] **Sidebar Fixe**
  - [ ] Sidebar visible à gauche (280px)
  - [ ] Header gradient violet
  - [ ] Avatar avec initiale
  - [ ] Nom et email
  - [ ] Card solde wallet
  - [ ] Menu items
  - [ ] Item actif en bleu
  - [ ] Hover effect
  - [ ] Bouton déconnexion en bas

- [ ] **Contenu Principal**
  - [ ] Padding-left: 280px
  - [ ] Pas de header mobile
  - [ ] Pas de bottom navigation
  - [ ] Pleine largeur disponible

### Page Index Colis Desktop

- [ ] **Header**
  - [ ] Titre et description
  - [ ] Boutons "Nouveau Colis" et "Création Rapide"
  - [ ] Alignés à droite

- [ ] **Filtres**
  - [ ] Toujours visibles (pas de bouton)
  - [ ] 4 colonnes: Statut, Délégation, Recherche, Filtrer
  - [ ] Actions groupées en dessous
  - [ ] Checkbox "Tout sélectionner"
  - [ ] Compteur de sélection
  - [ ] Boutons "Imprimer" et "Exporter"

- [ ] **Tableau**
  - [ ] 8 colonnes visibles
  - [ ] Header avec titres
  - [ ] Lignes alternées (hover)
  - [ ] Checkbox dans chaque ligne
  - [ ] Code colis cliquable
  - [ ] Badge de statut
  - [ ] Menu actions (⋮) à droite

- [ ] **Menu Actions Desktop**
  - [ ] Dropdown s'ouvre en dessous
  - [ ] Toutes les actions visibles
  - [ ] Icônes + texte
  - [ ] Hover effect
  - [ ] Ferme au clic extérieur

---

## 🎯 Tests Fonctionnels

### Navigation

- [ ] **Depuis Dashboard**
  - [ ] Cliquer sur "Mes Colis" dans le menu
  - [ ] Page index s'affiche
  - [ ] Item menu actif en bleu

- [ ] **Bottom Nav (Mobile)**
  - [ ] Cliquer sur icône "Colis"
  - [ ] Page index s'affiche
  - [ ] Icône active en bleu

- [ ] **Créer un colis**
  - [ ] Cliquer sur "Nouveau Colis"
  - [ ] Formulaire de création s'affiche
  - [ ] Cliquer sur "Création Rapide"
  - [ ] Formulaire rapide s'affiche

### Filtres

- [ ] **Filtrer par statut**
  - [ ] Sélectionner "Livré"
  - [ ] Cliquer "Filtrer"
  - [ ] Seuls les colis livrés affichés
  - [ ] URL contient ?status=DELIVERED

- [ ] **Filtrer par délégation**
  - [ ] Sélectionner une délégation
  - [ ] Cliquer "Filtrer"
  - [ ] Seuls les colis de cette délégation affichés

- [ ] **Rechercher par code**
  - [ ] Entrer un code colis
  - [ ] Cliquer "Filtrer"
  - [ ] Colis correspondant affiché

- [ ] **Combiner les filtres**
  - [ ] Statut + Délégation + Recherche
  - [ ] Résultats filtrés correctement

### Sélection Multiple

- [ ] **Sélectionner 1 colis**
  - [ ] Cocher un colis
  - [ ] Compteur affiche "1 sélectionné(s)"
  - [ ] Boutons activés

- [ ] **Sélectionner plusieurs**
  - [ ] Cocher 3 colis
  - [ ] Compteur affiche "3 sélectionné(s)"

- [ ] **Tout sélectionner**
  - [ ] Cocher "Tout sélectionner"
  - [ ] Tous les colis cochés
  - [ ] Compteur correct

- [ ] **Tout désélectionner**
  - [ ] Décocher "Tout sélectionner"
  - [ ] Tous les colis décochés
  - [ ] Compteur à 0
  - [ ] Boutons désactivés

### Actions Groupées

- [ ] **Imprimer Multiple**
  - [ ] Sélectionner 3 colis
  - [ ] Cliquer "Imprimer"
  - [ ] Nouvelle fenêtre s'ouvre
  - [ ] PDF généré avec 3 étiquettes

- [ ] **Limite 50 colis**
  - [ ] Sélectionner > 50 colis
  - [ ] Cliquer "Imprimer"
  - [ ] Message d'erreur affiché

- [ ] **Exporter**
  - [ ] Sélectionner des colis
  - [ ] Cliquer "Exporter"
  - [ ] Message "en développement" (temporaire)

### Actions Individuelles

- [ ] **Voir détails**
  - [ ] Cliquer sur code colis
  - [ ] Page détails s'affiche
  - [ ] Ou cliquer sur "Voir détails" dans menu

- [ ] **Suivre colis**
  - [ ] Cliquer "Suivre colis"
  - [ ] Page tracking public s'ouvre
  - [ ] Nouvelle fenêtre

- [ ] **Imprimer étiquette**
  - [ ] Cliquer "Imprimer"
  - [ ] Nouvelle fenêtre s'ouvre
  - [ ] PDF étiquette généré

- [ ] **Modifier colis**
  - [ ] Si colis modifiable
  - [ ] Cliquer "Modifier"
  - [ ] Formulaire édition s'affiche

- [ ] **Supprimer colis**
  - [ ] Si colis supprimable
  - [ ] Cliquer "Supprimer"
  - [ ] Confirmation demandée
  - [ ] Colis supprimé après confirmation

- [ ] **Créer réclamation**
  - [ ] Cliquer "Créer réclamation"
  - [ ] Formulaire réclamation s'affiche

### Pagination

- [ ] **Navigation pages**
  - [ ] Liens pagination visibles
  - [ ] Cliquer "Page 2"
  - [ ] Page 2 affichée
  - [ ] Filtres conservés dans URL

- [ ] **Retour page 1**
  - [ ] Cliquer "Page 1"
  - [ ] Retour à la première page

---

## 🎨 Tests Visuels

### Responsive

- [ ] **320px (iPhone SE)**
  - [ ] Pas de scroll horizontal
  - [ ] Texte lisible
  - [ ] Boutons accessibles
  - [ ] Touch targets ≥ 44px

- [ ] **375px (iPhone 12)**
  - [ ] Layout correct
  - [ ] Cartes bien espacées
  - [ ] Bottom nav visible

- [ ] **390px (iPhone 14)**
  - [ ] Idem 375px
  - [ ] Plus d'espace

- [ ] **768px (iPad)**
  - [ ] Layout mobile
  - [ ] Cartes plus larges
  - [ ] Lisibilité améliorée

- [ ] **1024px (Desktop)**
  - [ ] Bascule vers layout desktop
  - [ ] Sidebar visible
  - [ ] Tableau affiché
  - [ ] Pas de bottom nav

- [ ] **1920px (Full HD)**
  - [ ] Contenu centré (max-width)
  - [ ] Pas d'étirement excessif
  - [ ] Sidebar fixe

### Animations

- [ ] **Sidebar Mobile**
  - [ ] Slide-in fluide (300ms)
  - [ ] Slide-out fluide (200ms)
  - [ ] Overlay fade

- [ ] **Touch Feedback**
  - [ ] Boutons scale down au tap
  - [ ] Retour à la normale
  - [ ] Pas de lag

- [ ] **Filtres Toggle**
  - [ ] Apparition fluide
  - [ ] Disparition fluide
  - [ ] Pas de saut

- [ ] **Toast Notifications**
  - [ ] Slide-up depuis le bas
  - [ ] Disparition après 4s
  - [ ] Bouton fermer fonctionne

### Couleurs & Contraste

- [ ] **Texte lisible**
  - [ ] Contraste suffisant
  - [ ] Pas de gris trop clair

- [ ] **Badges statut**
  - [ ] Couleurs distinctes
  - [ ] Texte lisible sur fond

- [ ] **Boutons**
  - [ ] Couleurs vives
  - [ ] Hover visible
  - [ ] Active state visible

---

## 🐛 Tests d'Erreurs

### Cas limites

- [ ] **Aucun colis**
  - [ ] Message "Aucun colis trouvé"
  - [ ] Icône 📭
  - [ ] Bouton "Créer un colis"

- [ ] **Filtres sans résultat**
  - [ ] Message approprié
  - [ ] Possibilité de réinitialiser

- [ ] **Sélection sans colis**
  - [ ] Boutons désactivés
  - [ ] Message si clic

- [ ] **Erreur serveur**
  - [ ] Toast erreur affiché
  - [ ] Message clair

### Performance

- [ ] **Chargement initial**
  - [ ] < 2 secondes
  - [ ] Pas de flash

- [ ] **Filtrage**
  - [ ] Réponse rapide
  - [ ] Pas de freeze

- [ ] **Sélection multiple**
  - [ ] Pas de lag
  - [ ] Compteur instantané

---

## 📱 Tests Devices Réels

### iOS

- [ ] **iPhone SE (2020)**
- [ ] **iPhone 12/13**
- [ ] **iPhone 14 Pro**
- [ ] **iPad (9e gen)**
- [ ] **iPad Pro**

### Android

- [ ] **Samsung Galaxy S21**
- [ ] **Google Pixel 6**
- [ ] **OnePlus 9**
- [ ] **Tablet Android**

### Navigateurs

- [ ] **Safari iOS**
- [ ] **Chrome Android**
- [ ] **Chrome Desktop**
- [ ] **Firefox Desktop**
- [ ] **Edge Desktop**

---

## 🔍 Tests Accessibilité

- [ ] **Navigation clavier**
  - [ ] Tab entre les éléments
  - [ ] Enter pour activer
  - [ ] Esc pour fermer

- [ ] **Screen reader**
  - [ ] Labels appropriés
  - [ ] ARIA attributes
  - [ ] Ordre logique

- [ ] **Contraste**
  - [ ] WCAG AA minimum
  - [ ] Texte lisible

- [ ] **Touch targets**
  - [ ] Minimum 44x44px
  - [ ] Espacement suffisant

---

## ✅ Validation Finale

### Avant mise en production

- [ ] Tous les tests mobile passés
- [ ] Tous les tests desktop passés
- [ ] Tous les tests fonctionnels passés
- [ ] Tests sur devices réels OK
- [ ] Performance acceptable
- [ ] Pas d'erreurs console
- [ ] Pas de warnings
- [ ] Documentation à jour

### Commandes finales

```bash
# Vider les caches
php artisan view:clear
php artisan cache:clear
php artisan config:clear

# Optimiser
php artisan optimize

# Vérifier les routes
php artisan route:list --name=client
```

---

## 📝 Rapport de Test

### Template

```
Date: __________
Testeur: __________
Device: __________
Navigateur: __________

Tests Mobile: ☐ OK ☐ KO
Tests Desktop: ☐ OK ☐ KO
Tests Fonctionnels: ☐ OK ☐ KO

Bugs trouvés:
1. __________
2. __________
3. __________

Améliorations suggérées:
1. __________
2. __________
3. __________

Validation: ☐ Approuvé ☐ À corriger
```

---

**Bonne chance pour les tests! 🚀**
