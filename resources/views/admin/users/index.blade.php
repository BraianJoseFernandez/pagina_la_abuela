@extends('layouts.admin')

@section('title', 'Gestión de Usuarios')
@section('page-title', 'Usuarios y Personal')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl shadow-sm border border-slate-200/80">
        <div>
            <h3 class="text-xl font-black text-slate-800">Cuentas con Acceso al Panel</h3>
            <p class="text-xs text-slate-500 mt-0.5">Administra los accesos de administradores y personal de atención</p>
        </div>
        <a href="{{ route('admin.users.create') }}"
           class="px-5 py-3 rounded-2xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-bold text-sm shadow-md shadow-red-500/20 flex items-center justify-center space-x-2 transition">
            <i class="fas fa-user-plus"></i>
            <span>Nuevo Usuario</span>
        </a>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-400 tracking-wider">
                    <tr>
                        <th class="py-4 px-6">Usuario</th>
                        <th class="py-4 px-6">Rol de Acceso</th>
                        <th class="py-4 px-6">Estado</th>
                        <th class="py-4 px-6">Fecha de Creación</th>
                        <th class="py-4 px-6 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($users as $user)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-6">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-red-500 to-rose-600 text-white font-bold flex items-center justify-center shadow-xs">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 text-base leading-tight">{{ $user->name }}</h4>
                                        <span class="text-xs text-slate-400">{{ $user->email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider {{ $user->isAdmin() ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                                    {{ $user->isAdmin() ? 'Administrador' : 'Personal' }}
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $user->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ $user->is_active ? 'Activo' : 'Desactivado' }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-xs text-slate-400">
                                {{ $user->created_at->format('d/m/Y') }}
                            </td>
                            <td class="py-4 px-6 text-right space-x-2">
                                <a href="{{ route('admin.users.edit', $user) }}" class="p-2 rounded-xl bg-slate-100 hover:bg-purple-100 hover:text-purple-700 text-slate-600 text-xs font-bold transition inline-block" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>

                                @if($user->id !== Auth::id())
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de eliminar al usuario &quot;{{ $user->name }}&quot;?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded-xl bg-slate-100 hover:bg-rose-100 hover:text-rose-700 text-slate-600 text-xs font-bold transition" title="Eliminar">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
