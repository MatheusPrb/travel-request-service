#!/bin/bash
docker compose exec app php artisan test --coverage
