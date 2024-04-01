<?php

namespace App\Http\Controllers\SSO;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SSOController extends Controller
{
    private function getConfig($configName)
    {
        switch ($configName) {
            case 'callbackUrl':
                return "http://127.0.0.1:8080/callback";
            case 'serverUrl':
                return "http://127.0.0.1:8000";
            case 'clientId':
                return "129e5bbd-1fbd-4116-a06b-9e1011e830cc";
            case 'clientSecret':
                return "o75CNKPdoCx1fmVZXvzkXEZxRAAcSzsF3hOZgwWJ";
            default:
                return null;
        }
    }

    public function getLogin(Request $request)
    {
        $request->session()->put("state", $state = Str::random(40));

        $query = http_build_query([
            "client_id" => $this->getConfig('clientId'),
            "redirect_uri" => $this->getConfig('callbackUrl'),
            "response_type" => "code",
            "scope" => "access-user",
            "state" => $state,
        ]);

        return redirect($this->getConfig('serverUrl') . "/oauth/authorize?" . $query);
    }

    public function getCallback(Request $request)
    {
        $state = $request->session()->pull("state");

        throw_unless(strlen($state) > 0 && $state == $request->state, InvalidArgumentException::class);

        $response = Http::asForm()->post(
            $this->getConfig('serverUrl') . "/oauth/token",
            [
                "grant_type" => "authorization_code",
                "client_id" => $this->getConfig('clientId'),
                "client_secret" => $this->getConfig('clientSecret'),
                "redirect_uri" => $this->getConfig('callbackUrl'),
                "code" => $request->code
            ]
        );
        $request->session()->put($response->json());
        return redirect()->route("sso.connect");
    }

    public function connectUser(Request $request)
    {
        $access_token = $request->session()->get("access_token");
        $response = Http::withHeaders([
            "Accept" => "application/json",
            "Authorization" => "Bearer " . $access_token
        ])->get($this->getConfig('serverUrl') . "/api/user");

        $userArray = $response->json();

        $countAccess = count($userArray['oauth_client_users']);

        $request->session()->put('countAccess', $countAccess);

        $user = User::where("username", $userArray['username'])->first();

        if (!$user) {
            $client = collect($userArray['oauth_client_users'])->first();
            $user = new User;
            $user->name = $userArray['name'];
            $user->username = $userArray['username'];
            $user->phone = $userArray['phone'];
            $user->email_verified_at = $userArray['email_verified_at'];
            $user->oauth_client_role_id = $client['oauth_client_role_id'];
            $user->save();
        }

        Auth::login($user);

        return redirect()->route('home');
    }

    public function logout()
    {
        Auth::logout();
        return redirect($this->getConfig('serverUrl') . "/sso/logout");
    }

    public function portal()
    {
        Auth::logout();
        return redirect($this->getConfig('serverUrl') . "/portal");
    }

    public function editPassword()
    {
        return redirect($this->getConfig('serverUrl') . "/edit-password");
    }
}
