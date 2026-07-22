<?php

namespace Tests\Feature;

use App\Enums\ContactMessageStatus;
use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_stores_a_valid_message(): void
    {
        $response = $this->post('/lien-he', [
            'name' => 'Nguyễn Văn A',
            'phone' => '0901 234 567',
            'email' => 'customer@example.com',
            'company' => 'Công ty ABC',
            'subject' => 'Tư vấn sản phẩm',
            'message' => 'Tôi cần được tư vấn về sản phẩm phù hợp.',
            'website' => '',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('contact_success');

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Nguyễn Văn A',
            'phone' => '0901 234 567',
            'email' => 'customer@example.com',
            'source' => 'contact_page',
            'status' => ContactMessageStatus::New->value,
        ]);
    }

    public function test_contact_form_requires_a_contact_channel_and_valid_content(): void
    {
        $messageCount = ContactMessage::query()->count();
        $response = $this->from('/lien-he')->post('/lien-he', [
            'name' => '',
            'phone' => '',
            'email' => '',
            'message' => 'ngắn',
        ]);

        $response->assertRedirect('/lien-he');
        $response->assertSessionHasErrors(['name', 'phone', 'email', 'message']);
        $this->assertSame($messageCount, ContactMessage::query()->count());
    }

    public function test_contact_form_rejects_honeypot_submissions(): void
    {
        $messageCount = ContactMessage::query()->count();
        $response = $this->from('/lien-he')->post('/lien-he', [
            'name' => 'Spam Bot',
            'email' => 'bot@example.com',
            'message' => 'This looks like a valid message body.',
            'website' => 'https://spam.example.com',
        ]);

        $response->assertRedirect('/lien-he');
        $response->assertSessionHasErrors(['website']);
        $this->assertSame($messageCount, ContactMessage::query()->count());
    }
}
