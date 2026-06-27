<?php

namespace App\Http\Controllers;

use App\Models\Society;
use Illuminate\Http\JsonResponse;

class SocietyController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'societies' => Society::query()->orderBy('name')->get(),
        ]);
    }
}
