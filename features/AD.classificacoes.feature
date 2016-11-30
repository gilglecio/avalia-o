Feature: Classificacoes
    
    - Cadastra novas classificações
 
    Background:
        When I login with user "testador" and pass "testador" and must go "/admin"

    @javascript @cadastrarClassificacoes
    Scenario: Cadastrar classificações

        When I follow "menu-cadastros"
        When I follow "add classificações"
        Then I devo esta em "/admin/user/valued/rating"

        Given When I fill in "name" with "Junior,Pleno,Sênior,DBA"

        Given I press "Finalizar" button

        Then I should see "Junior"
        Then I should see "Pleno"
        Then I should see "Sênior"
        Then I should see "DBA"