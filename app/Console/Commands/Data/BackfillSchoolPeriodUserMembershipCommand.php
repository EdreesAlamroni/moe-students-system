<?php

namespace App\Console\Commands\Data;

use App\Enums\UserScope;
use App\Models\SchoolPeriod;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

#[Signature('data:backfill-school-period-user-membership')]
#[Description('Backfill school_period_user memberships from existing school users organization_id')]
class BackfillSchoolPeriodUserMembershipCommand extends Command
{
    public function handle(): int
    {
        if (! Schema::hasTable('school_period_user')) {
            $this->components->error('The school_period_user table does not exist. Run migrations first.');

            return self::FAILURE;
        }

        $query = $this->membershipsToBackfillQuery();
        $count = (clone $query)->count();

        if ($count === 0) {
            $this->components->info('No school user memberships to backfill.');

            return self::SUCCESS;
        }

        DB::table('school_period_user')->insertUsing(
            ['user_id', 'school_period_id'],
            $query,
        );

        $this->components->info(sprintf('Backfilled %d school user membership(s).', $count));

        return self::SUCCESS;
    }

    private function membershipsToBackfillQuery(): Builder
    {
        return DB::table('users')
            ->select(['id', 'organization_id'])
            ->where('scope', '=', UserScope::SCHOOL->value)
            ->where('organization_type', '=', SchoolPeriod::class)
            ->whereNotNull('organization_id')
            ->whereIn('organization_id', DB::table('school_periods')->select('id'))
            ->whereNotExists(function (Builder $query): void {
                $query->select(DB::raw(1))
                    ->from('school_period_user')
                    ->whereColumn('school_period_user.user_id', '=', 'users.id')
                    ->whereColumn('school_period_user.school_period_id', '=', 'users.organization_id');
            });
    }
}
