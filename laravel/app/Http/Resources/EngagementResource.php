<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EngagementResource extends JsonResource
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
            'project_id'    => $this->project_id,
            'bid_id'        => $this->bid_id,
            'provider_id'   => $this->provider_id,
            'client_id'     => $this->client_id,
            'agreed_amount' => $this->agreed_amount,
            'started_at'    => $this->started_at,
            'ended_at'      => $this->ended_at,
            'state'         => $this->state,
            'project'       => new ProjectResource($this->whenLoaded('project')),
            'bid'           => new BidResource($this->whenLoaded('bid')),
            'provider'      => new UserResource($this->whenLoaded('provider')),
            'client'        => new UserResource($this->whenLoaded('client')),
            'created_at'    => $this->created_at,
        ];
    }
}
