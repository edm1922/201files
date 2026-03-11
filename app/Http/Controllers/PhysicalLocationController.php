<?php

namespace App\Http\Controllers;

use App\Http\Requests\PhysicalLocationRequest;
use App\Models\PhysicalLocation;
use Illuminate\Http\Request;

class PhysicalLocationController extends Controller
{
    /**
     * Display a paginated list of physical locations.
     */
    public function index(Request $request)
    {
        $query = PhysicalLocation::withCount('documents');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('cabinet_id', 'like', "%{$search}%")
                  ->orWhere('rack_id', 'like', "%{$search}%")
                  ->orWhere('label', 'like', "%{$search}%");
            });
        }

        $physicalLocations = $query->orderBy('cabinet_id')
                                   ->orderBy('rack_id')
                                   ->paginate(15)
                                   ->withQueryString();

        return view('physical-locations.index', compact('physicalLocations'));
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
}
