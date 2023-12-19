<?php

namespace Tests\Unit;

use DOMDocument;
use PDO;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class DatabaseModelTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testXmlModelCollationProducesExpectedInconsistencies()
    {
        // Arrange: Set up the XML model with a specific collation
        $xmlString = '<schema collation="utf8_general_ci"></schema>';
        $domDocument = new DOMDocument();
        $domDocument->loadXML($xmlString);
        $xmlModel = new XmlSchemaModel($domDocument);

        // Arrange: Create a mock PDO object and a DatabaseModel instance
        $pdoMock = $this->createMock(PDO::class);
        $databaseModel = new MySqlSchemaModel($pdoMock);

        // Act: Check the integrity between the XML model and the Database model
        $inconsistencies = $databaseModel->checkIntegrity($xmlModel);

        // Assert: Verify that there is exactly one nonconformity
        $this->assertCount(1, $inconsistencies);

        // Assert: Check the details of the nonconformity
        $inconsistency = $inconsistencies->get(0);
        $instructions = $inconsistency->getInstructions();
        $this->assertCount(1, $instructions);

        // Assert: Verify that the collation change instruction is as expected
        $expectedQuery = 'ALTER DATABASE COLLATE utf8_general_ci';
        $this->assertEquals($expectedQuery, $instructions->get(0));
    }
}
