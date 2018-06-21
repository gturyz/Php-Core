<?php
namespace Splash\Tests\Core;

use Splash\Tests\Tools\TestCase;

use Splash\Core\SplashCore     as Splash;

use Splash\Components\Logger;
use Splash\Tests\Tools\AbstractBaseCase;

/**
 * @abstract    Core Test Suite - Raw Folders & Class Structure Verifications
 *
 * @author SplashSync <contact@splashsync.com>
 */
class C00StartupTest extends TestCase
{
    /**
     * @abstract    Display of Tested Sequences | Objects | Fields
     */
    public function testDisplayTestContext()
    {

                
        //====================================================================//
        //   SPLASH SCREEN
        //====================================================================//

        $Split  = "----------------------------------------------------------------";
        
        $Splash = " ______     ______   __         ______     ______     __  __    " . PHP_EOL;
        $Splash.= "/\  ___\   /\  == \ /\ \       /\  __ \   /\  ___\   /\ \_\ \   " . PHP_EOL;
        $Splash.= "\ \___  \  \ \  _-/ \ \ \____  \ \  __ \  \ \___  \  \ \  __ \  " . PHP_EOL;
        $Splash.= " \/\_____\  \ \_\    \ \_____\  \ \_\ \_\  \/\_____\  \ \_\ \_\ " . PHP_EOL;
        $Splash.= "  \/_____/   \/_/     \/_____/   \/_/\/_/   \/_____/   \/_/\/_/ " . PHP_EOL;
        $Splash.= "                                                                ";
        
        echo PHP_EOL;
        
        echo Logger::getConsoleLine(null, $Split, Logger::CMD_COLOR_MSG);
        
        echo Logger::getConsoleLine(null, $Splash, Logger::CMD_COLOR_WAR);
        
        echo Logger::getConsoleLine(null, $Split, Logger::CMD_COLOR_MSG);
        
        $this->displayTestedObjects();
        $this->displayTestedSequences();
        $this->displayFilteredFields();
        
        echo Logger::getConsoleLine(null, $Split, Logger::CMD_COLOR_MSG);
        echo PHP_EOL . ".";
        
        $this->assertTrue(true);
    }
    
    /**
     * @abstract    Display of Tested Objects List
     */
    private function displayTestedObjects()
    {
        //====================================================================//
        //   TESTED OBJECTS
        //====================================================================//
        $Objects    =   Splash::objects();
        if (!is_array($Objects)) {
            echo Logger::getConsoleLine(" !! Invalid Objects List !! ", " - Tested Objects ", Logger::CMD_COLOR_DEB);
            return;
        }
        foreach ($Objects as $Key => $ObjectType) {
            //====================================================================//
            //   Filter Tested Object Types  =>> Skip
            if (!AbstractBaseCase::isAllowedObjectType($ObjectType)) {
                unset($Objects[$Key]);
            }
        }
        echo Logger::getConsoleLine(implode(" | ", $Objects), "- Tested Objects: ", Logger::CMD_COLOR_DEB);
    }
    
    /**
     * @abstract    Display of Tested Sequences List
     */
    private function displayTestedSequences()
    {
        //====================================================================//
        //   TESTED SEQUENCES
        //====================================================================//
        
        //====================================================================//
        // Check if Local Tests Sequences are defined
        $Sequences  =   "None";
        if (!is_null(Splash::local()) && method_exists(Splash::local(), "TestSequences")) {
            $Sequences  =   Splash::local()->testSequences("List");
        }
        if (!is_array($Sequences) && ($Sequences !== "None")) {
            echo Logger::getConsoleLine("!!Invalid Tests Sequence List!!", " - Tested Objects ", Logger::CMD_COLOR_DEB);
            return;
        }
        if ($Sequences === "None") {
            return;
        }
        echo Logger::getConsoleLine(implode(" | ", $Sequences), "- Test Sequences: ", Logger::CMD_COLOR_DEB);
    }

    /**
     * @abstract    Display of Filter on Objets Fields
     */
    private function displayFilteredFields()
    {
        //====================================================================//
        //   FILTERED FIELDS
        //====================================================================//
        
        //====================================================================//
        //   Filter Tested Object Fields  =>> Skip
        if (defined("SPLASH_FIELDS") && is_scalar(SPLASH_FIELDS) && !empty(explode(",", SPLASH_FIELDS))) {
            echo Logger::getConsoleLine(SPLASH_FIELDS, "- Fields Filter: ", Logger::CMD_COLOR_DEB);
            echo Logger::getConsoleLine("!! TEST WILL FOCUS ON SPECIFIC FIELDS !!", null, Logger::CMD_COLOR_DEB);
        }
    }
}