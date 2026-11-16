<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVolunteerRequest;
use App\Models\Volunteer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VolunteerController extends Controller
{
    public function create(): View
    {
        return view('volunteers.create');
    }

    public function store(StoreVolunteerRequest $request): RedirectResponse
    {
        if ($request->isBot()) {
            return redirect()->route('volunteers.thanks');
        }

        $data = $request->safe()->except(['accept_terms', 'website']);

        $volunteer = Volunteer::create($data + [
            'terms_accepted_at' => now(),
            'terms_version' => config('laura.terminos_version'),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 250, ''),
            'source' => $request->session()->get('lr_source'),
        ]);

        return redirect()->route('volunteers.thanks')->with('volunteer_greeting', $volunteer->greeting);
    }

    public function thanks(): View
    {
        return view('volunteers.thanks', ['greeting' => session('volunteer_greeting')]);
    }
}
