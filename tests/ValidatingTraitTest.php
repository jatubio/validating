<?php

use Illuminate\Support\Facades\Validator;

class ValidatingTraitTest extends \PHPUnit_Framework_TestCase
{
    public $trait;

    public function setUp()
    {
        $this->trait = Mockery::mock('DatabaseValidatingTraitStub');
        $this->trait->shouldDeferMissing();
    }

    public function tearDown()
    {
        Mockery::close();
    }

    public function testGetsDefaultRules()
    {
        $this->assertEquals(['foo' => 'bar'], $this->trait->getRules());
    }

    public function testSetsDefaultRules()
    {
        $this->trait->setRules(['bar' => 'foo']);

        $this->assertEquals(['bar' => 'foo'], $this->trait->getRules());
    }

    public function testGetsRulesWithName()
    {
        $this->assertEquals(['foo' => 'bar'], $this->trait->getRules('saving'));        
    }

    public function testSetsRulesWithName()
    {
        $this->trait->setRules(['abc' => 123], 'foo');

        $this->assertEquals(['abc' => 123], $this->trait->getRules('foo'));
    }


    public function testGetsMessagesArray()
    {
        $this->assertEquals(['bar' => 'baz'], $this->trait->getMessages());
    }

    public function testSetsMessagesArray()
    {
        $this->trait->setMessages(['bar' => 'foo']);

        $this->assertEquals(['bar' => 'foo'], $this->trait->getMessages());
    }


    public function testGetsErrorsWithoutValidation()
    {
        $this->assertNull($this->trait->getErrors());
    }

    public function testGetsErrorsWithValidation()
    {
        Validator::shouldReceive('make')
            ->once()
            ->andReturn(Mockery::mock([
                'passes'   => false,
                'messages' => 'foo'
            ]));

        $this->trait->validate();

        $this->assertEquals('foo', $this->trait->getErrors());
    }

    /**
     * @expectedException \Watson\Validating\ValidationException
     * @expectedExceptionMessage Model failed validation
     */
    public function testGetsErrorsWithException()
    {
        Validator::shouldReceive('make')
            ->once()
            ->andReturn(Mockery::mock([
                'passes'   => false,
                'messages' => Mockery::mock('Illuminate\Support\MessageBag')
            ]));

        $this->trait->setThrowValidationExceptions(true);

        $this->trait->validate();
    }


    public function testGetsThrowValidationExceptionsDefaultsToFalse()
    {
        $this->assertFalse($this->trait->getThrowValidationExceptions());
    }

    public function testSetsThrowValidationExceptionsToTrue()
    {
        $this->trait->setThrowValidationExceptions(true);

        $this->assertTrue($this->trait->getThrowValidationExceptions());
    }

    public function testSetsThrowValidationExceptionsToFalse()
    {
        $this->trait->setThrowValidationExceptions(false);

        $this->assertFalse($this->trait->getThrowValidationExceptions());
    }


    public function testGetsInjectUniqueIdentifierDefaultsToTrue()
    {
        $this->assertTrue($this->trait->getInjectUniqueIdentifier());
    }

    public function testSetsInjectUniqueIdentifierToTrue()
    {
        $this->trait->setInjectUniqueIdentifier(true);

        $this->assertTrue($this->trait->getInjectUniqueIdentifier());
    }

    public function testSetsInjectUniqueIdentifierToFalse()
    {
        $this->trait->setInjectUniqueIdentifier(false);

        $this->assertFalse($this->trait->getInjectUniqueIdentifier());
    }
    

    public function testValidateReturnsTrueOnValidModel()
    {
        Validator::shouldReceive('make')
            ->once()
            ->andReturn(Mockery::mock(['passes' => true]));

        $result = $this->trait->validate();

        $this->assertTrue($result);
    }

    public function testValidateReturnsFalseOnInvalidModel()
    {
        Validator::shouldReceive('make')
            ->once()
            ->andReturn(Mockery::mock(['passes' => false, 'messages' => 'foo']));

        $result = $this->trait->validate();

        $this->assertFalse($result);
    }

    public function testForceSaveSavesOnInvalidModel()
    {
        $this->trait->shouldReceive('save')
            ->once()
            ->andReturn(true);

        $this->trait->setRules(['title' => 'required']);

        $result = $this->trait->forceSave();

        $this->assertTrue($result);
    }
}

class DatabaseValidatingTraitStub
{
    use Watson\Validating\ValidatingTrait;

    public $exists = false;

    protected $addUniqueIdentifierToRules = true;

    protected $rules = [
        'foo' => 'bar'
    ];

    protected $messages = [
        'bar' => 'baz'
    ];

    public function getTable()
    {
        return 'foo';
    }

    public function getKeyName()
    {
        return 'id';
    }

    public function getKey()
    {
        return 1;
    }

    public function getAttributes()
    {
        return ['abc' => '123'];
    }
}