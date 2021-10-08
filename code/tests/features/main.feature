Feature:
  In order to test main features of the project

  Scenario: Transactions should appear in the list
    When I make "PUT" request to "/transaction" endpoint with body
        """
        {
            "title": "Test",
            "amount": 261.99
        }
        """
    Then the response code should be 200

    When I make "GET" request to "/transaction" endpoint
    Then the response code should be 200
    And the response should contain "Test"
    And the response should contain "1.99"

    When I make "PUT" request to "/transaction" endpoint with body
        """
        {
            "title": "Another test",
            "amount": -1.99
        }
        """
    Then the response code should be 200

    When I make "GET" request to "/transaction" endpoint
    Then the response code should be 200
    And the response should contain "Another test"
    And the response should contain "-1.99"

  Scenario: Transactions should properly affect the balance
    # make sure that balance is empty at the beginning
    When I make "GET" request to "/balance" endpoint
    Then the response code should be 200
    And the response JSON node "balance" should be equal to 0

    # add income transactions
    When I make "PUT" request to "/transaction" endpoint with body
        """
        {
            "title": "Income",
            "amount": 100
        }
        """
    Then the response code should be 200
    And the response JSON node "balance" should be equal to 100

    When I make "PUT" request to "/transaction" endpoint with body
        """
        {
            "title": "Income",
            "amount": 59.99
        }
        """
    Then the response code should be 200
    And the response JSON node "balance" should be equal to 159.99

    # add expense transactions
    When I make "PUT" request to "/transaction" endpoint with body
        """
        {
            "title": "Expense",
            "amount": -48.64
        }
        """
    Then the response code should be 200
    And the response JSON node "balance" should be equal to 111.35

    When I make "PUT" request to "/transaction" endpoint with body
        """
        {
            "title": "Expense",
            "amount": -0.71
        }
        """
    Then the response code should be 200
    And the response JSON node "balance" should be equal to 110.64

    Scenario: Test for credit and debit Transactions
    # Let's confirm that our balance is increased in credit transactions
    When I make "PUT" request to "/transaction/credit" endpoint with body
        """
        {
            "title": "Salary",
            "amount": 400
        }
        """
    Then the response code should be 200
    And the response JSON node "balance" should be equal to 400

    # Let's confirm that our balance is decreased in debit transactions
    When I make "PUT" request to "/transaction/debit" endpoint with body
        """
        {
            "title": "Bought food",
            "amount": 100
        }
        """
    Then the response code should be 200
    And the response JSON node "balance" should be equal to 300

    # Let's confirm the total balance of our transactions
    When I make "Get" request to "/total-balance" endpoint
    Then the response code should be 200
    And the response JSON node "balance" should be equal to 300
