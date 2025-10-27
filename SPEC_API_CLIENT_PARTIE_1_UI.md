# 🔐 SPÉCIFICATION API CLIENT - PARTIE 1 : INTERFACE UTILISATEUR

**Version** : 1.0  
**Date** : 24 Octobre 2025

---

## 📋 NAVIGATION ET EMPLACEMENT

### **1.1. Emplacement dans l'Application**

```
Dashboard Client
└── Paramètres du Compte
    ├── Informations Personnelles
    ├── Adresses de Ramassage
    ├── 🆕 API & Intégrations ← NOUVELLE SECTION
    ├── Préférences de Notification
    └── Sécurité & Connexion
```

**URL** : `/client/settings/api`

**Accès** : Clients avec statut `VERIFIED` uniquement

---

## 🎨 COMPOSANTS DE L'INTERFACE

### **1. Section Info API**

```html
<div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-lg mb-6">
    <h3 class="text-lg font-semibold text-blue-900 mb-2">
        📘 À propos de l'API
    </h3>
    <p class="text-blue-800 mb-4">
        Automatisez la création de colis et suivez vos livraisons 
        directement depuis votre système e-commerce ou ERP.
    </p>
    <ul class="list-disc list-inside text-blue-700 mb-4">
        <li>Créer des colis automatiquement</li>
        <li>Exporter et suivre vos colis en temps réel</li>
        <li>Synchroniser les statuts de livraison</li>
    </ul>
    <a href="{{ route('client.api.docs') }}" class="btn-primary">
        📖 Documentation Complète
    </a>
</div>
```

---

### **2. Gestion du Token**

#### **État 1 : Aucun Token**

```html
<div class="card">
    <h3 class="text-lg font-semibold mb-4">🔑 Votre Token API</h3>
    <p class="text-gray-600 mb-4">
        Vous n'avez pas encore de token. Générez-en un pour commencer.
    </p>
    <button onclick="showGenerateModal()" class="btn-success">
        ✨ Générer Mon Token
    </button>
</div>
```

#### **État 2 : Token Actif**

```html
<div class="card">
    <h3 class="text-lg font-semibold mb-4">🔑 Votre Token API</h3>
    
    <!-- Statut -->
    <div class="flex items-center mb-3">
        <span class="w-3 h-3 bg-green-500 rounded-full mr-2 animate-pulse"></span>
        <span class="font-medium text-green-700">Actif</span>
    </div>
    
    <!-- Métadonnées -->
    <div class="text-sm text-gray-600 mb-4">
        <p>Créé le : <strong>{{ $token->created_at }}</strong></p>
        <p>Dernière utilisation : <strong>{{ $token->last_used_at }}</strong></p>
    </div>
    
    <!-- Token Display -->
    <div class="mb-4">
        <label class="block text-sm font-medium mb-2">Token :</label>
        <div class="relative">
            <input type="password" 
                   id="apiToken" 
                   value="{{ $token->token }}"
                   readonly
                   class="input-field font-mono">
            <button onclick="toggleVisibility()" 
                    class="absolute right-3 top-3">
                👁️
            </button>
        </div>
    </div>
    
    <!-- Actions -->
    <div class="flex gap-3">
        <button onclick="copyToken()" class="btn-primary flex-1">
            📋 Copier
        </button>
        <button onclick="showRegenerateModal()" class="btn-warning flex-1">
            🔄 Régénérer
        </button>
        <button onclick="showDeleteModal()" class="btn-danger">
            🗑️
        </button>
    </div>
</div>
```

---

### **3. Modales**

#### **Modale : Génération**

```html
<div class="modal" id="generateModal">
    <div class="modal-content">
        <h3 class="text-xl font-bold mb-4">✨ Générer un Token</h3>
        <p class="mb-4">
            Un nouveau token sera créé pour votre compte.
        </p>
        <div class="alert alert-warning mb-4">
            ⚠️ Copiez-le immédiatement. Il ne sera plus affiché.
        </div>
        <div class="flex gap-3">
            <button onclick="closeModal()" class="btn-secondary flex-1">
                Annuler
            </button>
            <button onclick="confirmGenerate()" class="btn-success flex-1">
                Générer
            </button>
        </div>
    </div>
</div>
```

#### **Modale : Régénération**

```html
<div class="modal" id="regenerateModal">
    <div class="modal-content">
        <h3 class="text-xl font-bold mb-4">🔄 Régénérer le Token</h3>
        <p class="mb-4">
            Cette action créera un nouveau token et invalidera l'ancien.
        </p>
        <div class="alert alert-danger mb-4">
            <p class="font-bold mb-2">⚠️ ATTENTION</p>
            <ul class="list-disc list-inside text-sm">
                <li>L'ancien token cessera de fonctionner</li>
                <li>Toutes vos intégrations seront impactées</li>
                <li>Vous devrez mettre à jour partout</li>
            </ul>
        </div>
        <label class="flex items-center mb-4">
            <input type="checkbox" id="confirmCheck" class="mr-2">
            <span class="text-sm">Je comprends les conséquences</span>
        </label>
        <div class="flex gap-3">
            <button onclick="closeModal()" class="btn-secondary flex-1">
                Annuler
            </button>
            <button onclick="confirmRegenerate()" 
                    id="confirmBtn"
                    disabled
                    class="btn-warning flex-1">
                Régénérer
            </button>
        </div>
    </div>
</div>
```

#### **Modale : Succès**

