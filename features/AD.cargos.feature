Feature: Cargos
    
    - Cadastra novos cargos
 
    Background:
        When I login with user "testador" and pass "testador" and must go "/admin"

    @javascript @cadastrarCargos
    Scenario: Cadastrar cargos

        When I follow "menu-cadastros"
        When I follow "add cargos"
        Then I devo esta em "/admin/user/valued/charge"

        Given When I fill in "name" with "Programador,Editor,Diretor,Secretário"

        Given I press "Finalizar" button

        Then I should see "Programador"
        Then I should see "Editor"
        Then I should see "Diretor"
        Then I should see "Secretário"