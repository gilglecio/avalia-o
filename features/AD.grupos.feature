Feature: Grupos
    
    - Cadastra grupos de usuários
 
    Background:
        When I login with user "testador" and pass "testador" and must go "/admin"

    @javascript @cadastrarGrupos
    Scenario Outline: Cadastrar usuários

        When I follow "menu-cadastros-grupos"
        Then I devo esta em "/admin/group"
        Given When I fill in "name" with "<name>"
        Given I press "Finalizar" button
        Then I should see "<name>"

        Examples:
            |name                |
            |Grupo de usuario 001|
            |Grupo de usuario 002|
            |Grupo de usuario 003|
            |Grupo de usuario 004|
            |Grupo de usuario 005|
            |Grupo de usuario 006|
