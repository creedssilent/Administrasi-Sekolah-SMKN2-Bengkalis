<?php
// Kunci enkripsi (SIMPAN INI DENGAN SANGAT AMAN!)
$encryption_key = "RahasiaAdmin123!"; // Ganti dengan kunci yang lebih kuat

function encryptPassword($password, $key)
{
    $plaintext = $password;
    $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
    $iv = openssl_random_pseudo_bytes($ivlen);
    $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
    $hmac = hash_hmac('sha256', $ciphertext_raw, $key, true);
    return base64_encode($iv . $hmac . $ciphertext_raw);
}

function decryptPassword($ciphertext, $key)
{
    $c = base64_decode($ciphertext);
    $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
    $iv = substr($c, 0, $ivlen);
    $hmac = substr($c, $ivlen, 32);
    $ciphertext_raw = substr($c, $ivlen + 32);
    $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
    $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, true);
    if (hash_equals($hmac, $calcmac)) //PHP 5.6+ timing attack safe comparison
    {
        return $original_plaintext;
    }
    return false;
}
// Kurung kurawal yang salah sudah dihapus dari sini
