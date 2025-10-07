<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BidResource extends JsonResource
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
            'id'               => $this->id,
            'project_id'       => $this->project_id,
            'provider_id'      => $this->provider_id,
            'amount'           => $this->amount,
            'message'          => $this->message,
            'status'           => $this->status,
            'days_to_complete' => $this->days_to_complete, 
            
            'project'          => new ProjectResource($this->whenLoaded('project')),
            'provider'         => new UserResource($this->whenLoaded('provider')),
            'engagement'       => new EngagementResource($this->whenLoaded('engagement')),
            'created_at'       => $this->created_at,
        ];
    }
}
