<?php

namespace App\Http\Controllers;

use App\Mail\InvoiceMail;
use App\Mail\WelcomeMail;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Module;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RegistrationController extends Controller
{
    public function showModuleSelection()
    {
        $modules = Module::active()->get();
        return view('register.modules', compact('modules'));
    }

    public function selectModule(Request $request, Module $module)
    {
        session(['registration.module_id' => $module->id]);
        return redirect()->route('register.plans');
    }

    public function showPlanSelection()
    {
        if (!session('registration.module_id')) {
            return redirect()->route('register.modules');
        }
        $module = Module::findOrFail(session('registration.module_id'));
        $plans  = Plan::active()->where('module_id', $module->id)->orderBy('price')->get();
        return view('register.plans', compact('plans', 'module'));
    }

    public function selectPlan(Request $request, Plan $plan)
    {
        session([
            'registration.plan_id'   => $plan->id,
            'registration.module_id' => $plan->module_id,
        ]);
        return redirect()->route('register.owner');
    }

    public function showOwnerForm()
    {
        if (!session('registration.plan_id')) {
            return redirect()->route('register.plans');
        }
        $plan  = Plan::findOrFail(session('registration.plan_id'));
        $owner = session('registration.owner', []);
        return view('register.owner', compact('plan', 'owner'));
    }

    public function submitOwner(Request $request)
    {
        if (!session('registration.plan_id')) {
            return redirect()->route('register.plans');
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email', 'max:200'],
            'phone'      => ['required', 'string', 'max:20'],
            'address'    => ['required', 'string', 'max:255'],
        ]);

        session(['registration.owner' => $validated]);
        return redirect()->route('register.company');
    }

    public function showCompanyForm()
    {
        if (!session('registration.plan_id') || !session('registration.owner')) {
            return redirect()->route('register.plans');
        }
        $plan    = Plan::findOrFail(session('registration.plan_id'));
        $company = session('registration.company', []);
        return view('register.company', compact('plan', 'company'));
    }

    public function submitCompany(Request $request)
    {
        if (!session('registration.plan_id') || !session('registration.owner')) {
            return redirect()->route('register.plans');
        }

        $validated = $request->validate([
            'company_name'    => ['required', 'string', 'max:200'],
            'company_address' => ['required', 'string', 'max:500'],
        ]);

        session(['registration.company' => $validated]);
        return redirect()->route('register.payment');
    }

    public function showPayment()
    {
        if (!session('registration.plan_id') || !session('registration.owner') || !session('registration.company')) {
            return redirect()->route('register.plans');
        }
        $plan = Plan::findOrFail(session('registration.plan_id'));
        $paymentSettings = \App\Models\SiteSetting::all_settings();
        return view('register.payment', compact('plan', 'paymentSettings'));
    }

    public function processPayment(Request $request)
    {
        if (!session('registration.plan_id') || !session('registration.owner') || !session('registration.company')) {
            return redirect()->route('register.plans');
        }

        $plan = Plan::findOrFail(session('registration.plan_id'));

        $allMethods = ['orange_money', 'wave', 'visa', 'bank'];
        $settings   = \App\Models\SiteSetting::all_settings();
        $enabled    = array_filter($allMethods, fn($m) => !empty($settings['payment_' . $m]));
        $allowed    = !empty($enabled) ? implode(',', $enabled) : implode(',', $allMethods);

        $rules = [
            'payment_method' => $plan->price > 0
                ? ['required', "in:$allowed"]
                : ['nullable'],
        ];

        $request->validate($rules);

        $ownerData     = session('registration.owner');
        $companyData   = session('registration.company');
        $paymentMethod = $request->input('payment_method', 'gratuit');

        try {
            DB::transaction(function () use ($ownerData, $companyData, $plan, $paymentMethod) {
                // Create company (owner_user_id filled after user is found/created)
                $company = Company::create([
                    'name'             => $companyData['company_name'],
                    'address'          => $companyData['company_address'],
                    'owner_first_name' => $ownerData['first_name'],
                    'owner_last_name'  => $ownerData['last_name'],
                    'owner_email'      => $ownerData['email'],
                    'owner_phone'      => $ownerData['phone'],
                    'owner_address'    => $ownerData['address'],
                    'status'           => $plan->price == 0 ? 'active' : 'pending',
                ]);

                // Reuse existing web account (role=client) or create new one
                $existingUser  = User::where('email', $ownerData['email'])
                                     ->where('role', 'client')
                                     ->first();
                $plainPassword = null;
                $isNewUser     = false;

                if ($existingUser) {
                    $user = $existingUser;
                    // Set company_id to first company if not set yet
                    if (!$user->company_id) {
                        $user->update(['company_id' => $company->id]);
                    }
                } else {
                    $plainPassword = Str::random(10);
                    $isNewUser     = true;
                    $user = User::create([
                        'name'       => $ownerData['first_name'] . ' ' . $ownerData['last_name'],
                        'email'      => $ownerData['email'],
                        'password'   => Hash::make($plainPassword),
                        'company_id' => $company->id,
                        'role'       => 'client',
                    ]);
                }

                // Link company to its web owner
                $company->update(['owner_user_id' => $user->id]);

                // Auto-create the mobile company_admin account for this company
                // (if no existing company_admin for this phone + company)
                $mobilePhone = $ownerData['phone'];
                $alreadyHasMobileAdmin = User::where('phone', $mobilePhone)
                    ->where('role', 'company_admin')
                    ->where('company_id', $company->id)
                    ->exists();

                if (!$alreadyHasMobileAdmin) {
                    User::create([
                        'name'       => $ownerData['first_name'] . ' ' . $ownerData['last_name'],
                        'email'      => $ownerData['email'],
                        'phone'      => $mobilePhone,
                        'password'   => Hash::make(Str::random(16)),
                        'company_id' => $company->id,
                        'role'       => 'company_admin',
                        'is_active'  => true,
                    ]);
                }

                // Create subscription
                $subscription = Subscription::create([
                    'company_id' => $company->id,
                    'plan_id'    => $plan->id,
                    'start_date' => now(),
                    'end_date'   => now()->addMonth(),
                    'status'     => 'active',
                ]);

                // Create invoice
                $invoiceStatus = ($plan->price == 0) ? 'paid' : 'pending';
                $invoice = Invoice::create([
                    'company_id'      => $company->id,
                    'subscription_id' => $subscription->id,
                    'invoice_number'  => Invoice::generateInvoiceNumber(),
                    'amount'          => $plan->price,
                    'status'          => $invoiceStatus,
                    'payment_method'  => $paymentMethod,
                    'paid_at'         => ($plan->price == 0) ? now() : null,
                ]);

                // Send emails
                try {
                    if ($isNewUser) {
                        Mail::to($user->email)->send(new WelcomeMail($company, $user, $plainPassword));
                    }
                    Mail::to($user->email)->send(new InvoiceMail($invoice));
                } catch (\Exception $e) {
                    Log::warning('Email sending failed: ' . $e->getMessage());
                }

                session(['registration.success_company' => $company->name]);
            });
        } catch (\Exception $e) {
            Log::error('Registration failed: ' . $e->getMessage());
            return back()->withErrors(['general' => 'Une erreur est survenue. Veuillez réessayer.']);
        }

        // Clear registration session
        session()->forget(['registration.module_id', 'registration.plan_id', 'registration.owner', 'registration.company']);

        return redirect()->route('register.success');
    }

    public function showSuccess()
    {
        $companyName = session('registration.success_company', 'votre entreprise');
        session()->forget('registration.success_company');
        return view('register.success', compact('companyName'));
    }
}
