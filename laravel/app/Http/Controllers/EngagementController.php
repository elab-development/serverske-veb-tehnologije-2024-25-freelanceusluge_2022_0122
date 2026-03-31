<?php

namespace App\Http\Controllers;

use App\Http\Resources\EngagementResource;
use App\Models\Engagement;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EngagementController extends Controller
{
    // GET /api/engagements?project_id=&provider_id=&client_id=&state=
    public function index(Request $request)
    {
        $q = Engagement::query()
            ->with(['project.client','bid','provider.profile','client.profile'])
            ->when($request->filled('project_id'), fn($qr) => $qr->where('project_id', (int)$request->input('project_id')))
            ->when($request->filled('provider_id'), fn($qr) => $qr->where('provider_id', (int)$request->input('provider_id')))
            ->when($request->filled('client_id'), fn($qr) => $qr->where('client_id', (int)$request->input('client_id')))
            ->when($request->filled('state'), fn($qr) => $qr->where('state', $request->string('state')))
            ->orderByDesc('id');

        return EngagementResource::collection($q->paginate(12));
    }

    // POST /api/engagements
    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id'    => ['required','integer','exists:projects,id'],
            'bid_id'        => ['nullable','integer','exists:bids,id'],
            'provider_id'   => ['required','integer','exists:users,id'],
            'client_id'     => ['required','integer','exists:users,id'],
            'agreed_amount' => ['nullable','numeric','gt:0'],
            'started_at'    => ['nullable','date'],
            'ended_at'      => ['nullable','date','after_or_equal:started_at'],
            'state'         => ['nullable', Rule::in(['active','completed','cancelled'])],
        ]);

        $data['state'] = $data['state'] ?? 'active';

        $eng = Engagement::create($data)
            ->load(['project.client','bid','provider.profile','client.profile']);

        return new EngagementResource($eng);
    }

    // GET /api/engagements/{engagement}
    public function show(Engagement $engagement)
    {
        $engagement->load(['project.client','bid','provider.profile','client.profile']);
        return new EngagementResource($engagement);
    }

    // PUT/PATCH /api/engagements/{engagement}
    public function update(Request $request, Engagement $engagement)
    {
        $data = $request->validate([
            'project_id'    => ['sometimes','integer','exists:projects,id'],
            'bid_id'        => ['sometimes','nullable','integer','exists:bids,id'],
            'provider_id'   => ['sometimes','integer','exists:users,id'],
            'client_id'     => ['sometimes','integer','exists:users,id'],
            'agreed_amount' => ['sometimes','nullable','numeric','gt:0'],
            'started_at'    => ['sometimes','nullable','date'],
            'ended_at'      => ['sometimes','nullable','date','after_or_equal:started_at'],
            'state'         => ['sometimes', Rule::in(['active','completed','cancelled'])],
        ]);

        $engagement->update($data);

        return new EngagementResource(
            $engagement->fresh(['project.client','bid','provider.profile','client.profile'])
        );
    }

    // DELETE /api/engagements/{engagement}
    public function destroy(Engagement $engagement)
    {
        $engagement->delete();
        return response()->json(['message' => 'Engagement deleted']);
    }

    // (opciono) POST /api/engagements/{engagement}/complete
    public function complete(Engagement $engagement)
    {
        $engagement->update(['state' => 'completed', 'ended_at' => now()]);
        return new EngagementResource($engagement->fresh());
    }

    // (opciono) POST /api/engagements/{engagement}/cancel
    public function cancel(Engagement $engagement)
    {
        $engagement->update(['state' => 'cancelled', 'ended_at' => now()]);
        return new EngagementResource($engagement->fresh());
    }
}
