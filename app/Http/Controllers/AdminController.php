<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\AdminGate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function loginForm(Request $request): View|RedirectResponse
    {
        abort_unless(AdminGate::enabled(), 404);

        if (AdminGate::check($request)) {
            return redirect()->route('admin.index');
        }

        return view('admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        abort_unless(AdminGate::enabled(), 404);

        $data = $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (! AdminGate::attempt($data['password'])) {
            return back()->withErrors(['password' => 'Password is incorrect.']);
        }

        AdminGate::grant($request);

        return redirect()->intended(route('admin.index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        abort_unless(AdminGate::enabled(), 404);

        AdminGate::revoke($request);

        return redirect()->route('admin.login');
    }

    public function index(): View
    {
        $devices = User::query()
            ->whereNotNull('device')
            ->withCount([
                'learnedKotobas as learned_count' => fn ($query) => $query->where('learned', true),
                'learnedKotobas as marked_count',
            ])
            ->orderByDesc('last_active_at')
            ->orderByDesc('id')
            ->paginate(50);

        return view('admin.index', [
            'totalDevices' => User::query()->whereNotNull('device')->count(),
            'devices' => $devices,
        ]);
    }
}
