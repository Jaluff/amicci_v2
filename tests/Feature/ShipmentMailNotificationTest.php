<?php

use App\Models\Party;
use App\Models\Shipment;
use App\Models\Company;
use App\Models\Ubicacion;
use App\Models\EmailLog;
use App\Models\Setting;
use App\Jobs\SendShipmentEmailJob;
use App\Mail\ShipmentStatusNotificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('queues SendShipmentEmailJob when shipment is created and client has notifications enabled', function () {
    Queue::fake();

    $company = Company::create([
        'name' => 'Amicci Test',
        'prefix' => 'AM',
        'active' => true,
    ]);

    $origin = Ubicacion::create([
        'nombre' => 'Dto origen',
    ]);

    $destination = Ubicacion::create([
        'nombre' => 'Dto destino',
    ]);

    // Create a client with notifications enabled for "created" (Dto origen)
    $client = Party::create([
        'name' => 'Client Test',
        'email' => 'client@example.com',
        'email_notifications' => ['created'],
    ]);

    // Create shipment
    $shipment = Shipment::create([
        'company_id' => $company->id,
        'remitente_id' => $client->id,
        'origen_id' => $origin->id,
        'destino_id' => $destination->id,
        'numero' => '12345-1',
        'fecha' => now(),
        'ubicacion_actual' => 'Dto origen',
    ]);

    // Assert that the job was queued
    Queue::assertPushed(SendShipmentEmailJob::class, function ($job) use ($client) {
        return $job->emailLog->recipient === $client->email && $job->emailLog->status === 'pending';
    });

    // Assert that the EmailLog was created in pending state
    $this->assertDatabaseHas('email_logs', [
        'shipment_id' => $shipment->id,
        'recipient' => $client->email,
        'status' => 'pending',
    ]);
});

it('does not queue job or create log when client has notifications disabled for that stage', function () {
    Queue::fake();

    $company = Company::create([
        'name' => 'Amicci Test',
        'prefix' => 'AM',
        'active' => true,
    ]);

    $origin = Ubicacion::create([
        'nombre' => 'Dto origen',
    ]);

    $destination = Ubicacion::create([
        'nombre' => 'Dto destino',
    ]);

    // Create a client with notifications enabled but NOT for "created"
    $client = Party::create([
        'name' => 'Client Test',
        'email' => 'client@example.com',
        'email_notifications' => ['en_transito'],
    ]);

    // Create shipment
    Shipment::create([
        'company_id' => $company->id,
        'remitente_id' => $client->id,
        'origen_id' => $origin->id,
        'destino_id' => $destination->id,
        'numero' => '12345-2',
        'fecha' => now(),
        'ubicacion_actual' => 'Dto origen',
    ]);

    Queue::assertNotPushed(SendShipmentEmailJob::class);
    $this->assertDatabaseCount('email_logs', 0);
});

it('queues job when shipment status is updated and client has notifications enabled', function () {
    Queue::fake();

    $company = Company::create([
        'name' => 'Amicci Test',
        'prefix' => 'AM',
        'active' => true,
    ]);

    $origin = Ubicacion::create([
        'nombre' => 'Dto origen',
    ]);

    $destination = Ubicacion::create([
        'nombre' => 'Dto destino',
    ]);

    $client = Party::create([
        'name' => 'Client Test',
        'email' => 'client@example.com',
        'email_notifications' => ['en_transito'],
    ]);

    $shipment = Shipment::create([
        'company_id' => $company->id,
        'remitente_id' => $client->id,
        'origen_id' => $origin->id,
        'destino_id' => $destination->id,
        'numero' => '12345-3',
        'fecha' => now(),
        'ubicacion_actual' => 'Dto origen',
    ]);

    // Reset queue fake for update assertion
    Queue::fake();

    // Update status to En transito
    $shipment->update(['ubicacion_actual' => 'En transito']);

    Queue::assertPushed(SendShipmentEmailJob::class, function ($job) use ($client) {
        return $job->emailLog->recipient === $client->email && $job->emailLog->stage === 'En transito';
    });
});

it('does not send email or create log when global notifications are disabled', function () {
    Queue::fake();
    Setting::set('email_notifications_enabled', '0');

    $company = Company::create([
        'name' => 'Amicci Test',
        'prefix' => 'AM',
        'active' => true,
    ]);

    $origin = Ubicacion::create([
        'nombre' => 'Dto origen',
    ]);

    $destination = Ubicacion::create([
        'nombre' => 'Dto destino',
    ]);

    $client = Party::create([
        'name' => 'Client Test',
        'email' => 'client@example.com',
        'email_notifications' => ['created'],
    ]);

    Shipment::create([
        'company_id' => $company->id,
        'remitente_id' => $client->id,
        'origen_id' => $origin->id,
        'destino_id' => $destination->id,
        'numero' => '12345-4',
        'fecha' => now(),
        'ubicacion_actual' => 'Dto origen',
    ]);

    Queue::assertNotPushed(SendShipmentEmailJob::class);
    $this->assertDatabaseCount('email_logs', 0);
});

