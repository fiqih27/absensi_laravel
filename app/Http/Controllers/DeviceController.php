<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index()
    {
        $devices = Device::all();
        return view('devices.index', compact('devices'));
    }

    public function create()
    {
        return view('devices.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_name' => 'required|string|max:50',
            'ip_address' => 'required|ip',
            'port' => 'required|integer',
            'location' => 'nullable|string|max:100',
        ]);

        Device::create($validated);

        return redirect()->route('devices.index')->with('success', 'Device berhasil ditambahkan');
    }

    public function edit(Device $device)
    {
        return view('devices.edit', compact('device'));
    }

    public function update(Request $request, Device $device)
    {
        $validated = $request->validate([
            'device_name' => 'required|string|max:50',
            'ip_address' => 'required|ip',
            'port' => 'required|integer',
            'location' => 'nullable|string|max:100',
        ]);

        $device->update($validated);

        return redirect()->route('devices.index')->with('success', 'Device berhasil diupdate');
    }

    public function destroy(Device $device)
    {
        $device->delete();
        return redirect()->route('devices.index')->with('success', 'Device berhasil dihapus');
    }
}
