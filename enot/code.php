<?php

// Задачу можно решить следующим образом

// Для способов отправки кода используем следующий интерфейс и классы

interface SendProvider {
	public function send($code) 
}

class SmsSendProvider implements SendProvider {
	public function send($code) {
		// реализация для смс
	}
}

class EmailSendProvider implements SendProvider {
	public function send($code) {
		// реализация для email
	}
}

class SmsSendProvider implements SendProvider {
	public function send($code) {
		// реализация для telegram
	}
}

// Для работы c отправкой кода используем класс

class SendCode {
	private $sendProvider;

	public function __construct(SendProvider $sendProvider){
        $this->sendProvider = $sendProvider;
    }

	function sendCode() {
		$code = generateCode(); // генерация кода
		$this->sendProvider->send($code);
        return $code;
	}
}

// Для работы с сохранением/получением кода используем класс

class UserSettingsCode {
	private $storeCodeProvider;

	public function __construct(StoreCodeProvider $storeCodeProvider) {
        // сохранение кода  — БД или Redis etc
		$this->storeCodeProvider = $storeCodeProvider; 
    }

	function storeCode($code, $settingName, $settingValue) {
        // сохранение данных с userId
		$this->storeCodeProvider->store($app->userId, $code, $settingName, $settingValue);
	}

    function getCode($code) {
        return $this->storeCodeProvider->get($app->userId, $code);
    }
}

// Для обновления настроек 

class UserSettings {
	private $settingsProvider;

	public function __construct(SettingsProvider $settingsProvider) {
        // сохранение настроек
		$this->settingsProvider = $settingsProvider; 
    }

	function updateSettings($settingName, $settingValue) {
        // сохранение данных с userId
		$this->settingsProvider->store($app->userId, $settingName, $settingValue);
	}
}

// КОНТРОЛЛЕР - ОТПРАВКА КОДА ПОДТВЕРЖДЕНИЯ

$sendType = $request[`sendType`];
$settingName = $request[`settingName`];
$settingValue = $request[`settingValue`];

if ($sendType == `sms`) // например для смс
	$sendProvider = new SmsSendProvider();

// Отправка кода    
$sendCode = SendCode($sendProvider);

$confirmCode = $sendCode->sendCode();

$storeCodeProvider = new RedisCodeStoreProvider();

$userSettingsCode = new UserSettingsCode($storeCodeProvider);

// Код в хранилище
$userSettingsCode->storeCode($confirmCode, $settingName, $settingValue);



// КОНТРОЛЛЕР - ПРОВЕРКА КОДА И ОБНОВЛЕНИЕ НАСТРОЙКИ 

$confirmCode = $request[`code`];

$storeCodeProvider = new RedisCodeStoreProvider();

$userSettingsCode = new UserSettingsCode($storeCodeProvider);

// Извлекаем из хранилища данные о коде, названии настройки и значении настройки
$settingsArr = $userSettingsCode->getCode($confirmCode);

if ($settingsArr['code'] == $confirmCode) {
    $settingsProvider = new DBSettingsProvider();

    $userSettings = new UserSettings($settingsProvider);
    $userSettings->updateSettings($settingsArr['settingName'], $settingsArr['settingValue']);
}






