<?php

namespace Modules\Education\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class EvaluationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'client_uuid' => $this->client_uuid, // 🎯 أضف هذا السطر هنا
            'score' => $this->score,
            'notes' => $this->notes,
            'dimensions' => $this->dimensions,
            'voice_note' => $this->voiceNote ? [
                'id' => $this->voiceNote->id,
                'url' => $this->voiceNote->url,   // 👈 Supabase URL الحقيقي
                'type' => $this->voiceNote->type,
            ] : null,
            'surah_name' => $this->surah_name,
            'from_ayah' => $this->from_ayah,
            'to_ayah' => $this->to_ayah,
            'evaluated_at' => $this->evaluated_at,
            'student' => [
                'id' => $this->student?->id,
                'name' => $this->student?->first_name . ' ' . $this->student?->last_name,
            ],
            'halaqa' => [
                'id' => $this->halaqa?->id,
                'name' => $this->halaqa?->name,
            ],
        ];
    }
}
