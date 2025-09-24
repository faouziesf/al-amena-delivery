<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class SettingsController extends Controller
{
    protected $settingsFile;

    public function __construct()
    {
        $this->settingsFile = storage_path('app/system_settings.json');
    }

    public function index()
    {
        $categories = [
            'general' => [
                'title' => 'Paramètres Généraux',
                'description' => 'Configuration de base du système',
                'icon' => 'cog',
                'url' => route('supervisor.settings.general'),
            ],
            'financial' => [
                'title' => 'Paramètres Financiers',
                'description' => 'Frais, tarifs, et configuration des paiements',
                'icon' => 'dollar-sign',
                'url' => route('supervisor.settings.financial'),
            ],
            'delivery' => [
                'title' => 'Paramètres de Livraison',
                'description' => 'Zones, délais, et règles de livraison',
                'icon' => 'truck',
                'url' => route('supervisor.settings.delivery'),
            ],
            'notifications' => [
                'title' => 'Paramètres de Notifications',
                'description' => 'Email, SMS, et notifications push',
                'icon' => 'bell',
                'url' => route('supervisor.settings.notifications'),
            ],
            'security' => [
                'title' => 'Paramètres de Sécurité',
                'description' => 'Authentification, sessions, et sécurité',
                'icon' => 'shield',
                'url' => route('supervisor.settings.security'),
            ],
        ];

        return view('supervisor.settings.index', compact('categories'));
    }

    public function general()
    {
        $settings = $this->getSettings();

        $generalSettings = $settings['general'] ?? [
            'app_name' => 'Al-Amena Delivery',
            'app_description' => 'Système de livraison intelligent',
            'timezone' => 'Africa/Tunis',
            'language' => 'fr',
            'maintenance_message' => 'Le système est temporairement en maintenance.',
            'max_file_upload_size' => '10MB',
            'items_per_page' => 20,
        ];

        return view('supervisor.settings.general', compact('generalSettings'));
    }

    public function updateGeneral(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_description' => 'nullable|string|max:500',
            'timezone' => 'required|string|max:50',
            'language' => 'required|in:fr,en,ar',
            'maintenance_message' => 'nullable|string|max:255',
            'max_file_upload_size' => 'required|string',
            'items_per_page' => 'required|integer|min:10|max:100',
        ]);

        $settings = $this->getSettings();
        $settings['general'] = $request->only([
            'app_name',
            'app_description',
            'timezone',
            'language',
            'maintenance_message',
            'max_file_upload_size',
            'items_per_page',
        ]);

        $this->saveSettings($settings);

        return back()->with('success', 'Paramètres généraux mis à jour avec succès.');
    }

    public function financial()
    {
        $settings = $this->getSettings();

        $financialSettings = $settings['financial'] ?? [
            'delivery_fee_fast' => 7.000,
            'delivery_fee_advanced' => 5.000,
            'cod_commission_rate' => 2.5,
            'min_cod_amount' => 1.000,
            'max_cod_amount' => 5000.000,
            'wallet_min_balance' => 0.000,
            'deliverer_commission_rate' => 1.000,
            'auto_payment_threshold' => 100.000,
            'currency' => 'TND',
            'currency_symbol' => 'د.ت',
        ];

        return view('supervisor.settings.financial', compact('financialSettings'));
    }

    public function updateFinancial(Request $request)
    {
        $request->validate([
            'delivery_fee_fast' => 'required|numeric|min:0',
            'delivery_fee_advanced' => 'required|numeric|min:0',
            'cod_commission_rate' => 'required|numeric|min:0|max:100',
            'min_cod_amount' => 'required|numeric|min:0',
            'max_cod_amount' => 'required|numeric|min:0',
            'wallet_min_balance' => 'required|numeric',
            'deliverer_commission_rate' => 'required|numeric|min:0',
            'auto_payment_threshold' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'currency_symbol' => 'required|string|max:5',
        ]);

        $settings = $this->getSettings();
        $settings['financial'] = $request->only([
            'delivery_fee_fast',
            'delivery_fee_advanced',
            'cod_commission_rate',
            'min_cod_amount',
            'max_cod_amount',
            'wallet_min_balance',
            'deliverer_commission_rate',
            'auto_payment_threshold',
            'currency',
            'currency_symbol',
        ]);

        $this->saveSettings($settings);

        return back()->with('success', 'Paramètres financiers mis à jour avec succès.');
    }

    public function delivery()
    {
        $settings = $this->getSettings();

        $deliverySettings = $settings['delivery'] ?? [
            'max_delivery_attempts' => 3,
            'default_delivery_time' => 24,
            'pickup_time_limit' => 2,
            'return_after_days' => 7,
            'auto_assign_packages' => true,
            'allow_weekend_delivery' => true,
            'delivery_start_hour' => 8,
            'delivery_end_hour' => 18,
            'tracking_update_interval' => 15,
            'sms_notifications' => true,
        ];

        return view('supervisor.settings.delivery', compact('deliverySettings'));
    }

    public function updateDelivery(Request $request)
    {
        $request->validate([
            'max_delivery_attempts' => 'required|integer|min:1|max:10',
            'default_delivery_time' => 'required|integer|min:1',
            'pickup_time_limit' => 'required|integer|min:1',
            'return_after_days' => 'required|integer|min:1',
            'auto_assign_packages' => 'boolean',
            'allow_weekend_delivery' => 'boolean',
            'delivery_start_hour' => 'required|integer|min:0|max:23',
            'delivery_end_hour' => 'required|integer|min:0|max:23',
            'tracking_update_interval' => 'required|integer|min:5|max:60',
            'sms_notifications' => 'boolean',
        ]);

        $settings = $this->getSettings();
        $settings['delivery'] = [
            'max_delivery_attempts' => $request->max_delivery_attempts,
            'default_delivery_time' => $request->default_delivery_time,
            'pickup_time_limit' => $request->pickup_time_limit,
            'return_after_days' => $request->return_after_days,
            'auto_assign_packages' => $request->boolean('auto_assign_packages'),
            'allow_weekend_delivery' => $request->boolean('allow_weekend_delivery'),
            'delivery_start_hour' => $request->delivery_start_hour,
            'delivery_end_hour' => $request->delivery_end_hour,
            'tracking_update_interval' => $request->tracking_update_interval,
            'sms_notifications' => $request->boolean('sms_notifications'),
        ];

        $this->saveSettings($settings);

        return back()->with('success', 'Paramètres de livraison mis à jour avec succès.');
    }

    public function notifications()
    {
        $settings = $this->getSettings();

        $notificationSettings = $settings['notifications'] ?? [
            'email_notifications' => true,
            'sms_notifications' => true,
            'push_notifications' => true,
            'admin_email' => 'admin@al-amena.com',
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'smtp_username' => '',
            'smtp_password' => '',
            'smtp_encryption' => 'tls',
            'sms_provider' => 'twilio',
            'sms_api_key' => '',
            'sms_sender_name' => 'Al-Amena',
            'notification_sound' => true,
        ];

        return view('supervisor.settings.notifications', compact('notificationSettings'));
    }

    public function updateNotifications(Request $request)
    {
        $request->validate([
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'admin_email' => 'required|email',
            'smtp_host' => 'nullable|string',
            'smtp_port' => 'nullable|integer',
            'smtp_username' => 'nullable|string',
            'smtp_password' => 'nullable|string',
            'smtp_encryption' => 'nullable|in:tls,ssl',
            'sms_provider' => 'nullable|string',
            'sms_api_key' => 'nullable|string',
            'sms_sender_name' => 'nullable|string|max:11',
            'notification_sound' => 'boolean',
        ]);

        $settings = $this->getSettings();
        $settings['notifications'] = [
            'email_notifications' => $request->boolean('email_notifications'),
            'sms_notifications' => $request->boolean('sms_notifications'),
            'push_notifications' => $request->boolean('push_notifications'),
            'admin_email' => $request->admin_email,
            'smtp_host' => $request->smtp_host,
            'smtp_port' => $request->smtp_port,
            'smtp_username' => $request->smtp_username,
            'smtp_password' => $request->smtp_password,
            'smtp_encryption' => $request->smtp_encryption,
            'sms_provider' => $request->sms_provider,
            'sms_api_key' => $request->sms_api_key,
            'sms_sender_name' => $request->sms_sender_name,
            'notification_sound' => $request->boolean('notification_sound'),
        ];

        $this->saveSettings($settings);

        return back()->with('success', 'Paramètres de notifications mis à jour avec succès.');
    }

    public function security()
    {
        $settings = $this->getSettings();

        $securitySettings = $settings['security'] ?? [
            'session_timeout' => 120,
            'max_login_attempts' => 5,
            'lockout_duration' => 15,
            'password_min_length' => 8,
            'require_password_confirmation' => true,
            'force_https' => false,
            'two_factor_auth' => false,
            'api_rate_limit' => 60,
            'backup_frequency' => 'daily',
            'log_retention_days' => 30,
        ];

        return view('supervisor.settings.security', compact('securitySettings'));
    }

    public function updateSecurity(Request $request)
    {
        $request->validate([
            'session_timeout' => 'required|integer|min:15|max:480',
            'max_login_attempts' => 'required|integer|min:3|max:20',
            'lockout_duration' => 'required|integer|min:5|max:60',
            'password_min_length' => 'required|integer|min:6|max:50',
            'require_password_confirmation' => 'boolean',
            'force_https' => 'boolean',
            'two_factor_auth' => 'boolean',
            'api_rate_limit' => 'required|integer|min:10|max:1000',
            'backup_frequency' => 'required|in:daily,weekly,monthly',
            'log_retention_days' => 'required|integer|min:7|max:365',
        ]);

        $settings = $this->getSettings();
        $settings['security'] = [
            'session_timeout' => $request->session_timeout,
            'max_login_attempts' => $request->max_login_attempts,
            'lockout_duration' => $request->lockout_duration,
            'password_min_length' => $request->password_min_length,
            'require_password_confirmation' => $request->boolean('require_password_confirmation'),
            'force_https' => $request->boolean('force_https'),
            'two_factor_auth' => $request->boolean('two_factor_auth'),
            'api_rate_limit' => $request->api_rate_limit,
            'backup_frequency' => $request->backup_frequency,
            'log_retention_days' => $request->log_retention_days,
        ];

        $this->saveSettings($settings);

        return back()->with('success', 'Paramètres de sécurité mis à jour avec succès.');
    }

    protected function getSettings()
    {
        if (!File::exists($this->settingsFile)) {
            return [];
        }

        $content = File::get($this->settingsFile);
        return json_decode($content, true) ?? [];
    }

    protected function saveSettings($settings)
    {
        File::put($this->settingsFile, json_encode($settings, JSON_PRETTY_PRINT));

        // Invalider le cache des paramètres
        Cache::forget('system_settings');
    }

    public static function getSetting($category, $key, $default = null)
    {
        $settings = Cache::remember('system_settings', 3600, function () {
            $settingsFile = storage_path('app/system_settings.json');

            if (!File::exists($settingsFile)) {
                return [];
            }

            $content = File::get($settingsFile);
            return json_decode($content, true) ?? [];
        });

        return data_get($settings, "{$category}.{$key}", $default);
    }
}