it('successfully sends email when job is executed and updates log to sent', function () {
    Mail::fake();

    $company = Company::create([
        'name' => 'Amicci Test',
        'prefix' => 'AM',
        'active' => true,
    ]);

    $origin = Ubicacion::create([
        'nombre' => 'Dto origen',
    ]);

    $destination = Ubicacion::create([
        'nombre' => 'Dto destino',
    ]);

    $client = Party::create([
        'name' => 'Client Test',
        'email' => 'client@example.com',
    ]);

    $shipment = Shipment::create([
        'company_id' => $company->id,
        'remitente_id' => $client->id,
        'origen_id' => $origin->id,
        'destino_id' => $destination->id,
        'numero' => '12345-5',
        'fecha' => now(),
        'ubicacion_actual' => 'Dto origen',
    ]);

    $emailLog = EmailLog::create([
        'shipment_id' => $shipment->id,
        'company_id' => $company->id,
        'party_id' => $client->id,
        'recipient' => $client->email,
        'stage' => 'Dto origen',
        'status' => 'pending',
    ]);

    // Execute job
    $job = new SendShipmentEmailJob($emailLog);
    $job->handle();

    // Assert mail was sent synchronously inside job
    Mail::assertSent(ShipmentStatusNotificationMail::class, function ($mail) use ($client) {
        return $mail->hasTo($client->email);
    });

    // Assert database log was updated to sent
    $this->assertDatabaseHas('email_logs', [
        'id' => $emailLog->id,
        'status' => 'sent',
        'error_message' => null,
    ]);
});

it('marks log as failed and records error message when email sending fails', function () {
    $mockMailer = Mockery::mock(\Illuminate\Mail\Mailer::class);
    Mail::swap($mockMailer);
    
    $mockMailer->shouldReceive('to')->andReturnSelf();
    $mockMailer->shouldReceive('sendNow')->andThrow(new Exception('SMTP connection failed'));

    $company = Company::create([
        'name' => 'Amicci Test',
        'prefix' => 'AM',
        'active' => true,
    ]);

    $origin = Ubicacion::create([
        'nombre' => 'Dto origen',
    ]);

    $destination = Ubicacion::create([
        'nombre' => 'Dto destino',
    ]);

    $client = Party::create([
        'name' => 'Client Test',
        'email' => 'client@example.com',
    ]);

    $shipment = Shipment::create([
        'company_id' => $company->id,
        'remitente_id' => $client->id,
        'origen_id' => $origin->id,
        'destino_id' => $destination->id,
        'numero' => '12345-6',
        'fecha' => now(),
        'ubicacion_actual' => 'Dto origen',
    ]);

    $emailLog = EmailLog::create([
        'shipment_id' => $shipment->id,
        'company_id' => $company->id,
        'party_id' => $client->id,
        'recipient' => $client->email,
        'stage' => 'Dto origen',
        'status' => 'pending',
    ]);

    try {
        $job = new SendShipmentEmailJob($emailLog);
        $job->handle();
        $this->fail('Expected exception was not thrown');
    } catch (Exception $e) {
        $this->assertEquals('SMTP connection failed', $e->getMessage());
    }

    // Assert database log was updated to failed with error message
    $this->assertDatabaseHas('email_logs', [
        'id' => $emailLog->id,
        'status' => 'failed',
        'error_message' => 'SMTP connection failed',
    ]);
});

it('sends to both sender and recipient if both have different emails loaded and notifications enabled', function () {
    Queue::fake();

    $company = Company::create([
        'name' => 'Amicci Test',
        'prefix' => 'AM',
        'active' => true,
    ]);

    $origin = Ubicacion::create([
        'nombre' => 'Dto origen',
    ]);

    $destination = Ubicacion::create([
        'nombre' => 'Dto destino',
    ]);

    // Sender wants created notification
    $sender = Party::create([
        'name' => 'Sender Client',
        'email' => 'sender@example.com',
        'email_notifications' => ['created'],
    ]);

    // Recipient also wants created notification
    $recipient = Party::create([
        'name' => 'Recipient Client',
        'email' => 'recipient@example.com',
        'email_notifications' => ['created'],
    ]);

    // Create shipment
    Shipment::create([
        'company_id' => $company->id,
        'remitente_id' => $sender->id,
        'destinatario_id' => $recipient->id,
        'origen_id' => $origin->id,
        'destino_id' => $destination->id,
        'numero' => '12345-7',
        'fecha' => now(),
        'ubicacion_actual' => 'Dto origen',
    ]);

    // Assert both jobs are pushed
    Queue::assertPushed(SendShipmentEmailJob::class, 2);

    $this->assertDatabaseHas('email_logs', [
        'recipient' => 'sender@example.com',
        'status' => 'pending',
    ]);

    $this->assertDatabaseHas('email_logs', [
        'recipient' => 'recipient@example.com',
        'status' => 'pending',
    ]);
});

it('does not send duplicate email if sender and recipient have the same email address', function () {
    Queue::fake();

    $company = Company::create([
        'name' => 'Amicci Test',
        'prefix' => 'AM',
        'active' => true,
    ]);

    $origin = Ubicacion::create([
        'nombre' => 'Dto origen',
    ]);

    $destination = Ubicacion::create([
        'nombre' => 'Dto destino',
    ]);

    // Both sender and recipient have the same email
    $sender = Party::create([
        'name' => 'Sender Client',
        'email' => 'same@example.com',
        'email_notifications' => ['created'],
    ]);

    $recipient = Party::create([
        'name' => 'Recipient Client',
        'email' => 'same@example.com',
        'email_notifications' => ['created'],
    ]);

    // Create shipment
    Shipment::create([
        'company_id' => $company->id,
        'remitente_id' => $sender->id,
        'destinatario_id' => $recipient->id,
        'origen_id' => $origin->id,
        'destino_id' => $destination->id,
        'numero' => '12345-8',
        'fecha' => now(),
        'ubicacion_actual' => 'Dto origen',
    ]);

    // Assert only one job was pushed to avoid duplicates
    Queue::assertPushed(SendShipmentEmailJob::class, 1);
});
