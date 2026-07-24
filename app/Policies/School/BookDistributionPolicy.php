<?php

namespace App\Policies\School;

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
}
