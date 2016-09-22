@cadastrarUsuarios
Feature: Login
    
    - Cadastra usuários diversos
 
    Background:
        When I login with user "testador" and pass "testador" and must go "/admin"

    @javascript
    Scenario: Cadastrar usuários

        When I follow "menu-cadastros"
        Then I devo esta em "/admin/user"

        When I follow "criar"
        Then I devo esta em "/admin/user/new"

        When I select "Avaliador" from "profile_type"
        Given When I fill in "birth" with "01/01/2000"
        Given When I fill in "graduated_at" with "2005"
        Given When I fill in "salary" with "20000"
        Given When I fill in "name" with "Avaliador 001"
        Given When I fill in "email" with "avaliador001@avaliacao.com"
        Given When I fill in "entry_at" with "01/01/2007"

        Given I press "Finalizar e Editar" button

        Then I should see "Editando Avaliador 001"