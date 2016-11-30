Feature: Usuários
    
    - Cadastra usuários diversos
 
    Background:
        When I login with user "testador" and pass "testador" and must go "/admin"

    @javascript @cadastrarUsuarios
    Scenario Outline: Cadastrar usuários

        When I follow "menu-cadastros"
        Then I devo esta em "/admin/user"

        When I follow "criar"
        Then I devo esta em "/admin/user/new"

        Given When I fill in "profile_type" with "<type>"

        Given When I fill in "birth" with "01/01/2000"
        Given When I fill in "graduated_at" with "2005"
        Given When I fill in "salary" with "20000"
        Given When I fill in "name" with "<name>"
        Given When I fill in "email" with "<email>@avaliacao.com"
        Given When I fill in "entry_at" with "01/01/2007"

        Given I press "Finalizar e Editar" button

        Then I should see "Editando <name> (<tipo>)"

        Examples:
            |type     |tipo     |name         |email       |
            |appraiser|Avaliador|Avaliador 001|avaliador001|
            |valued   |Avaliado |Avaliado 001 |avaliado001 |
            |appraiser|Avaliador|Avaliador 002|avaliador002|
            |valued   |Avaliado |Avaliado 002 |avaliado002 |
