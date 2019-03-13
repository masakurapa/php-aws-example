<?php
require_once '../vendor/autoload.php';

function logout(string $token): void
{
    $cognito = new Aws\CognitoIdentityProvider\CognitoIdentityProviderClient([
        'region' => 'ap-northeast-1',
        'version' => '2016-04-18',
        'profile' => 'cognito-test',
    ]);
    $cognito->globalSignOut([
        'AccessToken' => $token,
    ]);
}

$accessToken = $_COOKIE['access-token'] ?? '';
if (!empty($accessToken)) {
    try {
        logout($accessToken);
    } catch (\Exception $e) {
        // 無視
    }
}

// アクセストークン、セッショントークンを消す
setcookie('access-token', null, -1);
setcookie('refresh-token', null, -1);
header('Location: http://localhost:8080/', true, 301);
exit;
