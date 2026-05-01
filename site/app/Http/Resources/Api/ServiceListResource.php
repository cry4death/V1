<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ServiceListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $raw = $this->description;
        $plain = is_string($raw) ? Str::of(strip_tags($raw))->squish()->value() : null;
        $short = $plain !== null && $plain !== '' ? Str::limit($plain, 160) : '';

        $price = (float) $this->price;
        $priceLabel = $price > 0
            ? 'от '.number_format($price, $price == floor($price) ? 0 : 2, ',', ' ').' BYN'
            : 'По запросу';

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $short,
            'long_description' => $raw,
            'indications' => $this->indications,
            'preparation' => $this->preparation,
            'price_label' => $priceLabel,
        ];
    }
}
