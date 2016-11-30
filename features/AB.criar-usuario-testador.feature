Feature: Criar usuário testador

    @javascript @criarTestador
    Scenario:

        Given I am on "/add/user/testador" visit
        Then I should see "OK"