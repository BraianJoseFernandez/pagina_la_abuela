@extends('layouts.admin')

@section('title', 'Gestión de Usuarios')
@section('page-title', 'Usuarios y Personal')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 sm:p-6 rounded-3xl shadow-sm border border-slate-200/80">
        <div>
            <h3 class="text-xl font-black text-slate-800">Cuentas con Acceso al Panel</h3>
            <p class="text-xs text-slate-500 mt-0.5">Administra los accesos de administradores y personal de atención</p>
        </div>
        <a href="{{ route('admin.users.create') }}"
           class="px-5 py-2.5 rounded-2xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-bold text-sm shadow-md shadow-red-500/20 flex items-center justify-center space-x-2 transition flex-shrink-0">
            <i class="fas fa-user-plus"></i>
            <span>Nuevo Usuario</span>
        </a>
    </div>

    <!-- Contenedor de Usuarios (Desktop: Tabla / Mobile: Tarjetas Adaptativas) -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
        <!-- Vista Desktop: Tabla Compacta con Acciones Sticky -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-400 tracking-wider">
                    <tr>
                        <th class="py-3 px-4 min-w-[200px]">Usuario</th>
                        <th class="py-3 px-3.5 whitespace-nowrap">Rol de Acceso</th>
                        <th class="py-3 px-3.5 whitespace-nowrap">Estado</th>
                        <th class="py-3 px-3.5 whitespace-nowrap">Fecha de Creación</th>
                        <th class="py-3 px-4 text-right sticky-action-col bg-slate-50 whitespace-nowrap">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($users as $user)
                        <tr class="hover:bg-slate-50/80 transition group">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-red-500 to-rose-600 text-white font-bold flex items-center justify-center shadow-xs flex-shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="font-bold text-slate-800 text-sm sm:text-base leading-tight truncate">{{ $user->name }}</h4>
                                        <span class="text-xs text-slate-400 block truncate">{{ $user->email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-xs font-black uppercase tracking-wider whitespace-nowrap inline-block {{ $user->isAdmin() ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                                    {{ $user->isAdmin() ? 'Administrador' : 'Personal' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold whitespace-nowrap inline-block {{ $user->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ $user->is_active ? 'Activo' : 'Desactivado' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-3.5 text-xs text-slate-400 whitespace-nowrap">
                                {{ $user->created_at->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1.5 sticky-action-col bg-white group-hover:bg-slate-50 transition-colors whitespace-nowrap">
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

        <!-- Vista Mobile: Tarjetas Adaptativas (Perfecto para iPhone y Android sin scroll horizontal) -->
        <div class="block lg:hidden p-3 space-y-3 bg-slate-100/60">
            @forelse($users as $user)
                <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-xs space-y-3">
                    <!-- Cabecera: Avatar + Nombre + Email + Badges de Rol y Estado -->
                    <div class="flex items-start justify-between gap-2.5">
                        <div class="flex items-center space-x-3 min-w-0">
                            <div class="w-11 h-11 rounded-full bg-gradient-to-tr from-red-500 to-rose-600 text-white font-bold flex items-center justify-center text-base shadow-xs flex-shrink-0">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-bold text-slate-800 text-sm leading-snug truncate">{{ $user->name }}</h4>
                                <span class="text-xs text-slate-400 block truncate">{{ $user->email }}</span>
                            </div>
                        </div>

                        <div class="flex flex-col items-end gap-1 flex-shrink-0">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $user->isAdmin() ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                                {{ $user->isAdmin() ? 'Admin' : 'Personal' }}
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $user->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                {{ $user->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                    </div>

                    <!-- Fila Inferior: Fecha de Creación y Botones de Acción -->
                    <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-slate-400 flex items-center space-x-1">
                            <i class="fas fa-calendar-alt text-[10px] text-slate-300"></i>
                            <span>Creado: {{ $user->created_at->format('d/m/Y') }}</span>
                        </span>

                        <div class="flex items-center space-x-2 flex-shrink-0">
                            <a href="{{ route('admin.users.edit', $user) }}"
                               class="px-3 py-1.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 text-xs font-bold transition flex items-center space-x-1">
                                <i class="fas fa-edit text-xs"></i>
                                <span>Editar</span>
                            </a>

                            @if($user->id !== Auth::id())
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de eliminar al usuario &quot;{{ $user->name }}&quot;?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-bold transition" title="Eliminar">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-slate-400 text-xs bg-white rounded-2xl p-6">
                    No se encontraron usuarios registrados.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
