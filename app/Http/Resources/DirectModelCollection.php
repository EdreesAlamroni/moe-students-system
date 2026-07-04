<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * A resource collection that maps Eloquent models directly for index/list payloads.
 *
 * Use this when a companion {@see \Illuminate\Http\Resources\Json\JsonResource} exists
 * for show/detail actions but must not auto-wrap paginated rows (e.g. UserResource + UserCollection).
 */
abstract class DirectModelCollection extends ResourceCollection
{
    /**
     * Disable Laravel's automatic *Resource wrapping for collection items.
     *
     * @return null
     */
    protected function collects()
    {
        return null;
    }
}
