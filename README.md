Бандл для проверки авторизации на сервисе авторизации youtool.
============================================================

Бандл реализует `authenticator` для проверки токена авторизации. Предназначен в первую очередь для тех сервисов, которые хотят 
использовать сервис авторизации youtool.



Установка.
----------

Бандл устанавливается с помощью `composer` и следует стандартной структуре, поэтому на `symfony >=4.2` устанавливается автоматически.

1. Добавить репозиторий в `composer.json` проекта:

    ```json
    "repositories": [
        {
            "type": "git",
            "url": "https://git.crtweb.ru/youtool/bundle-check-auth"
        }
    ]
    ```

2. Добавить пакет бандла в проект:

    ```bash
    $ composer require youtool/bundle-check-auth
    ```

3. Добавить `authenticator` в настройки `security`:

    ```yaml
    # app/config/packages/security.yaml
    security:
        firewalls:
            main:
                stateless: true
                guard:
                    authenticators:
                        - Youtool\AuthBundle\Security\JwtAuthenticator
    ```

4. Бандл не предоставляет [класса для получения пользователей `UserProviderInterface`](https://symfony.com/doc/current/security.html#b-the-user-provider). 
Провайдер пользователей должен быть реализован для каждого проекта отдельно. В случае, если в `authenticator` будет передан 
стандартный провайдер пользователей `UserProviderInterface`, то в метод провайдера `loadUserByUsername` будет передан `sub` 
параметр (идентификатор пользователя на сервисе авторизации), полученный из jwt. Если требуется передать в провайдер пользователей 
весь объект jwt, то провайдер должен реализовывать интерфейс `Youtool\AuthBundle\Security\JwtUserProviderInterface`.




Настройка.
----------

Если требуется переопределить какие-либо опции, то нужно создать файл конфигурации и добавить в него соответствующие значения:

```yaml
# app/config/packages/youtool_auth.yaml
 youtool_auth:
    client_id: 550e8400-e29b-41d4-a716-446655440000
    client_secret: secret
    redirect_url: http://api.youtool.ru/auth-callback
    auth_scopes:
        - api
    required_scopes:
        - api
```

Доступные опции бандла:

* `expired_timeout` - время в секундах, которое должно оставаться до истечения jwt, чтобы считать его валидным, используется для учета лага на сетевой запрос,

* `required_scopes` - массив разрешений, которые в обязательном порядке должен содержать jwt для успешной авторизации,

* `public_key_path` - абсолютный путь к файлу с открытым ключом сервиса авторизации для проверки подписи jwt,

* `base_url` - базовая ссылка на сервис авторизации,

* `client_id` - идентификатор клиента на сервисе авторизации,

* `client_secret` - пароль клиента на сервисе авторизации,

* `redirect_url` - ссылка, на которую будет возвращен пользователь после успешной авторизации.

* `auth_scopes` - массив разрешений, которые нужно запросить при авторизации пользовтеля.

Если требуется добавить дополнительную проверку токена, то следует объявить сервис c тегом `youtool_auth.token.validator`, 
который будет реализовывать `Youtool\AuthBundle\Jwt\Validator\ValidatorInterface`.



Использование в локальном окружении.
------------------------------------

Для локальной разработки или для запуска тестов также доступен упрощенный алгоритм авторизации, без обращения к сервису авторизации. 
Для этого нужно настроить заглушку для сервиса авторизации:

```yaml
# app/config/packages/dev/youtool_auth.yaml для локальной разработки
# app/config/packages/test/youtool_auth.yaml для тестов
 youtool_auth:
    # для подписи jwt нужно использовать тестовый токен
    public_key_path: '%kernel.project_dir%/vendor/youtool/bundle-check-auth/Resources/keys/test.public.key'

services:
    # для тестового окружения используем тестовый сервис,
    # чтобы не обращаться каждый раз к сервису авторизации
     you_tool_auth.service.service:
        class: Youtool\AuthBundle\Service\TestService
        arguments:
            $config: '@youtool_auth.service.config'
            $sub: d069366c-a669-4379-b9b5-0f0f18d6b0c5
            $allowedSubs:
                - 53618f8a-3372-11e9-b210-d663bd873d93
```

При таких настройках любой правильно оформленный jwt, который еще не истек и содержит поле `sub`, 
равное `d069366c-a669-4379-b9b5-0f0f18d6b0c5` или `53618f8a-3372-11e9-b210-d663bd873d93`, будет считаться валидным. 
При этом обращения к сервису авторизации не будет.
