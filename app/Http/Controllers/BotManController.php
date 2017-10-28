<?php

namespace App\Http\Controllers;

use App\Exceptions\SpotifyAccountNotLinkedException;
use App\Exceptions\SpotifyNotPremiumException;
use App\Http\Services\SpotifyService;
use App\Playlist;
use App\User;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Middleware\ApiAi;
use BotMan\Drivers\Facebook\Extensions\Element;
use BotMan\Drivers\Facebook\Extensions\ElementButton;
use BotMan\Drivers\Facebook\Extensions\GenericTemplate;
use BotMan\Drivers\Facebook\FacebookDriver;
use Illuminate\Http\Request;

class BotManController extends Controller
{

    /**
     *
     */
    private $user;


    public static function ApiAiFactory(){

    }

    public static function functionFindOrCreateUser(string $senderId): User{

        $user = User::where('messenger_sender_id', $_SERVER)->first();
        if ($user === null){
            $user = User::create([
                'messenger_sender_id' => $senderId,
            ]);
        }
        return $user;
    }

    public function notLinkedSpotifyAnswer(Botman $bot){
        $bot->reply('Tu dois d\'abbord connecter ton compte spotify pour créer une playlist');
        $bot->reply($this->link_spotify_button($this->getUserFromSenderId($bot->getUser()->getId())));
    }

