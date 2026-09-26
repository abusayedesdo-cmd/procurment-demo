<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Step 7 "Select Terms & Conditions": replaces the fixed, hardcoded T&C
 * list baked into RfqDocumentBuilder with an editable master list the
 * officer picks from when creating an RFQ. Seeded with the 9 static
 * lines that were hardcoded before (the 2 per-RFQ dynamic lines — opening
 * date/time and delivery location — stay auto-generated in the document
 * builder), so nothing changes for existing RFQs until someone edits the
 * list.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfq_terms_conditions', function (Blueprint $table) {
            $table->id();
            $table->text('text');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('rfq_terms_condition_rfq', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained('rfqs')->cascadeOnDelete();
            $table->foreignId('rfq_terms_condition_id')->constrained('rfq_terms_conditions')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['rfq_id', 'rfq_terms_condition_id'], 'rfq_terms_condition_unique');
        });

        // NOTE: the original hardcoded list had two more lines — the
        // quotation-opening date/time and the delivery location — but
        // those are per-RFQ FACTS (computed from the RFQ's own closing
        // date and the PR's delivery location), not general terms an
        // officer would edit here. RfqDocumentBuilder keeps generating
        // those two automatically and prints them ahead of this editable
        // list, so nothing is lost.
        $now = now();
        $lines = [
            'Legal Document PDF Copy must be Submitted with Quotation: (Trade License, VAT Registration, TIN Certificate, PSR)',
            'Relevant Experience Certificate PDF Copy must be Submitted with Quotation.',
            'General Experience Certificate PDF Copy must be Submitted with Quotation.',
            'RFQ Receiving PDF Copy Need to Attach with the Quotation.',
            'As per govt. rules and regulation vat & tax will be deducted at the time of payment.',
            'The given price of the product must be valid for at least 15 days, and within this time frame the supplier is bound to supply products at the given price.',
            'Mode of payment: Payment will be made through Account Payee cheque/Pay order/RTGS/BEFTN or DD in favour of the supplying vendor after successful delivery of goods.',
            'ESDO reserves the authority to cancel — partially or fully — any quotation with or without explanation.',
            'ESDO never allows any harassment to women and children, and never allows child labour. Any institution or organization associated with such practices is strongly discouraged from participating in the bid.',
        ];

        foreach ($lines as $i => $text) {
            \Illuminate\Support\Facades\DB::table('rfq_terms_conditions')->insert([
                'text' => $text,
                'sort_order' => $i,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rfq_terms_condition_rfq');
        Schema::dropIfExists('rfq_terms_conditions');
    }
};
