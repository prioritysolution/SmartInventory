<div class="sidebar" id="sidebar">
<div class="sidebar-inner slimscroll">
<div id="sidebar-menu" class="sidebar-menu">
<ul>
    @foreach($agentMenu as $parent)
        @if(count($parent->children) > 0)
        {{-- Parent with submenus --}}
        <li class="submenu {{ collect($parent->children)->contains(fn($c) => $c->route && request()->routeIs($c->route)) ? 'active' : '' }}">
            <a href="javascript:void(0);">
                @if($parent->icon)
                    <i class="{{ $parent->icon }}"></i>
                @else
                    <img src="{{ asset('agenttemplate/assets/img/icons/product.svg') }}" alt="img">
                @endif
                <span> {{ $parent->name }}</span>
                <span class="menu-arrow"></span>
            </a>
            <ul>
                @foreach($parent->children as $child)
                @if($child->name)
                <li>
                    <a href="{{ $child->route ? route($child->route) : 'javascript:void(0);' }}"
                       class="{{ $child->route && request()->routeIs($child->route) ? 'active' : '' }}">
                        <span>{{ $child->name }}</span>
                    </a>
                </li>
                @endif
                @endforeach
            </ul>
        </li>
        @else
        {{-- Single menu item --}}
        <li class="{{ $parent->route && request()->routeIs($parent->route) ? 'active' : '' }}">
            <a href="{{ $parent->route ? route($parent->route) : 'javascript:void(0);' }}">
                @if($parent->icon)
                    <i class="{{ $parent->icon }}"></i>
                @else
                    <img src="{{ asset('agenttemplate/assets/img/icons/dashboard.svg') }}" alt="img">
                @endif
                <span> {{ $parent->name }}</span>
            </a>
        </li>
        @endif
    @endforeach
</ul>
</div>
</div>
</div>
