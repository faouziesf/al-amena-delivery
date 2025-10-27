<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleMileageReading;
use App\Models\VehicleMaintenanceAlert;
use App\Services\ActionLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VehicleManagementController extends Controller
{
    protected $actionLogService;

    public function __construct(ActionLogService $actionLogService)
    {
        $this->actionLogService = $actionLogService;
    }

    // ==================== VÉHICULES ====================

    /**
     * Liste des véhicules
     */
    public function index()
    {
        $vehicles = Vehicle::with(['creator', 'mileageReadings' => function($query) {
            $query->latest()->limit(1);
        }])
        ->withCount(['maintenanceAlerts as unread_alerts_count' => function($query) {
            $query->where('is_read', false);
        }])
        ->orderBy('created_at', 'desc')
        ->paginate(20);

        $stats = [
            'total_vehicles' => Vehicle::count(),
            'active_vehicles' => Vehicle::active()->count(),
            'total_alerts' => VehicleMaintenanceAlert::unread()->count(),
            'critical_alerts' => VehicleMaintenanceAlert::unread()->critical()->count(),
        ];

        return view('supervisor.vehicles.index', compact('vehicles', 'stats'));
    }

    /**
     * Formulaire de création de véhicule
     */
    public function create()
    {
        return view('supervisor.vehicles.create');
    }

    /**
     * Enregistre un nouveau véhicule
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'registration_number' => 'nullable|string|max:255|unique:vehicles',
            'purchase_price' => 'required|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'max_depreciation_km' => 'required|integer|min:1',
            'current_km' => 'required|integer|min:0',
            'oil_change_cost' => 'required|numeric|min:0',
            'oil_change_interval_km' => 'required|integer|min:1',
            'last_oil_change_km' => 'required|integer|min:0',
            'spark_plug_cost' => 'required|numeric|min:0',
            'spark_plug_interval_km' => 'required|integer|min:1',
            'last_spark_plug_change_km' => 'required|integer|min:0',
            'tire_unit_cost' => 'required|numeric|min:0',
            'tire_change_interval_km' => 'required|integer|min:1',
            'last_tire_change_km' => 'required|integer|min:0',
            'fuel_price_per_liter' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $vehicle = Vehicle::create([
            'name' => $request->name,
            'registration_number' => $request->registration_number,
            'purchase_price' => $request->purchase_price,
            'purchase_date' => $request->purchase_date,
            'max_depreciation_km' => $request->max_depreciation_km,
            'current_km' => $request->current_km,
            'oil_change_cost' => $request->oil_change_cost,
            'oil_change_interval_km' => $request->oil_change_interval_km,
            'last_oil_change_km' => $request->last_oil_change_km,
            'spark_plug_cost' => $request->spark_plug_cost,
            'spark_plug_interval_km' => $request->spark_plug_interval_km,
            'last_spark_plug_change_km' => $request->last_spark_plug_change_km,
            'tire_unit_cost' => $request->tire_unit_cost,
            'tire_change_interval_km' => $request->tire_change_interval_km,
            'last_tire_change_km' => $request->last_tire_change_km,
            'fuel_price_per_liter' => $request->fuel_price_per_liter,
            'notes' => $request->notes,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => auth()->id(),
        ]);

        $this->actionLogService->logCreated('Vehicle', $vehicle->id, [
            'name' => $vehicle->name,
            'registration_number' => $vehicle->registration_number,
        ]);

        return redirect()->route('supervisor.vehicles.index')
            ->with('success', 'Véhicule créé avec succès.');
    }

    /**
     * Affiche un véhicule avec son historique
     */
    public function show(Vehicle $vehicle)
    {
        $vehicle->load(['creator', 'mileageReadings.creator', 'maintenanceAlerts' => function($query) {
            $query->orderBy('created_at', 'desc');
        }]);

        // Statistiques du véhicule
        $stats = [
            'total_km' => $vehicle->current_km,
            'avg_daily_km' => $vehicle->calculateAverageDailyKm(),
            'total_readings' => $vehicle->mileageReadings->count(),
            'depreciation_percentage' => $vehicle->max_depreciation_km > 0 
                ? round(($vehicle->current_km / $vehicle->max_depreciation_km) * 100, 2) 
                : 0,
            'cost_per_km' => $vehicle->total_cost_per_km,
        ];

        // Maintenance due
        $maintenanceDue = [
            'oil_change' => [
                'due' => $vehicle->isMaintenanceDue('oil'),
                'km_remaining' => $vehicle->km_until_oil_change,
            ],
            'spark_plugs' => [
                'due' => $vehicle->isMaintenanceDue('spark_plug'),
                'km_remaining' => $vehicle->km_until_spark_plug_change,
            ],
            'tires' => [
                'due' => $vehicle->isMaintenanceDue('tire'),
                'km_remaining' => $vehicle->km_until_tire_change,
            ],
        ];

        return view('supervisor.vehicles.show', compact('vehicle', 'stats', 'maintenanceDue'));
    }

    /**
     * Formulaire d'édition de véhicule
     */
    public function edit(Vehicle $vehicle)
    {
        return view('supervisor.vehicles.edit', compact('vehicle'));
    }

    /**
     * Met à jour un véhicule
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'registration_number' => 'nullable|string|max:255|unique:vehicles,registration_number,' . $vehicle->id,
            'purchase_price' => 'required|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'max_depreciation_km' => 'required|integer|min:1',
            'oil_change_cost' => 'required|numeric|min:0',
            'oil_change_interval_km' => 'required|integer|min:1',
            'spark_plug_cost' => 'required|numeric|min:0',
            'spark_plug_interval_km' => 'required|integer|min:1',
            'tire_unit_cost' => 'required|numeric|min:0',
            'tire_change_interval_km' => 'required|integer|min:1',
            'fuel_price_per_liter' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $oldData = $vehicle->only([
            'name', 'registration_number', 'purchase_price', 'max_depreciation_km',
            'oil_change_cost', 'spark_plug_cost', 'tire_unit_cost', 'fuel_price_per_liter'
        ]);

        $vehicle->update($request->only([
            'name', 'registration_number', 'purchase_price', 'purchase_date',
            'max_depreciation_km', 'oil_change_cost', 'oil_change_interval_km',
            'spark_plug_cost', 'spark_plug_interval_km', 'tire_unit_cost',
            'tire_change_interval_km', 'fuel_price_per_liter', 'notes', 'is_active'
        ]));

        $newData = $vehicle->only([
            'name', 'registration_number', 'purchase_price', 'max_depreciation_km',
            'oil_change_cost', 'spark_plug_cost', 'tire_unit_cost', 'fuel_price_per_liter'
        ]);

        $this->actionLogService->logUpdated('Vehicle', $vehicle->id, $oldData, $newData);

        return redirect()->route('supervisor.vehicles.show', $vehicle)
            ->with('success', 'Véhicule mis à jour avec succès.');
    }

    /**
     * Supprime un véhicule
     */
    public function destroy(Vehicle $vehicle)
    {
        $vehicleData = $vehicle->only(['name', 'registration_number']);
        $vehicle->delete();

        $this->actionLogService->logDeleted('Vehicle', $vehicle->id, $vehicleData);

        return redirect()->route('supervisor.vehicles.index')
            ->with('success', 'Véhicule supprimé avec succès.');
    }

    // ==================== RELEVÉS KILOMÉTRIQUES ====================

    /**
     * Formulaire d'ajout de relevé kilométrique
     */
    public function createReading(Vehicle $vehicle)
    {
        return view('supervisor.vehicles.readings.create', compact('vehicle'));
    }

    /**
     * Enregistre un nouveau relevé kilométrique
     */
    public function storeReading(Request $request, Vehicle $vehicle)
    {
        $validator = Validator::make($request->all(), [
            'mileage' => 'required|integer|min:' . $vehicle->current_km,
            'reading_date' => 'required|date',
            'fuel_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $reading = VehicleMileageReading::create([
            'vehicle_id' => $vehicle->id,
            'mileage' => $request->mileage,
            'reading_date' => $request->reading_date,
            'fuel_cost' => $request->fuel_cost,
            'notes' => $request->notes,
            'created_by' => auth()->id(),
        ]);

        $this->actionLogService->logCreated('VehicleMileageReading', $reading->id, [
            'vehicle_id' => $vehicle->id,
            'vehicle_name' => $vehicle->name,
            'mileage' => $reading->mileage,
        ]);

        return redirect()->route('supervisor.vehicles.show', $vehicle)
            ->with('success', 'Relevé kilométrique enregistré avec succès.');
    }

    /**
     * Historique des relevés kilométriques
     */
    public function readingsHistory(Vehicle $vehicle)
    {
        $readings = $vehicle->mileageReadings()
            ->with('creator')
            ->orderBy('reading_date', 'desc')
            ->paginate(50);

        return view('supervisor.vehicles.readings.history', compact('vehicle', 'readings'));
    }

    // ==================== ALERTES MAINTENANCE ====================

    /**
     * Liste de toutes les alertes de maintenance
     */
    public function alerts()
    {
        $alerts = VehicleMaintenanceAlert::with('vehicle')
            ->orderBy('is_read', 'asc')
            ->orderBy('severity', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $stats = [
            'total_unread' => VehicleMaintenanceAlert::unread()->count(),
            'critical' => VehicleMaintenanceAlert::unread()->critical()->count(),
            'warning' => VehicleMaintenanceAlert::unread()->warning()->count(),
        ];

        return view('supervisor.vehicles.alerts.index', compact('alerts', 'stats'));
    }

    /**
     * Marque une alerte comme lue
     */
    public function markAlertRead(VehicleMaintenanceAlert $alert)
    {
        $alert->markAsRead(auth()->id());

        return redirect()->back()
            ->with('success', 'Alerte marquée comme lue.');
    }

    /**
     * Marque toutes les alertes d'un véhicule comme lues
     */
    public function markVehicleAlertsRead(Vehicle $vehicle)
    {
        $vehicle->maintenanceAlerts()
            ->unread()
            ->each(function($alert) {
                $alert->markAsRead(auth()->id());
            });

        return redirect()->back()
            ->with('success', 'Toutes les alertes du véhicule ont été marquées comme lues.');
    }

    /**
     * Enregistre une maintenance effectuée
     */
    public function recordMaintenance(Request $request, Vehicle $vehicle)
    {
        $validator = Validator::make($request->all(), [
            'maintenance_type' => 'required|in:oil_change,spark_plugs,tires',
            'current_km' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $oldData = [];
        $newData = [];

        switch ($request->maintenance_type) {
            case 'oil_change':
                $oldData['last_oil_change_km'] = $vehicle->last_oil_change_km;
                $vehicle->update(['last_oil_change_km' => $request->current_km]);
                $newData['last_oil_change_km'] = $request->current_km;
                break;
            
            case 'spark_plugs':
                $oldData['last_spark_plug_change_km'] = $vehicle->last_spark_plug_change_km;
                $vehicle->update(['last_spark_plug_change_km' => $request->current_km]);
                $newData['last_spark_plug_change_km'] = $request->current_km;
                break;
            
            case 'tires':
                $oldData['last_tire_change_km'] = $vehicle->last_tire_change_km;
                $vehicle->update(['last_tire_change_km' => $request->current_km]);
                $newData['last_tire_change_km'] = $request->current_km;
                break;
        }

        // Marquer les alertes correspondantes comme lues
        $vehicle->maintenanceAlerts()
            ->where('alert_type', strtoupper($request->maintenance_type))
            ->unread()
            ->each(function($alert) {
                $alert->markAsRead(auth()->id());
            });

        $this->actionLogService->logUpdated('Vehicle', $vehicle->id, $oldData, $newData);

        return redirect()->route('supervisor.vehicles.show', $vehicle)
            ->with('success', 'Maintenance enregistrée avec succès.');
    }

    // ==================== API ====================

    /**
     * API: Récupère les statistiques d'un véhicule
     */
    public function apiVehicleStats(Vehicle $vehicle)
    {
        return response()->json([
            'vehicle' => [
                'id' => $vehicle->id,
                'name' => $vehicle->name,
                'current_km' => $vehicle->current_km,
                'avg_daily_km' => $vehicle->calculateAverageDailyKm(),
                'cost_per_km' => $vehicle->total_cost_per_km,
            ],
            'maintenance' => [
                'oil_change' => [
                    'km_remaining' => $vehicle->km_until_oil_change,
                    'due' => $vehicle->isMaintenanceDue('oil'),
                ],
                'spark_plugs' => [
                    'km_remaining' => $vehicle->km_until_spark_plug_change,
                    'due' => $vehicle->isMaintenanceDue('spark_plug'),
                ],
                'tires' => [
                    'km_remaining' => $vehicle->km_until_tire_change,
                    'due' => $vehicle->isMaintenanceDue('tire'),
                ],
            ],
            'alerts' => [
                'total' => $vehicle->maintenanceAlerts()->unread()->count(),
                'critical' => $vehicle->maintenanceAlerts()->unread()->critical()->count(),
            ],
        ]);
    }
}
