<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Rfq;
use App\Models\Vendor;
use App\Models\VendorDocument;
use Illuminate\Http\Request;

/**
 * পাবলিক, লগইনবিহীন ভেন্ডর পোর্টাল — RFQ-এর public_token লিংক দিয়ে
 * এক্সেস হয় ("Copy Vendor Link" অ্যাকশন দেখুন)। যেকোনো ভেন্ডর এখানে নতুন
 * করে রেজিস্টার করে quotation জমা দিতে পারে, কিন্তু একই ইমেইল দিয়ে একই
 * RFQ-তে দ্বিতীয়বার জমা দেওয়া যাবে না, আর Closing Date পার হলে যাবে না।
 */
class VendorPortalController extends Controller
{
    public function show(string $token)
    {
        $rfq = Rfq::where('public_token', $token)
            ->with(['items.unit', 'procurementCase.purchaseRequisition'])
            ->firstOrFail();

        $closed = $rfq->closing_date && now()->startOfDay()->gt($rfq->closing_date);
        $pr = $rfq->procurementCase?->purchaseRequisition;

        return view('vendor-portal.show', [
            'rfq' => $rfq,
            'closed' => $closed,
            'pr' => $pr,
        ]);
    }

    public function store(Request $request, string $token)
    {
        $rfq = Rfq::where('public_token', $token)->with('items')->firstOrFail();

        if ($rfq->closing_date && now()->startOfDay()->gt($rfq->closing_date)) {
            return back()->withErrors(['form' => 'এই RFQ-এর Closing Date পার হয়ে গেছে — আর নতুন quotation জমা দেওয়া যাবে না।']);
        }

        $validated = $request->validate([
            'vendor_name' => 'required|string|max:255',
            'vendor_address' => 'nullable|string|max:500',
            'representative_name' => 'required|string|max:255',
            'representative_contact' => 'required|string|max:50',
            'email' => 'required|email',
            'trade_license_no' => 'nullable|string|max:100',
            'vat_reg_no' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:100',
            'general_experience' => 'nullable|string',
            'relevant_experience' => 'nullable|string',
            'terms_accepted' => 'required|accepted',
            'delivery_terms_accepted' => 'required|accepted',
            'items' => 'required|array|min:1',
            'items.*.rfq_item_id' => 'required|exists:rfq_items,id',
            'items.*.unit_price' => 'required|numeric|min:0',
            'trade_license_file' => 'required|file|max:10240',
            'tin_file' => 'required|file|max:10240',
            'bin_file' => 'required|file|max:10240',
            'experience_file' => 'nullable|file|max:10240',
        ]);

        $alreadySubmitted = Quotation::where('rfq_id', $rfq->id)
            ->whereHas('vendor', fn ($q) => $q->where('email', $validated['email']))
            ->exists();
        if ($alreadySubmitted) {
            return back()->withErrors(['email' => 'এই ইমেইল দিয়ে এই RFQ-তে ইতিমধ্যে একটা quotation জমা দেওয়া হয়েছে।'])->withInput();
        }

        $vendor = Vendor::firstOrNew(['email' => $validated['email']]);
        $vendor->fill([
            'name' => $validated['vendor_name'],
            'address' => $validated['vendor_address'] ?? $vendor->address,
            'contact_person' => $validated['representative_name'],
            'phone' => $validated['representative_contact'],
            'trade_license_no' => $validated['trade_license_no'] ?? $vendor->trade_license_no,
            'vat_reg_no' => $validated['vat_reg_no'] ?? $vendor->vat_reg_no,
            'tax_id' => $validated['tax_id'] ?? $vendor->tax_id,
        ]);
        $vendor->save();

        // আগে টোটাল হিসাব করে নেওয়া হচ্ছে, কারণ quotations.quoted_amount কলাম
        // NOT NULL (ডিফল্ট ভ্যালু নেই) — create()-এর সময়ই দিতে হবে, পরে update()
        // করলে প্রথম insert-এই এরর দেবে।
        $lineAmounts = [];
        $quotedTotal = 0;
        foreach ($validated['items'] as $line) {
            $rfqItem = $rfq->items->firstWhere('id', $line['rfq_item_id']);
            $amount = $rfqItem ? $rfqItem->quantity * $line['unit_price'] : $line['unit_price'];
            $lineAmounts[] = ['rfq_item_id' => $line['rfq_item_id'], 'unit_price' => $line['unit_price'], 'amount' => $amount];
            $quotedTotal += $amount;
        }

        $quotation = Quotation::create([
            'rfq_id' => $rfq->id,
            'vendor_id' => $vendor->id,
            'submitted_at' => now(),
            'status' => 'received',
            'representative_name' => $validated['representative_name'],
            'representative_contact' => $validated['representative_contact'],
            'quoted_amount' => $quotedTotal,
            'trade_license_submitted' => true,
            'tin_submitted' => true,
            'bin_submitted' => true,
            'terms_accepted' => true,
            'delivery_terms_accepted' => true,
            'general_experience' => $validated['general_experience'] ?? null,
            'relevant_experience' => $validated['relevant_experience'] ?? null,
            'submitted_via_portal' => true,
        ]);

        foreach ($lineAmounts as $line) {
            QuotationItem::create([
                'quotation_id' => $quotation->id,
                'rfq_item_id' => $line['rfq_item_id'],
                'unit_price' => $line['unit_price'],
                'amount' => $line['amount'],
            ]);
        }

        $documentFields = [
            'trade_license_file' => 'trade_license',
            'tin_file' => 'tax_certificate',
            'bin_file' => 'vat_certificate',
            'experience_file' => 'experience',
        ];
        foreach ($documentFields as $field => $docType) {
            if ($request->hasFile($field)) {
                $path = $request->file($field)->store('vendor-documents', 'public');
                VendorDocument::create([
                    'vendor_id' => $vendor->id,
                    'document_type' => $docType,
                    'file_path' => $path,
                ]);
            }
        }

        return view('vendor-portal.thank-you', ['rfq' => $rfq]);
    }
}