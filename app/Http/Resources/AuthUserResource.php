<?php

namespace App\Http\Resources;

use App\Enums\VendorStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public static $wrap = false;
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'permission' => $this->getAllPermissions()->map(fn ($perm) => $perm->name),
            'roles' => $this->getRoleNames(),
            'vendor' => !$this->vendor?null:[
                'status'=>$this->vendor->status,
                'status_label'=>VendorStatusEnum::from($this->vendor->status)->label(),
                'shop_name'=>$this->vendor->shop_name,
                'shop_address'=>$this->vendor->shop_address,
                'cover_image'=>$this->vendor->cover_image,
            ]
        ];
    }
}
