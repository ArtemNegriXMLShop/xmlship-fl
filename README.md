
# Project start

## Docker launch

If you're x86_64 user, please start with:
```bash
cp docker-compose.x86_64-sample.yml docker-compose.yml
docker-compose up
```

If you're Mac M1+ or arm64 user, please start with:
```bash
cp docker-compose.arm64-sample.yml docker-compose.yml
docker-compose up
```

```bash
./d.sh php
php artisan key:generate
```

http://localhost:888/api/app/settings/users

