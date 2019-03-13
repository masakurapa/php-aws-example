<?php
require_once '../vendor/autoload.php';

function invite(string $email): void
{
    $cognito = new Aws\CognitoIdentityProvider\CognitoIdentityProviderClient([
        'region' => 'ap-northeast-1',
        'version' => '2016-04-18',
        'profile' => 'cognito-test',
    ]);
    $cognito->adminCreateUser([
        'UserPoolId' => 'ap-northeast-1_xxxxxxxxx',
        'Username' => $email,
        'UserAttributes' => [
            ['Name' => 'email', 'Value' => $email],
        ],
    ]);
}

$error = '';
$email = $_POST['email'] ?? '';
if (!empty($email)) {
    try {
        invite($email);
        header('Location: http://localhost:8080/user-invite', true, 301);
        exit;
    } catch (Aws\Exception\AwsException $e) {
        if ($e->getAwsErrorCode() === 'UsernameExistsException') {
            $error = 'ユーザーが既に存在する';
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
        <title>user invite</title>
    </head>
    <body>
        <h2>ユーザー招待サンプル</h2>
        <div style="color: red">$error</div>
        <form method="POST" action="/user-invite">
            <div style="margin-bottom: 20px;">
                <label>メールアドレス: </label>
                <input type="text" name="email" value="$email">
            </div>
            <input type="submit" value="ユーザー招待">
        </form>
    </body>
</html>
EOT;
