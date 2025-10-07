<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TunisiaAdministrativeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // On vérifie si l'utilisateur avec l'ID 1 existe avant de continuer.
        if (!DB::table('users')->where('id', 1)->exists()) {
            $this->command->error('User with ID 1 not found. Please make sure you have a user with ID 1 to act as creator.');
            return;
        }

        Schema::disableForeignKeyConstraints();
        DB::table('delegations')->truncate();
        
        $data = [
            'Ariana' => ['Ariana Ville', 'Ettadhamen', 'Kalaat el-Andaleus', 'La Soukra', 'Mnihla', 'Raoued', 'Sidi Thabet'],
            'Béja' => ['Amdoun', 'Béja Nord', 'Béja Sud', 'Goubellat', 'Medjez el-Bab', 'Nefza', 'Téboursouk', 'Testour', 'Thibar'],
            'Ben Arous' => ['Ben Arous', 'Bou Mhel el-Bassatine', 'El Mourouj', 'Ezzahra', 'Fouchana', 'Hammam Chott', 'Hammam Lif', 'Mohamedia', 'Medina Jedida', 'Mégrine', 'Mornag', 'Radès'],
            'Bizerte' => ['Bizerte Nord', 'Bizerte Sud', 'El Alia', 'Ghar El Melh', 'Ghezala', 'Joumine', 'Mateur', 'Menzel Bourguiba', 'Menzel Jemil', 'Ras Jebel', 'Sejnane', 'Tinja', 'Utique', 'Zarzouna'],
            'Gabès' => ['Gabès Médina', 'Gabès Ouest', 'Gabès Sud', 'Ghannouch', 'El Hamma', 'Matmata', 'Mareth', 'Menzel El Habib', 'Métouia', 'Nouvelle Matmata'],
            'Gafsa' => ['Belkhir', 'El Guettar', 'Gafsa Nord', 'Gafsa Sud', 'Mdhilla', 'Métlaoui', 'Moularès', 'Redeyef', 'Sened', 'Sidi Aïch', 'El Ksar'],
            'Jendouba' => ['Aïn Draham', 'Balta-Bou Aouane', 'Bou Salem', 'Fernana', 'Ghardimaou', 'Jendouba Sud', 'Jendouba Nord', 'Oued Meliz', 'Tabarka'],
            'Kairouan' => ['Alaâ', 'Bou Hajla', 'Chebika', 'Echrarda', 'Haffouz', 'Hajeb El Ayoun', 'Kairouan Nord', 'Kairouan Sud', 'Nasrallah', 'Oueslatia', 'Sbikha', 'Chrarda'],
            'Kasserine' => ['El Ayoun', 'Ezzouhour', 'Fériana', 'Foussana', 'Haïdra', 'Hassi El Ferid', 'Jedelienne', 'Kasserine Nord', 'Kasserine Sud', 'Majel Bel Abbès', 'Sbeïtla', 'Sbiba', 'Thala'],
            'Kébili' => ['Douz Nord', 'Douz Sud', 'Faouar', 'Kébili Nord', 'Kébili Sud', 'Souk Lahad'],
            'Le Kef' => ['Dahmani', 'El Ksour', 'Jérissa', 'Kalaat Senan', 'Kef Est', 'Kef Ouest', 'Nebeur', 'Sakiet Sidi Youssef', 'Sers', 'Tajerouine', 'Touiref', 'Kallat Khasba'],
            'Mahdia' => ['Bou Merdes', 'Chebba', 'Chorbane', 'El Djem', 'Essouassi', 'Hebira', 'Ksour Essef', 'Mahdia', 'Melloulèche', 'Ouled Chamekh', 'Sidi Alouane', 'Rejiche'],
            'La Manouba' => ['Borj El Amri', 'Douar Hicher', 'El Batan', 'Jedaida', 'Manouba', 'Mornaguia', 'Oued Ellil', 'Tebourba'],
            'Médenine' => ['Ben Gardane', 'Beni Khedache', 'Djerba - Ajim', 'Djerba - Houmt Souk', 'Djerba - Midoun', 'Médenine Nord', 'Médenine Sud', 'Sidi Makhlouf', 'Zarzis'],
            'Monastir' => ['Bekalta', 'Bembla', 'Beni Hassen', 'Jemmal', 'Ksar Hellal', 'Ksibet el-Médiouni', 'Moknine', 'Monastir', 'Ouerdanine', 'Sahline', 'Sayada-Lamta-Bou Hajar', 'Téboulba', 'Zéramdine'],
            'Nabeul' => ['Béni Khalled', 'Béni Khiar', 'Bou Argoub', 'Dar Chaâbane El Fehri', 'El Haouaria', 'El Mida', 'Grombalia', 'Hammam Ghezèze', 'Hammamet', 'Kélibia', 'Korba', 'Menzel Bouzelfa', 'Menzel Temime', 'Nabeul', 'Soliman', 'Takelsa'],
            'Sfax' => ['Agareb', 'Bir Ali Ben Khalifa', 'El Amra', 'El Hencha', 'Graïba', 'Jebiniana', 'Kerkennah', 'Mahrès', 'Menzel Chaker', 'Sfax Ville', 'Sfax Ouest', 'Sfax Sud', 'Sakiet Eddaïer', 'Sakiet Ezzit', 'Skhira', 'Thyna'],
            'Sidi Bouzid' => ['Bir El Hafey', 'Cebbala Ouled Asker', 'Jilma', 'Meknassy', 'Menzel Bouzaiane', 'Mezzouna', 'Ouled Haffouz', 'Regueb', 'Sidi Ali Ben Aoun', 'Sidi Bouzid Est', 'Sidi Bouzid Ouest', 'Souk Jedid'],
            'Siliana' => ['Bargou', 'Bou Arada', 'El Aroussa', 'Gaâfour', 'Kesra', 'Makthar', 'Rohia', 'Sidi Bou Rouis', 'Siliana Nord', 'Siliana Sud', 'El Krib'],
            'Sousse' => ['Akouda', 'Bouficha', 'Enfidha', 'Hammam Sousse', 'Hergla', 'Kalaa El Kebira', 'Kalaa Seghira', 'Kondar', 'Msaken', 'Sidi Bou Ali', 'Sidi El Hani', 'Sousse Jawhara', 'Sousse Médina', 'Sousse Riadh', 'Sousse Sidi Abdelhamid'],
            'Tataouine' => ['Bir Lahmar', 'Dehiba', 'Ghomrassen', 'Remada', 'Smâr', 'Tataouine Nord', 'Tataouine Sud'],
            'Tozeur' => ['Degache', 'Hazoua', 'Nefta', 'Tameghza', 'Tozeur'],
            'Tunis' => ['Bab El Bhar', 'Bab Souika', 'Carthage', 'Cité El Khadra', 'Djebel Jelloud', 'El Kabaria', 'El Kram', 'El Menzah', 'El Omrane', 'El Omrane Supérieur', 'El Ouardia', 'Ettahrir', 'Ezzouhour', 'Hraïria', 'La Goulette', 'La Marsa', 'Le Bardo', 'Médina', 'Sidi El Béchir', 'Sidi Hassine', 'Séjoumi'],
            'Zaghouan' => ['Bir Mcherga', 'El Fahs', 'Nadhour', 'Saouaf', 'Zaghouan', 'Hammam Zriba'],
        ];

        $allDelegations = [];
        foreach ($data as $gouvernoratName => $delegations) {
            foreach ($delegations as $delegationName) {
                $allDelegations[] = [
                    'name' => $delegationName,
                    'zone' => $gouvernoratName,
                    'active' => true,
                    'created_by' => 1, // **CORRECTION : Ajout de l'ID de l'utilisateur créateur**
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('delegations')->insert($allDelegations);

        Schema::enableForeignKeyConstraints();
    }
}