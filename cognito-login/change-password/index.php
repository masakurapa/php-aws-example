<?php
require_once '../vendor/autoload.php';

function changePassword(string $email, string $password, string $session): Aws\Result
{
    $cognito = new Aws\CognitoIdentityProvider\CognitoIdentityProviderClient([
        'region' => 'ap-northeast-1',
        'version' => '2016-04-18',
        'profile' => 'cognito-test',
    ]);
    return $cognito->adminRespondToAuthChallenge([
        'UserPoolId' => 'ap-northeast-1_xxxxxxxxx',
        'ClientId' => 'xxxxxxxxxxxxxxxxxxxxxxxxxx',
        'Session' => $session,
        'ChallengeName' => 'NEW_PASSWORD_REQUIRED',
        'ChallengeResponses' => [
            'USERNAME' => $email,
            'NEW_PASSWORD' => $password,
        ],
    ]);
}

$error = '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
if (!empty($email) && !empty($password)) {
    try {
        $result = changePassword($email, $password, $_COOKIE['session-token']);
        $authResult = $result->get('AuthenticationResult');

        setcookie('access-token', $authResult['AccessToken'], time() + 60 * 60);
        setcookie('refresh-token', $authResult['RefreshToken'], time() + 60 * 60 * 30);
        setcookie('session-token', null, -1);
        header('Location: http://localhost:8080', true, 301);
        exit;
    } catch (Aws\Exception\AwsException $e) {
        if ($e->getAwsErrorCode() === 'InvalidPasswordException') {
            $error = 'パスワードポリシー違反';
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
        <title>change password</title>
    </head>
    <body>
        <h2>初期パスワード変更サンプル</h2>
        <div style="color: red">$error</div>
        <form method="POST" action="/change-password">
            <div style="margin-bottom: 20px;">
                <label>メールアドレス: </label>
                <input type="text" name="email" value="$email">
            </div>
            <div style="margin-bottom: 20px;">
                <label>パスワード: </label>
                <input type="password" name="password">
            </div>
            <input type="submit" value="パスワードリセット">
        </form>
    </body>
</html>
EOT;
