@criarTestador
Feature: Criar usuário testador

    @javascript
    Scenario:

        Given I am on "/add/user/testador" visit
        Then I should see "OK"