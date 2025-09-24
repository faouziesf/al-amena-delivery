<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SystemController extends Controller
{
    public function overview()
    {
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'memory_usage' => memory_get_usage(true),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'database_connection' => config('database.default'),
        ];

        $diskUsage = [
            'total' => disk_total_space(base_path()),
            'free' => disk_free_space(base_path()),
        ];

        $diskUsage['used'] = $diskUsage['total'] - $diskUsage['free'];
        $diskUsage['percentage'] = ($diskUsage['used'] / $diskUsage['total']) * 100;

        $databaseStats = [
            'users_count' => DB::table('users')->count(),
            'packages_count' => DB::table('packages')->count(),
            'complaints_count' => DB::table('complaints')->count(),
            'transactions_count' => DB::table('transactions')->count(),
        ];

        $queueStats = [
            'pending_jobs' => DB::table('jobs')->count(),
            'failed_jobs' => DB::table('failed_jobs')->count(),
        ];

        return view('supervisor.system.overview', compact(
            'systemInfo',
            'diskUsage',
            'databaseStats',
            'queueStats'
        ));
    }

    public function logs(Request $request)
    {
        $logFiles = [
            'laravel' => storage_path('logs/laravel.log'),
            'emergency' => storage_path('logs/emergency.log'),
            'critical' => storage_path('logs/critical.log'),
        ];

        $selectedLog = $request->get('log', 'laravel');
        $logContent = '';

        if (isset($logFiles[$selectedLog]) && File::exists($logFiles[$selectedLog])) {
            $content = File::get($logFiles[$selectedLog]);
            $lines = explode("\n", $content);
            $logContent = implode("\n", array_slice($lines, -1000)); // Dernières 1000 lignes
        }

        return view('supervisor.system.logs', compact('logFiles', 'selectedLog', 'logContent'));
    }

    public function maintenance()
    {
        $isMaintenanceMode = app()->isDownForMaintenance();

        return view('supervisor.system.maintenance', compact('isMaintenanceMode'));
    }

    public function enableMaintenance(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string|max:255',
            'retry' => 'nullable|integer|min:60',
        ]);

        $options = [
            '--render' => 'maintenance.offline',
        ];

        if ($request->message) {
            $options['--message'] = $request->message;
        }

        if ($request->retry) {
            $options['--retry'] = $request->retry;
        }

        Artisan::call('down', $options);

        return back()->with('success', 'Mode maintenance activé.');
    }

    public function disableMaintenance()
    {
        Artisan::call('up');
        return back()->with('success', 'Mode maintenance désactivé.');
    }

    public function backup()
    {
        $backups = collect(Storage::disk('local')->files('backups'))
                  ->filter(function ($file) {
                      return str_ends_with($file, '.sql');
                  })
                  ->map(function ($file) {
                      return [
                          'name' => basename($file),
                          'path' => $file,
                          'size' => Storage::disk('local')->size($file),
                          'date' => Storage::disk('local')->lastModified($file),
                      ];
                  })
                  ->sortByDesc('date');

        return view('supervisor.system.backup', compact('backups'));
    }

    public function createBackup()
    {
        try {
            $filename = 'backup_' . now()->format('Y_m_d_H_i_s') . '.sql';

            // Utiliser mysqldump pour créer le backup
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s --port=%s %s > %s',
                config('database.connections.mysql.username'),
                config('database.connections.mysql.password'),
                config('database.connections.mysql.host'),
                config('database.connections.mysql.port'),
                config('database.connections.mysql.database'),
                storage_path('app/backups/' . $filename)
            );

            // Créer le dossier backups s'il n'existe pas
            if (!Storage::disk('local')->exists('backups')) {
                Storage::disk('local')->makeDirectory('backups');
            }

            exec($command, $output, $returnCode);

            if ($returnCode === 0) {
                return back()->with('success', 'Backup créé avec succès: ' . $filename);
            } else {
                return back()->with('error', 'Erreur lors de la création du backup.');
            }
        } catch (\Exception $e) {
            Log::error('Backup creation failed: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la création du backup: ' . $e->getMessage());
        }
    }

    public function restoreBackup(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|string',
        ]);

        try {
            $backupPath = storage_path('app/' . $request->backup_file);

            if (!File::exists($backupPath)) {
                return back()->with('error', 'Fichier de backup introuvable.');
            }

            // Utiliser mysql pour restaurer le backup
            $command = sprintf(
                'mysql --user=%s --password=%s --host=%s --port=%s %s < %s',
                config('database.connections.mysql.username'),
                config('database.connections.mysql.password'),
                config('database.connections.mysql.host'),
                config('database.connections.mysql.port'),
                config('database.connections.mysql.database'),
                $backupPath
            );

            exec($command, $output, $returnCode);

            if ($returnCode === 0) {
                return back()->with('success', 'Base de données restaurée avec succès.');
            } else {
                return back()->with('error', 'Erreur lors de la restauration.');
            }
        } catch (\Exception $e) {
            Log::error('Backup restoration failed: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la restauration: ' . $e->getMessage());
        }
    }

    public function deleteBackup($backup)
    {
        $backupPath = 'backups/' . basename($backup);

        if (Storage::disk('local')->exists($backupPath)) {
            Storage::disk('local')->delete($backupPath);
            return back()->with('success', 'Backup supprimé avec succès.');
        }

        return back()->with('error', 'Backup introuvable.');
    }

    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            return back()->with('success', 'Cache vidé avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors du vidage du cache: ' . $e->getMessage());
        }
    }

    public function optimizeCache()
    {
        try {
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');

            return back()->with('success', 'Cache optimisé avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'optimisation du cache: ' . $e->getMessage());
        }
    }

    public function optimizeDatabase()
    {
        try {
            // Optimiser toutes les tables
            $tables = DB::select('SHOW TABLES');
            $database = config('database.connections.mysql.database');

            foreach ($tables as $table) {
                $tableName = $table->{"Tables_in_$database"};
                DB::statement("OPTIMIZE TABLE `$tableName`");
            }

            return back()->with('success', 'Base de données optimisée avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'optimisation: ' . $e->getMessage());
        }
    }

    public function runMigrations()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            return back()->with('success', 'Migrations exécutées avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors des migrations: ' . $e->getMessage());
        }
    }

    public function activities(Request $request)
    {
        $activities = collect(); // Sera remplacé par un vrai système d'audit plus tard

        return view('supervisor.system.activities', compact('activities'));
    }

    public function transactionLogs(Request $request)
    {
        $transactions = DB::table('transactions')
                         ->join('users', 'transactions.user_id', '=', 'users.id')
                         ->select('transactions.*', 'users.name as user_name')
                         ->orderBy('transactions.created_at', 'desc')
                         ->paginate(50);

        return view('supervisor.system.transaction-logs', compact('transactions'));
    }

    public function loginLogs(Request $request)
    {
        // Cette fonctionnalité nécessiterait un middleware pour enregistrer les connexions
        $loginLogs = collect();

        return view('supervisor.system.login-logs', compact('loginLogs'));
    }

    public function errorLogs(Request $request)
    {
        $errorLog = storage_path('logs/laravel.log');
        $errors = collect();

        if (File::exists($errorLog)) {
            $content = File::get($errorLog);
            $lines = explode("\n", $content);

            // Parser les erreurs (version simplifiée)
            $currentError = null;
            foreach (array_reverse($lines) as $line) {
                if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] local\.ERROR:/', $line, $matches)) {
                    if ($currentError) {
                        $errors->push($currentError);
                    }
                    $currentError = [
                        'timestamp' => $matches[1],
                        'message' => $line,
                        'stack' => [],
                    ];
                } elseif ($currentError && $line) {
                    $currentError['stack'][] = $line;
                }

                if ($errors->count() >= 100) break; // Limiter à 100 erreurs
            }

            if ($currentError) {
                $errors->push($currentError);
            }
        }

        return view('supervisor.system.error-logs', compact('errors'));
    }

    public function exportActivities()
    {
        return back()->with('info', 'Export des activités en cours de développement.');
    }

    public function exportTransactions()
    {
        return back()->with('info', 'Export des transactions en cours de développement.');
    }

    // API Methods
    public function apiHealth()
    {
        $health = [
            'status' => 'healthy',
            'checks' => [
                'database' => $this->checkDatabase(),
                'cache' => $this->checkCache(),
                'storage' => $this->checkStorage(),
                'queue' => $this->checkQueue(),
            ]
        ];

        $overallStatus = collect($health['checks'])->every(function ($check) {
            return $check['status'] === 'ok';
        }) ? 'healthy' : 'unhealthy';

        $health['status'] = $overallStatus;

        return response()->json($health);
    }

    public function apiPerformance()
    {
        return response()->json([
            'memory_usage' => [
                'current' => memory_get_usage(true),
                'peak' => memory_get_peak_usage(true),
                'limit' => $this->parseSize(ini_get('memory_limit')),
            ],
            'response_time' => [
                'avg' => rand(50, 200), // Simulé
                'p95' => rand(100, 300), // Simulé
                'p99' => rand(200, 500), // Simulé
            ],
            'database' => [
                'queries_count' => DB::getQueryLog() ? count(DB::getQueryLog()) : 0,
                'slow_queries' => 0, // À implémenter
            ]
        ]);
    }

    public function apiStorage()
    {
        $total = disk_total_space(base_path());
        $free = disk_free_space(base_path());
        $used = $total - $free;

        return response()->json([
            'disk' => [
                'total' => $total,
                'used' => $used,
                'free' => $free,
                'percentage' => ($used / $total) * 100,
            ],
            'storage' => [
                'logs_size' => $this->getDirectorySize(storage_path('logs')),
                'uploads_size' => $this->getDirectorySize(storage_path('app/public')),
                'cache_size' => $this->getDirectorySize(storage_path('framework/cache')),
            ]
        ]);
    }

    private function checkDatabase()
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'ok', 'message' => 'Database connection successful'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkCache()
    {
        try {
            Cache::put('health_check', 'test', 10);
            $value = Cache::get('health_check');
            return ['status' => $value === 'test' ? 'ok' : 'error', 'message' => 'Cache working'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkStorage()
    {
        try {
            Storage::put('health_check.txt', 'test');
            $exists = Storage::exists('health_check.txt');
            Storage::delete('health_check.txt');
            return ['status' => $exists ? 'ok' : 'error', 'message' => 'Storage working'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkQueue()
    {
        try {
            $failedJobs = DB::table('failed_jobs')->count();
            return [
                'status' => 'ok',
                'message' => "Queue working, {$failedJobs} failed jobs"
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function parseSize($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
        $size = preg_replace('/[^0-9\.]/', '', $size);

        if ($unit) {
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        }

        return round($size);
    }

    private function getDirectorySize($directory)
    {
        if (!is_dir($directory)) {
            return 0;
        }

        $size = 0;
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        );

        foreach ($files as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size;
    }
}