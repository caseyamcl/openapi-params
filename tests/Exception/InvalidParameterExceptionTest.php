<?php

/**
 *  Paramee Library
 *
 *  @license http://opensource.org/licenses/MIT
 *  @link https://github.com/caseyamcl/paramee
 *  @package caseyamcl/paramee
 *  @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE.md
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------
 */

namespace Paramee\Exception;

use PHPUnit\Framework\TestCase;
use Paramee\Model\ParameterError;
use Paramee\PreparationStep\CallbackStep;

class InvalidParameterExceptionTest extends TestCase
{
    public function testFromMessages()
    {
        $obj = InvalidValueException::fromMessages(
            new CallbackStep('trim', 'test'),
            'test',
            'boo',
            ['Bad Stuff']
        );

        $this->assertEquals('Bad Stuff', current($obj->getErrors())->getTitle());
    }

    public function testConstructor()
    {
        $obj = new InvalidValueException(
            new CallbackStep('trim', 'test'),
            'boo',
            [new ParameterError('Bad stuff happened', 'test')]
        );

        $this->assertInstanceOf(ParameterError::class, current($obj->getErrors()));
        $this->assertEquals(422, $obj->getCode());
    }

    public function testGetStepAndValue(): void
    {
        $err = new CallbackStep('trim', 'test');
        $obj = new InvalidValueException(
            $err,
            'boo',
            [new ParameterError('Bad stuff happened', 'test')]
        );

        $this->assertSame($err, $obj->getStep());
        $this->assertSame('boo', $obj->getValue());
    }

    public function testGetErrorsWithPointerPrefix()
    {
        $step = new CallbackStep('trim', 'test');
        $obj = InvalidValueException::fromMessage($step, 'test', 'boo', 'Bad stuff happened');
        $this->assertSame(
            '/data/attributes/test',
            current($obj->getErrorsWithPointerPrefix('data/attributes'))->getPointer()
        );
    }
}
