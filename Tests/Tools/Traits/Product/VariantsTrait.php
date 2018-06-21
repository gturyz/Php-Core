<?php

namespace Splash\Tests\Tools\Traits\Product;

use Splash\Client\Splash;

/**
 * @abstract    Splash Test Tools - Products Variants PhpUnit Specific Features
 *
 * @author SplashSync <contact@splashsync.com>
 */
trait VariantsTrait
{

    //==============================================================================
    //      SPLASH PRODUCT VARIANTS SPECIFIC FUNCTIONS
    //==============================================================================

    /**
     * @abstract    Generate Fields Variations Attributes
     */
    public function objectVariantsProvider()
    {
        $Result = array();

        $Name2   =  $this->getVariantName();
        for ($i=0; $i<3; $i++) {
            $Result[]   =   array_merge($Name2, $this->getVariantAttributes(['VariantA','VariantB']));
        }

        return $Result;
    }

    /**
     * @abstract    Generate Variations Base Name Fialds Data
     * @return      array
     */
    public function getVariantName()
    {
        //====================================================================//
        //   Verify Product Base Name
        $Field   =   self::findFieldByTag($this->Fields, "http://schema.org/Product", "alternateName");
        $this->assertNotEmpty($Field);
        //====================================================================//
        //   Generate Random Value
        return array(
            $Field->id  =>  self::fakeFieldData($Field->type),
        );
    }

    /**
     * @abstract    Generate Variations Attributes
     */
    public function getVariantAttributes($AttributesCodes)
    {
        //====================================================================//
        //   Load Required Fields
        $Code   =   self::findFieldByTag($this->Fields, "http://schema.org/Product", "VariantAttributeCode");
        $this->assertNotEmpty($Code);

        $Result = array();
        foreach ($AttributesCodes as $AttributesCode) {
            $Result[] = $this->getVariantCustomAttribute($AttributesCode);
        }
        return array(
            self::lists()->listName($Code->id) => $Result
        );
    }

    /**
     * @abstract    Generate Variations CustomAttribute
     */
    public function getVariantCustomAttribute($AttributesCode)
    {
        //====================================================================//
        //   Load Required Fields
        $Code   =   self::findFieldByTag($this->Fields, "http://schema.org/Product", "VariantAttributeCode");
        $this->assertNotEmpty($Code);
        $Name   =   self::findFieldByTag($this->Fields, "http://schema.org/Product", "VariantAttributeName");
        $this->assertNotEmpty($Name);
        $Value  =   self::findFieldByTag($this->Fields, "http://schema.org/Product", "VariantAttributeValue");
        $this->assertNotEmpty($Value);
        //====================================================================//
        //   Generate Random Attributes Set
        return array(
            self::lists()->fieldName($Code->id)     =>      strtolower($AttributesCode),
            self::lists()->fieldName($Name->id)     =>      self::fakeFieldData(
                $Name->type,
                null,
                array_merge_recursive($Name->options, ["minLength" =>   3, "maxLength" =>   5])
            ),
            self::lists()->fieldName($Value->id)     =>      self::fakeFieldData(
                $Value->type,
                null,
                array_merge_recursive($Value->options, ["minLength" =>   5, "maxLength" =>   10])
            ),
        );
    }

    /**
     * @abstract    Override Parent Function to Filter on Products Fields
     */
    public function objectFieldsProvider()
    {
        $Fields = array();
        foreach (parent::objectFieldsProvider() as $Field) {
            //====================================================================//
            // Filter Non Product Fields
            if ($Field[1] != "Product") {
                continue;
            }
//            //====================================================================//
//            // DEBUG => Focus on a Specific Fields
//            if ($Field[2]->id == "image@images") {
//                continue;
//            }
            $Fields[] = $Field;
        }
        return $Fields;
    }

    /**
     * @abstract    Override Parent Function to Add Variants Attributes
     */
    public function prepareForTesting($ObjectType, $Field)
    {
        //====================================================================//
        //   Verify Test is Required
        if (!$this->verifyTestIsAllowed($ObjectType, $Field)) {
            return false;
        }

        //====================================================================//
        // Prepare Fake Object Data
        //====================================================================//

        $this->Fields   =   $this->fakeFieldsList($ObjectType, [$Field->id], true);
        $FakeData       =   $this->fakeObjectData($this->Fields);

        //====================================================================//
        //   Add Attributes Fields To Fields List for Verifications
        $Fields   =   Splash::object($ObjectType)->fields();
        $this->Fields[]   =   self::findFieldByTag($Fields, "http://schema.org/Product", "VariantAttributeCode");
        $this->Fields[]   =   self::findFieldByTag($Fields, "http://schema.org/Product", "VariantAttributeName");
        $this->Fields[]   =   self::findFieldByTag($Fields, "http://schema.org/Product", "VariantAttributeValue");
        
//var_dump($this->CurrentVariation);

        return array_merge($FakeData, $this->CurrentVariation);
    }
}