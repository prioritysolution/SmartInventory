<div class="sidebar" id="sidebar">
<div class="sidebar-inner slimscroll">
<div id="sidebar-menu" class="sidebar-menu">
<ul>
    @foreach($agentMenu as $parent)
        @if(count($parent->children) > 0)
        {{-- Parent with submenus --}}
        <li class="submenu {{ collect($parent->children)->contains(fn($c) => $c->route && routeExists($c->route) && request()->routeIs($c->route)) ? 'active' : '' }}">
            <a href="javascript:void(0);">
                <i class="{{ $parent->icon ?: 'fas fa-circle' }}"></i>
                <span> {{ $parent->name }}</span>
                <span class="menu-arrow"></span>
            </a>
            <ul>
                @foreach($parent->children as $child)
                @if($child->name)
                @continue($child->route && !routeExists($child->route))
                <li>
                    <a href="{{ safeRoute($child->route) }}"
                       class="{{ $child->route && routeExists($child->route) && request()->routeIs($child->route) ? 'active' : '' }}">
                        <i class="fa-solid fa-circle"></i>
                        <span>{{ $child->name }}</span>
                    </a>
                </li>
                @endif
                @endforeach
            </ul>
        </li>
        @else
        {{-- Single menu item --}}
        <li class="{{ $parent->route && routeExists($parent->route) && request()->routeIs($parent->route) ? 'active' : '' }}">
            <a href="{{ safeRoute($parent->route) }}">
                <i class="{{ $parent->icon ?: 'fas fa-circle' }}"></i>
                <span> {{ $parent->name }}</span>
            </a>
        </li>
        @endif
    @endforeach
</ul>
</div>
</div>
</div>
