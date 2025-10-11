To run a task project, follow these steps: go to:
"docker" directory and run the following command from there:
docker compose --env-file ../.env up -d --build

To stop the project, use:
docker-compose down -v

Access the application at http://localhost:7070

To test through PHPStorm, goto http folder,
open "POST UserData.http" and run
