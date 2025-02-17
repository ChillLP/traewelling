<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatusExportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request) {
        return [
            "status" => new StatusResource($this),
            "trip"   => new HafasTripResource($this->trainCheckin->HafasTrip),
        ];
    }
}
