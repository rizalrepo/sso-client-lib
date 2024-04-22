<!DOCTYPE html><html><head><meta charset="utf-8"><title>readme.md</title><style></style></head><body id="preview">
<h1 class="code-line" data-line-start="0" data-line-end="1"><a id="Client_Usage_Config_0"></a>Client Usage Config</h1>
<h1 class="code-line" data-line-start="2" data-line-end="3"><a id="Routes_2"></a>Routes</h1>
<p class="has-line-data" data-line-start="3" data-line-end="4">add code to web.php</p>
<pre><code class="has-line-data" data-line-start="5" data-line-end="17">Route::controller(SSOController::class)-&gt;group(function () {
    Route::get(&quot;/sso/login&quot;, 'getLogin')-&gt;name(&quot;sso.login&quot;);
    Route::get(&quot;/callback&quot;, 'getCallback')-&gt;name(&quot;sso.callback&quot;);
    Route::get(&quot;/sso/connect&quot;, 'connectUser')-&gt;name(&quot;sso.connect&quot;);

    Route::middleware('auth')-&gt;group(function () {
        Route::get(&quot;/sso/logout&quot;, 'logout')-&gt;name(&quot;sso.logout&quot;);
        Route::get(&quot;/sso/edit-password&quot;, 'editPassword')-&gt;name(&quot;sso.edit-password&quot;);
        Route::get(&quot;/sso/portal&quot;, 'portal')-&gt;name(&quot;sso.portal&quot;);
    });
});
</code></pre>
<h1 class="code-line" data-line-start="17" data-line-end="18"><a id="Table_17"></a>Table</h1>
<p class="has-line-data" data-line-start="18" data-line-end="19">modify file users migration with :</p>
<pre><code class="has-line-data" data-line-start="20" data-line-end="31">Schema::create('users', function (Blueprint $table) {
    $table-&gt;id();
    $table-&gt;string('name');
    $table-&gt;string('username')-&gt;unique();
    $table-&gt;string('phone')-&gt;unique();
    $table-&gt;bigInteger('oauth_client_role_id');
    $table-&gt;timestamp('email_verified_at')-&gt;nullable();
    $table-&gt;rememberToken();
    $table-&gt;timestamps();
});
</code></pre>
<h1 class="code-line" data-line-start="31" data-line-end="32"><a id="Middleware_Settings_31"></a>Middleware Settings</h1>
<p class="has-line-data" data-line-start="32" data-line-end="33">for Laravel 11 add command :</p>
<pre><code class="has-line-data" data-line-start="34" data-line-end="36">php artisan make:middleware Authenticate
</code></pre>
<p class="has-line-data" data-line-start="36" data-line-end="37">then update code bellow to Middleware/Authenticate.php</p>
<pre><code class="has-line-data" data-line-start="38" data-line-end="61">namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    private function getConfig($configName)
    {
        switch ($configName) {
            case 'serverUrl':
                return &quot;http://127.0.0.1:8000/login&quot;;
            default:
                return null;
        }
    }

    protected function redirectTo(Request $request): ?string
    {
        return $request-&gt;expectsJson() ? null : $this-&gt;getConfig('serverUrl');
    }
}
</code></pre>
<p class="has-line-data" data-line-start="61" data-line-end="62">then copy code below to file bootstrap/app.php</p>
<pre><code class="has-line-data" data-line-start="63" data-line-end="65">$middleware-&gt;alias(['auth' =&gt; Authenticate::class]);
</code></pre>
<p class="has-line-data" data-line-start="65" data-line-end="67">for Laravel 10 :<br>
update code bellow to Middleware/Authenticate.php</p>
<pre><code class="has-line-data" data-line-start="68" data-line-end="91">namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    private function getConfig($configName)
    {
        switch ($configName) {
            case 'serverUrl':
                return &quot;http://127.0.0.1:8000/login&quot;;
            default:
                return null;
        }
    }

    protected function redirectTo(Request $request): ?string
    {
        return $request-&gt;expectsJson() ? null : $this-&gt;getConfig('serverUrl');
    }
}
</code></pre>
<h1 class="code-line" data-line-start="92" data-line-end="93"><a id="Views_Config_92"></a>Views Config</h1>
<p class="has-line-data" data-line-start="93" data-line-end="94">use code bellow for direct url portal, edit-password and logout</p>
<pre><code class="has-line-data" data-line-start="95" data-line-end="128">{{-- in app blade --}}

&lt;div class=&quot;dropdown-menu dropdown-menu-end&quot; aria-labelledby=&quot;navbarDropdown&quot;&gt;
    @if(session()-&gt;has('countAccess'))
        @if (session('countAccess') &gt; 1)
            &lt;a class=&quot;dropdown-item&quot; href=&quot;{{ route('sso.portal') }}&quot;&gt;Portal&lt;/a&gt;
        @endif
    @endif
    &lt;a class=&quot;dropdown-item&quot; href=&quot;{{ route('sso.edit-password') }}&quot; onclick=&quot;saveReferrer()&quot;&gt;
        Edit Password
    &lt;/a&gt;
    &lt;a class=&quot;dropdown-item&quot; href=&quot;{{ route('sso.logout') }}&quot;
        onclick=&quot;event.preventDefault(); document.getElementById('logout-form').submit();&quot;&gt;
        {{ __('Logout') }}
    &lt;/a&gt;

    &lt;form id=&quot;logout-form&quot; action=&quot;{{ route('sso.logout') }}&quot; method=&quot;GET&quot; class=&quot;d-none&quot;&gt;
        @csrf
    &lt;/form&gt;
&lt;/div&gt;

{{-- previous url config in js.blade --}}

&lt;script&gt;
    function saveReferrer() {
        var previousUrl = document.referrer;
        var previousUrlInput = document.getElementById(&quot;previous_url&quot;);
        if (previousUrlInput) {
            previousUrlInput.value = previousUrl;
        }
    }
&lt;/script&gt;
</code></pre>
</body></html>
