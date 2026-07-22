<?php

namespace App\Http\Resources\School;

use App\Http\Resources\DirectModelCollection;
use App\Models\StudentTransfer;
use Illuminate\Http\Request;

class StudentTransferCollection extends DirectModelCollection
{
    public function toArray(Request $request): array
    {
        return $this->collection->map(fn (StudentTransfer $transfer) => [
            'id' => $transfer->id,
            'uuid' => $transfer->uuid,

            'left_academic_year' => $transfer->relationLoaded('leftAcademicYear')
                ? $transfer->leftAcademicYear->only(['id', 'name']) : null,

            'joined_academic_year' => $transfer->relationLoaded('joinedAcademicYear')
            ? $transfer->joinedAcademicYear->only(['id', 'name']) : null,

            'from_school' => $transfer->relationLoaded('fromSchool')
            ? $transfer->fromSchool->only(['id', 'name', 'monitor']) : null,

            'to_school' => $transfer->relationLoaded('toSchool')
                ? $transfer->toSchool->only(['id', 'name', 'monitor']) : null,

            'left_school_at' => $transfer->left_school_at?->toDateString(),
            'joined_school_at' => $transfer->joined_school_at?->toDateString(),
        ])->all();
    }
}
