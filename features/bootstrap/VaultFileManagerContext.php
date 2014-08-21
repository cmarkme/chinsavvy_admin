<?php
use Behat\Mink\Mink,
    Behat\MinkExtension\Context\MinkDictionary,
    Behat\Mink\Session,
    Behat\Mink\Driver\GoutteDriver,
    Behat\Mink\Driver\ZombieDriver,
    Behat\Mink\Driver\SahiDriver,
    Behat\Mink\Driver\Selenium2Driver,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException,
    Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

class VaultFileManagerContext extends BehatContext {

    public function initialise($start_url, $drivers=array('goutte'), $username = 'test_admin', $password = 'password') {
        $this->mink = new Mink();
        foreach ($drivers as $drivername) {
            switch($drivername) {
                case 'goutte':
                    $driver = new GoutteDriver();
                    break;
                case 'zombie':
                    $driver = new ZombieDriver('127.0.0.1', 3000);
                    break;
                case 'sahi':
                    $driver = new SahiDriver('chrome');
                    break;
                case 'selenium2':
                    $driver = new Selenium2Driver();
                    break;
                default:
                    $driver = new GoutteDriver();
            }

            $this->mink->registerSession($drivername, new Session($driver));
            $this->mink->getSession($drivername)->visit($start_url);
            $page = $this->mink->getSession($drivername)->getPage();
            $page->findByID('username')->setValue($username);
            $page->findByID('password')->setValue($password);
            $page->findByID('submit')->press();
        }
    }

    /**
     * @Given /^I am logged in as "([^"]*)" or "([^"]*)"$/
     */
    public function iAmLoggedInAsOr($role1, $role2) {
        self::initialise('http://admin.chinasavvy/vault/filemanager', array('goutte'));
        $goutte_page = $this->mink->getSession('goutte')->getPage();
        // $selenium_page = $this->mink->getSession('selenium2')->getPage();
        return preg_match("/[$role1|$role2]/i", $goutte_page->findByID('roles')->getText());
        // && preg_match("/[$role1|$role2]/i", $selenium_page->findByID('roles')->getText());
    }

    /**
     * @When /^I upload a file "([^"]*)"$/
     */
    public function iUploadAFile($filename)
    {
        self::initialise('http://admin.chinasavvy/vault/filemanager', array('selenium2'));
        $page = $this->mink->getSession('selenium2')->getPage();
        $style = $page->find('css', '#uploader')->getAttribute('style');
        $this->assertElementAttributeContains($page->find('css', '#uploader'), 'style', '/display:\ ?none/i');
        $page->find('css', '#top a')->click();
        $page->find('css', "#upload")->click();
        $this->assertElementAttributeNotContains($page->find('css', '#uploader'), 'style', '/display:\ ?none/i');
        $page->find('css', '#uploader_browse')->click();
    }

    /**
     * @Given /^the date\/time is "([^"]*)"$/
     */
    public function theDateTimeIs($time) {
        return $time;
    }

    /**
     * @Then /^The uploaded file should be renamed "([^"]*)"$/
     */
    public function theUploadedFileShouldBeRenamed($new_filename)
    {
        return $new_filename == "1010121430randomfile.pdf";
    }

    /**
     * @Given /^a file named "([^"]*)" exists for enquirer "([^"]*)"$/
     */
    public function aFileNamedExistsForEnquirer($filename, $enquirer)
    {
        return $filename == 'Barbie Doll' && $enquirer == 'Toys R US';
    }

    /**
     * @When /^I upload a file named "([^"]*)" for enquirer "([^"]*)"$/
     */
    public function iUploadAFileNamedForEnquirer($filename, $enquirer)
    {
        return $filename == 'Barbie Doll' && $enquirer == 'Toys R US';
    }

    /**
     * @Then /^I should be shown the message "([^"]*)"$/
     */
    public function iShouldBeShownTheMessage($message)
    {
        return $message;
    }

    /**
     * @Given /^the current page is "([^"]*)"$/
     */
    public function theCurrentPageIs($page) {
        self::initialise('http://admin.chinasavvy' . $page, array('goutte'));
        return "http://admin.chinasavvy" . $page == $this->mink->getSession('goutte')->getCurrentUrl();
    }

    /**
     * @Given /^I select the "([^"]*)" folder$/
     */
    public function iSelectTheFolder($folder)
    {
        throw new PendingException();
    }

    /**
     * @Given /^a file named "([^"]*)" exists$/
     */
    public function aFileNamedExists($arg1)
    {
        throw new PendingException();
    }

    /**
     * @When /^I upload a file named "([^"]*)"$/
     */
    public function iUploadAFileNamed($arg1)
    {
        throw new PendingException();
    }

    public function assertElementAttributeContains($element, $attribute, $pattern) {
        assertRegExp($pattern, $element->getAttribute($attribute));
    }

    public function assertElementAttributeNotContains($element, $attribute, $pattern) {
        assertNotRegExp($pattern, $element->getAttribute($attribute));
    }

}
