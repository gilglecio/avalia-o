@resetar
Feature: Apaga e recria o banco de dados

    @javascript
    Scenario:

    	When I go to "/resetar" visit
    	Then I should see "OK"