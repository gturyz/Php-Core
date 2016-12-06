<?php

use Splash\Tests\Tools\ObjectsCase;
use Splash\Client\Splash;

/**
 * @abstract    Objects Test Suite - Verify Read/Write of any R/W fields is Ok. 
 *
 * @author SplashSync <contact@splashsync.com>
 */
class O07SetTest extends ObjectsCase {
    
    /**
     * @dataProvider ObjectFieldsProvider
     */
    public function testSingleFieldFromModule($ObjectType, $Field)
    {
        //====================================================================//
        //   Generate Dummy Object Data (Required Fields Only)   
        $NewData = $this->PrepareForTesting($ObjectType,$Field);
        if ( $NewData == False ) {
            return True;
        }
        
        //====================================================================//
        //   OBJECT CREATE TEST  
        //====================================================================//
        
        //====================================================================//
        // Lock New Objects To Avoid Action Commit 
        Splash::Object($ObjectType)->Lock();
        //====================================================================//
        // Clean Objects Commited Array 
        Splash::$Commited = Array();
        //====================================================================//
        //   Create a New Object on Module  
        $ObjectId = Splash::Object($ObjectType)->Set(Null, $NewData);

        //====================================================================//
        //   Verify Response
        $this->VerifyResponse($ObjectType, $ObjectId, SPL_A_CREATE, $NewData);        
        
        //====================================================================//
        // UnLock New Objects To Avoid Action Commit 
        Splash::Object($ObjectType)->Unlock();

        //====================================================================//
        //   OBJECT UPDATE TEST  
        //====================================================================//
        
        //====================================================================//
        //   Update Focused Field Data
        $UpdateData = $this->PrepareForTesting($ObjectType,$Field);
        if ( $UpdateData == False ) {
            return True;
        }
        //====================================================================//
        // Lock New Objects To Avoid Action Commit 
        Splash::Object($ObjectType)->Lock($ObjectId);
        //====================================================================//
        // Clean Objects Commited Array 
        Splash::$Commited = Array();
        //====================================================================//
        //   Update Object on Module  
        Splash::Object($ObjectType)->Set($ObjectId, $UpdateData);
        
        //====================================================================//
        //   Verify Response
        $this->VerifyResponse($ObjectType, $ObjectId, SPL_A_UPDATE, $UpdateData);        
        
        //====================================================================//
        //   OBJECT DELETE  
        //====================================================================//
        
        //====================================================================//
        // Lock New Objects To Avoid Action Commit 
        Splash::Object($ObjectType)->Lock($ObjectId);
        
        //====================================================================//
        //   Delete Object on Module  
        $Data = Splash::Object($ObjectType)->Delete($ObjectId);
        
        //====================================================================//
        //   Verify Response
        $this->VerifyDeleteResponse($ObjectType, $ObjectId, $Data);
        
        
    }
    
