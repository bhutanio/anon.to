<header class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <nav class="flex items-center justify-between flex-wrap gap-4">
            {{-- Left Side: Logo, Home, and Future Features --}}
            <div class="flex items-center gap-6">
                {{-- Logo and Home Link --}}
                <a href="/" class="flex items-center gap-3 hover:opacity-80 transition">
                    <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">anon.to</h1>
                </a>

                {{-- Future Features - Hidden on Mobile, Shown on Desktop --}}
                <div class="hidden lg:flex items-center gap-4">
                    {{-- QR Code Link --}}
                    <flux:link
                        href="/qr"
                        icon="qr-code"
                        class="text-sm font-medium"
                    >
                        QR Code
                    </flux:link>

                    {{-- Notes Link --}}
                    <flux:link
                        href="/notes/create"
                        icon="document-text"
                        class="text-sm font-medium"
                    >
                        Notes
                    </flux:link>
                </div>
            </div>

            {{-- Right Side: Theme Switcher and Auth Links --}}
            <div class="flex items-center gap-4 flex-wrap">
                {{-- Theme Switcher --}}
                <div class="hidden sm:block">
                    <flux:dropdown x-data align="end">
                        <flux:button variant="subtle" square class="group" aria-label="Preferred color scheme">
                            <flux:icon.sun x-show="$flux.appearance === 'light'" variant="mini" class="text-zinc-500 dark:text-white" />
                            <flux:icon.moon x-show="$flux.appearance === 'dark'" variant="mini" class="text-zinc-500 dark:text-white" />
                            <flux:icon.moon x-show="$flux.appearance === 'system' && $flux.dark" variant="mini" />
                            <flux:icon.sun x-show="$flux.appearance === 'system' && ! $flux.dark'" variant="mini" />
                        </flux:button>

                        <flux:menu>
                            <flux:menu.item icon="sun" x-on:click="$flux.appearance = 'light'">Light</flux:menu.item>
                            <flux:menu.item icon="moon" x-on:click="$flux.appearance = 'dark'">Dark</flux:menu.item>
                            <flux:menu.item icon="computer-desktop" x-on:click="$flux.appearance = 'system'">System</flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </div>

                {{-- Authentication Links --}}
                @auth
                    <div class="flex items-center gap-4">
                        <flux:link href="/dashboard" class="text-sm font-medium">
                            Dashboard
                        </flux:link>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <flux:button type="submit" variant="ghost" size="sm">
                                Logout
                            </flux:button>
                        </form>
                    </div>
                @else
                    <div class="flex items-center gap-4">
                        <flux:link href="/login" class="text-sm font-medium">
                            Sign In
                        </flux:link>
                        <flux:button href="/register" variant="primary">
                            Sign Up
                        </flux:button>
                    </div>
                @endauth
            </div>
        </nav>
    </div>
</header>