```html
<div class="modal" id="successModal">
    <div class="modal-content">
        <div class="text-center mb-4">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                ✅
            </div>
            <h3 class="text-xl font-bold">Token Créé !</h3>
        </div>
        
        <div class="alert alert-warning mb-4">
            <p class="font-bold mb-2">⚠️ Copiez maintenant</p>
            <p class="text-sm">Ce token ne sera plus jamais affiché.</p>
        </div>
        
        <div class="mb-4">
            <label class="block text-sm font-medium mb-2">Votre Token :</label>
            <div class="relative">
                <input type="text" 
                       id="newToken" 
                       value="{{ $newToken }}"
                       readonly
                       class="input-field font-mono select-all">
                <button onclick="copyNewToken()" class="absolute right-3 top-3">
                    📋
                </button>
            </div>
        </div>
        
        <button onclick="closeSuccessModal()" class="btn-success w-full">
            J'ai Copié
        </button>
    </div>
</div>
```

---

### **4. Section Sécurité**

```html
<div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-lg mb-6">
    <h3 class="text-lg font-semibold text-red-900 mb-3">
        ⚠️ Consignes de Sécurité
    </h3>
    <ul class="space-y-2 text-red-800">
        <li class="flex items-start">
            <span class="mr-2">🔒</span>
            <span><strong>Ne partagez JAMAIS</strong> - Accès complet à votre compte</span>
        </li>
        <li class="flex items-start">
            <span class="mr-2">🌐</span>
            <span><strong>HTTPS uniquement</strong> - Jamais en HTTP non sécurisé</span>
        </li>
        <li class="flex items-start">
            <span class="mr-2">💾</span>
            <span><strong>Variables d'environnement</strong> - Pas de code en dur</span>
        </li>
        <li class="flex items-start">
            <span class="mr-2">🔄</span>
            <span><strong>Régénérez si compromis</strong> - En cas de doute</span>
        </li>
    </ul>
</div>
```

---

### **5. Statistiques**

```html
<div class="card">
    <h3 class="text-lg font-semibold mb-4">📊 Statistiques</h3>
    
    <!-- Stats Grid -->
    <div class="grid grid-cols-3 gap-4 mb-4">
        <div class="stat-card bg-blue-50">
            <p class="text-sm text-blue-700">Aujourd'hui</p>
            <p class="text-2xl font-bold text-blue-900">{{ $stats->today }}</p>
        </div>
        <div class="stat-card bg-green-50">
            <p class="text-sm text-green-700">Ce mois</p>
            <p class="text-2xl font-bold text-green-900">{{ $stats->month }}</p>
        </div>
        <div class="stat-card bg-purple-50">
            <p class="text-sm text-purple-700">Limite</p>
            <p class="text-2xl font-bold text-purple-900">120/min</p>
        </div>
    </div>
    
    <!-- Dernières Activités -->
    <div class="border-t pt-4">
        <h4 class="font-medium mb-3">Dernières Activités</h4>
        <div class="space-y-2">
            @foreach($recentActivity as $activity)
            <div class="activity-item">
                <span class="method {{ $activity->method }}">{{ $activity->method }}</span>
                <span class="endpoint">{{ $activity->endpoint }}</span>
                <span class="time">{{ $activity->time }}</span>
            </div>
            @endforeach
        </div>
    </div>
    
    <button onclick="viewHistory()" class="btn-secondary w-full mt-4">
        📈 Historique Complet
    </button>
</div>
```

---

## 💻 JAVASCRIPT

```javascript
// Toggle visibilité token
function toggleVisibility() {
    const input = document.getElementById('apiToken');
    input.type = input.type === 'password' ? 'text' : 'password';
}

// Copier token
async function copyToken() {
    const input = document.getElementById('apiToken');
    const type = input.type;
    input.type = 'text';
    input.select();
    document.execCommand('copy');
    input.type = type;
    showToast('✅ Token copié', 'success');
}

// Générer token
async function confirmGenerate() {
    try {
        const response = await fetch('/client/api/token/generate', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            }
        });
        const data = await response.json();
        if (data.success) {
            document.getElementById('newToken').value = data.token;
            showModal('successModal');
        }
    } catch (error) {
        showToast('❌ Erreur', 'error');
    }
}

// Régénérer token
async function confirmRegenerate() {
    if (!document.getElementById('confirmCheck').checked) return;
    
    try {
        const response = await fetch('/client/api/token/regenerate', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            }
        });
        const data = await response.json();
        if (data.success) {
            document.getElementById('newToken').value = data.token;
            closeModal('regenerateModal');
            showModal('successModal');
        }
    } catch (error) {
        showToast('❌ Erreur', 'error');
    }
}

// Enable/disable bouton régénérer
document.getElementById('confirmCheck')?.addEventListener('change', (e) => {
    document.getElementById('confirmBtn').disabled = !e.target.checked;
});
```

---

## 🎨 CSS CLASSES

```css
.card {
    @apply bg-white border border-gray-300 rounded-lg p-6 shadow-sm;
}

.stat-card {
    @apply p-4 rounded-lg;
}

.activity-item {
    @apply flex justify-between items-center p-2 bg-gray-50 rounded text-sm;
}

.method {
    @apply px-2 py-1 rounded text-xs font-mono font-bold;
}

.method.GET {
    @apply bg-blue-100 text-blue-800;
}

.method.POST {
    @apply bg-green-100 text-green-800;
}

.btn-primary {
    @apply px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700;
}

.btn-success {
    @apply px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700;
}

.btn-warning {
    @apply px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700;
}

.btn-danger {
    @apply px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700;
}

.alert {
    @apply p-4 rounded-lg;
}

.alert-warning {
    @apply bg-yellow-50 border-l-4 border-yellow-500 text-yellow-800;
}

.alert-danger {
    @apply bg-red-50 border-l-4 border-red-500 text-red-800;
}
```

---

**Suite** : Voir `SPEC_API_CLIENT_PARTIE_2_ENDPOINTS.md`
