<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
            'id'          => $this->id,
            'client_id'   => $this->client_id,
            'title'       => $this->title,
            'description' => $this->description,
            'budget_min'  => $this->budget_min,
            'budget_max'  => $this->budget_max,
            'status'      => $this->status,
            'tags'        => $this->tags,
            'client'      => new UserResource($this->whenLoaded('client')),
            'bids'        => BidResource::collection($this->whenLoaded('bids')),
            'engagement'  => new EngagementResource($this->whenLoaded('engagement')),
            'created_at'  => $this->created_at,
        ];
    }
}
