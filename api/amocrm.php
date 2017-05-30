<?php
/**
 * Created by PhpStorm.
 * User: leonidbugaenko
 * Date: 29.05.17
 * Time: 19:16
 */
class amo{

    #Поддомен
    const subdomain = 'new592c615cc43fc';

    #Массив с параметрами, которые нужно передать методом POST к API системы
    const user  = array (
                        'USER_LOGIN'=>'bugaenko.leonid@gmail.com', #Ваш логин (электронная почта)
                        'USER_HASH'=>'5c7d5017287b0ea76e5a3f14ee224731' #Хэш для доступа к API (смотрите в профиле пользователя)
                    );

    public function auth(){

        #Формируем ссылку для запроса
        $link='https://'.self::subdomain.'.amocrm.ru/private/api/auth.php?type=json';

        return $this->sendPostAuth($link, self::user);
    }

    public function request($link, $type = 'get', $data = null){
        if ($link){
            $method = explode('/', $link);
            $method = $method[0];
        }

        $link = 'https://'.self::subdomain.'.amocrm.ru/private/api/v2/json/'.$link;
        $cookie = file_get_contents('api/cookie.txt');


        $curl=curl_init();
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
        curl_setopt($curl,CURLOPT_URL, $link);
        if ($type == 'post' && $data){
            curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
            curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($data));
        }
        curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
        curl_setopt($curl,CURLOPT_HEADER,false);
        curl_setopt($curl,CURLOPT_COOKIEFILE,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
        curl_setopt($curl,CURLOPT_COOKIEJAR,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);

        $out=curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
        $code=curl_getinfo($curl,CURLINFO_HTTP_CODE); #Получим HTTP-код ответа сервера
        curl_close($curl); #Завершаем сеанс cURL

        return json_decode($out);
    }

    private function sendPostAuth($link, $user){
        $curl=curl_init();
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
        curl_setopt($curl,CURLOPT_URL,$link);
        curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
        curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($user));
        curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
        curl_setopt($curl,CURLOPT_HEADER,false);
        curl_setopt($curl,CURLOPT_COOKIEFILE,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
        curl_setopt($curl,CURLOPT_COOKIEJAR,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);

        $out=curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
        $code=curl_getinfo($curl,CURLINFO_HTTP_CODE); #Получим HTTP-код ответа сервера
        curl_close($curl); #Завершаем сеанс cURL
        return $this->response($code, $out);
    }

    private function response($code, $out){
        $code=(int)$code;
        $errors=array(
            301=>'Moved permanently',
            400=>'Bad request',
            401=>'Unauthorized',
            403=>'Forbidden',
            404=>'Not found',
            500=>'Internal server error',
            502=>'Bad gateway',
            503=>'Service unavailable'
        );
        try
        {
            #Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке
            if($code!=200 && $code!=204)
                throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error',$code);
        }
        catch(Exception $E)
        {
            die('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
        }

        /**
         * Данные получаем в формате JSON, поэтому, для получения читаемых данных,
         * нам придётся перевести ответ в формат, понятный PHP
         */
        $Response=json_decode($out,true);
        $Response=$Response['response'];
        if(isset($Response['auth'])) #Флаг авторизации доступен в свойстве "auth"
            return 'Авторизация прошла успешно';
        return 'Авторизация не удалась';
    }

    public function debug($data){
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        echo '<hr>';
    }
}