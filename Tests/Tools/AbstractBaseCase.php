<?php

namespace Splash\Tests\Tools;

use ArrayObject;

use Splash\Tests\Tools\TestCase;
    
use Splash\Client\Splash;

/**
 * @abstract    Abstract Base Class for Splash Modules Tests
 */
abstract class AbstractBaseCase extends TestCase
{
    use \Splash\Tests\Tools\Traits\ObjectsValidatorTrait;
    use \Splash\Tests\Tools\Traits\ObjectsAssertionsTrait;
        
    protected function setUp()
    {
        parent::setUp();
        
        //====================================================================//
        // BOOT or REBOOT MODULE
        Splash::reboot();
    }

    /**
     * @abstract    Check if Object Type Is to be tested or Not
     * @param       string  $objectType         Object Type Name
     * @return      bool
     */
    public static function isAllowedObjectType($objectType)
    {
        //====================================================================//
        //   Filter Tested Object Types  =>> Skip
        if ( defined("SPLASH_TYPES") && is_scalar(SPLASH_TYPES) && !empty(explode(",", SPLASH_TYPES))) {
            if ( !in_array($objectType, explode(",", SPLASH_TYPES))) {
                return false;
            }
        }
        //====================================================================//
        //   If Object Type Is Disabled Type  =>> Skip
        if (Splash::object($objectType)->getIsDisabled()) {
            return false;
        }
        return true;
    }
    
    /**
     * @abstract    Check if Object Field ID Is to be tested or Not
     * @param       string  $identifier         Object Field Identifier
     * @return      bool
     */
    public static function isAllowedObjectField($identifier)
    {
        //====================================================================//
        //   Filter Tested Object Fields  =>> Skip
        if ( defined("SPLASH_FIELDS") && is_scalar(SPLASH_FIELDS) && !empty(explode(",", SPLASH_FIELDS))) {
            if ( !in_array($identifier, explode(",", SPLASH_FIELDS))) {
                return false;
            }
        }
        return true;
    }    
    

            
    /**
     * @abstract        GENERATE FAKE SPLASH SERVER HOST URL
     *
     * @see             SERVER_NAME parameter that must be defined in PhpUnit Configuration File
     *
     * @return string   Local Server Soap Url
     * 
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function getLocalServerSoapUrl()
    {
        //====================================================================//
        // Get ServerInfos from WebService Componant
        $infos = Splash::ws()->getServerInfos();
        
        //====================================================================//
        //   SAFETY CHECK
        //====================================================================//

        //====================================================================//
        //   Verify ServerPath is  Not Empty
        $this->assertNotEmpty($infos["ServerPath"], "Splash Core Module was unable to detect your Soap root Server Path. Verify you do not overload 'ServerPath' parameter in your local confirguration.");
        //====================================================================//
        //   Verify ServerPath is  Not Empty
        $this->assertTrue(isset($_SERVER['SERVER_NAME']), "'SERVER_NAME' not defined in your PhpUnit XML configuration. Please define '<server name=\"SERVER_NAME\" value=\"http://localhost/Path/to/Your/Server\"/>'");
        $this->assertNotEmpty($_SERVER['SERVER_NAME'], "'SERVER_NAME' not defined in your PhpUnit XML configuration. Please define '<server name=\"SERVER_NAME\" value=\"http://localhost/Path/to/Your/Server\"/>'");
        
        //====================================================================//
        // GENERATE FAKE SPLASH SERVER HOST URL
        $soapUrl    =    $_SERVER['SERVER_NAME'] . $infos["ServerPath"];
        
        return  $soapUrl;
    }
    
    /**
     * @abstract      Verify Response Is Valid
     *
     * @param   string      $response       WebService Raw Response Block
     * @param   string      $config         WebService Request Configuration
     *
     * @return  ArrayObject
     */
    public function checkResponse($response, $config = null)
    {
        
        //====================================================================//
        // RESPONSE BLOCK IS NOT EMPTY
        $this->assertNotEmpty($response, "Response Block is Empty");
        //====================================================================//
        // DECODE BLOCK
        $data       =   Splash::ws()->unPack($response);
        //====================================================================//
        // CHECK RESPONSE DATA
        $this->assertNotEmpty($data, "Response Data is Empty or Malformed");
        $this->assertInstanceOf("ArrayObject", $data, "Response Data is Not an ArrayObject");
        $this->assertArrayHasKey("result", $data, "Request Result is Missing");
        
        //====================================================================//
        // CHECK RESPONSE LOG
        if (array_key_exists("log", $data)) {
            $this->checkResponseLog($data->log, $config);
        }
        
        //====================================================================//
        // CHECK RESPONSE SERVER INFOS
        if (array_key_exists("server", $data)) {
            $this->checkResponseServer($data->server);
        }
        
        //====================================================================//
        // CHECK RESPONSE TASKS RESULTS
        if (array_key_exists("tasks", $data)) {
            $this->checkResponseTasks($data->tasks, $config);
        }
        
        //====================================================================//
        // CHECK RESPONSE RESULT
        if (empty($data->result)) {
            print_r($data);
        }
        $this->assertNotEmpty($data->result, "Request Result is not True, Why??");
        return $data;
    }
    
