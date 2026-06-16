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

// uses(RefreshDatabase::class);


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
        'email_notifications' => ['entregado'],
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

    // Update status to Entregado
    $shipment->update(['ubicacion_actual' => 'Entregado']);

    Queue::assertPushed(SendShipmentEmailJob::class, function ($job) use ($client) {
        return $job->emailLog->recipient === $client->email && $job->emailLog->stage === 'Entregado';
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

it('groups notifications for clients when multiple shipments are processed in a batch', function () {
    Queue::fake();

    $company = Company::create(['name' => 'Test', 'prefix' => 'T', 'active' => true]);
    $origin = Ubicacion::create(['nombre' => 'Dto origen']);
    $destination = Ubicacion::create(['nombre' => 'Dto destino']);

    $client = Party::create([
        'name' => 'Grouped Client',
        'email' => 'group@example.com',
        'email_notifications' => ['en_transito'],
    ]);

    $shipment1 = Shipment::create([
        'company_id' => $company->id,
        'remitente_id' => $client->id,
        'origen_id' => $origin->id,
        'destino_id' => $destination->id,
        'numero' => 'G-1',
        'fecha' => now(),
        'ubicacion_actual' => 'Dto origen',
    ]);

    $shipment2 = Shipment::create([
        'company_id' => $company->id,
        'remitente_id' => $client->id,
        'origen_id' => $origin->id,
        'destino_id' => $destination->id,
        'numero' => 'G-2',
        'fecha' => now(),
        'ubicacion_actual' => 'Dto origen',
    ]);

    $shipments = collect([$shipment1, $shipment2]);

    // Send grouped notifications
    app(\App\Services\GroupedNotificationService::class)->sendGroupedNotifications($shipments, 'en_transito', 'En transito');

    Queue::assertPushed(\App\Jobs\SendGroupedShipmentsEmailJob::class, 1);
    Queue::assertNotPushed(SendShipmentEmailJob::class);

    $this->assertDatabaseHas('email_logs', [
        'shipment_id' => $shipment1->id,
        'recipient' => 'group@example.com',
        'stage' => 'En transito',
        'status' => 'pending',
    ]);

    $this->assertDatabaseHas('email_logs', [
        'shipment_id' => $shipment2->id,
        'recipient' => 'group@example.com',
        'stage' => 'En transito',
        'status' => 'pending',
    ]);
});

it('sends grouped email successfully and updates log statuses', function () {
    Mail::fake();

    $company = Company::create(['name' => 'Test', 'prefix' => 'T', 'active' => true]);
    $origin = Ubicacion::create(['nombre' => 'Dto origen']);
    $destination = Ubicacion::create(['nombre' => 'Dto destino']);

    $client = Party::create([
        'name' => 'Grouped Client',
        'email' => 'group@example.com',
    ]);

    $shipment1 = Shipment::create([
        'company_id' => $company->id,
        'remitente_id' => $client->id,
        'origen_id' => $origin->id,
        'destino_id' => $destination->id,
        'numero' => 'G-3',
        'fecha' => now(),
        'ubicacion_actual' => 'Dto origen',
    ]);

    $emailLog1 = EmailLog::create([
        'shipment_id' => $shipment1->id,
        'company_id' => $company->id,
        'party_id' => $client->id,
        'recipient' => $client->email,
        'stage' => 'En transito',
        'status' => 'pending',
    ]);

    $emailLog2 = EmailLog::create([
        'shipment_id' => $shipment1->id,
        'company_id' => $company->id,
        'party_id' => $client->id,
        'recipient' => $client->email,
        'stage' => 'En transito',
        'status' => 'pending',
    ]);

    $job = new \App\Jobs\SendGroupedShipmentsEmailJob([$emailLog1->id, $emailLog2->id]);
    $job->handle();

    Mail::assertSent(\App\Mail\GroupedShipmentsStatusNotificationMail::class, function ($mail) use ($client) {
        return $mail->hasTo($client->email) && $mail->shipments->count() === 1 && $mail->recipient === $client->email;
    });

    $this->assertDatabaseHas('email_logs', [
        'id' => $emailLog1->id,
        'status' => 'sent',
    ]);

    $this->assertDatabaseHas('email_logs', [
        'id' => $emailLog2->id,
        'status' => 'sent',
    ]);
});

it('sends one grouped email to sender and one grouped email to recipient for shared and individual shipments', function () {
    Queue::fake();

    $company = Company::create(['name' => 'Test', 'prefix' => 'T', 'active' => true]);
    $origin = Ubicacion::create(['nombre' => 'Dto origen']);
    $destination = Ubicacion::create(['nombre' => 'Dto destino']);

    $clientR = Party::create([
        'name' => 'Client Remitente',
        'email' => 'remitente@example.com',
        'email_notifications' => ['en_transito'],
    ]);

    $clientD = Party::create([
        'name' => 'Client Destinatario',
        'email' => 'destinatario@example.com',
        'email_notifications' => ['en_transito'],
    ]);

    $clientX = Party::create([
        'name' => 'Client X',
        'email' => 'clientX@example.com',
        'email_notifications' => [],
    ]);

    // G1: Shared (Sender = R, Recipient = D)
    $shipment1 = Shipment::create([
        'company_id' => $company->id,
        'remitente_id' => $clientR->id,
        'destinatario_id' => $clientD->id,
        'origen_id' => $origin->id,
        'destino_id' => $destination->id,
        'numero' => 'G-SHARED-1',
        'fecha' => now(),
        'ubicacion_actual' => 'Dto origen',
    ]);

    // G2: Only for D
    $shipment2 = Shipment::create([
        'company_id' => $company->id,
        'remitente_id' => $clientX->id,
        'destinatario_id' => $clientD->id,
        'origen_id' => $origin->id,
        'destino_id' => $destination->id,
        'numero' => 'G-D-2',
        'fecha' => now(),
        'ubicacion_actual' => 'Dto origen',
    ]);

    // G3: Only for D
    $shipment3 = Shipment::create([
        'company_id' => $company->id,
        'remitente_id' => $clientX->id,
        'destinatario_id' => $clientD->id,
        'origen_id' => $origin->id,
        'destino_id' => $destination->id,
        'numero' => 'G-D-3',
        'fecha' => now(),
        'ubicacion_actual' => 'Dto origen',
    ]);

    $shipments = collect([$shipment1, $shipment2, $shipment3]);

    // Run grouped notification service
    app(\App\Services\GroupedNotificationService::class)->sendGroupedNotifications($shipments, 'en_transito', 'En transito');

    // Assert that exactly 2 jobs were pushed
    Queue::assertPushed(\App\Jobs\SendGroupedShipmentsEmailJob::class, 2);

    // Assert job for remitente is pushed with exactly 1 log (for G-SHARED-1)
    Queue::assertPushed(\App\Jobs\SendGroupedShipmentsEmailJob::class, function ($job) {
        $logs = \App\Models\EmailLog::whereIn('id', $job->emailLogIds)->get();
        if ($logs->first()->recipient !== 'remitente@example.com') {
            return false;
        }
        return $logs->count() === 1 && $logs->first()->shipment->numero === 'G-SHARED-1';
    });

    // Assert job for destinatario is pushed with 3 logs (for G-SHARED-1, G-D-2, G-D-3)
    Queue::assertPushed(\App\Jobs\SendGroupedShipmentsEmailJob::class, function ($job) {
        $logs = \App\Models\EmailLog::whereIn('id', $job->emailLogIds)->get();
        if ($logs->first()->recipient !== 'destinatario@example.com') {
            return false;
        }
        $shipmentNums = $logs->map(fn($l) => $l->shipment->numero)->toArray();
        return $logs->count() === 3 
            && in_array('G-SHARED-1', $shipmentNums)
            && in_array('G-D-2', $shipmentNums)
            && in_array('G-D-3', $shipmentNums);
    });
});

