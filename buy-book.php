<?php
/**
 * Creates a signed PayFast checkout and redirects the buyer.
 */

declare(strict_types=1);

$config = require __DIR__ . '/config.payfast.php';

function payfastBaseUrl(): string
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (string) $_SERVER['SERVER_PORT'] === '443')
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $dir = rtrim($dir, '/');

    return $scheme . '://' . $host . ($dir === '' || $dir === '/' ? '' : $dir);
}

/**
 * PayFast requires URL-encoded values with uppercase hex and spaces as +.
 */
function generatePayfastSignature(array $data, string $passPhrase): string
{
    $pairs = [];

    foreach ($data as $key => $val) {
        if ($val === '' || $val === null) {
            continue;
        }
        $pairs[] = $key . '=' . urlencode(trim((string) $val));
    }

    $getString = implode('&', $pairs);
    $getString .= '&passphrase=' . urlencode(trim($passPhrase));

    return md5($getString);
}

$base = payfastBaseUrl();
$pfHost = !empty($config['sandbox']) ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';

$data = [
    'merchant_id'      => (string) $config['merchant_id'],
    'merchant_key'     => (string) $config['merchant_key'],
    'return_url'       => $base . '/payment-success.html',
    'cancel_url'       => $base . '/payment-cancel.html',
    'notify_url'       => $base . '/payfast-notify.php',
    'm_payment_id'     => 'book-' . date('YmdHis') . '-' . bin2hex(random_bytes(3)),
    'amount'           => number_format((float) $config['amount'], 2, '.', ''),
    'item_name'        => (string) $config['item_name'],
    'item_description' => (string) $config['item_description'],
];

$data['signature'] = generatePayfastSignature($data, (string) $config['passphrase']);

header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Redirecting to PayFast&hellip;</title>
  <style>
    body {
      margin: 0;
      min-height: 100vh;
      display: grid;
      place-items: center;
      background: #f7f4ee;
      color: #384d59;
      font-family: Georgia, serif;
      text-align: center;
    }
    p { margin: 0 0 12px; font-size: 1.25rem; }
    small { color: #5a6f7a; font-family: Arial, sans-serif; font-size: 0.85rem; }
  </style>
</head>
<body>
  <div>
    <p>Taking you to secure checkout&hellip;</p>
    <small>If nothing happens, use the button below.</small>
    <form id="payfast" action="https://<?php echo htmlspecialchars($pfHost, ENT_QUOTES, 'UTF-8'); ?>/eng/process" method="post" style="margin-top: 24px;">
<?php foreach ($data as $name => $value): ?>
      <input type="hidden" name="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" value="<?php echo htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?>">
<?php endforeach; ?>
      <button type="submit" style="padding: 12px 22px; border: 0; background: #384d59; color: #fffdf8; font-size: 0.85rem; cursor: pointer;">
        Continue to PayFast
      </button>
    </form>
  </div>
  <script>document.getElementById('payfast').submit();</script>
</body>
</html>