    public function handle(Request $request){

        // Use Facebook messenger driver
        DriverManager::loadDriver(FacebookDriver::class);

        // Define botman config
        $config = [
            'facebook' => [
                'token' => env('FACEBOOK_TOKEN'),
                'app_secret' => env('FACEBOOK_APP_SECRET'),
                'verification' => env('FACEBOOK_VERIFICATION'),
            ]
        ];

        // Create BotMan instance
        $botman = BotManFactory::create($config);

        // Dialogflow API
        $dialogflow = ApiAi::create(env('DIALOGFLOW_API_KEY'))->listenForAction();


        // Apply global "received" middleware
        $botman->middleware->received($dialogflow);

        // welcome intent action
        $botman->hears('input.welcome', function (BotMan $bot) use ($request){

            // The incoming message matched the "input.welcome" on Dialoglfow.com
            // Retrieve API.ai information:
            $extras = $bot->getMessage()->getExtras();

            // response content
            $apiReply = $extras['apiReply'];
            $apiAction = $extras['apiAction'];
            $apiIntent = $extras['apiIntent'];
            $apiParameters = $extras['apiParameters'];

            $bot->reply($apiReply);
            if ($this->getUserFromSenderId($bot->getUser()->getId()) === null){
                $bot->reply('Bienvenue ! ');
            }
        })->middleware($dialogflow);

        $botman->hears('dialog', function (BotMan $bot){

            // The incoming message matched the "input.welcome" on Dialoglfow.com
            // Retrieve API.ai information:
            $extras = $bot->getMessage()->getExtras();
            $dialogflowReply = $extras['apiReply'];

            $bot->reply($dialogflowReply);

        })->middleware($dialogflow);

        /**
         * Test
         */
        $botman->hears('test', function (BotMan $bot) {
            $extras = $bot->getMessage()->getExtras();
            $apiReply = $extras['apiReply'];
            $bot->reply($apiReply);
        })->middleware($dialogflow);

        /**
         * Login
         */
        //$botman->hears('login', function (BotMan $bot) {
        //    $bot->reply($this->login_button());
        //})->middleware($dialogflow);

        /**
         * Logout
         */
        //$botman->hears('logout', function (BotMan $bot) {
        //    $bot->reply($this->logout_button());
        //})->middleware($dialogflow);

        /**
         * Spotify connect
         */
        $botman->hears('spotify.connect', function (BotMan $bot) {
            $user = $this->getUserFromSenderId($bot->getUser()->getId());
            $bot->reply($this->link_spotify_button($user));
        })->middleware($dialogflow);

        /**
         * Spotify play
         */
        $botman->hears('spotify.play', function (BotMan $bot) {
            $bot->types();
            $user = $this->getUserFromSenderId($bot->getUser()->getId());

            if (!$user->isLinkedToSpotify()) {
                $this->notLinkedSpotifyAnswer($bot);

                return;
            }

            $api = SpotifyService::createApiForUser($user);
            $api->play();
            $bot->reply('Ca y est, la musique est lancée !');


        })->middleware($dialogflow);


        $botman->hears('spotify.next', function (BotMan $bot) {
            $bot->types();
            $user = $this->getUserFromSenderId($bot->getUser()->getId());

            if (!$user->isLinkedToSpotify()) {
                $this->notLinkedSpotifyAnswer($bot);
                return;
            }

            $api = SpotifyService::createApiForUser($user);
            $api->next();
            $bot->reply('Chanson suivante !');


        })->middleware($dialogflow);

        $botman->hears('spotify.previous', function (BotMan $bot) {
            $bot->types();
            $user = $this->getUserFromSenderId($bot->getUser()->getId());

            if (!$user->isLinkedToSpotify()) {
                $this->notLinkedSpotifyAnswer($bot);
                return;
            }

            $api = SpotifyService::createApiForUser($user);
            $api->previous();
            $bot->reply('Chanson précédente !');


        })->middleware($dialogflow);

        $botman->hears('playlist.join', function (BotMan $bot) {
            $bot->types();
            $user = $this->getUserFromSenderId($bot->getUser()->getId());

            $extras = $bot->getMessage()->getExtras();
            $playlistID = $extras['apiParameters']['id'];


            if ($user->currentPlaylist() !== null){
                $bot->reply('Tu participe déjà à une playlist! Tu dois d\'abbord la quitter avant d\'en joindre une autre' );
                return;
            }


            $playlist = Playlist::find($playlistID);
            if ($playlist === null){
                $bot->reply('Je n\'ai pas trouvé cette playlist');
            } else if ($playlist->status === Playlist::STATUS_CLOSE){
                $bot->reply('Cette playlist est maintenant fermée');
            } else {
                $user->playlistAsGuests()->save($playlist);
                $bot->reply('Ca y est tu peux maintenant ajouter des morceaux !');
            }

        })->middleware($dialogflow);

        // pause
        $botman->hears('spotify.pause', function (BotMan $bot) {
            $bot->types();

            $user = $this->getUserFromSenderId($bot->getUser()->getId());

            if (!$user->isLinkedToSpotify()) {
                $this->notLinkedSpotifyAnswer($bot);
                return;
            }

            $api = SpotifyService::createApiForUser($user);
            $api->pause();
            $bot->reply('La playlist est sur pause!');


        })->middleware($dialogflow);


        // search song
        $botman->hears('song.search', function (BotMan $bot) {
            $bot->types();
            // The incoming message matched the "input.welcome" on Dialoglfow.com
            // Retrieve API.ai information:
            $extras = $bot->getMessage()->getExtras();
            $apiReply = $extras['apiReply'];

            // response content
            $apiAction = $extras['apiAction'];
            $apiIntent = $extras['apiIntent'];
            $apiParameters = $extras['apiParameters'];
            $bot->reply($apiReply);

            $spotify = SpotifyService::load();
            $results = $spotify->search($apiParameters['title'], 'track')->tracks->items;

            //$bot->reply('ok');
            $bot->reply($this->songListTemplate($results));


        })->middleware($dialogflow);

        // search song
        $botman->hears('playlist.songs.add', function (BotMan $bot) {
            $bot->types();
            // The incoming message matched the "input.welcome" on Dialoglfow.com
            // Retrieve API.ai information:

            $user = $this->getUserFromSenderId($bot->getUser()->getId());


            $playlist = $user->currentPlaylist();
            if ($playlist === null) {
                $bot->reply("Tu dois d'abbord créer une playlist pour y ajouter des musiques");
            } else {

                $extras = $bot->getMessage()->getExtras();
                $trackId = $extras['apiParameters']['id'];


                try {
                    $playlist->addSong($trackId, $user);
                } catch (SpotifyAccountNotLinkedException $exception){
                    return 'Error while trying to add playlist';
                }

                $bot->reply('Morceau ajouté à la playlist !');
            }


        })->middleware($dialogflow);

        $botman->hears('playlist.songs.current', function (BotMan $bot) {
            $bot->types();
            // The incoming message matched the "input.welcome" on Dialoglfow.com
            // Retrieve API.ai information:

            $user = $this->getUserFromSenderId($bot->getUser()->getId());


            $playlist = $user->currentPlaylist();
            if ($playlist === null) {
                $bot->reply("Tu dois d'abbord créer une playlist pour y ajouter des musiques");
            } else {


                $bot->reply($this->songTemplate($playlist->currentSong()->item));
            }


        })->middleware($dialogflow);

        // search song
        $botman->hears('playlist.songs', function (BotMan $bot) {
            $bot->types();
            // The incoming message matched the "input.welcome" on Dialoglfow.com
            // Retrieve API.ai information:
            $user = $this->getUserFromSenderId($bot->getUser()->getId());

            $playlist = $user->currentPlaylist();
            if ($playlist === null) {
                $bot->reply("Tu dois d'abbord céer une playlist pour y ajouter des musiques");
            } else {

                //$extras = $bot->getMessage()->getExtras();
                //$trackId = $extras['apiParameters']['id'];

                //$api = SpotifyService::load();
                //$song = $api->getTrack($trackId);


                $songs = $playlist->songs()->get();

                $reply = "";
                if (count($songs )=== 0){
                    $reply = "Il n'y a pas encore de morceau dans cette playlist";
                } else {
                    foreach ($songs as $song) {
                        $reply .= ' - ' . $song->getName() . '  (' . $song->getDurationToHuman() . ")\r\n";
                    }
                }

                $bot->reply($reply);
            }


        })->middleware($dialogflow);


        /**
         * Spotify play
         */
        $botman->hears('playlist.create', function (BotMan $bot) {
            $bot->types();
            $user = $this->getUserFromSenderId($bot->getUser()->getId());

            if ($user->currentPlaylist() !== null) {
                $bot->reply("Tu dois d'abbord fermer ta playlist actuelle pour en créer une nouvelle");
            } else {

                try {
                    $playlist = $user->createAndOpenPlaylist();

                    $bot->reply('Ta playlist a été créée, tu peux maintenant ajouter des musiques'/*, voici son identifiant : '*/);
                    $bot->reply($playlist->id);

                } catch (SpotifyAccountNotLinkedException $exception){
                    $bot->reply($this->notLinkedSpotifyAnswer($bot));
                }


            }

        })->middleware($dialogflow);

        /**
         * Get playlist id
         */
        $botman->hears('playlist.id.get', function (BotMan $bot) {
            $bot->types();
            $user = $this->getUserFromSenderId($bot->getUser()->getId());

            $playlist = $user->playlists()->where('status', Playlist::STATUS_OPEN)->first();
            if ($playlist === null) {
                $bot->reply("Tu n'as aucune playlist ouverte. Crées en une !");
            } else {

                $bot->reply('Voici l\'identifiant : ' . $playlist->id);
            }

        })->middleware($dialogflow);

        /**
         * Get playlist id
         */
        $botman->hears('playlist.close', function (BotMan $bot) {
            $bot->types();
            $user = $this->getUserFromSenderId($bot->getUser()->getId());

            $playlist = $user->playlists()->where('status', Playlist::STATUS_OPEN)->first();
            if ($playlist === null) {

                if ($user->currentPlaylist() !== null){
                    $bot->reply("Tu ne pas fermer une playlist que tu n'as pas créé.");
                } else {
                    $bot->reply("Tu n'as aucune playlist ouverte. Crées en une !");
                }
            } else {
                $playlist->close();
                $playlist->save();
                $bot->reply("T'as playlist est maintenant fermée !");
            }

        })->middleware($dialogflow);


        // default response
        $botman->fallback(function (BotMan $bot){
            $bot->reply("Je n'ai pas compris... :/");
        });


        // start listening
        $botman->listen();
    }

