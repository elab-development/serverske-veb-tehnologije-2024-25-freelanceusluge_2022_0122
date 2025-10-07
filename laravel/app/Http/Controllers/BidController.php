<?php

namespace App\Http\Controllers;

use App\Http\Resources\BidResource;
use App\Models\Bid;
use App\Models\Engagement;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BidController extends Controller
{
    // GET /api/bids?project_id=...&mine=1&status=pending
    public function index(Request $request)
    {
        $q = Bid::query()
            ->with(['project.client', 'provider.profile'])
            ->when($request->filled('project_id'), fn($qr) => $qr->where('project_id', (int)$request->input('project_id')))
            ->when($request->filled('status'), fn($qr) => $qr->where('status', $request->string('status')))
            ->when($request->boolean('mine') && $request->user(), fn($qr) => $qr->where('provider_id', $request->user()->id))
            ->orderByDesc('id');

        return BidResource::collection($q->paginate(12));
    }

    // POST /api/projects/{project}/bids
    public function store(Request $request, Project $project)
    {
        // dozvoli samo na otvorenim projektima (možeš obrisati ako nećeš ograničenje)
        if ($project->status !== 'open') {
            abort(422, 'Project is not open for bids');
        }

        $data = $request->validate([
            // ako ima auth, provider_id je opcioni; bez auth-a obavezan
            'provider_id'      => [$request->user() ? 'nullable' : 'required','integer','exists:users,id'],
            'amount'           => ['required','numeric','gt:0'],
            'message'          => ['nullable','string','max:2000'],
            'days_to_complete' => ['nullable','integer','min:1','max:365'],
        ]);

        $providerId = $request->user()->id ?? (int)$data['provider_id'];

        $bid = Bid::create([
            'project_id'       => $project->id,
            'provider_id'      => $providerId,
            'amount'           => $data['amount'],
            'message'          => $data['message'] ?? null,
            'days_to_complete' => $data['days_to_complete'] ?? null,
            'status'           => 'pending',
        ])->load(['project.client','provider.profile']);

        return new BidResource($bid);
    }

    // GET /api/bids/{bid}
    public function show(Bid $bid)
    {
        $bid->load(['project.client','provider.profile','engagement']);
        return new BidResource($bid);
    }

    // PATCH /api/bids/{bid}
    public function update(Request $request, Bid $bid)
    {
        // po želji zadrži ograničenje da se menja samo dok je pending
        if ($bid->status !== 'pending') {
            abort(422, 'Only pending bids can be updated');
        }

        $data = $request->validate([
            'amount'           => ['sometimes','numeric','gt:0'],
            'message'          => ['sometimes','nullable','string','max:2000'],
            'days_to_complete' => ['sometimes','nullable','integer','min:1','max:365'],
            // status se ne menja ovde
            'status'           => ['prohibited', Rule::in(['pending','accepted','rejected','withdrawn'])],
        ]);

        $bid->update($data);

        return new BidResource($bid->fresh(['project.client','provider.profile']));
    }

    // DELETE /api/bids/{bid}
    public function destroy(Request $request, Bid $bid)
    {
        // po želji: dozvoli brisanje samo pending
        if ($bid->status !== 'pending') {
            abort(422, 'Only pending bids can be deleted');
        }

        $bid->delete();
        return response()->json(['message' => 'Bid deleted']);
    }

    // POST /api/bids/{bid}/withdraw
    public function withdraw(Request $request, Bid $bid)
    {
        if ($bid->status !== 'pending') {
            abort(422, 'Only pending bids can be withdrawn');
        }

        $bid->update(['status' => 'withdrawn']);
        return new BidResource($bid->fresh());
    }

    // POST /api/bids/{bid}/accept
    public function accept(Request $request, Bid $bid)
    {
        if ($bid->status !== 'pending') {
            abort(422, 'Only pending bids can be accepted');
        }
        if ($bid->project->status !== 'open') {
            abort(422, 'Project is not open');
        }

        $engagement = DB::transaction(function () use ($bid) {
            // označi izabrani bid kao accepted
            $bid->update(['status' => 'accepted']);

            // odbij ostale pending bidove na projektu
            Bid::where('project_id', $bid->project_id)
                ->where('id', '!=', $bid->id)
                ->where('status', 'pending')
                ->update(['status' => 'rejected']);

            // zatvori projekat u rad
            $bid->project()->update(['status' => 'in_progress']);

            // napravi engagement ako ne postoji
            return Engagement::firstOrCreate(
                ['project_id' => $bid->project_id],
                [
                    'bid_id'        => $bid->id,
                    'provider_id'   => $bid->provider_id,
                    'client_id'     => $bid->project->client_id,
                    'agreed_amount' => $bid->amount,
                    'started_at'    => now(),
                    'state'         => 'active',
                ]
            );
        });

        $bid->load(['project','provider','engagement']);

        return response()->json([
            'message'    => 'Bid accepted',
            'engagement' => $engagement,
            'bid'        => new BidResource($bid),
        ]);
    }
}
