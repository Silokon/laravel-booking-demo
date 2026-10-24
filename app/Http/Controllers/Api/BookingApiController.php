<?php

namespace App\Http\Controllers\Api;

use App\Actions\CreateAppointment;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Master;
use App\Models\Service;
use App\Services\SlotService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingApiController extends Controller
{
    public function services(): JsonResponse
    {
        return response()->json(
            Service::where('is_active', true)->orderBy('name')
                ->get(['id', 'name', 'description', 'duration_minutes', 'price'])
        );
    }

    public function masters(Request $request): JsonResponse
    {
        $request->validate(['service_id' => ['nullable', 'integer', 'exists:services,id']]);

        $masters = Master::where('is_active', true)
            ->when($request->integer('service_id'), fn ($q, $id) => $q->whereHas('services', fn ($s) => $s->whereKey($id)))
            ->orderBy('name')
            ->get(['id', 'name', 'specialty']);

        return response()->json($masters);
    }

    public function slots(Request $request, SlotService $slots): JsonResponse
    {
        $data = $request->validate([
            'master_id' => ['required', 'integer', 'exists:masters,id'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
        ]);

        $free = $slots->freeSlots(
            Master::findOrFail($data['master_id']),
            Service::findOrFail($data['service_id']),
            CarbonImmutable::createFromFormat('Y-m-d', $data['date'])->startOfDay(),
        );

        return response()->json($free->map(fn ($s) => $s->format('H:i'))->values());
    }

    public function store(StoreAppointmentRequest $request, CreateAppointment $create): JsonResponse
    {
        $appointment = $create->handle($request->validated());

        return response()->json([
            'id' => $appointment->id,
            'starts_at' => $appointment->starts_at->format('Y-m-d H:i'),
            'status' => $appointment->status,
        ], 201);
    }
}