    /**
     * @abstract      Verify Response Log Is Valid
     *
     * @param   ArrayObject     $logs           WebService Log Array
     * @param   string          $config         WebService Request Configuration
     */
    public function checkResponseLog($logs, $config = null)
    {
        //====================================================================//
        // SERVER LOG ARRAY FORMAT
        $this->assertInstanceOf("ArrayObject", $logs, "Response Log is Not an ArrayObject");
        
        //====================================================================//
        // SERVER LOGS MESSAGES FORMAT
        $this->checkResponseLogArray($logs, 'err', "Error");
        $this->checkResponseLogArray($logs, 'war', "Warning");
        $this->checkResponseLogArray($logs, 'msg', "Message");
        $this->checkResponseLogArray($logs, 'deb', "Debug Trace");
        
        //====================================================================//
        // UNEXPECTED SERVER LOG ITEMS
        foreach (array_keys($logs->getArrayCopy()) as $key) {
            $this->assertTrue(
                    in_array($key, array("err", "msg", "war", "deb")), 
                    "Received Unexpected Log Messages. ( Data->log->" . $key . ")"
                    );
        }
        
        //====================================================================//
        // SERVER LOG With Silent Option Activated
        if (is_a($config, "ArrayObject") && array_key_exists("silent", $config)) {
            $this->assertEmpty($logs->war, "Requested Silent operation but Received Warnings, Why??");
            $this->assertEmpty($logs->msg, "Requested Silent operation but Received Messages, Why??");
            $this->assertEmpty($logs->deb, "Requested Silent operation but Received Debug Traces, Why??");
        }

        //====================================================================//
        // SERVER LOG Without Debug Option Activated
        if (is_a($config, "ArrayObject") && !array_key_exists("debug", $config)) {
            $this->assertEmpty($logs->deb, "Requested Non Debug operation but Received Debug Traces, Why??");
        }
            
        //====================================================================//
        //   Extract Logs From Response
        Splash::log()->merge($logs);
    }
    /**
     * @abstract    Verify Response Log Is Valid
     * @param       ArrayObject     $logs        WebService Log Array
     * @param       string          $type       Log Key
     * @param       string          $name       Log Type Name
     * @return      void
     */
    public function checkResponseLogArray($logs, $type, $name)
    {
        if (!array_key_exists($type, $logs) || empty($logs->$type)) {
            return;
        }
        
        //====================================================================//
        // SERVER LOG FORMAT
        $this->assertInstanceOf("ArrayObject", $logs->$type, "Logger " . $name . " List is Not an ArrayObject");
        foreach ($logs->$type as $message) {
            $this->assertTrue((is_scalar($message) || is_null($message)), $name . " is Not a string. (" . print_r($message, true) . ")");
        }
    }
    
    /**
     * @abstract    Verify Response Server Infos Are Valid
     * @param       ArrayObject     $server         WebService Server Infos Array
     * @return      void
     */
    public function checkResponseServer($server)
    {
        //====================================================================//
        // SERVER Informations  => Available
        $this->assertArrayHasKey("ServerHost", $server, "Server Info (ServerHost) is Missing");
        $this->assertArrayHasKey("ServerPath", $server, "Server Info (ServerPath) is Missing");
        $this->assertArrayHasKey("ServerType", $server, "Server Info (ServerType) is Missing");
        $this->assertArrayHasKey("ServerVersion", $server, "Server Info (ServerVersion) is Missing");
        $this->assertArrayHasKey("ServerAddress", $server, "Server Info (ServerAddress) is Missing");
        
        //====================================================================//
        // SERVER Informations  => Not Empty
        $this->assertNotEmpty($server["ServerHost"], "Server Info (ServerHost) is Empty");
        $this->assertNotEmpty($server["ServerPath"], "Server Info (ServerPath) is Empty");
        $this->assertNotEmpty($server["ServerType"], "Server Info (ServerType) is Empty");
        $this->assertNotEmpty($server["ServerVersion"], "Server Info (ServerVersion) is Empty");
    }
    
