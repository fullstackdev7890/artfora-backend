<?php

namespace App\Tests;

use App\Mails\CommissionRequestMail;
use App\Mails\ContactUsMail;
use App\Services\SettingService;
use Biscolab\ReCaptcha\Facades\ReCaptcha;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

class ContactUsTest extends TestCase
{
    protected string $adminEmail;

    public function setUp(): void
    {
        parent::setUp();

        $this->adminEmail = app(SettingService::class)->get('contact_us.email');
    }

    public function testContactUsRequest()
    {
        ReCaptcha::shouldReceive('validate')->andReturn(true);

        $data = $this->getJsonFixture('contact_us_request.json');

        $response = $this->post('/contact-us', $data);

        $response->assertOk();
    }

    public function testContactUsRequestCheckEmail()
    {
        ReCaptcha::shouldReceive('validate')->andReturn(true);

        $data = $this->getJsonFixture('contact_us_request.json');

        $this->post('/contact-us', $data);

        $this->assertMailEquals(ContactUsMail::class, [
            'emails' => $this->adminEmail,
            'fixture' => 'contact_us_request.html',
            'subject' => 'New contact us request',
            'cc' => $data['email']
        ]);
    }

    public function testContactUsRequestRecaptchaFailed()
    {
        ReCaptcha::shouldReceive('validate')->andReturn(false);

        $data = $this->getJsonFixture('contact_us_request.json');

        $response = $this->post('/contact-us', $data);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testCommissionUsRequest()
    {
        ReCaptcha::shouldReceive('validate')->andReturn(true);

        $data = $this->getJsonFixture('commission_request.json');

        $response = $this->post('/users/1/commission', $data);

        $response->assertOk();
    }

    public function testCommissionUsRequestCheckEmail()
    {
        ReCaptcha::shouldReceive('validate')->andReturn(true);

        $data = $this->getJsonFixture('commission_request.json');

        $this->post('/users/1/commission', $data);

        $this->assertMailEquals(CommissionRequestMail::class, [
            'emails' => $this->adminEmail,
            'fixture' => 'commission_request.html',
            'subject' => 'New commission request',
            'cc' => $data['email']
        ]);
    }

    public function testCommissionUsRequestRecaptchaFailed()
    {
        ReCaptcha::shouldReceive('validate')->andReturn(false);

        $data = $this->getJsonFixture('commission_request.json');

        $response = $this->post('/users/1/commission', $data);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testCommissionUsProductNotFoundRequest()
    {
        ReCaptcha::shouldReceive('validate')->andReturn(true);

        $data = $this->getJsonFixture('commission_request.json');

        $response = $this->post('/users/0/commission', $data);

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
