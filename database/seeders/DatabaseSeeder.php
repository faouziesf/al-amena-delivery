<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        $this->seedUsers();
        $this->seedDelegations();
        $this->seedClientProfiles();

        // Truncate other system tables
        DB::table('password_reset_tokens')->truncate();
        DB::table('sessions')->truncate();
        DB::table('cache')->truncate();
        DB::table('cache_locks')->truncate();
        DB::table('jobs')->truncate();
        DB::table('job_batches')->truncate();
        DB::table('failed_jobs')->truncate();

        Schema::enableForeignKeyConstraints();

        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('');
        $this->command->info('📧 SUPERVISOR: admin@gmail.com | Password: 12345678');
        $this->command->info('📧 COMMERCIAL: commercial@test.com | Password: 12345678');
        $this->command->info('📧 DELIVERER: deliverer@test.com | Password: 12345678');
        $this->command->info('📧 CLIENT: client@test.com | Password: 12345678');
        $this->command->info('');
        $this->command->info('📍 Delegations loaded: ' . DB::table('delegations')->count());
    }

    private function seedUsers(): void
    {
        DB::table('users')->truncate();
        
        $now = now();
        $password = Hash::make('12345678');
        
        DB::table('users')->insert([
            // SUPERVISOR Account
            [
                'id' => 1,
                'name' => 'Administrator',
                'email' => 'admin@gmail.com',
                'email_verified_at' => $now,
                'password' => $password,
                'remember_token' => null,
                'role' => 'SUPERVISOR',
                'phone' => '+216 20 000 000',
                'address' => 'Tunis, Tunisia',
                'account_status' => 'ACTIVE',
                'verified_at' => $now,
                'verified_by' => null,
                'created_by' => null,
                'last_login' => $now,
                'created_at' => $now,
                'updated_at' => $now
            ],
            
            // COMMERCIAL Account
            [
                'id' => 2,
                'name' => 'Commercial Test',
                'email' => 'commercial@test.com',
                'email_verified_at' => $now,
                'password' => $password,
                'remember_token' => null,
                'role' => 'COMMERCIAL',
                'phone' => '+216 21 000 000',
                'address' => 'Tunis, Bureau Commercial',
                'account_status' => 'ACTIVE',
                'verified_at' => $now,
                'verified_by' => 1,
                'created_by' => 1,
                'last_login' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            
            // DELIVERER Account
            [
                'id' => 3,
                'name' => 'Livreur Test',
                'email' => 'deliverer@test.com',
                'email_verified_at' => $now,
                'password' => $password,
                'remember_token' => null,
                'role' => 'DELIVERER',
                'phone' => '+216 22 000 000',
                'address' => 'Zone Tunis',
                'account_status' => 'ACTIVE',
                'verified_at' => $now,
                'verified_by' => 1,
                'created_by' => 1,
                'last_login' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            
            // CLIENT Account
            [
                'id' => 4,
                'name' => 'Client Test',
                'email' => 'client@test.com',
                'email_verified_at' => $now,
                'password' => $password,
                'remember_token' => null,
                'role' => 'CLIENT',
                'phone' => '+216 23 000 000',
                'address' => 'Tunis, Tunisia',
                'account_status' => 'ACTIVE',
                'verified_at' => $now,
                'verified_by' => 1,
                'created_by' => 2,
                'last_login' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            
            // DEPOT_MANAGER Account
            [
                'id' => 5,
                'name' => 'Manager Depot',
                'email' => 'depot@test.com',
                'email_verified_at' => $now,
                'password' => $password,
                'remember_token' => null,
                'role' => 'DEPOT_MANAGER',
                'phone' => '+216 24 000 000',
                'address' => 'Depot Central, Tunis',
                'account_status' => 'ACTIVE',
                'verified_at' => $now,
                'verified_by' => 1,
                'created_by' => 1,
                'last_login' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
        ]);
    }

    private function seedDelegations(): void
    {
        DB::table('delegations')->truncate();
        
        $now = now();
        $delegations = [];

        // ALL TUNISIA DELEGATIONS BY GOVERNORATE
        $tunisianDelegations = [
            'TUNIS' => [
                'Tunis Médina', 'Bab Bhar', 'Bab Souika', 'Omrane', 'Omrane Supérieur', 
                'Ettahrir', 'Djebel Jelloud', 'El Menzah', 'Cité El Khadra', 'El Kabaria',
                'Sidi Hassine', 'Sidi El Béchir', 'La Goulette', 'Le Kram', 'La Marsa',
                'Carthage', 'Sidi Bou Saïd', 'Le Bardo', 'El Ouardia', 'Ezzouhour', 'Hrairia'
            ],
            'ARIANA' => [
                'Ariana Ville', 'Ettadhamen', 'Mnihla', 'Kalâat El Andalous', 
                'Raoued', 'Sidi Thabet', 'La Soukra'
            ],
            'BEN_AROUS' => [
                'Ben Arous', 'El Mourouj', 'Hammam Lif', 'Hammam Chott', 'Boumhel',
                'Ezzahra', 'Radès', 'Megrine', 'Mohamedia', 'Fouchana', 'Mornag', 'Medina Jedida'
            ],
            'MANOUBA' => [
                'Manouba', 'Den Den', 'Douar Hicher', 'Oued Ellil', 'Mornaguia',
                'Borj El Amri', 'Djedeida', 'Tebourba'
            ],
            'NABEUL' => [
                'Nabeul', 'Dar Chaâbane El Fehri', 'Béni Khalled', 'El Haouaria', 'Takelsa',
                'Soliman', 'Menzel Temime', 'Korba', 'Hammamet', 'Grombalia', 'Béni Khiar',
                'Kelibia', 'Menzel Bouzelfa', 'El Mida', 'Bou Argoub'
            ],
            'ZAGHOUAN' => [
                'Zaghouan', 'Zriba', 'Bir Mcherga', 'El Fahs', 'Nadhour', 'Saouaf'
            ],
            'BIZERTE' => [
                'Bizerte Nord', 'Bizerte Sud', 'Sejnane', 'Joumine', 'Mateur',
                'Ghezala', 'Menzel Bourguiba', 'Tinja', 'Ghar El Melh', 'Menzel Jemil',
                'El Alia', 'Ras Jebel', 'Utique', 'Zarzouna'
            ],
            'BÉJA' => [
                'Béja Nord', 'Béja Sud', 'Amdoun', 'Nefza', 'Teboursouk',
                'Tibar', 'Testour', 'Goubellat', 'Mejez El Bab'
            ],
            'JENDOUBA' => [
                'Jendouba', 'Jendouba Nord', 'Bou Salem', 'Tabarka', 'Aïn Draham',
                'Fernana', 'Balta Bou Aouane', 'Ghardimaou', 'Oued Meliz'
            ],
            'KEF' => [
                'Le Kef', 'Le Kef Ouest', 'Nebeur', 'Sakiet Sidi Youssef', 'Tajerouine',
                'Kalâa Khasba', 'Kalaat Senan', 'Dahmani', 'Sers', 'El Ksour', 'Jérissa'
            ],
            'SILIANA' => [
                'Siliana Nord', 'Siliana Sud', 'Bou Arada', 'Gaâfour', 'El Krib',
                'Bargou', 'Makthar', 'Rouhia', 'Kesra', 'Sidi Bou Rouis', 'El Aroussa'
            ],
            'KAIROUAN' => [
                'Kairouan Nord', 'Kairouan Sud', 'Echbika', 'Sbikha', 'Oueslatia',
                'Haffouz', 'El Alâa', 'Hajeb El Ayoun', 'Nasrallah', 'Cherarda', 'Bouhajla'
            ],
            'KASSERINE' => [
                'Kasserine Nord', 'Kasserine Sud', 'Ezzouhour', 'Hassi El Ferid', 'Sbeïtla',
                'Sbiba', 'Jedeliane', 'El Ayoun', 'Thala', 'Hidra', 'Foussana',
                'Feriana', 'Mejel Bel Abbès'
            ],
            'SIDI_BOUZID' => [
                'Sidi Bouzid Ouest', 'Sidi Bouzid Est', 'Cebalat Ouled Asker', 'Bir El Hafey',
                'Sidi Ali Ben Aoun', 'Menzel Bouzaiene', 'Meknassy', 'Souk Jedid',
                'Mezzouna', 'Regueb', 'Ouled Haffouz', 'Jilma'
            ],
            'SOUSSE' => [
                'Sousse Ville', 'Sousse Riadh', 'Sousse Jawhara', 'Sousse Sidi Abdelhamid',
                'Hammam Sousse', 'Akouda', 'Kalâa Kebira', 'Sidi Bou Ali', 'Hergla',
                'Enfidha', 'Bouficha', 'Kondar', 'Sidi El Hani', 'Msaken', 'Kalâa Sghira'
            ],
            'MONASTIR' => [
                'Monastir', 'Ouerdanine', 'Sahline', 'Zeramdine', 'Beni Hassen',
                'Jemmal', 'Bembla', 'Moknine', 'Bekalta', 'Téboulba',
                'Ksar Hellal', 'Ksibet El Mediouni', 'Sayada-Lamta-Bou Hajar'
            ],
            'MAHDIA' => [
                'Mahdia', 'Bou Merdes', 'Ouled Chamekh', 'Chorbane', 'Hebira',
                'Essouassi', 'El Jem', 'Chebba', 'Melloulèche', 'Sidi Alouane', 'Ksour Essef'
            ],
            'SFAX' => [
                'Sfax Ville', 'Sfax Ouest', 'Sfax Sud', 'Sakiet Ezzit', 'Sakiet Eddaier',
                'Thyna', 'Agareb', 'Jebiniana', 'El Hencha', 'Menzel Chaker',
                'El Amra', 'El Ghraiba', 'Bir Ali Ben Khalifa', 'Skhira', 'Mahrès', 'Kerkennah'
            ],
            'KAIROUAN' => [
                'Kairouan Nord', 'Kairouan Sud', 'Echbika', 'Sbikha', 'Oueslatia',
                'Haffouz', 'El Alâa', 'Hajeb El Ayoun', 'Nasrallah', 'Cherarda', 'Bouhajla'
            ],
            'GAFSA' => [
                'Gafsa Nord', 'Gafsa Sud', 'Sidi Aïch', 'El Ksar', 'Oum El Araies',
                'Redeyef', 'Métlaoui', 'Mdhilla', 'El Guettar', 'Belkhir', 'Sened'
            ],
            'TOZEUR' => [
                'Tozeur', 'Degache', 'Tameghza', 'Nefta', 'Hazoua'
            ],
            'KEBILI' => [
                'Kébili Sud', 'Kébili Nord', 'Souk Lahad', 'Douz Nord', 'Douz Sud', 'Faouar'
            ],
            'GABÈS' => [
                'Gabès Ville', 'Gabès Ouest', 'Gabès Sud', 'Ghannouch', 'El Metouia',
                'Menzel El Habib', 'Nouvelle Matmata', 'Matmata', 'Mareth', 'El Hamma'
            ],
            'MEDENINE' => [
                'Médenine Nord', 'Médenine Sud', 'Beni Khedache', 'Ben Gardane',
                'Zarzis', 'Houmt Souk', 'Midoun', 'Ajim', 'Sidi Makhlouf'
            ],
            'TATAOUINE' => [
                'Tataouine Nord', 'Tataouine Sud', 'Smâr', 'Bir Lahmar', 'Ghomrassen',
                'Dhehiba', 'Remada'
            ],
        ];

        foreach ($tunisianDelegations as $zone => $delegationNames) {
            foreach ($delegationNames as $name) {
                $delegations[] = [
                    'name' => $name,
                    'zone' => $zone,
                    'active' => true,
                    'created_by' => 1,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }
        }

        // Insert in batches of 50
        foreach (array_chunk($delegations, 50) as $chunk) {
            DB::table('delegations')->insert($chunk);
        }
    }

    private function seedClientProfiles(): void
    {
        DB::table('client_profiles')->truncate();
        
        $now = now();
        
        DB::table('client_profiles')->insert([
            'user_id' => 4,
            'shop_name' => 'Boutique Test',
            'fiscal_number' => '1234567A890',
            'business_sector' => 'E-commerce',
            'identity_document' => 'CIN_12345678.pdf',
            'offer_delivery_price' => 7.000,
            'offer_return_price' => 5.000,
            'internal_notes' => 'Client de test',
            'validation_status' => 'PENDING',
            'validated_by' => null,
            'validated_at' => null,
            'validation_notes' => null,
            'created_at' => $now,
            'updated_at' => $now
        ]);
    }
}