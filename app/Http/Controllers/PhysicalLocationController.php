<?php

namespace App\Http\Controllers;

use App\Http\Requests\PhysicalLocationRequest;
use App\Models\PhysicalLocation;
use Illuminate\Http\Request;

class PhysicalLocationController extends Controller
{
    /**
     * Display physical locations grouped by cabinet.
     */
    public function index()
    {
        $locations = PhysicalLocation::withCount('documents')
            ->orderBy('cabinet_id')
            ->orderBy('rack_id')
            ->get()
            ->groupBy('cabinet_id');

        return view('physical-locations.index', compact('locations'));
    }

    /**
     * Show the form for creating a new physical location.
     */
    public function create()
    {
        return view('physical-locations.create');
    }

    /**
     * Store a newly created physical location in storage.
     */
    public function store(PhysicalLocationRequest $request)
    {
        PhysicalLocation::create($request->validated());

        return redirect()
            ->route('settings.physical-locations.index')
            ->with('success', 'Physical location created successfully.');
    }

    /**
     * Show the form for editing the specified physical location.
     */
    public function edit(PhysicalLocation $physicalLocation)
    {
        return view('physical-locations.edit', compact('physicalLocation'));
    }

    /**
     * Update the specified physical location in storage.
     */
    public function update(PhysicalLocationRequest $request, PhysicalLocation $physicalLocation)
    {
        $physicalLocation->update($request->validated());

        return redirect()
            ->route('settings.physical-locations.index')
            ->with('success', 'Physical location updated successfully.');
    }

    /**
     * Remove the specified physical location from storage safely.
     */
    public function destroy(PhysicalLocation $physicalLocation)
    {
        if ($physicalLocation->documents()->count() > 0) {
            return redirect()
                ->route('settings.physical-locations.index')
                ->with('error', 'Cannot delete this rack because it contains folders.');
        }

        $physicalLocation->delete();

        return redirect()
            ->route('settings.physical-locations.index')
            ->with('success', 'Physical location deleted successfully.');
    }
}
