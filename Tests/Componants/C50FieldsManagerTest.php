<?php
namespace Splash\Tests\Componants;

use PHPUnit\Framework\TestCase;

use Splash\Components\FieldsManager;

use Splash\Core\SplashCore     as Splash;

/**
 * @abstract    Componants Test Suite - Fields Manager Verifications
 *
 * @author SplashSync <contact@splashsync.com>
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class C50FieldsManagerTest extends TestCase
{
    use \Splash\Models\Objects\ObjectsTrait;
    use \Splash\Models\Objects\ListsTrait;
    
    protected function setUp()
    {
        parent::setUp();
        //====================================================================//
        // BOOT MODULE
        Splash::core();
    }
    
    //==============================================================================
    //      FIELDS LIST FUNCTIONS
    //==============================================================================
    
    // TODO
    
    //==============================================================================
    //      LISTS FIELDS MANAGEMENT
    //==============================================================================

    /**
     * @dataProvider providerIsListFieldFunction
     */
    public function testIsListFieldFunction($input, $result)
    {
        $this->assertEquals($result, FieldsManager::isListField($input));
    }

    public function providerIsListFieldFunction()
    {
        return array(
            array(null,                 false),
            array("",                   false),
            array("Whatever",           false),
            array("object::id",         false),
            array("object@list",        ["fieldname" => "object",       "listname" => "list"]),
            array("list@object",        ["fieldname" => "list",         "listname" => "object"]),
            array("object::id@list",    ["fieldname" => "object::id",   "listname" => "list"]),
            array("object-id@list",     ["fieldname" => "object-id",   "listname" => "list"]),
        );
    }
    
    /**
     * @dataProvider providerFieldNameFunction
     */
    public function testFieldNameFunction($input, $result)
    {
        $this->assertEquals($result, FieldsManager::fieldName($input));
    }

    public function providerFieldNameFunction()
    {
        return array(
            array(null,                 false),
            array("",                   false),
            array("Whatever",           false),
            array("object::id",         false),
            array("object@list",        "object"),
            array("list@object",        "list"),
            array("object::id@list",    "object::id"),
            array("object@list::id",    "object"),
            array("object-id@list",     "object-id"),
        );
    }

    /**
     * @dataProvider providerListNameFunction
     */
    public function testListNameFunction($input, $result)
    {
        $this->assertEquals($result, FieldsManager::listName($input));
    }

    public function providerListNameFunction()
    {
        return array(
            array(null,                 false),
            array("",                   false),
            array("Whatever",           false),
            array("object::id",         false),
            array("object@list",        "list"),
            array("list@object",        "object"),
            array("object::id@list",    "list"),
            array("object@list::id",    "list::id"),
            array("object-id@list",     "list"),
        );
    }
    
    /**
     * @dataProvider providerBaseTypeFunction
     */
    public function testBaseTypeFunction($input, $result)
    {
        $this->assertEquals($result, FieldsManager::baseType($input));
    }

    public function providerBaseTypeFunction()
    {
        //====================================================================//
        // BOOT MODULE
        Splash::core();
        
        return array(
            array(null,                 false),
            array("",                   ""),
            array("Whatever",           "Whatever"),
            array("object::id",         "id"),
            array("object@list",        "object"),
            array("list@object",        "list"),
            array("id::object@list",    "object"),
            array("object@list::id",    "object"),
            array("object-id@list",     "object-id"),
            array(
                self::objects()->Encode("Object", SPL_T_ID),
                "Object"
            ),
            array(
                self::lists()->Encode("Listname", "FieldName"),
                "FieldName"
            ),
            array(
                self::lists()->Encode(
                    "ListName",
                    self::objects()->Encode("Object", SPL_T_ID)
                ),
                "Object"
            ),
            array(
                self::lists()->Encode(
                    self::objects()->Encode("Object", SPL_T_ID),
                    "FieldName"
                ),
                "FieldName"
            ),
            array(
                self::lists()->Encode(
                    self::objects()->Encode("Error", SPL_T_ID),
                    self::objects()->Encode("Object", SPL_T_ID)
                ),
                "Object"
            ),
        );
    }
    
    //==============================================================================
    //      OBJECT ID FIELDS MANAGEMENT
    //==============================================================================
    
    /**
     * @dataProvider providerIsIdFieldFunction
     */
    public function testIsIdFieldFunction($input, $result)
    {
        $this->assertEquals($result, FieldsManager::isIdField($input));
    }

    public function providerIsIdFieldFunction()
    {
        return array(
            array(null,                 false),
            array("",                   false),
            array("Whatever",           false),
            array("id::type",           ["ObjectType" => "type",        "ObjectId" => "id"]),
            array("id::type@list",      ["ObjectType" => "type@list",   "ObjectId" => "id"]),
            array("id@list::type",      ["ObjectType" => "type",        "ObjectId" => "id@list"]),
            array("id-id::type-list",   ["ObjectType" => "type-list",   "ObjectId" => "id-id"]),
        );
    }
    
    /**
     * @dataProvider providerObjectIdFunction
     */
    public function testObjectIdFunction($input, $result)
    {
        $this->assertEquals($result, FieldsManager::objectId($input));
    }

    public function providerObjectIdFunction()
    {
        return array(
            array(null,                 false),
            array("",                   false),
            array("Whatever",           false),
            array("id::object",         "id"),
            array("object::id",         "object"),
            array("object@list",        false),
            array("list@object",        false),
            array("id::object@list",    "id"),
            array("object@list::id",    "object@list"),
        );
    }

    /**
     * @dataProvider providerObjectTypeFunction
     */
    public function testObjectTypeFunction($input, $result)
    {
        $this->assertEquals($result, FieldsManager::objectType($input));
    }

    public function providerObjectTypeFunction()
    {
        return array(
            array(null,                 false),
            array("",                   false),
            array("Whatever",           false),
            array("id::object",         "object"),
            array("object::id",         "id"),
            array("object@list",        false),
            array("list@object",        false),
            array("id::object@list",    "object@list"),
            array("object@list::id",    "id"),
        );
    }
    
    //==============================================================================
    //      OBJECTS DATA BLOCKS FUNCTIONS
    //==============================================================================
            
    // TODO
}
