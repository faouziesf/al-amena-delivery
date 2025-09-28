<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <!-- Header -->
    <div class="bg-white shadow-lg border-b-4 border-blue-600">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div class="flex items-center space-x-4">
            <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
              </svg>
            </div>
            <div>
              <h1 class="text-2xl font-bold text-gray-900">Gestion des Boîtes de Transit</h1>
              <p class="text-gray-600">Dépôt de {{ currentDepot.name }}</p>
            </div>
          </div>
          <div class="text-right">
            <div class="text-sm text-gray-500">{{ currentDate }}</div>
            <div class="text-lg font-semibold text-blue-600">Chef: {{ currentUser.name }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
      <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
          <button
            @click="activeTab = 'preparation'"
            :class="[
              'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
              activeTab === 'preparation'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            Préparation / Tri
          </button>
          <button
            @click="activeTab = 'transit'"
            :class="[
              'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
              activeTab === 'transit'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
            </svg>
            Départs / Arrivées
          </button>
        </nav>
      </div>
    </div>

    <!-- Tab Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

      <!-- ONGLET PRÉPARATION -->
      <div v-show="activeTab === 'preparation'" class="space-y-8">
        <!-- Scanner de Colis -->
        <div class="bg-white rounded-xl shadow-lg p-6">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-900">Scanner des Colis pour Tri</h2>
            <div class="text-sm text-gray-500">
              Total colis triés aujourd'hui: <span class="font-bold text-blue-600">{{ totalPackagesScanned }}</span>
            </div>
          </div>

          <div class="flex space-x-4">
            <div class="flex-1">
              <label class="block text-sm font-medium text-gray-700 mb-2">Code du Colis</label>
              <div class="relative">
                <input
                  v-model="packageCodeInput"
                  @keyup.enter="scanPackage"
                  type="text"
                  placeholder="Scannez ou saisissez le code du colis..."
                  class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-lg"
                  :class="{ 'ring-2 ring-green-500 border-green-500': lastScanSuccess }"
                >
                <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                  <svg v-if="isScanning" class="w-6 h-6 text-blue-500 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h4"/>
                  </svg>
                  <svg v-else-if="lastScanSuccess" class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
              </div>
            </div>
            <button
              @click="scanPackage"
              :disabled="!packageCodeInput.trim() || isScanning"
              class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              {{ isScanning ? 'Traitement...' : 'Valider' }}
            </button>
          </div>

          <!-- Message de confirmation -->
          <div v-if="lastScanMessage" class="mt-4 p-4 rounded-lg" :class="lastScanSuccess ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'">
            {{ lastScanMessage }}
          </div>
        </div>

        <!-- Grille des Boîtes -->
        <div class="bg-white rounded-xl shadow-lg p-6">
          <h2 class="text-xl font-bold text-gray-900 mb-6">Boîtes par Gouvernorat</h2>

          <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-6">
            <div
              v-for="box in boxes"
              :key="box.governorate"
              class="relative border-2 rounded-xl p-4 transition-all duration-300 cursor-pointer"
              :class="[
                box.isActive ? 'border-blue-500 bg-blue-50 shadow-lg scale-105' : 'border-gray-200 hover:border-gray-300',
                box.isSealed ? 'opacity-50' : ''
              ]"
            >
              <!-- Indicateur d'activité -->
              <div v-if="box.isActive" class="absolute -top-2 -right-2 w-4 h-4 bg-blue-500 rounded-full animate-ping"></div>

              <!-- En-tête de la boîte -->
              <div class="text-center mb-4">
                <h3 class="font-bold text-lg text-gray-900">{{ box.governorate }}</h3>
                <div class="text-sm text-gray-500">{{ box.code }}</div>
              </div>

              <!-- Compteur de colis -->
              <div class="text-center mb-4">
                <div class="text-3xl font-bold" :class="box.packageCount > 0 ? 'text-blue-600' : 'text-gray-400'">
                  {{ box.packageCount }}
                </div>
                <div class="text-sm text-gray-500">colis</div>
              </div>

              <!-- Statut -->
              <div class="text-center mb-4">
                <span
                  class="inline-block px-2 py-1 rounded-full text-xs font-medium"
                  :class="box.isSealed ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                >
                  {{ box.isSealed ? 'Scellée' : 'En préparation' }}
                </span>
              </div>

              <!-- Action -->
              <div class="text-center">
                <button
                  v-if="!box.isSealed && box.packageCount > 0"
                  @click="sealBox(box)"
                  class="w-full px-3 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors"
                >
                  🔒 Sceller & Imprimer
                </button>
                <button
                  v-else-if="box.isSealed"
                  @click="viewBoxDetails(box)"
                  class="w-full px-3 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200 transition-colors"
                >
                  📋 Voir Détails
                </button>
                <div v-else class="text-xs text-gray-400">
                  Aucun colis
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ONGLET DÉPARTS/ARRIVÉES -->
      <div v-show="activeTab === 'transit'" class="space-y-8">

        <!-- Actions Principales -->
        <div class="grid md:grid-cols-2 gap-8">

          <!-- Réception -->
          <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="text-center mb-6">
              <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
              </div>
              <h2 class="text-xl font-bold text-gray-900">Réception de Boîtes</h2>
              <p class="text-gray-600">Scanner les boîtes arrivées</p>
            </div>

            <button
              @click="startReceiving"
              class="w-full py-4 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-lg font-medium"
            >
              🔴 Réceptionner des Boîtes
            </button>

            <div class="mt-4 text-sm text-gray-500 text-center">
              Boîtes reçues aujourd'hui: <span class="font-bold">{{ receivedBoxesToday.length }}</span>
            </div>
          </div>

          <!-- Expédition -->
          <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="text-center mb-6">
              <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4"/>
                </svg>
              </div>
              <h2 class="text-xl font-bold text-gray-900">Expédition</h2>
              <p class="text-gray-600">Boîtes prêtes à partir</p>
            </div>

            <div class="space-y-3">
              <div class="text-center text-lg font-bold text-blue-600">
                {{ readyToShipBoxes.length }} boîtes prêtes
              </div>
              <button
                v-if="readyToShipBoxes.length > 0"
                @click="viewShippingList"
                class="w-full py-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-lg font-medium"
              >
                📋 Voir la Liste d'Expédition
              </button>
            </div>
          </div>
        </div>

        <!-- Historique Récent -->
        <div class="bg-white rounded-xl shadow-lg p-6">
          <h2 class="text-xl font-bold text-gray-900 mb-6">Activité Récente</h2>

          <div class="space-y-4">
            <div
              v-for="activity in recentActivities"
              :key="activity.id"
              class="flex items-center justify-between p-4 border border-gray-200 rounded-lg"
            >
              <div class="flex items-center space-x-4">
                <div
                  class="w-10 h-10 rounded-full flex items-center justify-center"
                  :class="activity.type === 'received' ? 'bg-green-100' : 'bg-blue-100'"
                >
                  <svg v-if="activity.type === 'received'" class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                  </svg>
                  <svg v-else class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4"/>
                  </svg>
                </div>
                <div>
                  <div class="font-medium text-gray-900">{{ activity.description }}</div>
                  <div class="text-sm text-gray-500">{{ activity.time }}</div>
                </div>
              </div>
              <div class="text-right">
                <div class="font-bold text-lg">{{ activity.packageCount }}</div>
                <div class="text-sm text-gray-500">colis</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Scanner de Réception -->
    <div v-if="isReceiving" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click="stopReceiving">
      <div class="bg-white rounded-xl shadow-2xl p-8 max-w-md w-full mx-4" @click.stop>
        <div class="text-center mb-6">
          <h3 class="text-xl font-bold text-gray-900 mb-2">Scanner une Boîte</h3>
          <p class="text-gray-600">Pointez vers le code-barres ou QR code</p>
        </div>

        <div class="mb-6">
          <input
            v-model="receivingCodeInput"
            @keyup.enter="receiveBox"
            type="text"
            placeholder="Scanner le code de la boîte..."
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-lg text-center"
            autofocus
          >
        </div>

        <div class="flex space-x-3">
          <button
            @click="stopReceiving"
            class="flex-1 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
          >
            Annuler
          </button>
          <button
            @click="receiveBox"
            :disabled="!receivingCodeInput.trim()"
            class="flex-1 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            Valider
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Détails de Boîte -->
    <div v-if="selectedBox" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click="selectedBox = null">
      <div class="bg-white rounded-xl shadow-2xl p-8 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl font-bold text-gray-900">Détails de la Boîte {{ selectedBox.governorate }}</h3>
          <button @click="selectedBox = null" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <div class="space-y-4">
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <span class="font-medium text-gray-700">Code:</span>
              <div class="text-gray-900">{{ selectedBox.code }}</div>
            </div>
            <div>
              <span class="font-medium text-gray-700">Destination:</span>
              <div class="text-gray-900">{{ selectedBox.governorate }}</div>
            </div>
            <div>
              <span class="font-medium text-gray-700">Statut:</span>
              <div class="text-gray-900">{{ selectedBox.isSealed ? 'Scellée' : 'En préparation' }}</div>
            </div>
            <div>
              <span class="font-medium text-gray-700">Nombre de colis:</span>
              <div class="text-gray-900">{{ selectedBox.packageCount }}</div>
            </div>
          </div>

          <div v-if="selectedBox.packages.length > 0" class="border-t pt-4">
            <h4 class="font-medium text-gray-700 mb-3">Liste des Colis</h4>
            <div class="max-h-64 overflow-y-auto space-y-2">
              <div
                v-for="pkg in selectedBox.packages"
                :key="pkg.code"
                class="flex justify-between items-center p-2 bg-gray-50 rounded"
              >
                <span class="font-mono text-sm">{{ pkg.code }}</span>
                <span class="text-xs text-gray-500">{{ pkg.scanTime }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DepotBoxManager',
  data() {
    return {
      activeTab: 'preparation',
      packageCodeInput: '',
      isScanning: false,
      lastScanSuccess: false,
      lastScanMessage: '',
      currentDepot: {
        name: 'Dépôt Central Tunis',
        code: 'TUN'
      },
      currentUser: {
        name: 'Ahmed Ben Salah'
      },
      totalPackagesScanned: 0,
      isReceiving: false,
      receivingCodeInput: '',
      selectedBox: null,
      boxes: [
        { governorate: 'TUNIS', code: 'TUN-28092025-01', packageCount: 12, isSealed: true, isActive: false, packages: [] },
        { governorate: 'SFAX', code: 'SFAX-28092025-01', packageCount: 8, isSealed: false, isActive: false, packages: [] },
        { governorate: 'SOUSSE', code: 'SOUSSE-28092025-01', packageCount: 15, isSealed: true, isActive: false, packages: [] },
        { governorate: 'KAIROUAN', code: 'KAIROUAN-28092025-01', packageCount: 0, isSealed: false, isActive: false, packages: [] },
        { governorate: 'BIZERTE', code: 'BIZERTE-28092025-01', packageCount: 6, isSealed: false, isActive: false, packages: [] },
        { governorate: 'GABES', code: 'GABES-28092025-01', packageCount: 3, isSealed: false, isActive: false, packages: [] },
        { governorate: 'ARIANA', code: 'ARIANA-28092025-01', packageCount: 9, isSealed: false, isActive: false, packages: [] },
        { governorate: 'MANOUBA', code: 'MANOUBA-28092025-01', packageCount: 4, isSealed: false, isActive: false, packages: [] },
        { governorate: 'NABEUL', code: 'NABEUL-28092025-01', packageCount: 11, isSealed: true, isActive: false, packages: [] },
        { governorate: 'ZAGHOUAN', code: 'ZAGHOUAN-28092025-01', packageCount: 0, isSealed: false, isActive: false, packages: [] },
        { governorate: 'BEJA', code: 'BEJA-28092025-01', packageCount: 2, isSealed: false, isActive: false, packages: [] },
        { governorate: 'JENDOUBA', code: 'JENDOUBA-28092025-01', packageCount: 7, isSealed: false, isActive: false, packages: [] },
        { governorate: 'KASSE', code: 'KASSE-28092025-01', packageCount: 5, isSealed: false, isActive: false, packages: [] },
        { governorate: 'SILIANA', code: 'SILIANA-28092025-01', packageCount: 1, isSealed: false, isActive: false, packages: [] },
        { governorate: 'KEBILI', code: 'KEBILI-28092025-01', packageCount: 0, isSealed: false, isActive: false, packages: [] },
        { governorate: 'TOZEUR', code: 'TOZEUR-28092025-01', packageCount: 3, isSealed: false, isActive: false, packages: [] },
        { governorate: 'GAFSA', code: 'GAFSA-28092025-01', packageCount: 4, isSealed: false, isActive: false, packages: [] },
        { governorate: 'SIDI', code: 'SIDI-28092025-01', packageCount: 2, isSealed: false, isActive: false, packages: [] },
        { governorate: 'MEDENINE', code: 'MEDENINE-28092025-01', packageCount: 6, isSealed: false, isActive: false, packages: [] },
        { governorate: 'TATAOUINE', code: 'TATAOUINE-28092025-01', packageCount: 1, isSealed: false, isActive: false, packages: [] },
        { governorate: 'MAHDIA', code: 'MAHDIA-28092025-01', packageCount: 8, isSealed: false, isActive: false, packages: [] },
        { governorate: 'MONASTIR', code: 'MONASTIR-28092025-01', packageCount: 5, isSealed: false, isActive: false, packages: [] },
        { governorate: 'KASER', code: 'KASER-28092025-01', packageCount: 3, isSealed: false, isActive: false, packages: [] },
        { governorate: 'BENARO', code: 'BENARO-28092025-01', packageCount: 2, isSealed: false, isActive: false, packages: [] },
      ],
      receivedBoxesToday: [
        { code: 'BIZERTE-27092025-01', governorate: 'BIZERTE', packageCount: 23, time: '08:30' },
        { code: 'NABEUL-27092025-02', governorate: 'NABEUL', packageCount: 18, time: '10:15' }
      ],
      recentActivities: [
        { id: 1, type: 'received', description: 'Boîte de BIZERTE reçue', packageCount: 23, time: '08:30' },
        { id: 2, type: 'sent', description: 'Boîte vers SFAX expédiée', packageCount: 15, time: '07:45' },
        { id: 3, type: 'received', description: 'Boîte de NABEUL reçue', packageCount: 18, time: '10:15' },
        { id: 4, type: 'sent', description: 'Boîte vers SOUSSE expédiée', packageCount: 12, time: '09:20' }
      ]
    }
  },
  computed: {
    currentDate() {
      return new Date().toLocaleDateString('fr-FR', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    },
    readyToShipBoxes() {
      return this.boxes.filter(box => box.isSealed && box.packageCount > 0)
    }
  },
  methods: {
    async scanPackage() {
      if (!this.packageCodeInput.trim() || this.isScanning) return

      this.isScanning = true
      this.lastScanSuccess = false
      this.lastScanMessage = ''

      try {
        // Simulation d'appel API
        await new Promise(resolve => setTimeout(resolve, 1000))

        // Logique de simulation
        const packageCode = this.packageCodeInput.trim()
        const destinationGovernorate = this.getDestinationForPackage(packageCode)

        if (destinationGovernorate) {
          const targetBox = this.boxes.find(box => box.governorate === destinationGovernorate)

          if (targetBox && !targetBox.isSealed) {
            // Animer la boîte
            targetBox.isActive = true
            setTimeout(() => {
              targetBox.isActive = false
            }, 2000)

            // Ajouter le colis à la boîte
            targetBox.packageCount++
            targetBox.packages.push({
              code: packageCode,
              scanTime: new Date().toLocaleTimeString('fr-FR')
            })

            this.totalPackagesScanned++
            this.lastScanSuccess = true
            this.lastScanMessage = `✅ Colis ${packageCode} ajouté à la boîte ${destinationGovernorate}`
            this.packageCodeInput = ''
          } else if (targetBox && targetBox.isSealed) {
            this.lastScanMessage = `❌ La boîte ${destinationGovernorate} est déjà scellée`
          } else {
            this.lastScanMessage = `❌ Destination introuvable pour ${packageCode}`
          }
        } else {
          this.lastScanMessage = `❌ Code colis invalide: ${packageCode}`
        }
      } catch (error) {
        this.lastScanMessage = `❌ Erreur lors du scan: ${error.message}`
      } finally {
        this.isScanning = false

        // Effacer le message après 5 secondes
        setTimeout(() => {
          this.lastScanMessage = ''
          this.lastScanSuccess = false
        }, 5000)
      }
    },

    getDestinationForPackage(packageCode) {
      // Simulation basée sur le préfixe du code
      const governorates = ['SFAX', 'SOUSSE', 'BIZERTE', 'GABES', 'ARIANA', 'NABEUL', 'MONASTIR']
      const hash = packageCode.split('').reduce((a, b) => {
        a = ((a << 5) - a) + b.charCodeAt(0)
        return a & a
      }, 0)
      return governorates[Math.abs(hash) % governorates.length]
    },

    async sealBox(box) {
      if (box.packageCount === 0) return

      try {
        // Simulation d'appel API pour sceller la boîte
        await new Promise(resolve => setTimeout(resolve, 500))

        box.isSealed = true

        // Générer et ouvrir le bon de boîte
        this.generateBoxReceipt(box)

        this.lastScanMessage = `✅ Boîte ${box.governorate} scellée avec ${box.packageCount} colis`
        setTimeout(() => {
          this.lastScanMessage = ''
        }, 3000)
      } catch (error) {
        alert('Erreur lors du scellage: ' + error.message)
      }
    },

    generateBoxReceipt(box) {
      // Génération du bon de boîte (ouverture dans une nouvelle fenêtre)
      const receiptContent = this.createBoxReceiptHTML(box)
      const newWindow = window.open('', '_blank', 'width=800,height=600')
      newWindow.document.write(receiptContent)
      newWindow.document.close()
      newWindow.print()
    },

    createBoxReceiptHTML(box) {
      const currentDate = new Date().toLocaleDateString('fr-FR')
      const currentTime = new Date().toLocaleTimeString('fr-FR')

      return `
        <!DOCTYPE html>
        <html>
        <head>
          <title>Bon de Boîte - ${box.code}</title>
          <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
            .title { font-size: 24px; font-weight: bold; margin-bottom: 10px; }
            .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
            .info-item { padding: 10px; border: 1px solid #ddd; }
            .code-section { text-align: center; margin: 30px 0; padding: 20px; border: 2px solid #333; }
            .barcode { font-family: 'Courier New', monospace; font-size: 20px; letter-spacing: 3px; }
            .package-list { margin-top: 30px; }
            .package-list h3 { border-bottom: 1px solid #333; padding-bottom: 10px; }
            .package-item { padding: 5px; border-bottom: 1px dotted #ccc; }
          </style>
        </head>
        <body>
          <div class="header">
            <div class="title">BON DE BOÎTE DE TRANSIT</div>
            <div>AL-AMENA DELIVERY</div>
          </div>

          <div class="info-grid">
            <div class="info-item">
              <strong>Dépôt d'Origine:</strong><br>${this.currentDepot.name}
            </div>
            <div class="info-item">
              <strong>Gouvernorat de Destination:</strong><br>${box.governorate}
            </div>
            <div class="info-item">
              <strong>Date:</strong><br>${currentDate} ${currentTime}
            </div>
            <div class="info-item">
              <strong>Nombre de colis:</strong><br>${box.packageCount}
            </div>
          </div>

          <div class="code-section">
            <div><strong>Code Unique de la Boîte:</strong></div>
            <div style="font-size: 18px; font-weight: bold; margin: 10px 0;">${box.code}</div>

            <div style="margin: 20px 0;">
              <div><strong>Code-barres:</strong></div>
              <div class="barcode">|||${box.code.split('').join(' | ')}|||</div>
            </div>

            <div>
              <div><strong>QR Code:</strong></div>
              <div style="border: 1px solid #333; width: 100px; height: 100px; margin: 10px auto; display: flex; align-items: center; justify-content: center;">
                QR_${box.code}
              </div>
            </div>
          </div>

          <div class="package-list">
            <h3>Liste des Colis (${box.packageCount})</h3>
            ${box.packages.map(pkg => `
              <div class="package-item">
                <strong>${pkg.code}</strong> - Scanné à ${pkg.scanTime}
              </div>
            `).join('')}
          </div>

          <div style="margin-top: 50px; text-align: center; font-size: 12px; color: #666;">
            Document généré le ${currentDate} à ${currentTime} par ${this.currentUser.name}
          </div>
        </body>
        </html>
      `
    },

    viewBoxDetails(box) {
      this.selectedBox = box
    },

    startReceiving() {
      this.isReceiving = true
      this.receivingCodeInput = ''
    },

    stopReceiving() {
      this.isReceiving = false
      this.receivingCodeInput = ''
    },

    async receiveBox() {
      if (!this.receivingCodeInput.trim()) return

      try {
        // Simulation d'appel API
        await new Promise(resolve => setTimeout(resolve, 500))

        const boxCode = this.receivingCodeInput.trim()

        // Extraire les informations du code (ex: SFAX-TUN-28092025-01)
        const parts = boxCode.split('-')
        if (parts.length >= 4) {
          const governorate = parts[0]
          const packageCount = Math.floor(Math.random() * 30) + 10 // Simulation

          // Ajouter à l'historique
          this.receivedBoxesToday.unshift({
            code: boxCode,
            governorate: governorate,
            packageCount: packageCount,
            time: new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })
          })

          this.recentActivities.unshift({
            id: Date.now(),
            type: 'received',
            description: `Boîte de ${governorate} reçue`,
            packageCount: packageCount,
            time: new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })
          })

          alert(`✅ Boîte de ${governorate} reçue avec ${packageCount} colis.`)
          this.stopReceiving()
        } else {
          alert('❌ Code de boîte invalide')
        }
      } catch (error) {
        alert('❌ Erreur lors de la réception: ' + error.message)
      }
    },

    viewShippingList() {
      const shippingList = this.readyToShipBoxes.map(box =>
        `${box.governorate}: ${box.packageCount} colis (${box.code})`
      ).join('\n')

      alert(`📋 Liste d'Expédition:\n\n${shippingList}`)
    }
  }
}
</script>

<style scoped>
/* Animations personnalisées */
@keyframes pulse-blue {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: .5;
  }
}

.animate-pulse {
  animation: pulse-blue 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>