<?php

namespace App\Http\Controllers;

use App\Models\GoogleAccount;
use App\Services\Google;
use Google\Service\PeopleService; // Importa la clase PeopleService
use Illuminate\Http\Request;

class GoogleAccountController extends Controller
{
    protected $google;

    public function __construct(Google $google)
    {
        $this->middleware('auth');
        $this->google = $google;
    }

    /**
     * Display a listing of the google accounts.
     */
    public function index()
    {
        return view('accounts', [
            'accounts' => auth()->user()->googleAccounts,
        ]);
    }

    /**
     * Handle the OAuth connection which leads to
     * the creating of a new Google Account.
     */
    public function store(Request $request)
    {
        if (! $request->has('code')) {
            return redirect($this->google->createAuthUrl());
        }

        $this->google->authenticate($request->get('code'));

        // Obtén la instancia de Google\Client
        $client = $this->google->getClient();

        // Crea una instancia de la API de PeopleService
        $peopleService = new PeopleService($client);

        // Haz una llamada para obtener información sobre el usuario actual
        $profile = $peopleService->people->get('people/me', ['personFields' => 'names,emailAddresses']);

        // Accede a los datos del perfil
        $name = $profile->getNames()[0]->getDisplayName();
        $email = $profile->getEmailAddresses()[0]->getValue();
        $googleId = $profile->getResourceName(); // Google ID

        auth()->user()->googleAccounts()->updateOrCreate(
            [
                'google_id' => $googleId,
            ],
            [
                'name' => $name,
                'token' => $this->google->getAccessToken(),
            ]
        );

        return redirect()->route('google.index');
    }

    /**
     * Revoke the account's token and delete the it locally.
     */
    public function destroy(GoogleAccount $googleAccount)
    {
        $googleAccount->calendars->each->delete();

        $googleAccount->delete();

        $this->google->revokeToken($googleAccount->token);

        return redirect()->back();
    }
}
