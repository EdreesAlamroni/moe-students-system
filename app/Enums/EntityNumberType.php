<?php

namespace App\Enums;

enum EntityNumberType: string
{
    case Warehouse = 'warehouse';
    case EducationMonitor = 'education_monitor';
    case EducationServicesOffice = 'education_services_office';
    case School = 'school';

    public function prefix(): string
    {
        return match ($this) {
            self::Warehouse => 'WH',
            self::EducationMonitor => 'EM',
            self::EducationServicesOffice => 'ESO',
            self::School => 'SCH',
        };
    }

    public function sequenceName(): string
    {
        return match ($this) {
            self::Warehouse => 'entity_number_wh',
            self::EducationMonitor => 'entity_number_em',
            self::EducationServicesOffice => 'entity_number_eso',
            self::School => 'entity_number_sch',
        };
    }

    public function padLength(): int
    {
        return match ($this) {
            self::Warehouse => 3,
            self::EducationMonitor => 4,
            self::EducationServicesOffice => 4,
            self::School => 5,
        };
    }

    public function format(int $sequence): string
    {
        return sprintf(
            '%s-%s',
            $this->prefix(),
            str_pad((string) $sequence, $this->padLength(), '0', STR_PAD_LEFT),
        );
    }

    public function regex(): string
    {
        return match ($this) {
            self::Warehouse => '/^WH-\d{3}$/',
            self::EducationMonitor => '/^EM-\d{4}$/',
            self::EducationServicesOffice => '/^ESO-\d{4}$/',
            self::School => '/^SCH-\d{5}$/',
        };
    }
}
