<?php
error_reporting(0);
use PHPUnit\Framework\TestCase;
require 'Scrapper.php';

class ScrapperTest extends TestCase {

    private $scapper;

    protected function setUp(): void {
        $this->scapper = new Scapper();
    }

    protected function tearDown(): void {
        $this->scapper = NULL;
    }

    public function testExecution() {
        $result = $this->scapper->execution();
        $json_array = json_decode($result, true);
        $this->assertEquals(6, count($json_array));
    }

}

?>