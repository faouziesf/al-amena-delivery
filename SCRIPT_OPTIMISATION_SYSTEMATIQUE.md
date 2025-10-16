# 🚀 Script d'Optimisation Systématique

## Pattern à Appliquer sur Toutes les Vues

### 1. Headers
```blade
<!-- AVANT -->
<h1 class="text-3xl lg:text-4xl font-bold mb-2">
<p class="text-gray-600">

<!-- APRÈS -->
<h1 class="text-xl sm:text-2xl font-bold mb-1">
<p class="text-sm text-gray-600">
```

### 2. Espacements
```blade
<!-- AVANT -->
mb-8 → mb-4 sm:mb-6
mb-6 → mb-3 sm:mb-4
p-6 → p-3 sm:p-4
gap-6 → gap-3 sm:gap-4
space-y-6 → space-y-3 sm:space-y-4

<!-- APRÈS -->
Appliquer systématiquement -50%
```

### 3. Cartes
```blade
<!-- AVANT -->
rounded-2xl → rounded-xl
shadow-lg → shadow-sm
p-6 → p-3 sm:p-4
hover:shadow-xl → Supprimer
transform hover:scale-105 → Supprimer
hover:-translate-y-1 → Supprimer
```

### 4. Boutons
```blade
<!-- AVANT -->
px-6 py-3 → px-3 sm:px-4 py-2
rounded-2xl → rounded-lg
shadow-lg → shadow-md
w-6 h-6 → w-4 h-4 (icônes)
```

### 5. Grilles
```blade
<!-- AVANT -->
grid-cols-1 sm:grid-cols-2 → grid-cols-2
grid-cols-1 md:grid-cols-3 → grid-cols-1 sm:grid-cols-3
gap-6 → gap-3 sm:gap-4
```

### 6. Formulaires
```blade
<!-- AVANT -->
rounded-2xl → rounded-lg
px-4 py-3 → px-3 py-2
text-base → text-sm
```

---

## Vues à Optimiser (38)

### Wallet (6)
- [ ] transactions.blade.php
- [ ] transaction-details.blade.php
- [ ] topup.blade.php
- [ ] topup-requests.blade.php
- [ ] topup-request-show.blade.php
- [ ] withdrawal.blade.php

### Pickup Addresses (3)
- [ ] index.blade.php
- [ ] create.blade.php
- [ ] edit.blade.php

### Bank Accounts (4)
- [ ] index.blade.php
- [ ] create.blade.php
- [ ] edit.blade.php
- [ ] show.blade.php

### Withdrawals (2)
- [ ] index.blade.php
- [ ] show.blade.php

### Profile (2)
- [ ] index.blade.php
- [ ] edit.blade.php

### Tickets (3)
- [ ] index.blade.php
- [ ] create.blade.php
- [ ] show.blade.php

### Returns (3)
- [ ] pending.blade.php
- [ ] show.blade.php
- [ ] return-package-details.blade.php

### Packages (6)
- [ ] index.blade.php (déjà fait partiellement)
- [ ] create.blade.php
- [ ] create-fast.blade.php
- [ ] edit.blade.php
- [ ] show.blade.php
- [ ] filtered.blade.php

### Manifests (5)
- [ ] index.blade.php
- [ ] create.blade.php
- [ ] show.blade.php
- [ ] print.blade.php
- [ ] pdf.blade.php

### Notifications (2)
- [ ] index.blade.php
- [ ] settings.blade.php

### Pickup Requests (3)
- [ ] index.blade.php
- [ ] create.blade.php
- [ ] show.blade.php

---

## Commandes de Remplacement Systématique

### Rechercher/Remplacer Global
```
text-3xl lg:text-4xl → text-xl sm:text-2xl
text-2xl md:text-3xl → text-lg sm:text-xl
text-xl lg:text-2xl → text-base sm:text-lg
text-lg → text-sm sm:text-base
text-base → text-sm

mb-8 → mb-4 sm:mb-6
mb-6 → mb-3 sm:mb-4
p-6 → p-3 sm:p-4
gap-6 → gap-3 sm:gap-4

rounded-2xl → rounded-xl
shadow-lg → shadow-sm
px-6 py-3 → px-3 sm:px-4 py-2

w-6 h-6 → w-5 h-5 (stats)
w-6 h-6 → w-4 h-4 (listes)
p-4 → p-2 (icônes)

grid-cols-1 sm:grid-cols-2 → grid-cols-2
grid-cols-1 md:grid-cols-3 → grid-cols-1 sm:grid-cols-3
```

---

**Application**: Appliquer ces remplacements systématiquement sur chaque vue
