@login
Feature: Login
    
    - Logar o usuário;
 
    Background:
        When I login with user "gilglecio" and pass "gilglecio" and must go "/admin"

    @javascript
    Scenario: Logar o usuário

        Then I devo esta em "/admin"