    public function notConnectedMessage(Botman $bot){
        $bot->reply('Tu n\'es pas connecté');
        $bot->reply($this->login_button());
    }

    public function songListTemplate($tracks){
        if (count($tracks) == 0){
            return "Aucun morceau trouvé..";
        }

        $trackTemplates = [];
        for ($i = 0; $i < 4 && $i < count($tracks) ; $i++){
            $trackTemplates []= $this->songTemplate($tracks[$i]);
        }

        return GenericTemplate::create()
            ->addImageAspectRatio(GenericTemplate::RATIO_SQUARE)
            ->addElements($trackTemplates);
    }

    public function songTemplate($track){

        $initial = (int)($track->duration_ms/1000);
        $seconds = $initial % 60;
        $minutes = ($initial - $seconds ) / 60;
        $duration = $minutes . ':' . $seconds;

        $artist = $track->artists[0]->name;

        return Element::create("{$track->name} ({$duration})")
            ->subtitle($artist)
            ->image($track->album->images[0]->url)
            //->addButton(ElementButton::create('visit')->url('http://botman.io'))
            ->addButton(ElementButton::create('Ajouter')
                ->payload('playlist.songs.add.' . $track->id)->type('postback'))
            ;
    }

    private function login_button(){
        return GenericTemplate::create()
            ->addElements([
                Element::create('Connexion')
                    ->subtitle('Connecte ton compte ' . env('APP_NAME') . ' ou crées toi en un !')
                    ->addButton(
                        ElementButton::create('Login')
                            ->url(env('APP_URL') . '/botman/authorize')
                            ->type('account_link')
                    )
            ]);
    }

