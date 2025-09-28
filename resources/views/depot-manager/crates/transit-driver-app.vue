<template>
  <div class="min-h-screen bg-gradient-to-br from-indigo-900 via-blue-900 to-purple-900">
    <!-- Header Mobile -->
    <div class="bg-white/10 backdrop-blur-lg border-b border-white/20">
      <div class="px-4 py-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4"/>
              </svg>
            </div>
            <div>
              <h1 class="text-lg font-bold text-white">Transit</h1>
              <p class="text-sm text-blue-200">{{ driverName }}</p>
            </div>
          </div>
          <div class="text-right">
            <div class="text-xs text-blue-200">{{ currentTime }}</div>
            <div class="text-sm font-semibold text-white">{{ currentDate }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tournée Info -->
    <div class="px-4 py-6">
      <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 border border-white/20">
        <div class="text-center mb-4">
          <h2 class="text-xl font-bold text-white mb-2">Ma Tournée Actuelle</h2>
          <div class="inline-flex items-center space-x-2 bg-blue-600/30 px-4 py-2 rounded-full">
            <svg class="w-5 h-5 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span class="text-blue-100 font-medium">{{ currentRoute.origin }} → {{ currentRoute.destination }}</span>
          </div>
        </div>

        <!-- Statistiques -->
        <div class="grid grid-cols-3 gap-4 text-center">
          <div class="bg-white/10 rounded-xl p-4">
            <div class="text-2xl font-bold text-white">{{ loadedBoxes.length }}</div>
            <div class="text-xs text-blue-200">Boîtes</div>
            <div class="text-xs text-blue-200">chargées</div>
          </div>
          <div class="bg-white/10 rounded-xl p-4">
            <div class="text-2xl font-bold text-white">{{ totalPackages }}</div>
            <div class="text-xs text-blue-200">Total</div>
            <div class="text-xs text-blue-200">colis</div>
          </div>
          <div class="bg-white/10 rounded-xl p-4">
            <div class="text-2xl font-bold text-white">{{ currentRoute.progress }}%</div>
            <div class="text-xs text-blue-200">Tournée</div>
            <div class="text-xs text-blue-200">complète</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Bouton Principal Scanner -->
    <div class="px-4 py-6">
      <button
        @click="startScanning"
        :disabled="isScanning"
        class="w-full bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 disabled:from-gray-500 disabled:to-gray-600 text-white font-bold py-6 px-8 rounded-2xl shadow-2xl transform transition-all duration-200 active:scale-95"
        :class="{ 'animate-pulse': isScanning }"
      >
        <div class="flex items-center justify-center space-x-3">
          <svg v-if="!isScanning" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
          </svg>
          <svg v-else class="w-8 h-8 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
          <span class="text-xl">
            {{ isScanning ? 'SCANNING...' : 'SCANNER UNE BOÎTE' }}
          </span>
        </div>
        <div class="mt-2 text-sm opacity-90">
          {{ isScanning ? 'Pointez vers le code de la boîte' : 'Chargement dans le camion' }}
        </div>
      </button>
    </div>

    <!-- Liste des Boîtes Chargées -->
    <div class="px-4 pb-6">
      <div class="bg-white/10 backdrop-blur-lg rounded-2xl border border-white/20 overflow-hidden">
        <div class="p-4 border-b border-white/20">
          <h3 class="text-lg font-bold text-white flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            Boîtes Chargées ({{ loadedBoxes.length }})
          </h3>
        </div>

        <div v-if="loadedBoxes.length === 0" class="p-6 text-center">
          <svg class="w-12 h-12 mx-auto text-white/40 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
          </svg>
          <p class="text-white/60">Aucune boîte chargée</p>
          <p class="text-sm text-white/40 mt-1">Scannez votre première boîte</p>
        </div>

        <div v-else class="divide-y divide-white/10">
          <div
            v-for="box in loadedBoxes"
            :key="box.id"
            class="p-4 flex items-center justify-between"
            :class="{ 'bg-green-500/20': box.isNewlyAdded }"
          >
            <div class="flex items-center space-x-3">
              <div class="w-10 h-10 bg-blue-500/30 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
              </div>
              <div>
                <div class="font-semibold text-white">{{ box.destination }}</div>
                <div class="text-sm text-blue-200">{{ box.packageCount }} colis</div>
                <div class="text-xs text-blue-300 font-mono">{{ box.code }}</div>
              </div>
            </div>
            <div class="text-right">
              <div class="text-sm text-white font-medium">{{ box.loadTime }}</div>
              <div class="text-xs text-green-300">✓ Chargée</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Statut du Camion -->
    <div class="px-4 pb-6">
      <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-4 border border-white/20">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div class="w-3 h-3 rounded-full" :class="truckStatus.online ? 'bg-green-400' : 'bg-red-400'"></div>
            <span class="text-white font-medium">{{ truckStatus.online ? 'En ligne' : 'Hors ligne' }}</span>
          </div>
          <div class="text-sm text-blue-200">
            Camion #{{ truckId }}
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Scanner -->
    <div v-if="showScanner" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center">
      <div class="bg-white/10 backdrop-blur-lg rounded-3xl p-6 mx-4 w-full max-w-sm border border-white/20">
        <div class="text-center mb-6">
          <div class="w-16 h-16 bg-green-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h4"/>
            </svg>
          </div>
          <h3 class="text-xl font-bold text-white mb-2">Scanner la Boîte</h3>
          <p class="text-blue-200 text-sm">Pointez vers le code-barres ou QR code</p>
        </div>

        <!-- Simulation de caméra -->
        <div class="bg-black/40 rounded-xl p-8 mb-6 text-center border-2 border-dashed border-white/30">
          <svg class="w-12 h-12 mx-auto text-white/60 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          <p class="text-white/60 text-sm">Caméra active</p>
        </div>

        <!-- Input manuel -->
        <div class="mb-6">
          <input
            v-model="scannedCode"
            @keyup.enter="processScannedCode"
            type="text"
            placeholder="Ou saisissez le code..."
            class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-white/50 focus:ring-2 focus:ring-green-500 focus:border-transparent"
            autofocus
          >
        </div>

        <div class="flex space-x-3">
          <button
            @click="stopScanning"
            class="flex-1 py-3 bg-gray-500/20 border border-gray-400/30 text-white rounded-xl hover:bg-gray-500/30 transition-colors"
          >
            Annuler
          </button>
          <button
            @click="processScannedCode"
            :disabled="!scannedCode.trim()"
            class="flex-1 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            Valider
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Confirmation Chargement -->
    <div v-if="pendingBox" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center">
      <div class="bg-white/10 backdrop-blur-lg rounded-3xl p-6 mx-4 w-full max-w-sm border border-white/20">
        <div class="text-center mb-6">
          <div class="w-16 h-16 bg-blue-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
          </div>
          <h3 class="text-xl font-bold text-white mb-2">Confirmer le Chargement</h3>
          <p class="text-blue-200 text-sm mb-4">Voulez-vous charger cette boîte ?</p>

          <!-- Détails de la boîte -->
          <div class="bg-white/10 rounded-xl p-4 mb-6 text-left">
            <div class="space-y-2">
              <div class="flex justify-between">
                <span class="text-blue-200">Destination:</span>
                <span class="text-white font-medium">{{ pendingBox.destination }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-blue-200">Colis:</span>
                <span class="text-white font-medium">{{ pendingBox.packageCount }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-blue-200">Code:</span>
                <span class="text-white font-mono text-sm">{{ pendingBox.code }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="flex space-x-3">
          <button
            @click="cancelLoading"
            class="flex-1 py-3 bg-gray-500/20 border border-gray-400/30 text-white rounded-xl hover:bg-gray-500/30 transition-colors"
          >
            Annuler
          </button>
          <button
            @click="confirmLoading"
            class="flex-1 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors"
          >
            Confirmer
          </button>
        </div>
      </div>
    </div>

    <!-- Toast Messages -->
    <div v-if="toastMessage" class="fixed top-4 left-4 right-4 z-60">
      <div
        class="bg-white/10 backdrop-blur-lg rounded-xl p-4 border border-white/20"
        :class="toastType === 'success' ? 'border-green-400/50' : 'border-red-400/50'"
      >
        <div class="flex items-center space-x-3">
          <div
            class="w-8 h-8 rounded-full flex items-center justify-center"
            :class="toastType === 'success' ? 'bg-green-500/20' : 'bg-red-500/20'"
          >
            <svg v-if="toastType === 'success'" class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <svg v-else class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div class="flex-1">
            <p class="text-white font-medium">{{ toastMessage }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TransitDriverApp',
  data() {
    return {
      driverName: 'Mohamed Trabelsi',
      truckId: 'TN-07-451',
      currentRoute: {
        origin: 'TUNIS',
        destination: 'SFAX',
        progress: 65
      },
      isScanning: false,
      showScanner: false,
      scannedCode: '',
      pendingBox: null,
      toastMessage: '',
      toastType: 'success', // 'success' or 'error'
      truckStatus: {
        online: true
      },
      loadedBoxes: [
        {
          id: 1,
          code: 'SFAX-TUN-28092025-01',
          destination: 'SFAX',
          packageCount: 15,
          loadTime: '08:30',
          isNewlyAdded: false
        },
        {
          id: 2,
          code: 'SFAX-TUN-28092025-02',
          destination: 'SFAX',
          packageCount: 22,
          loadTime: '08:45',
          isNewlyAdded: false
        },
        {
          id: 3,
          code: 'SOUSSE-TUN-28092025-01',
          destination: 'SOUSSE',
          packageCount: 18,
          loadTime: '09:15',
          isNewlyAdded: false
        }
      ]
    }
  },
  computed: {
    currentTime() {
      return new Date().toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit'
      })
    },
    currentDate() {
      return new Date().toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit'
      })
    },
    totalPackages() {
      return this.loadedBoxes.reduce((total, box) => total + box.packageCount, 0)
    }
  },
  mounted() {
    // Mettre à jour l'heure chaque minute
    setInterval(() => {
      this.$forceUpdate()
    }, 60000)
  },
  methods: {
    startScanning() {
      this.isScanning = true
      this.showScanner = true
      this.scannedCode = ''
    },

    stopScanning() {
      this.isScanning = false
      this.showScanner = false
      this.scannedCode = ''
    },

    async processScannedCode() {
      if (!this.scannedCode.trim()) return

      this.stopScanning()

      try {
        // Simulation d'appel API pour vérifier la boîte
        await new Promise(resolve => setTimeout(resolve, 1000))

        const boxCode = this.scannedCode.trim()

        // Vérifier si la boîte est déjà chargée
        if (this.loadedBoxes.some(box => box.code === boxCode)) {
          this.showToast('Cette boîte est déjà chargée', 'error')
          return
        }

        // Simuler les données de la boîte scannée
        const boxData = this.simulateBoxData(boxCode)

        if (boxData) {
          this.pendingBox = boxData
        } else {
          this.showToast('Code de boîte invalide ou introuvable', 'error')
        }
      } catch (error) {
        this.showToast('Erreur lors du scan: ' + error.message, 'error')
      }
    },

    simulateBoxData(code) {
      // Simulation basée sur le format du code (ex: SFAX-TUN-28092025-03)
      const parts = code.split('-')
      if (parts.length >= 4) {
        const destination = parts[0]
        const packageCount = Math.floor(Math.random() * 25) + 10

        return {
          code: code,
          destination: destination,
          packageCount: packageCount,
          origin: parts[1] || 'TUNIS'
        }
      }
      return null
    },

    async confirmLoading() {
      if (!this.pendingBox) return

      try {
        // Simulation d'appel API pour confirmer le chargement
        await new Promise(resolve => setTimeout(resolve, 500))

        // Ajouter la boîte à la liste des chargées
        const newBox = {
          id: Date.now(),
          code: this.pendingBox.code,
          destination: this.pendingBox.destination,
          packageCount: this.pendingBox.packageCount,
          loadTime: new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }),
          isNewlyAdded: true
        }

        this.loadedBoxes.unshift(newBox)

        // Mettre à jour le progrès de la tournée
        this.currentRoute.progress = Math.min(100, this.currentRoute.progress + 5)

        this.showToast(`Boîte ${this.pendingBox.destination} (${this.pendingBox.packageCount} colis) chargée avec succès`, 'success')

        // Enlever l'indicateur "nouveau" après 3 secondes
        setTimeout(() => {
          newBox.isNewlyAdded = false
        }, 3000)

        this.pendingBox = null
      } catch (error) {
        this.showToast('Erreur lors du chargement: ' + error.message, 'error')
      }
    },

    cancelLoading() {
      this.pendingBox = null
    },

    showToast(message, type = 'success') {
      this.toastMessage = message
      this.toastType = type

      // Masquer le toast après 4 secondes
      setTimeout(() => {
        this.toastMessage = ''
      }, 4000)
    }
  }
}
</script>

<style scoped>
/* Animations personnalisées pour mobile */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fadeInUp {
  animation: fadeInUp 0.3s ease-out;
}

/* Styles pour le focus sur mobile */
input:focus {
  outline: none;
}

/* Style pour les boutons tactiles */
button {
  -webkit-tap-highlight-color: transparent;
  touch-action: manipulation;
}

/* Animation de pulsation personnalisée */
@keyframes pulse-green {
  0%, 100% {
    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
  }
  70% {
    box-shadow: 0 0 0 10px rgba(34, 197, 94, 0);
  }
}

.animate-pulse {
  animation: pulse-green 2s infinite;
}
</style>