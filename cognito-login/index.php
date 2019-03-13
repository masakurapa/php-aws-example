<?php
require_once './vendor/autoload.php';

function getUser(string $token): Aws\Result
{
    $cognito = new Aws\CognitoIdentityProvider\CognitoIdentityProviderClient([
        'region' => 'ap-northeast-1',
        'version' => '2016-04-18',
        'profile' => 'cognito-test',
    ]);
    return $cognito->getUser([
        'AccessToken' => $_COOKIE['access-token'],
    ]);
}

function refresh(string $token): Aws\Result
{
    $cognito = new Aws\CognitoIdentityProvider\CognitoIdentityProviderClient([
        'region' => 'ap-northeast-1',
        'version' => '2016-04-18',
        'profile' => 'cognito-test',
    ]);
    return $cognito->adminInitiateAuth([
        'UserPoolId' => 'ap-northeast-1_OIk6bWHkX',
        'ClientId' => '4aee0piacmvori3f9r79j3nr2v',
        'AuthFlow' => 'REFRESH_TOKEN',
        'AuthParameters' => [
            'REFRESH_TOKEN' => $_COOKIE['refresh-token'],
        ],
    ]);
}

$error = '';
$username = '';
$email = '';
$accessToken = $_COOKIE['access-token'] ?? '';
$refreshToken = $_COOKIE['refresh-token'] ?? '';

if (!empty($accessToken) || !empty($refreshToken)) {
    // アクセストークンがないときはアクセストークンを更新
    if (empty($accessToken)) {
        $refreshResult = refresh($refreshToken);
        $authResult = $result->get('AuthenticationResult');
        $accessToken = $refreshResult['AccessToken'];
        setcookie('access-token', $accessToken, time() + 60 * 60);
    }

    try {
        $result = getUser($accessToken);
    } catch (Aws\Exception\AwsException $e) {
        if ($e->getAwsErrorCode() === 'NotAuthorizedException') {
            // アクセストークンが切れているかも？
            // リフレッシュトークンがあればアクセストークンを更新する
            // この中の例外はもう諦める
            if (!empty($refreshToken)) {
                try {
                    $refreshResult = refresh($refreshToken);
                    $authResult = $result->get('AuthenticationResult');
                    setcookie('access-token', $accessToken, time() + 60 * 60);

                    $result = getUser($accessToken);
                } catch (\Exception $e) {
                    $error = $e->getMessage();
                }
            }
        } else {
            $error = $e->getMessage();
        }
    } catch (\Exception $e) {
        $error = $e->getMessage();
    }

    if (empty($error)) {
        $username = $result->get('Username');
        // メールアドレスを持ってくる
        $filtered = array_values(array_filter($result->get('UserAttributes'), function ($value) {
            return $value['Name'] === 'email';
        }));
        $email = $filtered[0]['Value'] ?? '';
    }
}

echo <<<EOT
<html>
    <head>
        <meta charset="UTF-8">
        <title>index</title>
    </head>
    <body>
        <h2>インデックス</h2>
        <div style="margin-bottom: 30px">
            <div>ユーザー名: $username</div>
            <div>メールアドレス: $email</div>
        </div>

        <div style="color: red">$error</div>
        <ul>
            <li><a href="/user-invite">User Invite</a></li>
            <li><a href="/login">Login</a></li>
            <li><a href="/sign-out">Logout</a></li>
        </ul>
    </body>
</html>
EOT;
