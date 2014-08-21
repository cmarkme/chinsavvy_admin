Feature: Document vault file naming
    In order easily identify the upload date of each file
    As a Chinasavvy staff
    I need to have a date/tim prefix prepended to each file in the vault

    Scenario: Uploading a file on a given date and time
        Given I am logged in as "Admin" or "Chinasavvy staff"
        And the current page is "/vault/filemanager"
        And the date/time is "October 24, 2012, 10:54 am"
        When I upload a file "randomfile.pdf"
        Then The uploaded file should be renamed "1010121430randomfile.pdf"

