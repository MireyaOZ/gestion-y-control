<nav x-data="{ open: false }" class="relative z-[100] border-b border-white/10 bg-slate-950/60 backdrop-blur">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <div class="rounded-2xl bg-emerald-400 px-3 py-2 text-sm font-black uppercase tracking-[0.35em] text-slate-950">
                            GYT
                        </div>
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    @can('projects.view')
                        <x-nav-link :href="route('projects.index')" :active="request()->routeIs('projects.*')">
                            Proyectos
                        </x-nav-link>
                    @endcan
                    @can('tasks.view')
                        <x-nav-link :href="route('tasks.index')" :active="request()->routeIs('tasks.*')">
                            Tareas
                        </x-nav-link>
                    @endcan
                    @can('subtasks.view')
                        <x-nav-link :href="route('subtasks.index')" :active="request()->routeIs('subtasks.*')">
                            Subtareas
                        </x-nav-link>
                    @endcan
                    @can('admin.access')
                        <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.*')">
                            Administración
                        </x-nav-link>
                    @endcan
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center rounded-2xl border border-white/10 bg-white/5 px-3 py-2 text-sm font-medium leading-4 text-slate-200 hover:text-white focus:outline-none transition">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center rounded-2xl p-2 text-slate-400 hover:bg-white/5 hover:text-white focus:outline-none transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            @can('projects.view')
                <x-responsive-nav-link :href="route('projects.index')" :active="request()->routeIs('projects.*')">
                    Proyectos
                </x-responsive-nav-link>
            @endcan
            @can('tasks.view')
                <x-responsive-nav-link :href="route('tasks.index')" :active="request()->routeIs('tasks.*')">
                    Tareas
                </x-responsive-nav-link>
            @endcan
            @can('subtasks.view')
                <x-responsive-nav-link :href="route('subtasks.index')" :active="request()->routeIs('subtasks.*')">
                    Subtareas
                </x-responsive-nav-link>
            @endcan
            @can('admin.access')
                <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.*')">
                    Administración
                </x-responsive-nav-link>
            @endcan
        </div>

        <div class="border-t border-white/10 pt-4 pb-1">
            <div class="px-4">
                <div class="font-medium text-base text-slate-100">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-slate-400">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
