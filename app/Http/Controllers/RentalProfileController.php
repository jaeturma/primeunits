<?php

namespace App\Http\Controllers;

use App\Models\RentalProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class RentalProfileController extends Controller
{
    public function show(Request $request): Response
    {
        return Inertia::render('rental-provider/profile', ['profile' => $request->user()->rentalProfile]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email'],
            'business_address' => ['required', 'string', 'max:1000'],
            'business_registration_file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'valid_id_file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $request->user()->rentalProfile()->updateOrCreate([], [
            ...collect($data)->except(['business_registration_file', 'valid_id_file'])->all(),
            'slug' => Str::slug($data['business_name']).'-'.$request->user()->id,
            'business_registration_file' => $request->file('business_registration_file')->store('rental-profiles/business', 'public'),
            'valid_id_file' => $request->file('valid_id_file')->store('rental-profiles/identity', 'public'),
            'status' => RentalProfile::StatusPending,
        ]);

        return back()->with('success', 'Business documents submitted for manager validation.');
    }
}
