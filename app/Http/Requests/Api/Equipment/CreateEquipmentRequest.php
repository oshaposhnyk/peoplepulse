<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Equipment;

use Illuminate\Foundation\Http\FormRequest;

class CreateEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string'],
            'brand' => ['required', 'string'],
            'model' => ['required', 'string'],
            'serialNumber' => ['required', 'string', 'unique:equipment,serial_number'],
            'specifications' => ['nullable', 'array'],
            'purchaseDate' => ['required', 'date'],
            'purchasePrice' => ['required', 'numeric', 'min:0'],
            'purchaseCurrency' => ['nullable', 'string', 'size:3'],
            'supplier' => ['nullable', 'string'],
            'warrantyExpiryDate' => ['nullable', 'date', 'after:purchaseDate'],
            'warrantyProvider' => ['nullable', 'string'],
            'condition' => ['nullable', 'in:New,Good,Fair,Poor'],
        ];
    }
}

