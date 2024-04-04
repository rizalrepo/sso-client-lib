{{-- untuk implementasi akses ke menu portal, edit-password dan logout silahkan edit file main blade --}}

<div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
    @if(session()->has('countAccess'))
        @if (session('countAccess') > 1)
            <a class="dropdown-item" href="{{ route('sso.portal') }}">Portal</a>
        @endif
    @endif
    <a class="dropdown-item" href="{{ route('sso.edit-password') }}" onclick="saveReferrer()">
        Edit Password
    </a>
    <a class="dropdown-item" href="{{ route('sso.logout') }}"
        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        {{ __('Logout') }}
    </a>

    <form id="logout-form" action="{{ route('sso.logout') }}" method="GET" class="d-none">
        @csrf
    </form>
</div>

{{-- tambkan juga function js dibawah ini untuk menyimpan previous url saat edit-password --}}

<script>
    function saveReferrer() {
        var previousUrl = document.referrer;
        var previousUrlInput = document.getElementById("previous_url");
        if (previousUrlInput) {
            previousUrlInput.value = previousUrl;
        }
    }
</script>