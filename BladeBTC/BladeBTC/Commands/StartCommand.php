<?php


namespace BladeBTC\Commands;

use BladeBTC\Helpers\Btc;
use BladeBTC\Models\BotSetting;
use BladeBTC\Models\Referrals;
use BladeBTC\Models\Users;
use Exception;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class StartCommand extends Command
{
	/**
	 * @var string Command Name
	 */
	protected $name = "start";

	/**
	 * @var string Command Description
	 */
	protected $description = "Start bot";

	/**
	 * @inheritdoc
	 */
	public function handle($arguments)
	{
	    try {

            /**
             * Chat data
             */
            $username = $this->update->getMessage()->getFrom()->getUsername();
            $first_name = $this->update->getMessage()->getFrom()->getFirstName();
            $last_name = $this->update->getMessage()->getFrom()->getLastName();
            $id = $this->update->getMessage()->getFrom()->getId();


            /**
             * Display Typing...
             */
            $this->replyWithChatAction(['action' => Actions::TYPING]);


            /**
             * User model
             */
            $user = new Users($id);


            /**
             * Add user to our database
             */
            if ($user->exist() == false) {

                $user->create([
                    "username"   => isset($username) ? $username : "not set",
                    "first_name" => isset($first_name) ? $first_name : "not set",
                    "last_name"  => isset($last_name) ? $last_name : "not set",
                    "id"         => isset($id) ? $id : "not set",
                ]);

				/**
				 * Referral
				 */
				if (!empty($arguments)) {
					Referrals::BindAccount($arguments, $id);
				}

                /**
                 * Response
                 */
                $this->replyWithMessage([
                    'text'       => "Welcome <b>" . $first_name . "</b>. \xF0\x9F\x98\x84 \nTo get support please go to " . BotSetting::getValueByName("support_chat_id"),
                    'parse_mode' => 'HTML',
                ]);

                /**
                 * Go to start
                 */
                $this->triggerCommand('start');

            } else {


				/**
				 * Referral
				 */
				if (!empty($arguments)) {
					Referrals::BindAccount($arguments, $id);
				}


                /**
                 * Keyboard
                 */
                $keyboard = [
                    ["Meu Saldo " . Btc::Format($user->getBalance()) . " \xF0\x9F\x92\xB0"],
                    ["Investir \xF0\x9F\x92\xB5", "Sacar \xE2\x8C\x9B"],
                    ["ReInvestir \xE2\x86\xA9", "Ajuda \xE2\x9D\x93"],
                    ["Minha Equipe \xF0\x9F\x91\xAB"],
                ];

                $reply_markup = $this->telegram->replyKeyboardMarkup([
                    'keyboard'          => $keyboard,
                    'resize_keyboard'   => true,
                    'one_time_keyboard' => false,
                ]);

                /**
                 * Response
                 */
                $this->replyWithMessage([
                    'text'         => "Olá! Sou a Inteligência Artificial IA CR, é bom ver você por aqui <b>" . $first_name . "</b>.\nExplore o menu abaixo.",
                    'reply_markup' => $reply_markup,
                    'parse_mode'   => 'HTML',
                ]);
            }
        }
        catch (Exception $e){

            $keyboard = [
                ["My balance \xF0\x9F\x92\xB0"],
                ["Investir \xF0\x9F\x92\xB5", "Sacar \xE2\x8C\x9B"],
                ["Reinvestir \xE2\x86\xA9", "Ajuda \xE2\x9D\x93"],
                ["Minha Equipe \xF0\x9F\x91\xAB"],
            ];

            $reply_markup = $this->telegram->replyKeyboardMarkup([
                'keyboard'          => $keyboard,
                'resize_keyboard'   => true,
                'one_time_keyboard' => false,
            ]);

            $this->replyWithMessage([
                'text'         => "An error occurred.\n" . $e->getMessage() . ". \xF0\x9F\x98\x96",
                'reply_markup' => $reply_markup,
                'parse_mode'   => 'HTML'
            ]);
        }
	}
}
