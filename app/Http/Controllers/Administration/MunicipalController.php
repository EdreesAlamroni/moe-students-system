<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Http\Resources\Administration\MunicipalCollection;
use App\Models\Municipal;
use App\Support\ResourcePayloadBuilder;
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
            ->onEachSide(0);

        return Inertia::render('administration/municipals/index', [
            'municipals' => ResourcePayloadBuilder::paginate(
                $municipals,
                MunicipalCollection::make($municipals),
            ),
            'filter' => $request->input('filter', []),
        ]);
    }
}
