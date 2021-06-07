<?php

use PHPUnit\Framework\TestCase;
use KannelConfig\{Config,FilenameException };

class ConfigTest extends TestCase{

  public function testParseSingleOk(){
    $filename = __DIR__ .'/kannel1.conf';
    $out = Config::parseStatic($filename);

    $this->assertIsArray($out);
    $this->assertArrayHasKey('core',$out);
    $this->assertArrayHasKey('smsc',$out);
    $this->assertCount(3,$out['smsc']);
    $this->assertArrayHasKey('smpp-tlv',$out);
    $this->assertCount(2,$out['smpp-tlv']);
    $this->assertArrayHasKey('smsbox',$out);
    $this->assertArrayHasKey('sendsms-user',$out);
    $this->assertCount(2,$out['sendsms-user']);
    $this->assertArrayHasKey('sms-service',$out);
    $this->assertCount(2,$out['sms-service']);
    $this->assertEquals(13000,$out['core']['admin-port']);
    $this->assertEquals(13001,$out['core']['smsbox-port']);
  }

  public function testParseWithIncludeOk(){
    $filename = __DIR__ .'/kannel-include.conf';
    $out = Config::parseStatic($filename);

    $this->assertIsArray($out);
    $this->assertArrayHasKey('core',$out);
    $this->assertArrayHasKey('smsc',$out);
    $this->assertCount(4,$out['smsc']);
    $this->assertArrayHasKey('smpp-tlv',$out);
    $this->assertCount(2,$out['smpp-tlv']);
    $this->assertArrayHasKey('smsbox',$out);
    $this->assertArrayHasKey('sendsms-user',$out);
    $this->assertCount(2,$out['sendsms-user']);
    $this->assertArrayHasKey('sms-service',$out);
    $this->assertCount(3,$out['sms-service']);
    $this->assertEquals(13000,$out['core']['admin-port']);
    $this->assertEquals(13001,$out['core']['smsbox-port']);
  }

  public function testParseWithIncludeError(){
    $filename = __DIR__ .'/kannel-include-error.conf';
    $out = Config::parseStatic($filename);

    $this->assertIsArray($out);
    $this->assertArrayHasKey('core',$out);
    $this->assertArrayHasKey('smsc',$out);
    // the file included does not exists
    $this->assertCount(3,$out['smsc']);
    $this->assertArrayHasKey('smpp-tlv',$out);
    $this->assertCount(2,$out['smpp-tlv']);
    $this->assertArrayHasKey('smsbox',$out);
    $this->assertArrayHasKey('sendsms-user',$out);
    $this->assertCount(2,$out['sendsms-user']);
    $this->assertArrayHasKey('sms-service',$out);
    // file included does not exists
    $this->assertCount(2,$out['sms-service']);
    $this->assertEquals(13000,$out['core']['admin-port']);
    $this->assertEquals(13001,$out['core']['smsbox-port']);
  }

  public function testParseSingleFileNotExists(){
    $filename = __DIR__ .'/notexistent.conf';
    $this->expectException(FilenameException::class);
    $out = Config::parseStatic($filename);
  }

  public function testParseStringOk(){
    $filename = __DIR__ .'/kannel1.conf';
    $string = file_get_contents($filename) ;
    $out = Config::parseStringStatic($string);

    $this->assertIsArray($out);
    $this->assertArrayHasKey('core',$out);
    $this->assertArrayHasKey('smsc',$out);
    $this->assertCount(3,$out['smsc']);
    $this->assertArrayHasKey('smpp-tlv',$out);
    $this->assertCount(2,$out['smpp-tlv']);
    $this->assertArrayHasKey('smsbox',$out);
    $this->assertArrayHasKey('sendsms-user',$out);
    $this->assertCount(2,$out['sendsms-user']);
    $this->assertArrayHasKey('sms-service',$out);
    $this->assertCount(2,$out['sms-service']);
    $this->assertEquals(13000,$out['core']['admin-port']);
    $this->assertEquals(13001,$out['core']['smsbox-port']);
  }

  public function testParseFileSingleOkInstance(){
    $filename = __DIR__ .'/kannel1.conf';
    $out = (new Config)->parse($filename);

    $this->assertIsArray($out);
    $this->assertArrayHasKey('core',$out);
  }

  public function testParseStringSingleOkInstance(){
    $filename = __DIR__ .'/kannel1.conf';
    $string = file_get_contents($filename) ;
    $out = (new Config)->parseString($string);

    $this->assertIsArray($out);
    $this->assertArrayHasKey('core',$out);
  }
}
