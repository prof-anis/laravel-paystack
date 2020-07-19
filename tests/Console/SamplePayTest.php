<?php
/**
 * This file is part of the Xeviant Paystack package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @version         1.0
 *
 * @author          Olatunbosun Egberinde
 * @license         MIT Licence
 * @copyright       (c) Olatunbosun Egberinde <bosunski@gmail.com>
 *
 * @link            https://github.com/bosunski/lpaystack
 */

namespace Xeviant\LaravelPaystack\Test\Console;

use Xeviant\LaravelPaystack\Test\AbstractTestCase;

class SamplePayTest  extends AbstractTestCase
{
    public function setUp()
    {
        parent::setup();


    }

    public function testWillThrowConnectionError()
    {
        /**given**/
        Http::fake([
            route('xeviant.paystack.webhook'),500
        ]);
        /**then**/
        $this->artisan('paystack:pay')
            ->expectsOutput('Internal server error')
            ->assertExitCode(0);
    }

    public function testWillReturnServerError()
    {
        /**given**/
        Http::fake([
            route('xeviant.paystack.webhook'),500
        ]);
        /**then**/
        $this->artisan('paystack:pay')
            ->expectsOutput('Internal server error')
            ->assertExitCode(0);
    }

    public function testWillReturnSuccess()
    {
        /**given**/
        Http::fake([
            route('xeviant.paystack.webhook'),200
        ]);
        /**then**/
        $this->artisan('paystack:pay')
            ->expectsOutput('payment successful')
            ->assertExitCode(0);

    }
}
