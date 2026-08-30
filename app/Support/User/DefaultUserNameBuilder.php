<?php

namespace App\Support\User;

use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\SchoolPeriod;
use Illuminate\Support\Str;

class DefaultUserNameBuilder
{
    public function forEducationMonitor(EducationMonitor $monitor): string
    {
        return sprintf('مستخدم %s', trim($monitor->name));
    }

    public function forEducationServicesOffice(EducationServicesOffice $office): string
    {
        return $this->buildWithOptionalPrefix('مكتب', trim($office->name));
    }

    public function forSchoolPeriod(SchoolPeriod $period): string
    {
        $period->loadMissing(['school:id,name']);

        $hasMultiplePeriods = SchoolPeriod::query()
            ->where('school_id', '=', $period->school_id)
            ->count() > 1;

        $name = $this->buildWithOptionalPrefix('مدرسة', trim($period->school->name));

        if (! $hasMultiplePeriods) {
            return $name;
        }

        return sprintf('%s (%s)', $name, $period->academic_period->displayName());
    }

    private function buildWithOptionalPrefix(string $prefix, string $entityName): string
    {
        if (Str::startsWith($entityName, $prefix)) {
            return sprintf('مستخدم %s', $entityName);
        }

        return sprintf('مستخدم %s %s', $prefix, $entityName);
    }
}
