<?php
require_once '../vendor/autoload.php';

function login(string $email, string $password): Aws\Result
{
    $cognito = new Aws\CognitoIdentityProvider\CognitoIdentityProviderClient([
        'region' => 'ap-northeast-1',
        'version' => '2016-04-18',
        'profile' => 'cognito-test',
    ]);

    return $cognito->adminInitiateAuth([
        'UserPoolId' => 'ap-northeast-1_xxxxxxxxx',
        'ClientId' => 'xxxxxxxxxxxxxxxxxxxxxxxxxx',
        'AuthFlow' => 'ADMIN_NO_SRP_AUTH',
        'AuthParameters' => [
            'USERNAME' => $email,
            'PASSWORD' => $password,
        ],
    ]);
}

$error = '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
if (!empty($email) && !empty($password)) {
    try {
        $result = login($email, $password);
        $authResult = $result->get('AuthenticationResult');

        // 初回ログインの場合、AuthenticationResultが返却されない
        // パスワード初期化に必要なSessionだけ次の画面に引き継ぐ
        if ($authResult === null) {
            setcookie('session-token', $result->get('Session'), time() + 60 * 60);
            header('Location: http://localhost:8080/change-password', true, 301);
            exit;
        }

        // アクセストークン、セッショントークンを引き継ぐ
        setcookie('access-token', $authResult['AccessToken'], time() + 60 * 60);
        setcookie('refresh-token', $authResult['RefreshToken'], time() + 60 * 60 * 30);
        header('Location: http://localhost:8080/', true, 301);
        exit;
    } catch (Aws\Exception\AwsException $e) {
        if ($e->getAwsErrorCode() === 'PasswordResetRequiredException') {
            $error = 'パスワードリセットが必要';
        } elseif ($e->getAwsErrorCode() === 'NotAuthorizedException') {
            $error = 'パスワードが間違っている';
        } elseif ($e->getAwsErrorCode() === 'UserNotFoundException') {
            $error = '存在しないユーザー';
        } else {
            $error = $e->getMessage();
        }
    } catch (\Exception $e) {
        $error = $e->getMessage();
    }
}

echo <<<EOT
<html>
    <head>
        <meta charset="UTF-8">
        <title>login</title>
    </head>
    <body>
        <h2>ログインサンプル</h2>
        <div style="color: red">$error</div>
        <form method="POST" action="/login">
            <div style="margin-bottom: 20px;">
                <label>メールアドレス: </label>
                <input type="text" name="email" value="$email">
            </div>
            <div style="margin-bottom: 20px;">
                <label>パスワード: </label>
                <input type="password" name="password">
            </div>
            <input type="submit" value="ログイン">
        </form>
    </body>
</html>
EOT;
