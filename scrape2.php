<?php
$url = 'https://dgii.gov.do/app/WebApps/ConsultasWeb2/ConsultasWeb/consultas/rnc.aspx';
$rnc = '132049535';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
$htmlGet = curl_exec($ch);

function extractField($html, $id) {
    if (preg_match('/<input type="hidden" name="' . $id . '" id="' . $id . '" value="([^"]*)"/', $html, $matches)) {
        return $matches[1];
    }
    return '';
}

$viewstate = extractField($htmlGet, '__VIEWSTATE');
$viewstateGen = extractField($htmlGet, '__VIEWSTATEGENERATOR');
$eventValidation = extractField($htmlGet, '__EVENTVALIDATION');

$postData = http_build_query([
    '__EVENTTARGET' => '',
    '__EVENTARGUMENT' => '',
    '__VIEWSTATE' => $viewstate,
    '__VIEWSTATEGENERATOR' => $viewstateGen,
    '__EVENTVALIDATION' => $eventValidation,
    'ctl00$cphMain$txtRNCCedula' => $rnc,
    'ctl00$cphMain$btnBuscarPorRNC' => 'BUSCAR'
]);

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
$htmlPost = curl_exec($ch);
curl_close($ch);

// Search for typical DGII response structure
if (preg_match('/<td>([^<]+)<\/td>\s*<td>' . $rnc . '<\/td>/', $htmlPost, $matches)) {
    echo "SUCCESS: " . trim($matches[1]);
} else if (preg_match('/<td>132049535<\/td>\s*<td>([^<]+)<\/td>/', $htmlPost, $matches)) {
    echo "SUCCESS: " . trim($matches[1]);
} else {
    echo "FAIL\n";
    file_put_contents('dgii_fail.html', $htmlPost);
}
