# Sistema de avaliação

- Clonar o repositório.
- Entrar no repositório clonado `cd avaliacao`
- Criar uma rede `docker network create avaliacao`
- Levantar os containers `docker-compose up -d`
- Instalar as dependências `docker exec -it avaliacao_api composer install`
- Rodar as migrações `docker exec -it avaliacao_api ./db migrate`
- Criar usuário de teste, acesse: `http://localhost:3008/add/user/testador`
- Login, acesse `http://localhost:3008` use o login `testador` e a senha `testador` e faça login.
- Enjoy!