## Yandex Cloude Instance Auto Restarter
Данный скрипт проверяет доступность прерываемого инстанса в Яндекс.Облаке.
Если инстанс остановился, скрипт получает IAM-токен для сервисного аккаунта и запускает остановленный инстанс.

Рекомендуется запускать данный скрипт по cron.
```
php healthcheck.php
```

### Установка
```
composer install
cp .env.example .env
```

### Конфигурация
Выполните 1 и 2 шаг из инструкции https://cloud.yandex.ru/docs/iam/operations/iam-token/create-for-sa.

Заполните своими данными следующие переменные в файле `.env`
```
YC_SERVICE_ACCOUNT_ID=
YC_KEY_ID=

YC_INSTANCE_IP=
YC_INSTANCE_NAME=
```

Положите `private_key` - закрытый авторизованный ключ сервисного аккаунта в файл `private.pem`.
