<?php
use Illuminate\Support\Facades\Http;
$url = 'https://dgii.gov.do/app/WebApps/ConsultasWeb2/ConsultasWeb/consultas/rnc.aspx';
$cookieJar = new \GuzzleHttp\Cookie\CookieJar();
$responseGet = Http::withOptions(['cookies' => $cookieJar])->withoutVerifying()->timeout(10)->get($url);
file_put_contents('dgii_debug_get.html', $responseGet->body());
var_dump("GET STATUS: " . $responseGet->status());

$htmlGet = $responseGet->body();
preg_match('/<input type="hidden" name="__VIEWSTATE" id="__VIEWSTATE" value="([^"]*)"/', $htmlGet, $v);
dump("VIEWSTATE: " . (isset($v[1]) ? substr($v[1], 0, 20) : "MISSING"));

$vGen = '';
preg_match('/<input type="hidden" name="__VIEWSTATEGENERATOR" id="__VIEWSTATEGENERATOR" value="([^"]*)"/', $htmlGet, $vGenM);
if(isset($vGenM[1])) $vGen = $vGenM[1];

$vEvt = '';
preg_match('/<input type="hidden" name="__EVENTVALIDATION" id="__EVENTVALIDATION" value="([^"]*)"/', $htmlGet, $vEvtM);
if(isset($vEvtM[1])) $vEvt = $vEvtM[1];

$responsePost = Http::asForm()->withOptions(['cookies' => $cookieJar])->withoutVerifying()->timeout(10)->post($url, [
    '__EVENTTARGET' => '',
    '__EVENTARGUMENT' => '',
    '__VIEWSTATE' => isset($v[1]) ? $v[1] : '',
    '__VIEWSTATEGENERATOR' => $vGen,
    '__EVENTVALIDATION' => $vEvt,
    'ctl00$cphMain$txtRNCCedula' => '132049535',
    'ctl00$cphMain$btnBuscarPorRNC' => 'BUSCAR'
]);
var_dump("POST STATUS: " . $responsePost->status());
file_put_contents('dgii_debug_post.html', $responsePost->body());
preg_match('/Nombre\/Raz\&#243;n Social<\/td>\s*<td>([^<]+)<\/td>/is', $responsePost->body(), $m);
dump($m);
