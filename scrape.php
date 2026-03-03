<?php
$url = 'https://dgii.gov.do/app/WebApps/ConsultasWeb2/ConsultasWeb/consultas/rnc.aspx';

// Step 1: GET request to fetch hidden ASP.NET fields
$contextGet = stream_context_create([
    "http" => [
        "method" => "GET",
        "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36\r\n"
    ]
]);
$htmlGet = file_get_contents($url, false, $contextGet);

function extractField($html, $id) {
    if (preg_match('/<input type="hidden" name="' . $id . '" id="' . $id . '" value="([^"]*)"/', $html, $matches)) {
        return $matches[1];
    }
    return '';
}

$viewstate = extractField($htmlGet, '__VIEWSTATE');
$viewstateGen = extractField($htmlGet, '__VIEWSTATEGENERATOR');
$eventValidation = extractField($htmlGet, '__EVENTVALIDATION');

// Step 2: POST request to submit the form
$postData = http_build_query([
    '__EVENTTARGET' => '',
    '__EVENTARGUMENT' => '',
    '__VIEWSTATE' => $viewstate,
    '__VIEWSTATEGENERATOR' => $viewstateGen,
    '__EVENTVALIDATION' => $eventValidation,
    'ctl00$cphMain$txtRNCCedula' => '132049535',
    'ctl00$cphMain$btnBuscarPorRNC' => 'BUSCAR'
]);

$contextPost = stream_context_create([
    "http" => [
        "method" => "POST",
        "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36\r\n" .
                    "Content-Type: application/x-www-form-urlencoded\r\n" .
                    "Content-Length: " . strlen($postData) . "\r\n",
        "content" => $postData
    ]
]);
$htmlPost = file_get_contents($url, false, $contextPost);

file_put_contents('dgii_post_test.html', $htmlPost);

// Extract the Name
if (preg_match('/<td>([A-Z0-9\s.,-]+)<\/td>\s*<td>132049535<\/td>/is', $htmlPost, $matches)) {
    echo "Found Name: " . trim($matches[1]) . "\n";
} else {
    echo "Name not found in HTML. Check dgii_post_test.html\n";
}
