<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HasActiveCompany;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CompanyAdminController extends Controller
{
    use HasActiveCompany;

    private function company()
    {
        return $this->activeCompany();
    }

    public function index()
    {
        $company = $this->company();
        $admins = User::where('company_id', $company->id)
            ->where('role', 'company_admin')
            ->latest()
            ->get();

        return view('client.admins.index', compact('admins', 'company'));
    }

    public function create()
    {
        $company = $this->company();
        return view('client.admins.create', compact('company'));
    }

    public function store(Request $request)
    {
        $company = $this->company();

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $admin = User::create([
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'phone'      => $validated['phone'],
            'password'   => Hash::make(\Illuminate\Support\Str::random(16)),
            'pin_code'   => null,
            'company_id' => $company->id,
            'role'       => 'company_admin',
            'is_active'  => true,
        ]);

        SmsService::sendWelcomeMob($admin);

        return redirect()->route('client.admins.index')
            ->with('success', "Admin {$admin->name} créé. Un SMS de bienvenue a été envoyé au {$admin->phone}.");
    }

    public function edit(User $admin)
    {
        $this->authorizeAdmin($admin);
        $company = $this->company();
        return view('client.admins.edit', compact('admin', 'company'));
    }

    public function update(Request $request, User $admin)
    {
        $this->authorizeAdmin($admin);

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($admin->id)],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $admin->update($validated);

        return redirect()->route('client.admins.index')
            ->with('success', "Admin {$admin->name} mis à jour.");
    }

    public function resetPin(User $admin)
    {
        $this->authorizeAdmin($admin);

        $admin->update(['pin_code' => null]);

        return redirect()->route('client.admins.index')
            ->with('success', "PIN réinitialisé pour {$admin->name}. Il devra définir un nouveau code PIN lors de sa prochaine connexion.");
    }

    public function toggleActive(User $admin)
    {
        $this->authorizeAdmin($admin);
        $admin->update(['is_active' => !$admin->is_active]);

        $status = $admin->is_active ? 'activé' : 'désactivé';
        return redirect()->route('client.admins.index')
            ->with('success', "Compte de {$admin->name} $status.");
    }

    public function destroy(User $admin)
    {
        $this->authorizeAdmin($admin);
        $name = $admin->name;
        $admin->delete();

        return redirect()->route('client.admins.index')
            ->with('success', "Admin $name supprimé.");
    }

    private function authorizeAdmin(User $admin): void
    {
        $company = $this->company();
        abort_if($admin->company_id !== $company->id || $admin->role !== 'company_admin', 403);
    }
}
