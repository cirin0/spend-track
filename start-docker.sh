#!/bin/bash

cp .env.example .env

docker compose down -v

docker compose build 

docker compose up -d

sleep 5

docker compose ps

echo "http://localhost:8000"

