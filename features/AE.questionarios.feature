@questionarios
Feature: Quetionários
    
    - Cria questionários
 
    Background:
        When I login with user "testador" and pass "testador" and must go "/admin"

    @javascript @cadastrarQuestionarios
    Scenario Outline:

        When I follow "menu-cadastros-questionarios"
        Then I devo esta em "/admin/questionnaire"

        When I follow "CRIAR"
        Then I devo esta em "/admin/questionnaire/new"

        Given When I fill in "name_new" with "<name>"
        Given When I fill in "name_new_private" with "<name_private>"

        Given I press "Finalizar" button

        Then I should see "Adicionar Perguntas"

        # VISUALIZAR
        When I follow "Questionário"
        Then I should see "Lista"
        When I follow "visualizar-<key>"
        Then I should see "<name>"
        Then I should not see "<name_private>"

        # EDITAR
        When I follow "Questionário"
        Then I should see "Lista"
        When I follow "editar-<key>"
        Then I should see "Questionário Vazio"
        Then I should see "<name_private>"

        Then the "name_private" field should contain "<name_private>"
        Then the "name" field should contain "<name>"

        When I follow "Questionário"
        Then I should see "Lista"
        When I follow "duplicar-<key>"
        Then I should see "Copiar um questionário existente"

        Given When I fill in "name_copy" with "<name> (Cópia)"
        Given When I fill in "name_copy_private" with "<name_private> (Cópia)"

        Then I should see "<name>"
        Given I press "Finalizar" button
        Then I should see "Adicionar Perguntas"

        When I follow "Questionário"
        Then I should see "Lista"
        When I follow "apagar-0"
        When I will wait "1"
        Then I should see "Você tem certeza?"

        Given I press "Confirmar" button
        Then I should not see "<name_private> (Cópia)"

        Examples:
            |name                  |name_private    |key|
            |Primeiro Questionario |Questionario 001|0  |
            |Segundo Questionario  |Questionario 002|1  |
            |Tericeiro Questionario|Questionario 003|2  |
            |Quarto Questionario   |Questionario 004|3  |
            |Quinto Questionario   |Questionario 005|4  |