    /**
     * @dataProvider ObjectFieldsProvider
     */
    public function testSingleFieldFromService($ObjectType, $Field)
    {
        //====================================================================//
        //   Generate Dummy Object Data (Required Fields Only)   
        $NewData = $this->PrepareForTesting($ObjectType,$Field);
        if ( $NewData == False ) {
            return True;
        }
        
        //====================================================================//
        //   OBJECT CREATE TEST  
        //====================================================================//
        
        //====================================================================//
        // Clean Objects Commited Array 
        Splash::$Commited = Array();
        //====================================================================//
        //   Create a New Object via Service  
        $ObjectId = $this->GenericAction(SPL_S_OBJECTS, SPL_F_SET, __METHOD__, [ "id" => Null, "type" => $ObjectType, "fields" => $NewData]);

        //====================================================================//
        //   Verify Response
        $this->VerifyResponse($ObjectType, $ObjectId, SPL_A_CREATE, $NewData);        
        
        //====================================================================//
        // UnLock New Objects To Avoid Action Commit 
        Splash::Object($ObjectType)->Unlock();

        //====================================================================//
        // BOOT or REBOOT MODULE
        $this->setUp();
        
        //====================================================================//
        //   OBJECT UPDATE TEST  
        //====================================================================//
        
        //====================================================================//
        //   Update Focused Field Data
        $UpdateData = $this->PrepareForTesting($ObjectType,$Field);
        if ( $UpdateData == False ) {
            return True;
        }
        
        //====================================================================//
        // Clean Objects Commited Array 
        Splash::$Commited = Array();
        //====================================================================//
        //   Create a New Object via Service  
        $this->GenericAction(SPL_S_OBJECTS, SPL_F_SET, __METHOD__, [ "id" => $ObjectId, "type" => $ObjectType, "fields" => $UpdateData]);
        
        //====================================================================//
        //   Verify Response
        $this->VerifyResponse($ObjectType, $ObjectId, SPL_A_UPDATE, $UpdateData);        
        
        //====================================================================//
        //   OBJECT DELETE  
        //====================================================================//
        
        //====================================================================//
        // Lock New Objects To Avoid Action Commit 
        Splash::Object($ObjectType)->Lock($ObjectId);
        
        //====================================================================//
        //   Delete Object on Module  
        $Data = Splash::Object($ObjectType)->Delete($ObjectId);
        
        //====================================================================//
        //   Verify Response
        $this->VerifyDeleteResponse($ObjectType, $ObjectId, $Data);
        
        
    }
    
    
    public function VerifyTestIsAllowed($ObjectType,$Field = Null)
    {
        $Definition = Splash::Object($ObjectType)->Description();

        $this->assertNotEmpty($Definition);
        //====================================================================//
        //   Verify Create is Allowed
        if ( !$Definition["allow_push_created"] ) {
            return False;
        }    
        //====================================================================//
        //   Verify Update is Allowed
        if ( !$Definition["allow_push_updated"] ) {
            return False;
        }    
        //====================================================================//
        //   Verify Delete is Allowed
        if ( !$Definition["allow_push_deleted"] ) {
            return False;
        }    
        //====================================================================//
        //   Verify Field is To Be Tested
        if ( !is_null($Field) && $Field->notest ) {
            return False;
        }    
        
        return True;
    }

    public function PrepareForTesting($ObjectType,$Field)
    {
        //====================================================================//
        //   Verify Test is Required   
        if ( !$this->VerifyTestIsAllowed($ObjectType,$Field) ) {
            return False;
        }
        
        //====================================================================//
        // Prepare Fake Object Data
        //====================================================================//
        
        $this->Fields   =   $this->fakeFieldsList($ObjectType, [$Field->id], True);
        $FakeData       =   $this->fakeObjectData($this->Fields);
        
        return $FakeData;
        
    }
    
    
    public function VerifyResponse($ObjectType, $ObjectId, $Action, $ExpectedData)
    {
        //====================================================================//
        //   Verify Object Id Is Not Empty
        $this->assertNotEmpty( $ObjectId                    , "Returned New Object Id is Empty");

        //====================================================================//
        //   Verify Object Id Is in Right Format
        $this->assertTrue( 
                is_integer($ObjectId) || is_string($ObjectId), 
                "New Object Id is not an Integer or a Strings");
        
        //====================================================================//
        //   Verify Object Change Was Commited
        $this->assertIsLastCommited($Action,  $ObjectType , $ObjectId);
        
        //====================================================================//
        //   Read Object Data
        $CurrentData    =   Splash::Object($ObjectType)
                ->Get($ObjectId, $this->reduceFieldList($this->Fields));
        //====================================================================//
        //   Verify Object Data are Ok
        $this->compareDataBlocks($this->Fields, $ExpectedData, $CurrentData, $ObjectType);
        
        
    }
    
    public function VerifyDeleteResponse($ObjectType,$ObjectId,$Data)
    {
        //====================================================================//
        //   Verify Response
        $this->assertIsSplashBool( $Data                    , "Object Delete Response Must be a Bool");
        $this->assertNotEmpty( $Data                        , "Object Delete Response is Not True");
        
        //====================================================================//
        // Lock New Objects To Avoid Action Commit 
        Splash::Object($ObjectType)->Lock($ObjectId);
        
        //====================================================================//
        //   Verify Repeating Delete as Same Result
        $RepeatedResponse    =   Splash::Object($ObjectType)->Delete($ObjectId);
        $this->assertTrue( $RepeatedResponse                , "Object Repeated Delete, Must return True even if Object Already Deleted.");
        
        //====================================================================//
        //   Verify Object not Present anymore
        $Fields = $this->reduceFieldList(Splash::Object($ObjectType)->Fields(), True, False);
        $GetResponse    =   Splash::Object($ObjectType)->Get($ObjectId, $Fields );
        $this->assertFalse( $GetResponse                    , "Object Not Delete, I can still read it!!");
        
    }
    
    
}
