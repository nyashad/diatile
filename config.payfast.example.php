<?php
/**
 * Template for PayFast credentials.
 *
 * On the server, copy this file to config.payfast.php and fill in the real
 * values from the PayFast dashboard. config.payfast.php is gitignored so the
 * live merchant key and passphrase never reach the repository.
 */

return [
    'merchant_id'  => 'YOUR_MERCHANT_ID',
    'merchant_key' => 'YOUR_MERCHANT_KEY',
    'passphrase'   => 'YOUR_PASSPHRASE',
    // Set to true only while testing against PayFast sandbox credentials.
    'sandbox'      => false,
    'amount'       => '50.00',
    'item_name'    => 'Are You Awake?',
    'item_description' => 'Book by Diatile Ndhlovu',
];
