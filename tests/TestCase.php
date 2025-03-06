<?php

namespace Tests;

abstract class TestCase extends \PHPUnit\Framework\TestCase {
  
  protected int $shm_cnt;
  protected int $sem_cnt;
  
  protected function setUp():void {
    parent::setUp();
    $this->shm_cnt = `ipcs -m | wc -l `;
    $this->sem_cnt = `ipcs -s | wc -l `;
  }
  
  protected function tearDown():void {
    parent::tearDown();
    $this->assertEquals($this->shm_cnt, `ipcs -m | wc -l `);
    $this->assertEquals($this->sem_cnt, `ipcs -s | wc -l `);
  }
}
