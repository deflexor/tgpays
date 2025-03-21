
## Payment notification service


### Run
```
docker-compose build
docker-compose up -d
```

### Test

```
docker-compose exec app php bin/phpunit --testsuite=Integration
```


### Test by hand
```

curl -X POST http://localhost:8080/api/payments/default-gateway \
  -H "Content-Type: application/json" \
  -d '{"token":"test-123","status":"confirmed","order_id":12345,"amount":2000,"currency":"RUB","user_id":"876123654","language_code":"ru"}'

docker-compose exec app php bin/console messenger:consume async -vv

```