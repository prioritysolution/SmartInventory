<div class="header">

    <div class="header-left active">
        <a href="{{ route('agent.dashboard') }}" class="logo">
            <img src="{{ asset('template/assets/img/smart-inventory-logo.png') }}" alt="Smart Inventory">
        </a>
        <a href="{{ route('agent.dashboard') }}" class="logo-small">
            <img src="{{ asset('template/assets/img/smart-inventory-logo.png') }}" alt="Smart Inventory">
        </a>
    </div>

    <a id="mobile_btn" class="mobile_btn" href="#sidebar">
        <span class="bar-icon">
            <span></span>
            <span></span>
            <span></span>
        </span>
    </a>

    <ul class="nav user-menu">

        <li class="nav-item dropdown">
            <a href="javascript:void(0);" class="dropdown-toggle nav-link" data-bs-toggle="dropdown">
                <img src="{{ asset('agenttemplate/assets/img/icons/notification-bing.svg') }}" alt="img">
                @if (($agentNotificationCount ?? 0) > 0)
                    <span class="badge rounded-pill">{{ $agentNotificationCount > 9 ? '9+' : $agentNotificationCount }}</span>
                @endif
            </a>
            <div class="dropdown-menu notifications">
                <div class="topnav-dropdown-header">
                    <span class="notification-title">Notifications</span>
                </div>
                <div class="noti-content">
                    <ul class="notification-list">
                        @forelse ($agentNotifications ?? [] as $note)
                            <li class="notification-message">
                                <a href="{{ $note->url }}">
                                    <div class="media d-flex">
                                        <span class="avatar flex-shrink-0 d-flex align-items-center justify-content-center"
                                            style="background: {{ $note->bg }}; color: {{ $note->color }};">
                                            <i class="{{ $note->icon }}"></i>
                                        </span>
                                        <div class="media-body flex-grow-1">
                                            <p class="noti-details">{{ $note->message }}</p>
                                            <p class="noti-time"><span class="notification-time">{{ $note->when }}</span></p>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        @empty
                            <li class="notification-message">
                                <div class="media d-flex px-3 py-3">
                                    <div class="media-body flex-grow-1">
                                        <p class="noti-details text-muted mb-0">No new notifications</p>
                                    </div>
                                </div>
                            </li>
                        @endforelse
                    </ul>
                </div>
                <div class="topnav-dropdown-footer">
                    <a href="{{ route('agent.dashboard') }}">View dashboard</a>
                </div>
            </div>
        </li>

        <li class="nav-item dropdown has-arrow main-drop">
            <a href="javascript:void(0);" class="dropdown-toggle nav-link userset" data-bs-toggle="dropdown">
                <span class="user-img"><img src="{{ asset('agenttemplate/assets/img/profiles/chitra-logo.jpg') }}"
                        alt="Profile" style="object-fit: contain; background: #fff;">
                    <span class="status online"></span></span>
            </a>
            <div class="dropdown-menu menu-drop-user">
                <div class="profilename">
                    <div class="profileset">
                        <span class="user-img"><img
                                src="{{ asset('agenttemplate/assets/img/profiles/chitra-logo.jpg') }}" alt="Profile"
                                style="object-fit: contain; background: #fff;">
                            <span class="status online"></span></span>
                        <div class="profilesets">
                            <h6>{{ session('agent_name', 'Agent') }}</h6>
                            <h5>{{ session('branch_name', 'Agent') }}</h5>
                        </div>
                    </div>
                    <hr class="m-0">
                    <a class="dropdown-item logout pb-0" href="{{ route('agent.logout') }}"><img
                            src="{{ asset('agenttemplate/assets/img/icons/log-out.svg') }}" class="me-2"
                            alt="img">Logout</a>
                </div>
            </div>
        </li>
    </ul>


    <div class="dropdown mobile-user-menu">
        <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"
            aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
        <div class="dropdown-menu dropdown-menu-right">
            <a class="dropdown-item" href="{{ route('agent.logout') }}">Logout</a>
        </div>
    </div>

</div>
