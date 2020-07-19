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

namespace Xeviant\LaravelPaystack\Console;

use Http\Client\Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;


class SamplePay extends Command
{
    /**
     * Possible response from the request
     *
     * @var string[]
     */
    protected  $possibleResponse = [
        500=>'Internal Server error'
    ];

    /**
     *  Default event payload from config file
     *
     * @var string[]
     */
    protected $defaultConfiguration;

    /**
     * Expected options to  be changed in event payload
     *
     * @var string[]
     */
    protected $expected = [
        'amount',
        'reference',
        'email',
        'meta'
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paystack:pay {--amount=} {--reference=} {--email=}  {--meta=*} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run a test payment to trigger webhook response';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $this->setDefautltConfiguration()
              ->send();
    }

    /**
     * loads the default configuration from the config file
     *
     * @return $this
     */
    protected function setDefautltConfiguration()
    {
        $this->defaultConfiguration = config('paystack.testEvents')['charge.success'];
        return $this;
    }

    /**
     * Get the webhook url
     *
     * @return string
     */
    protected  function getWebhookUrl()
    {

        return route('xeviant.paystack.webhook');
    }


    /**
     * Resolve the console options to the default configuration
     *
     * @return string[]
     */
    protected function processArguments()
    {
        foreach ($this->options() as $option=>$value) {

            if (in_array($option,$this->expected)){
                $this->setValue($option,$value);
            }
        }
        return $this->defaultConfiguration;
    }

    /**
     * Set the options into the default configuration property
     *
     * @param string $option
     * @param string $value
     * @return void
     */
    protected function setValue($option,$value)
    {
        switch ($option){
            case 'email':
                $this->defaultConfiguration['data']['customer']['email'] = $value;
                break;
            case 'meta':

                $this->defaultConfiguration['data']['metadata'] = array_merge($this->defaultConfiguration['data']['metadata'],$value);

                break;
            default :
                $this->defaultConfiguration['data'][$option] = $value;
        }
    }

    /**
     * Prepare the request data
     *
     * @return string[]
     */
    protected function getRequestData()
    {
        return  $this->processArguments();
    }

    /**
     * Make a post request to the webhook endpoint
     *
     * @return string
     */
    protected function send()
    {
        try {
            $response = Http::post($this->getWebhookUrl(), $this->getRequestData());

            if (!$response->successful()){

                return $this->errorResponse($response);
            }

            return $this->info("payment successful");
        }
        catch ( \Exception $e){
                if ($e instanceof \Illuminate\Http\Client\ConnectionException){
                    return $this->line('could not connect to '.$this->getWebhookUrl());
                }
         }


    }

    /**
     * Prepare the error response
     *
     * @param \Illuminate\Http\Client\Response
     * @return string
     */
    protected function errorResponse($response)
    {
        if (in_array($response->status(),array_keys($this->possibleResponse))){
           $this->error($this->possibleResponse[ $response->status()]);
        }
    }
}
