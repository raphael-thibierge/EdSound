<?php

namespace App\Http\Controllers;

use App\Exceptions\SpotifyAccountNotLinkedException;
use App\Exceptions\SpotifyNotPremiumException;
use App\Http\Services\BotmanService;
use App\Http\Services\SpotifyService;
use App\Playlist;
use App\User;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Middleware\ApiAi;
use BotMan\Drivers\Facebook\Extensions\ButtonTemplate;
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
        $bot->reply($this->link_spotify_button());
    }


    public function getUser(BotMan &$bot){
        $bot->types();
        $this->user = $this->getUserFromSenderId($bot->getUser()->getId());
    }

    public function dialogflowResponse(BotMan &$bot){
        $bot->reply($bot->getMessage()->getExtras()['apiReply']);
    }

    private function getDialogflowParameter(BotMan &$bot, string $parameter){
        $extras = $bot->getMessage()->getExtras()['apiParameters'];
        if (isset($extras[$parameter]) && !empty($extras[$parameter])){
            return $extras[$parameter];
        } else {
            return null;
        }

    }

    public function handle(Request $request){

        $botman = BotmanService::instance();

            // Dialogflow API
        $dialogflow = ApiAi::create(env('DIALOGFLOW_API_KEY'))->listenForAction();


        // Apply global "received" middleware
        $botman->middleware->received($dialogflow);

        // welcome intent action
        $botman->hears('input.welcome', function (BotMan $bot) use ($request){
            $this->getUser($bot);
            $this->dialogflowResponse($bot);
        })->middleware($dialogflow);


        $botman->hears('dialog', function (BotMan $bot){
            $this->getUser($bot);
            $this->dialogflowResponse($bot);
        })->middleware($dialogflow);


        $botman->hears('test', function (BotMan $bot) {
            $this->getUser($bot);
            $this->dialogflowResponse($bot);
        })->middleware($dialogflow);


        $botman->hears('spotify.connect', function (BotMan $bot) {
            $this->getUser($bot);
            $bot->reply($this->link_spotify_button());
        })->middleware($dialogflow);


        $botman->hears('playlist.join', function (BotMan $bot) {
            $this->getUser($bot);

            $playlist = Playlist::find($this->getDialogflowParameter($bot, 'id'));

            if ($playlist === null){
                $bot->reply("Je n'ai pas trouvé cette playlist");
            } else if ($playlist->status === Playlist::STATUS_CLOSE){
                $bot->reply('Cette playlist est maintenant fermée');
            } else {
                $this->user->playlistAsGuests()->save($playlist);
                $this->user->setCurrentPlaylist($playlist->id);
                $this->user->save();
                $bot->reply('Ca y est tu peux maintenant ajouter des morceaux !');
            }
        })->middleware($dialogflow);

        // search song
        $botman->hears('song.search', function (BotMan $bot) {

            $this->getUser($bot);
            $this->dialogflowResponse($bot);
            $bot->types();

            $spotify = SpotifyService::load();
            $results = $spotify
                ->search($this->getDialogflowParameter($bot, 'title'), 'track')
                ->tracks->items;

            $bot->reply($this->songListTemplate($results));

        })->middleware($dialogflow);

        // search song
        $botman->hears('playlist.songs.add', function (BotMan $bot) {

            $this->getUser($bot);

            $playlist = $this->user->currentPlaylist();
            if ($playlist === null) {
                $bot->reply("Tu dois d'abbord créer ou rejoindre une playlist");
            } else {
                $playlist->addSong( $this->getDialogflowParameter($bot, 'id'), $this->user);
                $playlist->save();
                $bot->reply('Morceau ajouté à la playlist !');
            }

        })->middleware($dialogflow);


        $botman->hears('playlist.songs.current', function (BotMan $bot) {
            $this->getUser($bot);

            $playlist = $this->user->currentPlaylist();
            if ($playlist === null) {
                $bot->reply("Tu dois d'abbord créer ou participer à une playlist");
            } else {

                //$bot->reply($this->uniqueSongTemplate($playlist->currentSong()->item, false));
                $bot->reply('Pas encore dispo');
            }

        })->middleware($dialogflow);

        // search song
        $botman->hears('playlist.songs', function (BotMan $bot) {
            $this->getUser($bot);

            $playlist = $this->user->currentPlaylist();
            if ($playlist === null) {
                $bot->reply("Tu dois d'abbord céer ou participer à une playlist pour y ajouter des morceaux");
            } else {
                $bot->reply(ButtonTemplate::create('Click sur ce bouton frère !')
                    ->addButton(ElementButton::create('Voir')->url(route('playlist.show', ['playlist' => $playlist])))
                );
            }

        })->middleware($dialogflow);


        $botman->hears('playlist.create', function (BotMan $bot) {

            $this->getUser($bot);

            if ($this->user->currentPlaylist() !== null) {
                $bot->reply("Tu dois d'abbord fermer ta playlist actuelle pour en créer une nouvelle");
            }

            $playlist = $this->user->createAndOpenPlaylist();
            $this->user->setCurrentPlaylist($playlist->id);
            $this->user->save();

            $bot->reply('Ta playlist a été créée, tu peux maintenant ajouter des musiques'/*, voici son identifiant : '*/);
            $bot->reply($playlist->id);


        })->middleware($dialogflow);

        $botman->hears('playlist.id.get', function (BotMan $bot) {
            $this->getUser($bot);

            $playlist = $this->user->currentPlaylist();
            if ($playlist === null) {
                $bot->reply("Tu n'as aucune playlist ouverte. Crées en une !");
            } else {
                $bot->reply('Voici l\'identifiant : ' . $playlist->id);
            }

        })->middleware($dialogflow);

        $botman->hears('playlist.close', function (BotMan $bot) {
            $this->getUser($bot);

            $playlist = $this->user->currentPlaylist();
            if ($playlist === null) {
                $bot->reply("Tu n'as aucune playlist ouverte. Crées en une !");
            } else if ($playlist->user_id !== $this->user->id){
                $bot->reply("Tu ne pas fermer une playlist dont tu n'es pas le créateur");
            } else {
                $playlist->close();
                $playlist->save();
                $this->user->setCurrentPlaylist(null);
                $this->user->save();
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

    public function uniqueSongTemplate($track, $addButton = true){
        return GenericTemplate::create()->addElement($this->songTemplate($track, $addButton));
    }

    public function songTemplate($track, $addButton = true){

        $initial = (int)($track->duration_ms/1000);
        $seconds = $initial % 60;
        $minutes = ($initial - $seconds ) / 60;
        $duration = $minutes . ':' . $seconds;

        $artist = $track->artists[0]->name;

        $element =  Element::create("{$track->name} ({$duration})")
            ->subtitle($artist)
            ->image($track->album->images[0]->url);
            //->addButton(ElementButton::create('visit')->url('http://botman.io'))
        if ($addButton) {
            $element->addButton(ElementButton::create('Ajouter')
                ->payload('playlist.songs.add.' . $track->id)->type('postback'));
        }

        return $element;
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


    private function link_spotify_button()
    {
        return GenericTemplate::create()
            ->addElements([
                Element::create('Spotify')
                    ->subtitle('Connectes ton compte spotify')
                    ->addButton(
                        ElementButton::create('Connexion')
                            ->url(route('spotify.login', [
                                'user' => $this->user
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