<?php

declare(strict_types=1);

use App\Model\Document;
use App\Model\DocumentInterface;
use PHPUnit\Framework\TestCase;

final class DocumentTest extends TestCase
{

    private DocumentInterface $document;

    public function setUp(): void
    {
        $this->document = new Document();
    }

    public function testSetAndGetId(): void
    {
        $this->document->setId(1);
        $this->assertEquals(1, $this->document->getId());
    }

    public function testSetAndGetFirstName(): void
    {
        $this->document->setName('passport.jpg');
        $this->assertEquals('passport.jpg', $this->document->getName());
    }
}