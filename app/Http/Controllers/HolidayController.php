<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HolidayController extends Controller
{
    public function index()
    {
        $companyId = Auth::user()->company_id;
        
        $holidays = Holiday::where('company_id', $companyId)
            ->orderBy('date', 'desc')
            ->get();
        
        return view('management.holidays.index', compact('holidays'));
    }

    public function create()
    {
        return view('management.holidays.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string|max:500',
            'is_recurring' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $exists = Holiday::where('company_id', Auth::user()->company_id)
            ->where('date', $request->date)
            ->exists();

        if ($exists) {
            return redirect()->route('holidays.create')
                ->with('error', 'A holiday already exists on this date.');
        }

        Holiday::create([
            'company_id' => Auth::user()->company_id,
            'name' => $request->name,
            'date' => $request->date,
            'description' => $request->description,
            'is_recurring' => $request->is_recurring ?? false,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('holidays.index')
            ->with('success', 'Holiday added successfully.');
    }

    public function edit(Holiday $holiday)
    {
        return view('management.holidays.edit', compact('holiday'));
    }

    public function update(Request $request, Holiday $holiday)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string|max:500',
            'is_recurring' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $holiday->update([
            'name' => $request->name,
            'date' => $request->date,
            'description' => $request->description,
            'is_recurring' => $request->is_recurring ?? false,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('holidays.index')
            ->with('success', 'Holiday updated successfully.');
    }

    public function destroy(Holiday $holiday)
    {
        $holiday->delete();
        return redirect()->route('holidays.index')
            ->with('success', 'Holiday deleted successfully.');
    }

    public function toggle(Holiday $holiday)
    {
        $holiday->is_active = !$holiday->is_active;
        $holiday->save();

        return redirect()->route('holidays.index')
            ->with('success', 'Holiday ' . ($holiday->is_active ? 'activated' : 'deactivated') . ' successfully.');
    }
}