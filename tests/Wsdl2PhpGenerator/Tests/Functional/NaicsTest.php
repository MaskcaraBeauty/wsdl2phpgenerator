<?php
namespace Wsdl2PhpGenerator\Tests\Functional;

use SoapFault;

/**
 * Functional test where the response is a complex data structure containing
 * multiple nested objects and an array.
 *
 * The purpose here is to test that response contains the expected data
 * structure and that the actual data structure also matches the type
 * declarations in the Doc Blocks.
 */
class NaicsTest extends FunctionalTestCase
{

    protected function getWsdlPath()
    {
        // Source: http://www.webservicex.net/GenericNAICS.asmx?WSDL.
        return $this->fixtureDir . '/naics/GenericNAICS.wsdl';
    }

    /**
     * @vcr NaicsTest_testNaics
     */
    public function testNaics()
    {
        // Perform the request.
        $service = new \GenericNAICS();
        $request = new \GetNAICSByIndustry('Computer Systems');

        try {
            $response = $service->GetNAICSByIndustry($request);

            // Make sure we get a response where the actual types match expected values
            // and generated code comments.
            $this->assertTrue(get_class($response) == 'GetNAICSByIndustryResponse');
            $this->assertAttributeTypeConsistency('bool', 'GetNAICSByIndustryResult', $response);
            $this->assertAttributeInternalType('object', 'NAICSData', $response);
            $this->assertAttributeTypeConsistency('object', 'NAICSData', $response);
            $this->assertAttributeTypeConsistency('int', 'Records', $response->getNAICSData());
            $this->assertAttributeInternalType('object', 'NAICSData', $response->getNAICSData());
            // $response->NAICSData->NAICSData should a NAICS but is a stdClass.
            // TODO: Fix inconsistencies between actual type and DocBlock declaration.
            // $this->assertAttributeTypeConsistency('object', 'NAICSData', $response->NAICSData);
            $this->assertAttributeTypeConsistency('array', 'NAICS', $response->getNAICSData()->getNAICSData());
            $naicsArray = $response->getNAICSData()->getNAICSData()->NAICS;
            foreach ($naicsArray as $naics) {
                $this->assertAttributeTypeConsistency('string', 'NAICSCode', $naics);
                $this->assertAttributeTypeConsistency('string', 'Title', $naics);
                $this->assertAttributeTypeConsistency('string', 'IndustryDescription', $naics);
            }
        } catch (SoapFault $e) {
            // If an exception is thrown it should be due to a timeout. We cannot
            // guard against this when calling an external service.
            $this->assertContains('timeout', $e->getMessage());
        }

    }

}
