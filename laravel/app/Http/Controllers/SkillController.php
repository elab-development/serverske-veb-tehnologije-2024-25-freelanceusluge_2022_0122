<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProfileResource;
use App\Http\Resources\SkillResource;
use App\Models\Profile;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SkillController extends Controller
{
    // GET /api/skills?q=react
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $skills = Skill::query()
            ->when($q, fn($qr) => $qr->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->paginate(20);

        return SkillResource::collection($skills);
    }

    // POST /api/skills
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:100','unique:skills,name'],
            'category' => ['nullable','string','max:50'],
        ]);

        $skill = Skill::create($data);
        return new SkillResource($skill);
    }

    // GET /api/skills/{skill}
    public function show(Skill $skill)
    {
        return new SkillResource($skill);
    }

    // PUT/PATCH /api/skills/{skill}
    public function update(Request $request, Skill $skill)
    {
        $data = $request->validate([
            'name'     => ['sometimes','string','max:100', Rule::unique('skills','name')->ignore($skill->id)],
            'category' => ['sometimes','nullable','string','max:50'],
        ]);

        $skill->update($data);
        return new SkillResource($skill);
    }

    // DELETE /api/skills/{skill}
    public function destroy(Skill $skill)
    {
        $skill->delete();
        return response()->json(['message' => 'Skill deleted']);
    }

    // POST /api/skills/{skill}/attach  (provider dodaje sebi ili admin bilo kome)
    public function attachToProfile(Request $request, Skill $skill)
    {
        $data = $request->validate([
            'profile_id' => ['required','exists:profiles,id'],
        ]);

        $profile = Profile::findOrFail($data['profile_id']); 
        $profile->skills()->syncWithoutDetaching([$skill->id]);
        return response()->json(['message' => 'Skill attached']);
    }

    // DELETE /api/skills/{skill}/detach
    public function detachFromProfile(Request $request, Skill $skill)
    {
        $data = $request->validate([
            'profile_id' => ['required','exists:profiles,id'],
        ]);

        $profile = Profile::findOrFail($data['profile_id']); 

        $profile->skills()->detach($skill->id);
        return response()->json(['message' => 'Skill detached']);
    }

    // GET /api/skills/{skill}/profiles  (lista profila sa ovim skillom)
    public function profilesBySkill(Skill $skill)
    {
        $profiles = $skill->profiles()
            ->with('user')      
            ->paginate(20);

        return ProfileResource::collection($profiles);
    }
}