    /**
     * @abstract      Verify Response Tasks Results are Valid
     *
     * @param   ArrayObject     $tasks          WebService Server Tasks Results Array
     * @param   string          $config         WebService Request Configuration
     * 
     * @return      void
     */
    public function checkResponseTasks($tasks, $config = null)
    {
        //====================================================================//
        // TASKS RESULTS ARRAY FORMAT
        $this->assertInstanceOf("ArrayObject", $tasks, "Response Tasks Result is Not an ArrayObject");
        
        foreach ($tasks as $task) {
            //====================================================================//
            // TASKS Results  => Available
            $this->assertArrayHasKey("id", $task, "Task Results => Task Id is Missing");
            $this->assertArrayHasKey("name", $task, "Task Results => Name is Missing");
            $this->assertArrayHasKey("desc", $task, "Task Results => Description is Missing");
            $this->assertArrayHasKey("result", $task, "Task Results => Task Result is Missing");
            $this->assertArrayHasKey("data", $task, "Task Results => Data is Missing");
            
            //====================================================================//
            // TASKS Results  => Not Empty
            $this->assertNotEmpty($task["id"], "Task Results => Task Id is Empty");
            $this->assertNotEmpty($task["name"], "Task Results => Name is Empty");
            $this->assertNotEmpty($task["desc"], "Task Results => Description is Empty");
//            $this->assertNotEmpty( $Task["result"]             , "Task Results => Task Result is OK, Did this Task Really Failed?");
//            $this->assertNotEmpty( $Task["data"]               , "Task Results => Data is Empty");
            
            //====================================================================//
            // TASKS Delay Data
            if (is_a($config, "ArrayObject") && !array_key_exists("trace", $config)) {
                $this->assertArrayHasKey("delayms", $task, "Task Results => Trace requested but DelayMs is Missing");
                $this->assertArrayHasKey("delaystr", $task, "Task Results => Trace requested but DelayStr is Missing");
                $this->assertNotEmpty($task["delayms"], "Task Results => Trace requested but DelayMs is Empty");
                $this->assertNotEmpty($task["delaystr"], "Task Results => Trace requested but DelayStr is Empty");
            }
        }
    }
    
    /**
     *      @abstract   Perform generic Server Side Action
     *
     *      @return     mixed
     */
    protected function genericAction($service, $action, $description, array $parameters = array(true))
    {
        //====================================================================//
        //   Prepare Request Data
        Splash::ws()->addTask($action, $parameters, $description);
        //====================================================================//
        //   Execute Action From Splash Server to Module
        $response   =   Splash::ws()->simulate($service);
        //====================================================================//
        //   Check Response
        $data       =   $this->checkResponse($response);
        //====================================================================//
        //   Extract Task Result
        if (is_a($data->tasks, "ArrayObject")) {
            $data->tasks = $data->tasks->getArrayCopy();
        }
        $task = array_shift($data->tasks);

        //====================================================================//
        //   Turn On Output Buffering Again
        ob_start();
        
        return $task["data"];
    }
    
    /**
     * @abstract    Perform generic Server Side Action
     *
     * @return  mixed
     */
    protected function genericErrorAction($service, $action, $description, array $parameters = array(true))
    {
        //====================================================================//
        //   Prepare Request Data
        Splash::ws()->addTask($action, $parameters, $description);
        //====================================================================//
        //   Execute Action From Splash Server to Module
        $response   =   Splash::ws()->simulate($service);
        //====================================================================//
        // RESPONSE BLOCK IS NOT EMPTY
        $this->assertNotEmpty($response, "Response Block is Empty");
        //====================================================================//
        // DECODE BLOCK
        $data       =   Splash::ws()->unPack($response);
        //====================================================================//
        // CHECK RESPONSE DATA
        $this->assertNotEmpty($data, "Response Data is Empty or Malformed");
        $this->assertInstanceOf("ArrayObject", $data, "Response Data is Not an ArrayObject");
        $this->assertArrayHasKey("result", $data, "Request Result is Missing");
        $this->assertEmpty($data->result, "Expect Errors but Request Result is True, Why??");
        
        //====================================================================//
        //   Extract Task Result
        if (is_a($data->tasks, "ArrayObject")) {
            $data->tasks = $data->tasks->getArrayCopy();
        }
        $task = array_shift($data->tasks);

        //====================================================================//
        //   Turn On Output Buffering Again
        ob_start();
        
        return $task["data"];
    }
}
