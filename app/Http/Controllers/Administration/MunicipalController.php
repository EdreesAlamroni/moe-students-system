<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Http\Resources\Administration\MunicipalResource;
use App\Models\Municipal;
use App\Support\Http\ResourcePayloadBuilder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\QueryBuilder;

class MunicipalController extends Controller
{
    public function index(Request $request): Response
    {
        $municipals = QueryBuilder::for(Municipal::class)
            ->select([
                'municipals.id',
                'municipals.uuid',
                'municipals.name',
                'municipals.created_at',
                'municipals.deleted_at',
            ])
            ->allowedFilters(
                'name',
            )
            ->paginate()
            ->withQueryString()
            ->appends($request->query())
            ->onEachSide(0)
            ->through(fn (Municipal $municipal): array => ResourcePayloadBuilder::withAbilities(
                MunicipalResource::make($municipal),
                ['view'],
            ));

        return Inertia::render('administration/municipals/index', [
            'municipals' => $municipals,
            'filter' => $request->input('filter', []),
        ]);
    }
}
