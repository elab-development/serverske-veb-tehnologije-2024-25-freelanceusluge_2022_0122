<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
       return [
            'id'            => $this->id,
            'user_id'       => $this->user_id,
            'headline'      => $this->headline,
            'bio'           => $this->bio,
            'github_url'    => $this->github_url,
            'portfolio_url' => $this->portfolio_url,
            'avatar_url'    => $this->avatar_url, // accessor iz modela
            'banner_url'    => $this->banner_url,
            'links'         => $this->links,
            'skills'        => SkillResource::collection($this->whenLoaded('skills')),
            'created_at'    => $this->created_at,
        ];
    }
}
