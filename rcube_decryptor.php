<?php

/*
    PHP script for decrypting user's imap password in Roundcube stored in the session table.
    Extract password field from vars column in session table base64 decoded.

    Adapted from https://github.com/roundcube/roundcubemail/blob/master/program/lib/Roundcube/rcube.php#L943
    
    Usage: php rcube_decrypt.php <cipher_text> <decryption_key>
*/

function get_crypto_method()
{
    return 'DES-EDE3-CBC';
    // return $this->get('cipher_method') ?: 'DES-EDE3-CBC';
}

function decrypt($cipher, $key = 'rcmail-!24ByteDESkey*Str', $base64 = true)
{
    // @phpstan-ignore-next-line
    if (!is_string($cipher) || !strlen($cipher)) {
        return false;
    }

    if ($base64) {
        $cipher = base64_decode($cipher, true);
        if ($cipher === false) {
            return false;
        }
    }

    // $ckey = $this->config->get_crypto_key($key);
    $method = get_crypto_method();
    $iv_size = openssl_cipher_iv_length($method);
    $tag = null;

    if (preg_match('/^##(.{16})##/s', $cipher, $matches)) {
        $tag = $matches[1];
        $cipher = substr($cipher, strlen($matches[0]));
    }

    $iv = substr($cipher, 0, $iv_size);
    // session corruption? (#1485970)
    if (strlen($iv) < $iv_size) {
        return false;
    }

    $cipher = substr($cipher, $iv_size);
    $clear = openssl_decrypt($cipher, $method, $key, \OPENSSL_RAW_DATA, $iv, $tag);

    return $clear;
}

if ($argc < 3) {
    echo "Usage: php rcube_decrypt.php <cipher_text> <decryption_key>\n";
    exit(1);
}

$ciphertext = $argv[1];
$desKey = $argv[2];
$plaintext = decrypt($ciphertext, $desKey);

if ($plaintext) {
    echo "Ciphertext=" . $ciphertext . "\n";
    echo "Key=" . $desKey . "\n";
    echo "Plaintext=" . $plaintext . "\n";
} else {
    echo "Decryption failed :( \n";
}
