<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    // GET /api/projects?status=open&tag=react&min_budget=100&max_budget=2000&q=api&client_id=3&mine=1
    public function index(Request $request)
    {
        $q = Project::query()
            ->with(['client.profile'])
            ->when($request->filled('status'), fn ($qr) => $qr->where('status', $request->string('status')))
            ->when($request->filled('client_id'), fn ($qr) => $qr->where('client_id', $request->integer('client_id')))
            ->when($request->boolean('mine'), fn ($qr) => $qr->where('client_id', $request->user()?->id))
            ->when($request->filled('q'), fn ($qr) => $qr->where('title', 'like', '%'.$request->string('q').'%'))
            ->when($request->filled('min_budget'), fn ($qr) => $qr->where('budget_min', '>=', (float)$request->input('min_budget')))
            ->when($request->filled('max_budget'), fn ($qr) => $qr->where('budget_max', '<=', (float)$request->input('max_budget')))
            ->when($request->filled('tag'), function ($qr) use ($request) {
                // tags je JSON array; MySQL -> whereJsonContains, SQLite -> fallback
                return $qr->whereJsonContains('tags', $request->string('tag'));
            })
            ->orderByDesc('id');

        return ProjectResource::collection($q->paginate(12));
    }

    // POST /api/projects (client)
    public function store(Request $request)
    {
        $this->authorizeClient($request);

        $data = $request->validate([
            'title'       => ['required','string','max:160'],
            'description' => ['nullable','string'],
            'budget_min'  => ['nullable','numeric','gte:0'],
            'budget_max'  => ['nullable','numeric','gte:budget_min'],
            'status'      => ['nullable', Rule::in(['open','in_progress','closed'])],
            'tags'        => ['nullable'], // array ili csv; normalize ispod
        ]);

        $data['client_id'] = $request->user()->id;
        $data['status']    = $data['status'] ?? 'open';
        $data['tags']      = $this->normalizeTags($data['tags'] ?? null);

        $project = Project::create($data)->load('client.profile');

        return new ProjectResource($project);
    }

    // GET /api/projects/{project}
    public function show(Project $project)
    {
        $project->load(['client.profile','bids.provider','engagement']);
        return new ProjectResource($project);
    }

    // PUT/PATCH /api/projects/{project} (client vlasnik)
    public function update(Request $request, Project $project)
    {
        $this->authorizeOwner($request, $project);

        $data = $request->validate([
            'title'       => ['sometimes','string','max:160'],
            'description' => ['sometimes','nullable','string'],
            'budget_min'  => ['sometimes','nullable','numeric','gte:0'],
            'budget_max'  => ['sometimes','nullable','numeric','gte:budget_min'],
            'status'      => ['sometimes', Rule::in(['open','in_progress','closed'])],
            'tags'        => ['sometimes','nullable'],
        ]);

        if (array_key_exists('tags', $data)) {
            $data['tags'] = $this->normalizeTags($data['tags']);
        }

        $project->update($data);
        return new ProjectResource($project->fresh('client.profile'));
    }

    // DELETE /api/projects/{project} (client vlasnik)
    public function destroy(Request $request, Project $project)
    {
        $this->authorizeOwner($request, $project);
        $project->delete();

        return response()->json(['message' => 'Project deleted']);
    }

 
   
}
