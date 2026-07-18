<?php

namespace App\Policies\Warehouse;

use App\Models\User;

class BookDistributionPolicy
{
    public function view(User $user): bool
    {
        return $user->can('book-distribution:view');
    }

    public function distribute(User $user): bool
    {
        return $user->can('book-distribution:distribute');
    }

    public function viewStatistics(User $user): bool
    {
        return $user->can('book-distribution:view-statistics');
    }
}
