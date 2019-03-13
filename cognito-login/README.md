# Cognitoを使ったログインページのサンプルコード

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
- https://docs.aws.amazon.com/ja_jp/AWSCloudFormation/latest/UserGuide/cfn-reference-cognito.html

## サーバー起動
```
$ php -S localhost:8080
```


