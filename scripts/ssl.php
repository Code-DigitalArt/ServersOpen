<?php

$stringToVerify = '50.009781OK101092014125505';
$ECDSA =     '3045022100b4b4064158cb12f5b3d902e1e4487e0c6dfafd96b5bb5ab9765fc088e054d67e0220153f9bb5da20441c68ff0c3e8ba28cfe048e5c3152fc8c890def156cf09d5540';
$publicKey = "-----BEGIN PUBLIC KEY-----
MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEaq6djyzkpHdX7kt8DsSt6IuSoXjp
WVlLfnZPoLaGKc/2BSfYQuFIO2hfgueQINJN3ZdujYXfUJ7Who+XkcJqHQ==
-----END PUBLIC KEY-----";

var_dump(openssl_verify($stringToVerify, pack("H", $ECDSA), $publicKey,     OPENSSL_ALGO_SHA256));
var_dump(openssl_error_string());