    private function logout_button(){
        return GenericTemplate::create()
            ->addElements([
                Element::create('Dissociation du compte ' . env('APP_NAME'))
                    ->addButton(
                        ElementButton::create('Déconnexion')
                            ->type('account_unlink')
                    )
            ]);
    }

    /*
     * Not used anymore
     *
     */
    public static function check_linking(Request $request, &$botman){

        // try to get the message
        try {
            $message = $request->only(['entry'])['entry'][0]['messaging'][0];
            $sender_id = $message['sender']['id'];
        } catch (\Exception $e){
            $message = [];
        }

        if (isset($message['account_linking'])) {

            $account_linking = $message['account_linking'];


            // linking response
            if ($account_linking['status'] === "linked"){

                $user_id = $account_linking['authorization_code'];

                if ( ($user = User::find($user_id)) !== null){

                    $user->update(['messenger_sender_id' => $sender_id]);

                    $botman->say("Bienvenue {$user->name} ! Ton compte messenger est maitenant lié avec " . env('APP_NAME'),
                        $sender_id);
                }
            }
            // unlick response
            else if ($account_linking['status'] === "unlinked") {
                User::where('messenger_sender_id', $sender_id)->update(['messenger_sender_id' => null]);

                $botman->say("Ton compte messenger n'est plus lié à ". env('APP_NAME'),
                    $sender_id);
            }
        }
    }


    private function link_spotify_button(User $user)
    {
        return GenericTemplate::create()
            ->addElements([
                Element::create('Spotify')
                    ->subtitle('Connectes ton compte spotify')
                    ->addButton(
                        ElementButton::create('Connexion')
                            ->url(route('spotify.login', [
                                'user' => $user
                            ]))
                    )
            ]);
    }


    private function getUserFromSenderId($senderId){
        //$senderId = $request->get('message')[0]['sender']['id'];

        $user = User::where('messenger_sender_id', $senderId)->first();

        if ($user == null){
            $user = User::create([
                'messenger_sender_id' => $senderId
            ]);
        }

        return $user;

    }

}