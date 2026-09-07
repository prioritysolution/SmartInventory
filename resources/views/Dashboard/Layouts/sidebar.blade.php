      <!-- Sidenav Menu Start -->
      <div class="two-col-sidebar" id="two-col-sidebar">

          <div class="sidebar custom-wrap-sidebar " id="sidebar-two">

              <!-- Start Logo -->
              <div class="sidebar-logo custom-logo-fix">
                  <a href="{{ route('user-dashboard') }}" class="logo logo-normal">
                      <img src="{{ asset('template/assets/img/smart-inventory-logo.png') }}" alt="Smart Inventory">
                  </a>
                  <a href="{{ route('user-dashboard') }}" class="logo-small">
                      <img src="{{ asset('template/assets/img/smart-inventory-logo.png') }}" alt="Smart Inventory">
                  </a>
                  <a href="{{ route('user-dashboard') }}" class="dark-logo">
                      <img src="{{ asset('template/assets/img/smart-inventory-logo.png') }}" alt="Smart Inventory">
                  </a>
                  <a href="{{ route('user-dashboard') }}" class="dark-small">
                      <img src="{{ asset('template/assets/img/smart-inventory-logo.png') }}" alt="Smart Inventory">
                  </a>
                  <!-- Sidebar Hover Menu Toggle Button -->
                  <a id="toggle_btn" href="javascript:void(0);">
                      <i class="isax isax-menu-1"></i>
                  </a>
              </div>
              <!-- End Logo -->

              <!-- Search -->
              <div class="sidebar-search">
                  <div class="input-icon-end position-relative">
                      <input type="text" class="form-control" placeholder="Search">
                      <span class="input-icon-addon">
                          <i class="isax isax-search-normal"></i>
                      </span>
                  </div>
              </div>
              <!-- /Search -->

              <!--- Sidenav Menu -->
              <div class="sidebar-inner" data-simplebar>
                  <div id="sidebar-menu" class="sidebar-menu">
                      <ul>
                          <li class="menu-title"><span>Main Menu</span></li>
                          @foreach ($menu ?? [] as $parentId => $parent)
                              @if (count($parent->children) > 0)
                                  <li class="submenu">
                                      <a href="javascript:void(0);"
                                          style="display: flex; align-items: center; gap: 10px;">
                                          <i class="{{ $parent->icon ?: 'isax isax-box' }}"></i>
                                          <span>{{ $parent->name }}</span>
                                          <span class="menu-arrow"></span>
                                      </a>
                                      <ul style="padding-left: 20px;">
                                          @foreach ($parent->children as $child)
                                              <li>
                                                  <a href="{{ $child->route ? route($child->route) : 'javascript:void(0);' }}"
                                                      class="{{ $child->route && request()->routeIs($child->route) ? 'active' : '' }}"
                                                      style="font-weight: 300; display: flex; align-items: flex-start; gap: 10px;">
                                                      <i class="fa-solid fa-circle"
                                                          style="font-size: 6px; margin-top: 8px; flex-shrink: 0;"></i>
                                                      {{ $child->name }}
                                                  </a>
                                              </li>
                                          @endforeach
                                      </ul>
                                  </li>
                              @else
                                  <li class="{{ $parent->route && request()->routeIs($parent->route) ? 'active' : '' }}">
                                      <a href="{{ $parent->route ? route($parent->route) : 'javascript:void(0);' }}"
                                          style="display: flex; align-items: center; gap: 10px;">
                                          <i class="{{ $parent->icon ?: 'isax isax-box' }}"></i>
                                          <span>{{ $parent->name }}</span>
                                      </a>
                                  </li>
                              @endif
                          @endforeach
                      </ul>
                  </div>
              </div>
          </div>
      </div>
      <!-- Sidenav Menu End -->
