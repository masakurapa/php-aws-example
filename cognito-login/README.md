# Cognitoを使ったログイン画面

## 準備
- `cognito-idp:*` の権限がついているユーザーを用意する

```
$ aws configure --profile cognit-test
AWS Access Key ID [********************]:
AWS Secret Access Key [********************]:
Default region name [ap-northeast-1]:
Default output format [json]:
```

## CloudFormation
- `cognito_user_pool.yml` を実行しておく
- プログラム内のUserPoolID, ClientIDをそれぞれ置換する

## サーバー起動
```
$ php -S localhost:8080
$ open http://localhost:8080
```

## Links
- [AWS SDK](https://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.CognitoIdentityProvider.CognitoIdentityProviderClient.html)
- [CloudFormation](https://docs.aws.amazon.com/ja_jp/AWSCloudFormation/latest/UserGuide/aws-resource-cognito-userpool.html)
