<?php

namespace App\Controllers;

use App\Models\LoginModel;
use App\Models\MainModel;

class Oauth extends BaseController
{
    private array $keys;
    private MainModel $mainModel;

    public function __construct()
    {
        $this->mainModel = new MainModel();
        $this->keys = $this->mainModel->social_keys();
    }

    public function google()
    {
        return $this->redirectToProvider('google');
    }

    public function facebook()
    {
        return $this->redirectToProvider('facebook');
    }

    public function google_callback()
    {
        return $this->handleCallback('google');
    }

    public function facebook_callback()
    {
        return $this->handleCallback('facebook');
    }

    private function redirectToProvider(string $provider)
    {
        if (session()->get('id') && (new LoginModel())->validateSession(session()->get('id'))) {
            return redirect()->to(base_url('enduser'));
        }

        if (empty($this->keys[$provider . '_status'])) {
            return redirect()->to(base_url('login'));
        }

        $state = bin2hex(random_bytes(16));
        session()->set('oauth_' . $provider . '_state', $state);

        return redirect()->to($this->authorizationUrl($provider, $state));
    }

    private function handleCallback(string $provider)
    {
        if (empty($this->keys[$provider . '_status'])) {
            return redirect()->to(base_url('login'));
        }

        $state = (string) $this->request->getGet('state');
        $storedState = (string) session()->get('oauth_' . $provider . '_state');
        session()->remove('oauth_' . $provider . '_state');

        if ($state === '' || ! hash_equals($storedState, $state)) {
            return $this->oauthFailed('Invalid login state. Please try again.');
        }

        $code = (string) $this->request->getGet('code');

        if ($code === '') {
            return $this->oauthFailed('Social login was cancelled or denied.');
        }

        $profile = $this->fetchProfile($provider, $code);

        if (! $profile || empty($profile['email'])) {
            return $this->oauthFailed('Could not read your social login profile.');
        }

        $loginModel = new LoginModel();
        $userInfo = $loginModel->socialLogin($profile['email'], $profile['photoURL'] ?? '', $provider);

        if (! $userInfo) {
            return $this->oauthFailed('Could not create or update your account.');
        }

        if (! empty($userInfo['nlg']) && ! empty($userInfo['randomStr'])) {
            $loginModel->sendNewPassword($userInfo['email'], $userInfo['randomStr'], $this->mainModel->smtp_settings() ?? []);
        }

        session()->set('id', $userInfo['userId']);

        return redirect()->to(base_url('enduser'));
    }

    private function authorizationUrl(string $provider, string $state): string
    {
        $redirectUri = base_url('oauth/' . $provider . '/callback');

        if ($provider === 'google') {
            return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
                'client_id' => $this->keys['google_public'],
                'redirect_uri' => $redirectUri,
                'response_type' => 'code',
                'scope' => 'openid email profile',
                'state' => $state,
                'access_type' => 'online',
                'prompt' => 'select_account',
            ]);
        }

        return 'https://www.facebook.com/v19.0/dialog/oauth?' . http_build_query([
            'client_id' => $this->keys['facebook_public'],
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'email,public_profile',
            'state' => $state,
        ]);
    }

    private function fetchProfile(string $provider, string $code): ?array
    {
        return $provider === 'google'
            ? $this->fetchGoogleProfile($code)
            : $this->fetchFacebookProfile($code);
    }

    private function fetchGoogleProfile(string $code): ?array
    {
        $token = $this->postOAuthToken('https://oauth2.googleapis.com/token', [
            'code' => $code,
            'client_id' => $this->keys['google_public'],
            'client_secret' => $this->keys['google_secret'],
            'redirect_uri' => base_url('oauth/google/callback'),
            'grant_type' => 'authorization_code',
        ]);

        if (empty($token['access_token'])) {
            return null;
        }

        $profile = $this->getOAuthJson('https://openidconnect.googleapis.com/v1/userinfo', $token['access_token']);

        return $profile ? [
            'email' => strtolower((string) ($profile['email'] ?? '')),
            'photoURL' => (string) ($profile['picture'] ?? ''),
        ] : null;
    }

    private function fetchFacebookProfile(string $code): ?array
    {
        $tokenUrl = 'https://graph.facebook.com/v19.0/oauth/access_token?' . http_build_query([
            'client_id' => $this->keys['facebook_public'],
            'client_secret' => $this->keys['facebook_secret'],
            'redirect_uri' => base_url('oauth/facebook/callback'),
            'code' => $code,
        ]);
        $token = $this->getJson($tokenUrl);

        if (empty($token['access_token'])) {
            return null;
        }

        $profileUrl = 'https://graph.facebook.com/me?' . http_build_query([
            'fields' => 'id,name,email,picture.type(large)',
            'access_token' => $token['access_token'],
        ]);
        $profile = $this->getJson($profileUrl);
        $picture = $profile['picture']['data']['url'] ?? '';

        return $profile ? [
            'email' => strtolower((string) ($profile['email'] ?? '')),
            'photoURL' => (string) $picture,
        ] : null;
    }

    private function postOAuthToken(string $url, array $form): ?array
    {
        $client = service('curlrequest');
        $response = $client->post($url, [
            'form_params' => $form,
            'http_errors' => false,
            'timeout' => 10,
        ]);

        $decoded = json_decode($response->getBody(), true);

        return is_array($decoded) ? $decoded : null;
    }

    private function getOAuthJson(string $url, string $accessToken): ?array
    {
        $client = service('curlrequest');
        $response = $client->get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
            'http_errors' => false,
            'timeout' => 10,
        ]);

        $decoded = json_decode($response->getBody(), true);

        return is_array($decoded) ? $decoded : null;
    }

    private function getJson(string $url): ?array
    {
        $client = service('curlrequest');
        $response = $client->get($url, [
            'http_errors' => false,
            'timeout' => 10,
        ]);

        $decoded = json_decode($response->getBody(), true);

        return is_array($decoded) ? $decoded : null;
    }

    private function oauthFailed(string $message)
    {
        session()->setFlashdata('userMsg', $message);
        session()->setFlashdata('userMsg_class', 'alert alert-danger');

        return redirect()->to(base_url('login'));
    }
}
