<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function modules(Request $request)
    {
        return Module::when($request->has('search'), function ($query) use ($request) {
            $query->where('title', 'like', "%{$request->search}%");
        })->where('created_by', auth()->id())
            ->latest()->paginate($request->per_page ?? 10);
    }
}
