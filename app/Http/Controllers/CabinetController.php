<?php

namespace App\Http\Controllers;

use App\Models\Cabinet;
use App\Models\Rack;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CabinetController extends Controller
{
    /**
     * Display cabinets with racks, grouped by cabinet.
     */
    public function index()
    {
        $cabinets = Cabinet::with(['racks' => function ($q) {
            $q->withCount('slots')
              ->orderBy('rack_code');
        }])->orderBy('name')->get();

        return view('cabinets.index', compact('cabinets'));
    }

    /**
     * Store a newly created rack under a cabinet.
     */
    public function storeRack(Request $request)
    {
        $validated = $request->validate([
            'cabinet_name' => ['required', 'string', 'max:100'],
            'rack_code'    => ['required', 'string', 'max:50'],
        ]);

        // Find or create the cabinet
        $cabinet = Cabinet::firstOrCreate(
            ['name' => $validated['cabinet_name']],
        );

        // Check uniqueness of rack_code within this cabinet
        $exists = Rack::where('cabinet_id', $cabinet->id)
            ->where('rack_code', $validated['rack_code'])
            ->exists();

        if ($exists) {
            return redirect()
                ->route('settings.cabinets.index')
                ->with('error', "Rack '{$validated['rack_code']}' already exists in {$cabinet->name}.");
        }

        Rack::create([
            'cabinet_id' => $cabinet->id,
            'rack_code'  => $validated['rack_code'],
        ]);

        return redirect()
            ->route('settings.cabinets.index')
            ->with('success', 'Rack created successfully.');
    }

    /**
     * Update a rack.
     */
    public function updateRack(Request $request, Rack $rack)
    {
        $validated = $request->validate([
            'cabinet_name' => ['required', 'string', 'max:100'],
            'rack_code'    => ['required', 'string', 'max:50'],
        ]);

        $cabinet = Cabinet::firstOrCreate(
            ['name' => $validated['cabinet_name']],
        );

        // Check uniqueness (excluding current rack)
        $exists = Rack::where('cabinet_id', $cabinet->id)
            ->where('rack_code', $validated['rack_code'])
            ->where('id', '!=', $rack->id)
            ->exists();

        if ($exists) {
            return redirect()
                ->route('settings.cabinets.index')
                ->with('error', "Rack '{$validated['rack_code']}' already exists in {$cabinet->name}.");
        }

        $rack->update([
            'cabinet_id' => $cabinet->id,
            'rack_code'  => $validated['rack_code'],
        ]);

        return redirect()
            ->route('settings.cabinets.index')
            ->with('success', 'Rack updated successfully.');
    }

    /**
     * Delete a rack (only if no slots exist).
     */
    public function destroyRack(Rack $rack)
    {
        if ($rack->slots()->count() > 0) {
            return redirect()
                ->route('settings.cabinets.index')
                ->with('error', 'Cannot delete this rack because it contains slots.');
        }

        $rack->delete();

        // Clean up empty cabinets
        $cabinet = $rack->cabinet;
        if ($cabinet && $cabinet->racks()->count() === 0) {
            $cabinet->delete();
        }

        return redirect()
            ->route('settings.cabinets.index')
            ->with('success', 'Rack deleted successfully.');
    }
}
