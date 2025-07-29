<?php

namespace App\Http\Controllers;

use App\Mail\UserPasswordMail;
use App\Mail\WelcomeEmail;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;


class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', '');
        $status = $request->get('status', '');
        $role = $request->get('role', '');

        $query = User::with(['roles', 'projects', 'tasks'])->withTrashed();

        // Aplicar filtros
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nickname', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($role) {
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('value', $role);
            });
        }

        $users = $query->paginate($perPage);

        // Calculate user statistics
        $allUsers = User::with(['roles'])->withTrashed()->get();
        $stats = [
            'total' => $allUsers->count(),
            'active' => $allUsers->where('status', 'active')->count(),
            'developers' => $allUsers->filter(function ($user) {
                return $user->roles->contains('value', 'developer');
            })->count(),
            'admins' => $allUsers->filter(function ($user) {
                return $user->roles->contains('value', 'admin');
            })->count(),
        ];

        return Inertia::render('User/Index', [
            'users' => $users,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'role' => $role,
                'per_page' => $perPage,
            ]
        ]);
    }

    public function store(Request $request, EmailService $emailService): RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'nickname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'hour_value' => 'required|integer|min:0',
            'work_time' => 'required|string|min:0',
            'password' => 'nullable|string|min:8',
            'password_option' => 'required|in:manual,email',
        ]);

        // Generar contraseña según la opción
        if ($validatedData['password_option'] === 'email') {
            $password = Str::random(12); // Contraseña aleatoria de 12 caracteres
        } else {
            $password = $validatedData['password'] ?? 'developer123*';
        }

        $user = User::create([
            'name' => $validatedData['name'],
            'nickname' => $validatedData['nickname'],
            'email' => $validatedData['email'],
            'password' => Hash::make($password),
            'hour_value' => $validatedData['hour_value'],
            'work_time' => $validatedData['work_time'],
        ]);

        // Asignar rol de developer por defecto
        $developerRole = \App\Models\Role::where('value', 'developer')->first();
        if ($developerRole) {
            $user->roles()->attach($developerRole->id);
        }

        // Enviar email si se seleccionó esa opción
        if ($validatedData['password_option'] === 'email') {
            $emailSent = $emailService->sendWelcomeEmail($user, $password);
            $message = $emailSent 
                ? 'User created successfully. Welcome email sent.'
                : 'User created successfully, but there was an error sending the welcome email.';
        } else {
            $message = 'User created successfully.';
        }

        return redirect()->route('users.index')->with('success', $message);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nickname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'nullable|string|min:8',
            'hour_value' => 'required|integer',
            'status' => 'required|string|max:255',
            'work_time' => 'required|string|max:255',
        ]);

        $user = User::withTrashed()->where('id', $id)->first();

        if (trim($validated['password']) !== '') {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            $validated['password'] = $user->password;
        }

        if ($validated['status'] == 'active' && $user->status == 'inactive') {
            $validated['status'] = 'active';
            $user->restore();

        } elseif ($validated['status'] == 'inactive' && $user->status == 'active') {
            $validated['status'] = 'inactive';
        }

        $user->update([
            'name' => $validated['name'],
            'nickname' => $validated['nickname'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'hour_value' => $validated['hour_value'],
            'status' => $validated['status'],
            'work_time' => $validated['work_time'],
        ]);

        if ($user->status == 'inactive' && $validated['status'] == 'inactive') {
            $this->destroy($id);
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully');
    }

    public function destroy($id): void
    {
        $user = User::where('id', $id)->firstOrFail();
        $user->delete();

    }
}
