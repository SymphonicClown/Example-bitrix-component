Компонент записывает в сессию, кеширует определение города по домену.
Также отдает определение города для заголовков.

Пример вызова компонента:

```php
<?
$APPLICATION->IncludeComponent(
    'custom:geo.targeting',
    '',
    [
    
    ],
    false
);
?>
```