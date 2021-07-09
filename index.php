<?php
include ('ext/madeline.php');
include ('ext/phpQuery.php');
$listS = fopen('content/list.txt', 'r');
$listCitat = fopen('content/citatki.txt', 'r');
$list = stream_get_contents($listS);
$listCitat = stream_get_contents($listCitat);
$list = str_getcsv($list, ';');
$listCitat = str_getcsv($listCitat, ';');
$counter = intval(stream_get_contents(fopen('content/counter.txt', 'r')));
$counter++;
$counterStream = fopen('content/counter.txt', 'w');
fwrite($counterStream, $counter);
fclose($listS);
fclose($counterStream);
$usdlink = 'https://myfin.by/bank/kursy_valjut_nbrb';
$valutes = array('Доллар США' =>'', 'Евро'=>'', 'Российский рубль'=>'', 'Польский злотый'=>'');
function GetCurse($url, $valutes)
{
    $userAgent = 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.31 (KHTML, like Gecko) Chrome/26.0.1410.64 Safari/537.31' ;

    $ch = curl_init($url);

    $options = array(
        CURLOPT_CONNECTTIMEOUT => 20 ,
        CURLOPT_USERAGENT => $userAgent,
        CURLOPT_AUTOREFERER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_SSL_VERIFYPEER => 0 ,
        CURLOPT_SSL_VERIFYHOST => 0
    );

    curl_setopt_array($ch, $options);
    $kl = curl_exec($ch);
    //echo var_dump($kl);
    curl_close($ch);
    //echo var_dump($kl);

    foreach ($valutes as $vaWW => $coast) {
        $pq = phpQuery::newDocument($kl);
        //echo var_dump($vaWW);
        $pq = $pq->find('.default-table-container');
        $pq = $pq->find('tr');
        $is = 0;
        $nameValute = 'n';
        foreach ($pq as $key => $stroke) {
            $stroke = pq($stroke);
            $tds = $stroke->find('td');
            $Valute = array();

            foreach ($tds as $k => $v) {
                $v = pq($v);
                $Valute[$k] = $v;
                if ($v->text() == $vaWW) {
                    //echo var_dump($stroke->html());
                    $stroke = $stroke->find('td');
                    foreach ($stroke as $k => $v) {
                        if ($k == 1) {
                            $v = pq($v);
                            //echo var_dump($v->text());
                            global $valutes;
                            $valutes[$vaWW] = $v->text();
                        }
                    }

                }

            }

        }
    }
}
GetCurse($usdlink, $valutes);
echo var_dump($valutes);
function SendPhoto ($url, $valutes, $citatca )
{
    $usd = strval($valutes["Доллар США"]);
    $eur = strval($valutes["Евро"]);
    $rub = strval($valutes["Российский рубль"]);
    $pl =   strval($valutes["Польский злотый"]);
    $citatca = trim($citatca);
    $msg = '🇺🇸 USD = > '."$usd"."\r\n".'🇪🇺 EURO = > '."$eur"."\r\n".'🇷🇺 RUB = > '."$rub"."\r\n".'🇵🇱 Польский злотый = > '."$pl"."\r\n".'🐺:'.$citatca."\r\n";
    $MP = new \danog\MadelineProto\API('session.madeline');
    $MP->start();
    $contact = ['_' => 'inputPhoneContact', 'client_id' => 0, 'phone' => '!NUMBER_#######!', 'first_name' => '', 'last_name' => ''];
    $import = $MP->contacts->importContacts(['contacts' => [$contact]]);
    $id = $import['users']['0']['id'];
    echo var_dump($url);
    echo var_dump($import['users']['0']['id']);
    $url = trim($url);
    $sentMessage = $MP->messages->sendMedia([
        'peer' => $id,
        'media' => [
            '_' => 'inputMediaUploadedPhoto',
            'file' => $url
        ],
        'message' => $msg,
        'parse_mode' => 'Markdown'
    ]);
}
//echo var_dump($list[$counter]);
SendPhoto($list[$counter], $valutes, $listCitat[$counter]);
?>