clone repo

cd avaliacao

docker network create avaliacao

docker-compose up -d

docker exec -it avaliacao_api composer install

docker exec -it avaliacao_api ./db migrate

http://localhost:3008/add/user/testador

Login http://localhost:3008 
usuario: testador
senha: testador