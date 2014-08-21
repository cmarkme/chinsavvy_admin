Feature: Suggest new revision for existing file name
    In order to prevent duplication of uploaded files, which reduces staff productivity
    As a Chinasavy staff
    I need to be asked whether an uploaded file is a new revision or a new file

    Scenario: Uploading a file whose name is already on disk for the same enquirer
        Given I am logged in as "Admin" or "Chinasavvy staff"
        And the current page is "/vault/filemanager"
        And I select the "Toys R US" folder
        And a file named "1401121349Barbie Doll.pdf" exists
        When I upload a file named "Barbie Doll.pdf"
        Then I should be shown the message "A file with this name already exists for this enquirer. Do you want to create a new version?"
