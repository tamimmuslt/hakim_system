<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\Centers;
use App\Models\Service;
use App\Models\Services;

class SearchController extends Controller
{
    // البحث الشامل
    public function searchAll(Request $request)
    {
        $q = $request->input('q');

        $doctors = Doctor::with('user')
            ->whereHas('user', function($query) use ($q) {
                $query->where('name', 'like', "%$q%");
            })
            ->orWhere('specialty', 'like', "%$q%")
            ->get();

        $centers = Centers::with('user')
            ->whereHas('user', function($query) use ($q) {
                $query->where('name', 'like', "%$q%");
            })
            ->orWhere('type', 'like', "%$q%")
            ->orWhere('address', 'like', "%$q%")
            ->get();

        $services = Services::where('name', 'like', "%$q%")->get();

        return response()->json([
            'doctors' => $doctors,
            'centers' => $centers,
            'services' => $services
        ]);
    }

    // اقتراحات البحث (Autocomplete)
    public function suggestions(Request $request)
    {
        $q = $request->input('q');

        if (!$q) {
            return response()->json([]);
        }

        $results = [];

        // Doctors
        $doctors = Doctor::with('user')
            ->whereHas('user', function($query) use ($q) {
                $query->where('name', 'like', "%$q%");
            })
            ->orWhere('specialty', 'like', "%$q%")
            ->limit(5)
            ->get();

        foreach ($doctors as $doctor) {
            $results[] = [
                'type' => 'doctor',
                'id' => $doctor->doctor_id,
                'title' => $doctor->user->name ?? '',
                'subtitle' => $doctor->specialty
            ];
        }

        // Centers
        $centers = Centers::with('user')
            ->whereHas('user', function($query) use ($q) {
                $query->where('name', 'like', "%$q%");
            })
            ->orWhere('type', 'like', "%$q%")
            ->orWhere('address', 'like', "%$q%")
            ->limit(5)
            ->get();

        foreach ($centers as $center) {
            $results[] = [
                'type' => 'center',
                'id' => $center->center_id,
                'title' => $center->user->name ?? '',
                'subtitle' => $center->type
            ];
        }

        // Services
        $services = Services::where('name', 'like', "%$q%")
            ->limit(5)
            ->get();

        foreach ($services as $service) {
            $results[] = [
                'type' => 'service',
                'id' => $service->service_id,
                'title' => $service->name,
                'subtitle' => $service->description ?? ''
            ];
        }

        return response()->json([
            'query' => $q,
            'suggestions' => $results
        ]);
    }
}
