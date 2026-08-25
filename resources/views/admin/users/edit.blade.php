@extends('layouts.admin')

@section('title', 'Editar Usuario')
@section('page-title', 'Editar Usuario: ' . $user->name)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center space-x-2 text-sm font-bold text-slate-500 hover:text-slate-800 transition">
            <i class="fas fa-arrow-left text-xs"></i>
            <span>Volver a Usuarios</span>
        </a>
    </div>

    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80">
        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nombre Completo *
                    </label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Correo Electrónico *
                    </label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nueva Contraseña (dejar en blanco para mantener la actual)
                    </label>
                    <input type="password" name="password"
                           placeholder="••••••••"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Rol de Acceso *
                    </label>
                    <select name="role" required {{ $user->id === Auth::id() ? 'disabled' : '' }}
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                        <option value="personal" {{ old('role', $user->role) === 'personal' ? 'selected' : '' }}>Personal de Atención / Cocina</option>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrador (Control Total)</option>
                    </select>
                </div>
            </div>

            <!-- Estado Activo -->
            @if($user->id !== Auth::id())
                <div class="border-t border-slate-100 pt-6">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                               class="w-5 h-5 text-red-600 rounded-lg border-slate-300 focus:ring-red-500">
                        <div>
                            <span class="text-sm font-bold text-slate-800 block">Cuenta Habilitada / Activa</span>
                            <span class="text-xs text-slate-400">Si se desmarca, el usuario no podrá ingresar al sistema.</span>
                        </div>
                    </label>
                </div>
            @endif

            <div class="pt-4 flex justify-end space-x-3">
                <a href="{{ route('admin.users.index') }}" class="px-5 py-3 rounded-2xl border border-slate-200 text-slate-600 font-bold text-sm hover:bg-slate-50 transition">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-3 rounded-2xl bg-gradient-to-r from-red-600 to-rose-600 text-white font-bold text-sm shadow-md shadow-red-500/20 hover:from-red-700 hover:to-rose-700 transition">
                    Actualizar Usuario
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
