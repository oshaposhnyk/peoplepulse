<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EquipmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'assetTag' => $this->asset_tag,
            'serialNumber' => $this->serial_number,
            'type' => $this->equipment_type,
            'brand' => $this->brand,
            'model' => $this->model,
            'specifications' => $this->specifications,
            
            'purchaseDate' => $this->purchase_date->format('Y-m-d'),
            'purchasePrice' => (float) $this->purchase_price,
            'purchaseCurrency' => $this->purchase_currency,
            'supplier' => $this->supplier,
            
            'warrantyExpiryDate' => $this->warranty_expiry_date?->format('Y-m-d'),
            'warrantyProvider' => $this->warranty_provider,
            
            'status' => $this->status,
            'condition' => $this->condition,
            
            'currentAssignee' => $this->when($this->currentAssignee, fn() => [
                'id' => $this->currentAssignee->employee_id,
                'name' => $this->currentAssignee->full_name,
                'position' => $this->currentAssignee->position,
            ]),
            
            'assignedAt' => $this->assigned_at?->toIso8601String(),
            'physicalLocation' => $this->physical_location,
            
            'decommissionedAt' => $this->decommissioned_at?->toIso8601String(),
            'decommissionReason' => $this->decommission_reason,
            'disposalMethod' => $this->disposal_method,
            
            'createdAt' => $this->created_at->toIso8601String(),
            'updatedAt' => $this->updated_at->toIso8601String(),
        ];
    }
}

