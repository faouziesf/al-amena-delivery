<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Delegation;

class DelegationController extends Controller
{
    public function index(Request $request)
    {
        $query = Delegation::query();

        // Filtres de recherche
        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
        }

        if ($request->gouvernorat) {
            $query->where('gouvernorat', $request->gouvernorat);
        }

        if ($request->status !== null && $request->status !== '') {
            $query->where('active', (bool) $request->status);
        }

        $delegations = $query->orderBy('name', 'asc')->paginate(20);

        return view('supervisor.delegations.index', compact('delegations'));
    }

    public function create()
    {
        return view('supervisor.delegations.create');
    }

    public function store(Request $request)
    {
        return redirect()->route('supervisor.delegations.index');
    }

    public function show($delegation)
    {
        return view('supervisor.delegations.show');
    }

    public function edit($delegation)
    {
        return view('supervisor.delegations.edit');
    }

    public function update(Request $request, $delegation)
    {
        return redirect()->route('supervisor.delegations.index');
    }

    public function destroy($delegation)
    {
        return redirect()->route('supervisor.delegations.index');
    }

    public function bulkActivate(Request $request)
    {
        return response()->json(['success' => true]);
    }

    public function bulkDeactivate(Request $request)
    {
        return response()->json(['success' => true]);
    }

    public function export()
    {
        return response()->json(['success' => true]);
    }

    public function importTemplate()
    {
        return response()->json(['success' => true]);
    }

    public function import(Request $request)
    {
        return response()->json(['success' => true]);
    }

    public function apiSearch(Request $request)
    {
        return response()->json(['data' => []]);
    }

    public function apiStats()
    {
        return response()->json(['stats' => []]);
    }
}