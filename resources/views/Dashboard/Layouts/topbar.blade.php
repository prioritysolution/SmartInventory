 <div class="header">
     <div class="main-header">

         <!-- Logo -->
         {{-- <div class="header-left">
					<a href="{{route('user-dashboard')}}" class="logo">
						<img src="{{asset('template/assets/img/logo.svg')}}" alt="Logo">
					</a>
					<a href="index.html" class="dark-logo">
						<img src="{{asset('template/assets/img/logo-white.svg')}}" alt="Logo">
					</a>
				</div> --}}
         <!-- Logo -->
         <div class="header-left">
             <a href="{{ route('user-dashboard') }}" class="logo">
                 <img src="{{ asset('template/assets/img/smart-inventory-logo.png') }}" alt="Smart Inventory">
             </a>
             <a href="{{ route('user-dashboard') }}" class="dark-logo">
                 <img src="{{ asset('template/assets/img/smart-inventory-logo.png') }}" alt="Smart Inventory">
             </a>
         </div>


         <!-- Sidebar Menu Toggle Button -->
         <a id="mobile_btn" class="mobile_btn" href="#sidebar">
             <span class="bar-icon">
                 <span></span>
                 <span></span>
                 <span></span>
             </span>
         </a>

         <div class="header-user">
             <div class="nav user-menu nav-list">
                 <div class="me-auto d-flex align-items-center" id="header-search">

                     <!-- Add -->
                     <div class="dropdown me-3">
                         <a class="btn btn-primary bg-gradient btn-xs btn-icon rounded-circle d-flex align-items-center justify-content-center"
                             data-bs-toggle="dropdown" href="javascript:void(0);" role="button">
                             <i class="isax isax-add text-white"></i>
                         </a>
                         <ul class="dropdown-menu dropdown-menu-start p-2 add-menu-dropdown">
                             @forelse ($menuLinks ?? [] as $link)
                                 <li>
                                     <a href="{{ route($link->route) }}" class="dropdown-item d-flex align-items-start">
                                         <i class="{{ $link->icon }} me-2 mt-1"></i>
                                         <span>
                                             <span class="qa-link-name">{{ $link->name }}</span>
                                             @if (!empty($link->group))
                                                 <span class="qa-link-group">{{ $link->group }}</span>
                                             @endif
                                         </span>
                                     </a>
                                 </li>
                             @empty
                                 <li><span class="dropdown-item text-muted">No quick links</span></li>
                             @endforelse
                         </ul>
                     </div>

                     <!-- Breadcrumb -->
                     <nav aria-label="breadcrumb">
                         <ol class="breadcrumb breadcrumb-divide mb-0">
                             @if (isset($pageTitle))
                                 <li class="breadcrumb-item d-flex align-items-center"><a
                                         href="{{ route('user-dashboard') }}"><i
                                             class="isax isax-home-2 me-1"></i>Dashboard</a></li>
                                 <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
                             @else
                                 <li class="breadcrumb-item active d-flex align-items-center"><span
                                         class="d-none d-lg-inline">
                                         <i class="isax isax-home-2 me-1"></i>Dashboard
                                     </span></li>
                             @endif
                         </ol>
                     </nav>

                 </div>

                 <div class="d-flex align-items-center">

                     <!-- Search -->
                     <div class="input-icon-end position-relative me-2" id="menu-search-wrapper">
                         <input type="text" id="menu-search" class="form-control" placeholder="Menu Search"
                             autocomplete="off">
                         {{-- <span class="input-icon-addon">
									<i class="isax isax-search-normal"></i>
								</span>  --}}
                         <div id="search-results" class="dropdown-menu show"
                             style="display: none; width: 100%; max-height: 400px; overflow-y: auto;"></div>
                     </div>
                     <!-- /Search -->

                     <!-- Language Dropdown -->


                     <!-- Notification -->
                     <div class="notification_item me-2">
                         <a href="#" class="btn btn-menubar position-relative" id="notification_popup"
                             data-bs-toggle="dropdown" data-bs-auto-close="outside">
                             <i class="isax isax-notification-bing5"></i>
                             <span class="position-absolute badge bg-danger border border-white notification-count {{ ($adminNotificationCount ?? 0) > 0 ? 'is-visible' : '' }}">
                                 {{ ($adminNotificationCount ?? 0) > 9 ? '9+' : ($adminNotificationCount ?? 0) }}
                             </span>
                         </a>
                         <div class="dropdown-menu p-0 dropdown-menu-end dropdown-menu-lg">

                             <div class="p-2 border-bottom">
                                 <div class="row align-items-center">
                                     <div class="col">
                                         <h6 class="m-0 fs-16 fw-semibold">Notifications</h6>
                                     </div>
                                     <div class="col-auto">
                                         <span class="fs-12 text-muted">{{ $adminNotificationCount ?? 0 }} alert(s)</span>
                                     </div>
                                 </div>
                             </div>

                             <div class="notification-body position-relative z-2 rounded-0" style="max-height: 360px; overflow-y: auto;">
                                 @forelse ($adminNotifications ?? [] as $note)
                                     <a href="{{ $note->url }}" class="dropdown-item notification-item py-2 text-wrap border-bottom">
                                         <div class="d-flex">
                                             <div class="flex-shrink-0 me-2">
                                                 <div class="avatar-sm">
                                                     <span class="avatar-title rounded-circle"
                                                         style="background: {{ $note->bg }}; color: {{ $note->color }};">
                                                         <i class="{{ $note->icon }}"></i>
                                                     </span>
                                                 </div>
                                             </div>
                                             <div class="flex-grow-1">
                                                 <p class="mb-0 fw-semibold text-dark">{{ $note->title }}</p>
                                                 <p class="mb-1 text-wrap fs-14 text-muted">{{ $note->message }}</p>
                                                 <span class="fs-12"><i class="isax isax-clock me-1"></i>{{ $note->when }}</span>
                                             </div>
                                         </div>
                                     </a>
                                 @empty
                                     <div class="dropdown-item py-4 text-center text-muted">
                                         No new notifications
                                     </div>
                                 @endforelse
                             </div>

                             <div class="p-2 rounded-bottom border-top text-center">
                                 <a href="{{ route('user-dashboard') }}" class="text-center fw-medium fs-14 mb-0">
                                     View Dashboard
                                 </a>
                             </div>

                         </div>
                     </div>

                     <!-- Quick Access -->
                     <div class="me-2">
                         <a href="javascript:void(0);" data-toggle="quick-access" title="Quick Access"
                             class="btn btn-menubar">
                             <i class="fa-solid fa-grip"></i>
                         </a>
                     </div>

                     <!-- Light/Dark Mode Button -->
                     <div class="me-2 theme-item">
                         <a href="javascript:void(0);" id="dark-mode-toggle" class="theme-toggle btn btn-menubar">
                             <i class="isax isax-moon"></i>
                         </a>
                         <a href="javascript:void(0);" id="light-mode-toggle" class="theme-toggle btn btn-menubar">
                             <i class="isax isax-sun-1"></i>
                         </a>
                     </div>

                     <!-- User Dropdown -->
                     <div class="dropdown profile-dropdown">
                         <a href="javascript:void(0);" class="dropdown-toggle d-flex align-items-center"
                             data-bs-toggle="dropdown" data-bs-auto-close="outside">
                             <span class="avatar online">
                                 <img src="{{ asset('template/assets/img/profiles/chitra-logo.jpg') }}" alt="Profile"
                                     class="img-fluid rounded-circle" style="object-fit: contain; background: #fff;">
                             </span>
                         </a>
                         <div class="dropdown-menu p-2">
                             <div class="d-flex align-items-center bg-light rounded-1 p-2 mb-2">
                                 <span class="avatar avatar-lg me-2">
                                     <img src="{{ asset('template/assets/img/profiles/chitra-logo.jpg') }}"
                                         alt="Profile" class="rounded-circle" style="object-fit: contain; background: #fff;">
                                 </span>
                                 <div>
                                     <h6 class="fs-14 fw-medium mb-1">{{ session('user_name', 'User') }}</h6>
                                     <p class="fs-13 mb-0">{{ session('branch_name', 'Branch') }}</p>
                                 </div>

                             </div>

                             <hr class="dropdown-divider my-2">

                             <!-- Item-->
                             <a class="dropdown-item logout d-flex align-items-center" href="{{ route('logout') }}">
                                 <i class="isax isax-logout me-2"></i>Sign Out
                             </a>
                         </div>
                     </div>

                 </div>
             </div>
         </div>

         <!-- Mobile Menu -->
         <div class="dropdown mobile-user-menu profile-dropdown">
             <a href="javascript:void(0);" class="dropdown-toggle d-flex align-items-center"
                 data-bs-toggle="dropdown" data-bs-auto-close="outside">
                 <span class="avatar avatar-md online">
                     <img src="{{ asset('template/assets/img/profiles/chitra-logo.jpg') }}" alt="Profile"
                         class="img-fluid rounded-circle" style="object-fit: contain; background: #fff;">
                 </span>
             </a>
             <div class="dropdown-menu p-2 mt-0">
                 <a class="dropdown-item logout d-flex align-items-center" href="{{ route('logout') }}">
                     <i class="isax isax-logout me-2"></i>Signout
                 </a>
             </div>
         </div>
         <!-- /Mobile Menu -->

     </div>
 </div>
