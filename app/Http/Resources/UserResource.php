<?php

namespace App\Http\Resources;

use App\Abstracts\UserRoles;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $role = 'contractor';
        switch ($this->role) {
            case UserRoles::CONTRACTOR:
                $role = 'contractor';
                break;
            case UserRoles::CLIENT:
                $role = 'client';
                break;
            case UserRoles::BUDDY:
                $role = 'buddy';
                break;
        }

        return [
            'hashid' => encode($this->id, 'uuid'),
            'name' => $this->name,
            'email' => $this->email,
            'role' => $role,
            'banned' => $this->ban == 1,
            'active' => $this->status == 1,
        ];
    }
}
