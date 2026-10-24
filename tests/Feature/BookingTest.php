<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Master;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private Master $master;

    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        CarbonImmutable::setTestNow('2026-11-02 08:00:00');
        $this->service = Service::create(['name' => 'Стрижка', 'duration_minutes' => 60, 'price' => 2000]);
        $this->master = Master::create(['name' => 'Анна', 'specialty' => 'Парикмахер', 'work_from' => '10:00', 'work_to' => '13:00']);
        $this->master->services()->attach($this->service);
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'service_id' => $this->service->id,
            'master_id' => $this->master->id,
            'starts_at' => '2026-11-02 10:30',
            'client_name' => 'Ольга',
            'client_phone' => '8 (900) 123-45-67',
            'consent' => '1',
        ], $override);
    }

    public function test_slots_cover_working_day_with_service_length(): void
    {
        $this->getJson("/api/slots?master_id={$this->master->id}&service_id={$this->service->id}&date=2026-11-02")
            ->assertOk()
            ->assertExactJson(['10:00', '10:30', '11:00', '11:30', '12:00']);
    }

    public function test_booked_time_disappears_from_slots(): void
    {
        $this->postJson('/api/appointments', $this->payload())->assertCreated();

        $this->getJson("/api/slots?master_id={$this->master->id}&service_id={$this->service->id}&date=2026-11-02")
            ->assertOk()
            ->assertExactJson(['11:30', '12:00']);
    }

    public function test_cancelled_appointment_frees_the_slot(): void
    {
        $this->postJson('/api/appointments', $this->payload())->assertCreated();
        Appointment::first()->update(['status' => 'cancelled']);

        $this->getJson("/api/slots?master_id={$this->master->id}&service_id={$this->service->id}&date=2026-11-02")
            ->assertJsonFragment(['10:30']);
    }

    public function test_same_slot_cannot_be_booked_twice(): void
    {
        $this->postJson('/api/appointments', $this->payload())->assertCreated();
        $this->postJson('/api/appointments', $this->payload(['client_name' => 'Ирина']))
            ->assertStatus(422)->assertJsonValidationErrors('starts_at');

        $this->assertSame(1, Appointment::count());
    }

    public function test_overlapping_slot_is_rejected(): void
    {
        $this->postJson('/api/appointments', $this->payload())->assertCreated();
        $this->postJson('/api/appointments', $this->payload(['starts_at' => '2026-11-02 11:00']))
            ->assertStatus(422);
    }

    public function test_phone_is_normalized(): void
    {
        $this->postJson('/api/appointments', $this->payload())->assertCreated();
        $this->assertSame('+79001234567', Appointment::first()->client_phone);
        $this->assertSame('2026-11-02 11:30', Appointment::first()->ends_at->format('Y-m-d H:i'));
    }

    public function test_master_without_service_is_rejected(): void
    {
        $other = Service::create(['name' => 'Маникюр', 'duration_minutes' => 60, 'price' => 1500]);
        $this->postJson('/api/appointments', $this->payload(['service_id' => $other->id]))
            ->assertStatus(422)->assertJsonValidationErrors('master_id');
    }

    public function test_consent_and_honeypot_are_checked(): void
    {
        $this->postJson('/api/appointments', $this->payload(['consent' => null]))
            ->assertJsonValidationErrors('consent');
        $this->postJson('/api/appointments', $this->payload(['website' => 'spam']))
            ->assertJsonValidationErrors('website');
    }

    public function test_past_time_is_not_offered_today(): void
    {
        CarbonImmutable::setTestNow('2026-11-02 11:10:00');
        $this->getJson("/api/slots?master_id={$this->master->id}&service_id={$this->service->id}&date=2026-11-02")
            ->assertExactJson(['11:30', '12:00']);
    }

    public function test_booking_page_and_form_work(): void
    {
        $this->get('/')->assertOk()->assertSee('Запись к мастеру онлайн')->assertSee('Стрижка');
        $this->post('/book', $this->payload())->assertRedirect('/')->assertSessionHas('booked');
    }
}
