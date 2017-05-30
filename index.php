<?php
/**
 * Created by PhpStorm.
 * User: leonidbugaenko
 * Date: 29.05.17
 * Time: 19:16
 */

error_reporting(E_ALL);
ini_set("display_errors", 1);

include ('api/amocrm.php');

$amo = new amo();

$amo->auth();

#Понадобится
$current = $amo->request('accounts/current');
$responsible_user_id = $current->response->account->id;

//$get_leads = $amo->request('leads/list', 'get');
//$amo->debug( $get_leads );
//exit;

if ($_POST){
    $data = $_POST;

    $contacts['request']['contacts']['add'] = array(
        array(
            'name'=>$data['name'],
            "custom_fields"=>[
                [
                    "id"=>15051,
                    "values"=> [
                        [
                            "value"=>$data['phone'],
                            "enum"=>"WORK"
                        ]
                    ]
                ],
                [
                    "id"=>15053,
                    "values"=> [
                        [
                            "value"=>$data['email'],
                            "enum"=>"WORK"
                        ]
                    ]
                ]

            ]
        )
    );

    $contact_resp = $amo->request('contacts/set', 'post', $contacts);
    $user_id = $contact_resp->response->contacts->add[0]->id;


    #Добавляем сделку
    $leads['request']['leads']['add']=array(
        array(
            'name'=>'Deal for '.$data['name'],
            'price'=>500,
            'responsible_user_id'=>$responsible_user_id,
            'main_contact_id'=>$user_id
        )
    );
    $lead_resp = $amo->request('leads/set', 'post', $leads);
    $lead_id = $lead_resp->response->leads->add[0]->id;
//    $amo->debug($lead_resp);



    #Добавялем связь контакта со сделкой
    $links['request']['links']['link'] = array(
        array(
            'from' => 'leads',
            'to' => 'contacts',
            'to_id' => $user_id,
            'from_id' => $lead_id,
        )
    );
    $links_resp = $amo->request('links/set', 'post', $links);
//    $amo->debug($links_resp);


    #Создаем задачу
    $tasks['request']['tasks']['add']=array(
        #Привязываем к сделке
        array(
            'element_id'=>$lead_id, #ID сделки
            'element_type'=>2, #Показываем, что это - сделка, а не контакт
            'task_type'=>1, #Звонок
            'text'=>'Позвонить клиенту',
            'responsible_user_id'=>$responsible_user_id,
            'complete_till'=>time()
        ),
        #Привязываем к контакту
        array(
            'element_id'=>$user_id,
            'element_type'=>1,
            'task_type'=>2, #Встреча
            'text'=>'Встретиться с клиентом',
            'responsible_user_id'=>$responsible_user_id,
            'complete_till'=>time()
        )
    );
    $task_resp = $amo->request('tasks/set', 'post', $tasks);
//    $amo->debug($task_resp);
}