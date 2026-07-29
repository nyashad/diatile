<?php
/**
 * PayFast Instant Transaction Notification (ITN) handler.
 * PayFast POSTs here after a payment attempt. Respond with HTTP 200.
 */

declare(strict_types=1);

$config = require __DIR__ . '/config.payfast.php';

header('HTTP/1.0 200 OK');
header('Content-Type: text/plain; charset=utf-8');

$pfData = [];
foreach ($_POST as $key => $val) {
    $pfData[$key] = stripslashes((string) $val);
}

if (empty($pfData)) {
    echo 'Empty';
    exit;
}

$pfParamString = '';
foreach ($pfData as $key => $val) {
    if ($key === 'signature') {
        continue;
    }
    $pfParamString .= $key . '=' . urlencode($val) . '&';
}
$pfParamString = substr($pfParamString, 0, -1);
$pfParamString .= '&passphrase=' . urlencode(trim((string) $config['passphrase']));
$signature = md5($pfParamString);

if (!hash_equals($signature, $pfData['signature'] ?? '')) {
    error_log('PayFast ITN: signature mismatch');
    echo 'Invalid signature';
    exit;
}

$pfHost = !empty($config['sandbox']) ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';
$validateUrl = 'https://' . $pfHost . '/eng/query/validate';

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $validateUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER         => false,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query($pfData),
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_USERAGENT      => 'Diatile-PayFast-ITN',
]);
$response = curl_exec($ch);
$curlError = curl_error($ch);
curl_close($ch);

if ($response === false) {
    error_log('PayFast ITN: validate failed — ' . $curlError);
    echo 'Validate failed';
    exit;
}

if (trim($response) !== 'VALID') {
    error_log('PayFast ITN: host validation returned ' . $response);
    echo 'Not valid';
    exit;
}

$amountExpected = number_format((float) $config['amount'], 2, '.', '');
$amountPaid = number_format((float) ($pfData['amount_gross'] ?? 0), 2, '.', '');
$status = $pfData['payment_status'] ?? '';

if ($status === 'COMPLETE' && $amountPaid === $amountExpected) {
    error_log(
        'PayFast ITN COMPLETE: payment_id=' . ($pfData['m_payment_id'] ?? '') .
        ' pf_payment_id=' . ($pfData['pf_payment_id'] ?? '') .
        ' amount=' . $amountPaid
    );
}

echo 'OK';
