Feature: Login
 
    Background:
        When I login with user "testador" and pass "testador" and must go "/admin"

    @javascript @login
    Scenario: Verifica se está em /admin

        Then I devo esta em "/admin"