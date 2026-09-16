<?php
namespace App\Mail;
use App\Models\EcommerceOrder;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
class OrderConfirmationMail extends Mailable { public function __construct(public EcommerceOrder $order) {} public function envelope(): Envelope { return new Envelope(subject: "Order {$this->order->order_number} confirmed"); } public function content(): Content { return new Content(view: 'emails.orders.confirmation'); } }
