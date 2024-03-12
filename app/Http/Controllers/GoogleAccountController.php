<?php

namespace App\Http\Controllers;

use App\Models\GoogleAccount;
use App\Services\Google;
use Google\Service\PeopleService; // Importa la clase PeopleService
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GoogleAccountController extends Controller
{
    // protected $google;

    public function __construct()
    {
        $this->middleware('auth');
        // $this->google = $google;
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
    public function store(Request $request, Google $google)
    {
        // Verificar si se ha recibido el código de autorización
        if (! $request->has('code')) {
            Log::info('No se recibió el código de autorización');
            // Redirigir al usuario para autorizar la aplicación
            // return redirect($google->createAuthUrl(['profile']));
            return $this->redirectToGoogleCalendarAuthorization($google);
        }

        // Autenticar con el código de autorización recibido
        $google->authenticate($request->get('code'));

        // Obtener el token de acceso
        // $accessToken = $google->getAccessToken()['access_token'];
        $accessToken = $google->getAccessToken();

          // Conectar con la cuenta de Google del usuario autenticado
        // $google->connectUsing($accessToken);

        // Obtener información del perfil del usuario
        $profile = $this->getProfileInformation($google);

        // Actualizar o crear una cuenta de Google para el usuario autenticado
        $this->updateOrCreateGoogleAccount($profile, $accessToken);

        // Redirigir al usuario a la página de inicio
        return redirect()->route('google.index');
    }

    /**
     * Obtener la información del perfil del usuario actual.
     */
    protected function getProfileInformation($google)
    {
        // Obtener la instancia de Google\Client
        $client = $google->getClient();

        // Crear una instancia de la API de PeopleService
        $peopleService = new PeopleService($client);

        // Hacer una llamada para obtener información sobre el usuario actual
        $profile = $peopleService->people->get('people/me', ['personFields' => 'names,emailAddresses']);

        return $profile;
    }

    /**
     * Actualizar o crear una cuenta de Google para el usuario autenticado.
     */
    protected function updateOrCreateGoogleAccount($profile, $accessToken)
    {
        // Acceder a los datos del perfil
        $name = $profile->getNames()[0]->getDisplayName();
        // $email = $profile->getEmailAddresses()[0]->getValue();
        $googleId = substr($profile->getResourceName(), strlen("people/"));

        // Actualizar o crear una cuenta de Google para el usuario autenticado
        auth()->user()->googleAccounts()->updateOrCreate(
            ['google_id' => $googleId],
            ['name' => $name, 'token' => $accessToken]
        );
    }

    /**
     * Revoke the account's token and delete the it locally.
     */
    public function destroy(GoogleAccount $googleAccount, Google $google)
    {
        $googleAccount->calendars->each->delete();

        $googleAccount->delete();

        $google->revokeToken($googleAccount->token);

        return redirect()->back();
    }

    public function redirectToGoogleCalendarAuthorization(Google $google)
    {
        // Redirigir al usuario para autorizar la aplicación
        return redirect($google->createAuthUrl(['profile', 'https://www.googleapis.com/auth/calendar']));
    }
}
