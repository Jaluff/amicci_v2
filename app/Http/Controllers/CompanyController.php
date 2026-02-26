<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    /**
     * Show the company selector page.
     */
    public function select()
    {
        $companies = Auth::user()->companies;

        if ($companies->count() === 1) {
            session()->put('company_id', $companies->first()->id);
            return redirect()->intended(route('dashboard'));
        }

        return view('company.select', compact('companies'));
    }

    /**
     * Switch the active company.
     */
    public function switch (Request $request)
    {
        $request->validate([
            'company_id' => ['required', 'integer'],
        ]);

        $company = Auth::user()
            ->companies()
            ->findOrFail($request->company_id);

        session()->put('company_id', $company->id);

        return redirect()->back()->with('success', "Empresa activa: {$company->name}");
    }
}