<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Equipment;

use Illuminate\Foundation\Http\FormRequest;

class DecommissionEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string'],
            'disposalMethod' => ['required', 'in:Recycle,Donate,Destroy,Sell'],
        ];
    }
}

